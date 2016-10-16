<?php

// Example styles
// This is an example of how a "style sheet" should be structured
// to asign PHPWord styles to HTML elements, classes, and inline
// styles.
function readStyleArray() {
  
  // Set of default styles - 
  // to set initially whatever the element is:
  // NB - any defaults not set here will be provided by PHPWord.
  $styles['default'] = 
    array (
      'size' => 11,
    );
  
  // Element styles:
  // The keys of the elements array are valid HTML tags;
  // The arrays associated with each of these tags is a set
  // of PHPWord style definitions.
  $styles['elements'] = 
    array (
      'h1' => array (
        'bold' => TRUE,
        'size' => 20,
        ),
      'h2' => array (
        'bold' => TRUE,
        'size' => 15,
        'spaceAfter' => 150,
        ),
      'h3' => array (
        'size' => 12,
        'bold' => TRUE,
        'spaceAfter' => 100,
        ),
      'li' => array (
        ),
      'ol' => array (
        'spaceBefore' => 200,
        ),
      'ul' => array (
        'spaceAfter' => 150,
        ),
      'b' => array (
        'bold' => TRUE,
        ),
      'em' => array (
        'italic' => TRUE,
        ),
      'i' => array (
        'italic' => TRUE,
        ),
      'strong' => array (
        'bold' => TRUE,
        ),
      'b' => array (
        'bold' => TRUE,
        ),
      'sup' => array (
        'superScript' => TRUE,
        'size' => 6,
        ), // Superscript not working in PHPWord 
      'u' => array (
        'underline' => PHPWord_Style_Font::UNDERLINE_SINGLE,
        ),
      'a' => array (
        'color' => '0000FF',
        'underline' => PHPWord_Style_Font::UNDERLINE_SINGLE,
        ),
      'table' => array (
        // Note that applying a table style in PHPWord applies the relevant style to
        // ALL the cells in the table. So, for example, the borderSize applied here
        // applies to all the cells, and not just to the outer edges of the table:
        'borderColor' => '000000',  
        'borderSize' => 10,
        ),
      'th' => array (
        'borderColor' => '000000',
        'borderSize' => 10,
        ),
      'td' => array (
        'borderColor' => '000000',
        'borderSize' => 10,
        ),
      );
      
  // Classes:
  // The keys of the classes array are valid CSS classes;
  // The array associated with each of these classes is a set
  // of PHPWord style definitions.
  // Classes will be applied in the order that they appear here if
  // more than one class appears on an element.
  $styles['classes'] = 
    array (
      'underline' => array (
        'underline' => PHPWord_Style_Font::UNDERLINE_SINGLE,
        ),
       'purple' => array (
        'color' => '901391',
       ),
       'green' => array (
        'color' => '00A500',
       ),
      );
  
  // Inline style definitions, of the form:
  // array(css attribute-value - separated by a colon and a single space => array of
  // PHPWord attribute value pairs.    
  $styles['inline'] = 
    array(
      'text-decoration: underline' => array (
        'underline' => PHPWord_Style_Font::UNDERLINE_SINGLE,
      ),
      'vertical-align: left' => array (
        'align' => 'left',
      ),
      'vertical-align: middle' => array (
        'align' => 'center',
      ),
      'vertical-align: right' => array (
        'align' => 'right',
      ),
      'font-size: 8px' => array (
        'size' => 8,
      ),
      'font-size: 9px' => array (
        'size' => 9,
      ),
      'font-size: 10px' => array (
        'size' => 10,
      ),
      'font-size: 11px' => array (
        'size' => 11,
      ),
      'font-size: 12px' => array (
        'size' => 12,
      ),
      'font-size: 14px' => array (
        'size' => 14,
      ),
      'font-size: 16px' => array (
        'size' => 16,
      ),
      'font-size: 18px' => array (
        'size' => 18,
      ),
      'font-size: 20px' => array (
        'size' => 20,
      ),
      'font-size: 22px' => array (
        'size' => 22,
      ),
      'font-size: 24px' => array (
        'size' => 24,
      ),
      'font-size: 26px' => array (
        'size' => 26,
      ),
      'font-size: 28px' => array (
        'size' => 28,
      ),
      'font-size: 36px' => array (
        'size' => 36,
      ),
      'font-size: 48px' => array (
        'size' => 48,
      ),
      'font-size: 72px' => array (
        'size' => 72,
      ),
    );
    
  return $styles;
}