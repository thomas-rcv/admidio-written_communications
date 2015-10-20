<?php
/**
 * PHPWord
 *
 * Copyright (c) 2011 PHPWord
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 010 PHPWord
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    Beta 0.6.3, 08.07.2011
 */


/**
 * PHPWord_DocumentProperties
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 2009 - 2011 PHPWord (http://www.codeplex.com/PHPWord)
 */
class PHPWord_Template {

    /**
     * ZipArchive
     *
     * @var ZipArchive
     */
    private $_objZip;

    /**
     * Temporary Filename
     *
     * @var string
     */
    private $_tempFileName;

    /**
     * Document XML
     *
     * @var string
     */
    private $_documentXML;

    private $_documentXMLSEQ;

    private $_documentXMLFINAL;

    private $_documentXMLREM;


    /**
     * Create a new Template Object
     *
     * @param string $strFilename
     */


    public function __construct($strFilename) {
        $path = dirname($strFilename);
        $this->_tempFileName = $path.DIRECTORY_SEPARATOR.time().'.docx';

        copy($strFilename, $this->_tempFileName); // Copy the source File to the temp File

        $this->_objZip = new ZipArchive();
        $this->_objZip->open($this->_tempFileName);

        //Get xml
        $this->_documentXML = $this->_objZip->getFromName('word/document.xml');
        
        preg_match_all('/\$\{.+?\}/', $this->_documentXML, $matches, PREG_SET_ORDER);
        foreach ($matches as $val) {
        $oldval = $val[0];
        $newval = preg_replace('/\<.+?\>/', '', $val[0]);
        $newval = str_replace(' ', '', $newval );
        $this->_documentXML = str_replace($oldval, $newval, $this->_documentXML);
        }
        //Get code between body tag
        $this->_documentXMLSEQ = $this->getTextBetweenTags($this->_documentXML, 'w:body');

        //Get code outside tag and place string to replace later
        $count = null;
        $this->_documentXMLREM = preg_replace('/<w:body>(.*?)<\/w:body>/i', '[xmlremain]', $this->_documentXML, -1, $count);

        //Set code as string to replace
        $this->_documentXML = $this->_documentXMLSEQ; 

    }


    public  function getTextBetweenTags($string, $tagname)
    {
        $pattern = "/<$tagname>(.*?)<\/$tagname>/";
        preg_match($pattern, $string, $matches);
        return $matches[1];
    }


    /**
     * Set a Template value
     *
     * @param mixed $search
     * @param mixed $replace
     */
    public function setValue($search, $replace) {
        if(substr($search, 0, 2) !== '${' && substr($search, -1) !== '}') {
            $search = '${'.$search.'}';
        }

        preg_match_all('/\$[^\$]+?}/', $this->_documentXML, $matches);
        for ($i=0;$i<count($matches[0]);$i++){
            $matches_new[$i] = preg_replace('/(<[^<]+?>)/','', $matches[0][$i]);
            $this->_documentXML = str_replace(
            $matches[0][$i],
            $matches_new[$i],
            $this->_documentXML);
        }
        
        //$newstring = implode ( $newarray );
        $this->_documentXML = str_replace($search, $replace, $this->_documentXML);
    }


    public function AddPage() {
      //Add converted xml
      $this->_documentXMLFINAL .=  $this->_documentXML;
      //Reset doc xml
      $this->_documentXML = $this->_documentXMLSEQ;
      //Add new page
      $this->_documentXMLFINAL .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    /**
     * Save Template
     *
     * @param string $strFilename
     */
    public function save($strFilename) {
        if(file_exists($strFilename)) {
            unlink($strFilename);
        }
        $this->_documentXMLFINAL .=  $this->_documentXML;
        $this->_documentXML = str_replace('[xmlremain]', '<w:body>'.$this->_documentXMLFINAL.'</w:body>', $this->_documentXMLREM);
        $this->_objZip->addFromString('word/document.xml', $this->_documentXML);

        // Close zip file
        if($this->_objZip->close() === false) {
            throw new Exception('Could not close zip file.');
        }

        rename($this->_tempFileName, $strFilename);
    }

}
?>