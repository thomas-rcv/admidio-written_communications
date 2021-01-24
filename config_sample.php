<?php
/******************************************************************************
 * Configuration file for the  Admidio-Plugin Written Cummuincations
 *
 * Rename this file to config.php if you want to change some of the preferences below. The plugin
 * will only read the parameters from config.php and not the example file.
 *
 * Copyright    : (c) 2004-2021 The Admidio Team
 * Author       : Thomas-RCV
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

// Parameters enables access with role control
// 0 =(Default)Access for all members of the organization
// 1 = Access only for members with allowed roles in array $plg_wc_roleArray

$plg_wc_roleAccess = 0;

// Array with defined roles for valid access to the plugin
// This array is used if the parmater $plg_wc_roleAccess is set active to option "1"
// Feel free to extend or define own roles in the array. As default only the role "Administrator" is allowed to use the plugin.
// Example for further roles: $plg_wc_roleArray = array('Administrator', 'Vorstand', '...', '...');

$plg_wc_roleArray = array('Administrator');

// This array can be used to define additional profile fields for the template engine.
// Customizable key/value pairs can be defined according to your database definitions of the profile fields.
// The "key" represents the placeholder you want to use in the template while the "value" of the "key" defines the name of the
// profile field from database you want to use in the template.
// To define additional profile fields as a value in the array, the value of "usf_name_intern" of the table user_fields must be used!
// Please check your database entries of the needed columns for correct implementation!
// Example to configure the email address and the mobile number as placeholders for the template:
// $plg_wc_arrCustomFields = array('UserEmail' => 'EMAIL', 'UserMobile' 'MOBILE');
// The placeholders ${UserEmail} and ${UserMobile} can now be used in the templates and are automatically replaced by the values of the profile fields.

$plg_wc_arrCustomProfileFields = array('UserEmail' => 'EMAIL');

// This array can be used to define free text as placeholder that is often used in your communications.
// The definiton of text is like you do it in the array $plg_wc_arrCustomProfileFields

$plg_wc_arrCustomText = array(  'Text_de' => 'Dieser Textknoten wurde in der config.php des Plugin im Array $plg_wc_arrCustomProfileFields" erstellt und ersetzt den Platzhalter "${Text_de}" im Template.',
                                'Text_en' => 'This text is defined in the config.php of the plugin. It replaces the placeholder "${Text_en}" defined in the array "$plg_wc_arrCustomProfileFields".');

?>
