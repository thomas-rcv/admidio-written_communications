<?php
/*************************************************************************************
 * Plugin Written communications functions
 *
 * Copyright    : (c) 2004 - 2014 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Author       : Thomas-RCV
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 * Version      : 1.1
 * Required     : Admidio Version 2.4.4 or higher
 * 
 * Parameters:
 * 
 * communication                    - html text from CKEditor 
 * plg_wc_recipient_organization      - Organization from recipient
 * plg_wc_recipient_name              - recipient name
 * plg_wc_recipient_address           - recipient address
 * plg_wc_recipient_postcode          - recipient postcode
 * plg_wc_recipient_city              - recipient City
 * plg_wc_sender_organization       - Organization from sender
 * plg_wc_sender_name               - Sender name
 * plg_wc_sender_address            - Sender address
 * plg_wc_sender_postcode           - Sender postcode
 * plg_wc_sender_city               - Sender city
 * plg_wc_subject                   - Subject row
 * plg_wc_template                  - Choosen template for the letter
 * role_select                      - Selected Role ID to read members
 * show_members                     - Numeric Parameters for Role Conditon:
 *                                    0 -> show active members of a role
 *                                    1 -> show fromer members of a role
 *                                    2 -> show active and former members of a role
 *
 ************************************************************************************/
 
require_once('../../adm_program/system/common.php');
require_once('classes/PHPWord.php');
require_once('classes/simplehtmldom/simple_html_dom.php');
require_once('classes/htmltodocx_converter/h2d_htmlconverter.php');
require_once('classes/adm_RoleMembers.php');
require_once('classes/styles.inc');
require_once('config.php');

// Function to pick up all html color attributes in current string and convert it in a class attributes with value of the color. 
// It also assigns the created class attributes in the replaced string based on the color style to the style array on the fly.
function assignHtmlColorAttributes($htmlString, $offset = 0)
{
    global $initial_state; 
    // search color information in string and set dynamically in style array
    $pattern = '<span style="color:#';
    for($i = $offset; $i<strlen($htmlString); $i++)
    {
         $pos = strpos($htmlString,$pattern,$i);
         if($pos !== FALSE)
         {
             $offset =  $pos;
             if($offset >= $i)
             {
                 $i = $offset;
                 preg_match('/<span style="color:(.*?);">/s', substr($htmlString, $pos), $output);
                 // Convert in a class attribute with value of the color.
                 $htmlString = preg_replace($output[0], 'span class="'.$output[1].'"', $htmlString);
                 // Define class attribute for color in state array
                 $initial_state['style_sheet']['classes'][$output[1]] = array('color' => strtoupper(substr($output[1],1)));
             }
         }
    } 
    return $htmlString;
}

// Function to create the html value of profile fields registered in the config file
function getProfileFieldValue($fieldNameIntern, $userId)
{
    global $gProfileFields; 
    global $gDb;
    
    $htmlString = '';
    
    // read user data
    $user = new User($gDb, $gProfileFields, $userId);
    
    if($gProfileFields->getProperty($fieldNameIntern, 'usf_type')!= 'RADIO_BUTTON' 
       || $gProfileFields->getProperty($fieldNameIntern, 'usf_type')!= 'DROPDOWN')
    {
        $htmlString = $user->getValue($fieldNameIntern);
    }
    else
    {
        // Get fieldlist
        $arrListValues = $gProfileFields->mProfileFields[$fieldNameIntern]->getValue('usf_value_list', 'text');

    	// Get selected user value for profile field
    	foreach($arrListValues as $key => $valueList)
    	{
    	    if($user->getValue($fieldNameIntern, 'database') == $key)
    	    {
    	       
    	       $htmlString = $valueList;       
    	    }
    	}
    }
    unset ($user);
	return $htmlString;
}

// Check config parameters and define if not exists
if(!isset($plg_wc_arrCustomProfileFields))
{
    $plg_wc_arrCustomProfileFields = array();
}
if(!isset($plg_wc_arrCustomText))
{
    $plg_wc_arrCustomText = array();
}
// Access for users only!
if($gCurrentUser->getValue('usr_id') == 0)
{
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    exit();
}

// Initialize and check the parameters
$arrRecipient = array();

$getCommunication           = admFuncVariableIsValid($_POST, 'plugin_CKEditor', 'html', 'no Text');
$getRecipientMode           = admFuncVariableIsValid($_POST, 'recipient_mode', 'string', 'Role', false);
$getRecipientOrganization   = admFuncVariableIsValid($_POST, 'plg_wc_recipient_organization', 'string', '',false);
$getRecipientName           = admFuncVariableIsValid($_POST, 'plg_wc_recipient_name', 'string', '',false);
$getRecipientAddress        = admFuncVariableIsValid($_POST, 'plg_wc_recipient_address', 'string', '',false);
$getRecipientPostcode       = admFuncVariableIsValid($_POST, 'plg_wc_recipient_postcode', 'numeric', '',false);
$getRecipientCity           = admFuncVariableIsValid($_POST, 'plg_wc_recipient_city', 'string', '',false);
$getRoleSelect              = admFuncVariableIsValid($_POST, 'role_select', 'numeric', '', false);
$getSenderOrganization      = admFuncVariableIsValid($_POST, 'plg_wc_sender_organization', 'string', '',false);
$getSenderName              = admFuncVariableIsValid($_POST, 'plg_wc_sender_name', 'string', '',false);
$getSenderAddress           = admFuncVariableIsValid($_POST, 'plg_wc_sender_address', 'string', '',false);
$getSenderPostcode          = admFuncVariableIsValid($_POST, 'plg_wc_sender_postcode', 'numeric', '',false);
$getSenderCity              = admFuncVariableIsValid($_POST, 'plg_wc_sender_city', 'string', '',false);
$getShowMembers             = admFuncVariableIsValid($_POST, 'show_members', 'numeric', '', false);
$getSubject                 = admFuncVariableIsValid($_POST, 'plg_wc_subject', 'string', '',false);
$getTemplate                = admFuncVariableIsValid($_POST, 'plg_wc_template', 'string', '',false);

// Define sender address
if(isset($_POST['sender_user']))
{
    // Get profile fields of current user
    $arrSender = array( 'Sender_Organization'   => $gCurrentOrganization->getValue('org_longname'), 
                        'Sender_Name'           => $gCurrentUser->getValue('FIRST_NAME').' '.$gCurrentUser->getValue('LAST_NAME'),
                        'Sender_Address'        => $gCurrentUser->getValue('ADDRESS'),
                        'Sender_Postcode'       => $gCurrentUser->getValue('POSTCODE'),
                        'Sender_City'           => $gCurrentUser->getValue('CITY'));
}
else
{
    $arrSender = array( 'Sender_Organization'   => $getSenderOrganization,
                        'Sender_Name'           => $getSenderName ,
                        'Sender_Address'        => $getSenderAddress ,
                        'Sender_Postcode'       => $getSenderPostcode  ,
                        'Sender_City'           => $getSenderCity);
}

// Define recipient addresses
if($getRoleSelect > 0 && $getRecipientMode == 'Role')
{
    $arrMembers = array();
    $members = new RoleMembers($gDb, $getRoleSelect, $getShowMembers);
    
    // add additional profile fields to the role object if defined
    if(count($plg_wc_arrCustomProfileFields) > 0)
    {
        $members->addProfileFields($plg_wc_arrCustomProfileFields);
    }
    
    $arrMembers = $members->getRoleMembers(); 
    // Members are assigned to selected role
    if(count($arrMembers) > 0)
    {
        foreach($arrMembers as $memberData)
        {
            $arrRecipient[] = array('recipient_Organization'    => '',
                                    'recipient_Name'            => $memberData['first_name'].' '.$memberData['last_name'] ,
                                    'recipient_Address'         => $memberData['address'] ,
                                    'recipient_Postcode'        => $memberData['zip_code']  ,
                                    'recipient_City'            => $memberData['city']);
        }
    }
    else
    {
        $gMessage->show("Diese Rolle hat keine Mitglieder zugeordnet");
        exit();
    }
}
else
{
    // no role select. use form values instead
    $arrRecipient[] = array('recipient_Organization'   => $getRecipientOrganization,
                            'recipient_Name'           => $getRecipientName ,
                            'recipient_Address'        => $getRecipientAddress ,
                            'recipient_Postcode'       => $getRecipientPostcode  ,
                            'recipient_City'           => $getRecipientCity);
}

// Check if own templates are available and set template path
if(is_dir('../../adm_my_files/download/MSWord_Templates'))
{
    $template = '../../adm_my_files/download/MSWord_Templates/'.$getTemplate;
}
else
{
    $template='templates/'.$getTemplate;
}

// Get current date
$objDate = new DateTimeExtended(DATE_NOW, 'Y-m-d', 'date');
$dateSystemFormat = $objDate->format($gPreferences['system_date']);
// Define file name
$filename = $gL10n->get('PLG_WC_FILENAME').'_'.$dateSystemFormat.'.docx';

// New Word Document:
$phpword_object = new PHPWord();
// Define new section
$section = $phpword_object->createSection();
// Create HTML Dom object:
$html_dom = new simple_html_dom();
// Provide some initial settings:
$initial_state = array(
  // Required parameters:
  'phpword_object' => &$phpword_object, // Must be passed by reference.
  // Optional parameters - showing the defaults if you don't set anything:
  'current_style' => array('size' => '11'), // The PHPWord style on the top element - may be inherited by descendent elements.
  'parents' => array(0 => 'body'), // Our parent is body.
  'list_depth' => 0, // This is the current depth of any current list.
  'context' => 'section', // Possible values - section, footer or header.
  'pseudo_list' => TRUE, // NOTE: Word lists not yet supported (TRUE is the only option at present).
  'pseudo_list_indicator_font_name' => 'Wingdings', // Bullet indicator font.
  'pseudo_list_indicator_font_size' => '7', // Bullet indicator size.
  'pseudo_list_indicator_character' => 'l ', // Gives a circle bullet point with wingdings.
  'table_allowed' => TRUE, // Note, if you are adding this html into a PHPWord table you should set this to FALSE: tables cannot be nested in PHPWord.
  'treat_div_as_paragraph' => TRUE, // If set to TRUE, each new div will trigger a new line in the Word document.
  'style_sheet' => readStyleArray()
  );
// Convert all html color attributes to class attributes in HTML string and assign to the initial_state array on the fly
$htmlString = assignHtmlColorAttributes($getCommunication); // Pass HTML string from CKEditor to do the settings
// Note, we needed to nest the html in a couple of dummy elements.
$html_dom->load('<html><body>' . $htmlString . '</body></html>');
// Create the dom array of elements which we are going to work on:
$html_dom_array = $html_dom->find('html',0)->children();
// Convert the HTML and put it into the PHPWord object
htmltodocx_insert_html($section, $html_dom_array[0]->nodes, $initial_state);
// Clear the HTML dom object:
$html_dom->clear();
unset($html_dom);
// read host tmp directory with permission to write files
$tmpDir = 'templates/tempdir/';
// Save as temporary description file
$h2d_file_uri = tempnam($tmpDir,'zip');
$objWriter = PHPWord_IOFactory::createWriter($phpword_object, 'Word2007');
$objWriter->save($h2d_file_uri);
// read file as temporary template 
$communication_object = new PHPWord();
$communication = $communication_object->loadDescription($h2d_file_uri);
// Get the raw XML with new function getDocument() in template.php
$communication_document = $communication->getDocument();
// Extract Section from XML document
preg_match('/<w:p>(.*?)<w:sectPr>/s', $communication_document, $output);
// cancel current textrun in main template adding required tags and nesting validated CKEditor string
$description_output = '</w:t></w:r>'.$output[1].'<w:p><w:r><w:t>';
// delete temporary template from HD
unlink($h2d_file_uri);
// Load main template
$document = $phpword_object->loadTemplate($template);
// Count number of recipients
$numrecipient = count($arrRecipient);
// Loop recipient array and create a new document for each recipient
$i = 0;
foreach($arrRecipient as $recipientDocument)
{
    if($i > 0 && $i < $numrecipient)
    {
        $document->AddPage();
    }
    // Replace parameters
    $document->setValue('Communication', $description_output);
    $document->setValue('LetterDate', $dateSystemFormat);
    $document->setValue('Recipient_Organization', $arrRecipient[$i]['recipient_Organization']);
    $document->setValue('Recipient_Name', $arrRecipient[$i]['recipient_Name']);
    $document->setValue('Recipient_Address', $arrRecipient[$i]['recipient_Address']);
    $document->setValue('Recipient_Postcode', $arrRecipient[$i]['recipient_Postcode']);
    $document->setValue('Recipient_City', $arrRecipient[$i]['recipient_City'] );
    $document->setValue('Sender_Organization', $arrSender['Sender_Organization']);
    $document->setValue('Sender_Name', $arrSender['Sender_Name']);
    $document->setValue('Sender_Address', $arrSender['Sender_Address']);
    $document->setValue('Sender_Postcode', $arrSender['Sender_Postcode']);
    $document->setValue('Sender_City', $arrSender['Sender_City']);
    $document->setValue('Subject', $getSubject);
    
    // fill additional profile fields registered in config file
    if(count($plg_wc_arrCustomProfileFields) > 0)
    {   
        foreach($plg_wc_arrCustomProfileFields as $placeholder => $profilefield)
        {
            $document->setValue($placeholder, getProfileFieldValue($profilefield, $arrMembers[$i]['usr_id']));
        }
    }
    // fill defined text fields
    if(count($plg_wc_arrCustomText) > 0)
    {
        foreach($plg_wc_arrCustomText as $placeholder => $text)
        {
            $document->setValue($placeholder, $text);
        }
    }
    // next recipient
    $i ++;
}

// Output document
$document->save($filename);
// Open download dialog and delete file from disc after download
header('Content-disposition: attachment; filename='.$filename.'');
header('Content-type: application/docx');
readfile($filename);
unlink($filename);
exit(); 
?>