<?php
/******************************************************************************
 * Plugin Written communications
 *
 * Copyright    : (c) 2004 - 2015 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Author       : Thomas-RCV
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Version      : 2.0 
 *
 *****************************************************************************/

// create path to plugin
$plugin_folder_pos = strpos(__FILE__, 'adm_plugins') + 11;
$plugin_file_pos   = strpos(__FILE__, 'written_communications.php');
$plugin_folder     = substr(__FILE__, $plugin_folder_pos+1, $plugin_file_pos-$plugin_folder_pos-2);

if(!defined('PLUGIN_PATH'))
{
    define('PLUGIN_PATH', substr(__FILE__, 0, $plugin_folder_pos));
}

require_once('../../adm_program/system/common.php');
require_once('config.php');
require_once('../../adm_program/system/login_valid.php');

// Check config parameters and define if not exists
if(!isset($plg_wc_roleAccess))
{
    // set to "0" if missing and enable plugin for all members of the organization
    $plg_wc_roleAccess = 0;
}
if(!isset($plg_wc_roleArray))
{
    $plg_wc_roleArray = array('Webmaster');
}

// Check current user for valid access to plugin
if($gValidLogin)
{
    if($plg_wc_roleAccess == 0)
    {
        $plg_wc_access = true;
    }

    if(!$plg_wc_access)
    {
        if($plg_wc_roleAccess > 0 && count($plg_wc_roleArray) > 0)
        {
            foreach($plg_wc_roleArray as $role)
            {
                if(hasRole($role))
                {
                    $plg_wc_access = true;
                }
            }
        }
        else
        {
            throw new Exception('No roles defined in your configuration! Please check your parameters in the config.php!');
        }
    }
}
if(!$plg_wc_access)
{
    // Access for defined users only!
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    exit();
}

// Register plugin language files
$gL10n->addLanguagePath(PLUGIN_PATH. '/'.$plugin_folder.'/languages');
// Intitialize parameter
$getHeadline  = admFuncVariableIsValid($_GET, 'headline', 'string', array('defaultValue' => $gL10n->get('PLG_WC_CREATE_WRITTEN_COMMUNICATIONS')),false);
//* Check if own templates are available and set template path
if(is_dir('../../adm_my_files/download/MSWord_Templates'))
{
    $dir = '../../adm_my_files/download/MSWord_Templates';
}
else
{    
    $dir = 'templates';
}

// Define selectbox for membership conditon
$selectBoxEntries = array(0 => $gL10n->get('LST_ACTIVE_MEMBERS'), 1 => $gL10n->get('LST_FORMER_MEMBERS'), 2 => $gL10n->get('LST_ACTIVE_FORMER_MEMBERS'));
// read templates and create template array
$templateSelectionBox = array();
$folder = opendir($dir);
$i = 0;
while($file = readdir($folder))
{
    if ($file != "." && $file != "..") 
    { 
        // if docx template available assign to options
        if(preg_match('/.docx/', $file))
        {
            $templateSelectionBox[$file] = substr($file, 0, -5);       
        }
        $i++;
    }
}
closedir($folder);
// create html page object
$page = new HtmlPage($getHeadline);
// Javascript for select boxes
$page->addJavascript('$(document).ready(function () {
    $("#plg_wc_recipient_manual").hide();
    $("input[id=recipient_mode]:checkbox").change(function (){
        if ($(this).is(":checked"))
        {
            $("#plg_wc_recipient_role").hide();
            $("#plg_wc_recipient_manual").show("slow");
        }
        else
        {
            $("#plg_wc_recipient_role").show("slow");
            $("#plg_wc_recipient_manual").hide();
        }
    })
    $("#plg_wc_sender_manual").hide();
    $("input[id=sender_user]:checkbox").change(function () {
        if ($(this).is(":checked")) 
        {
            $("#plg_wc_sender_manual").hide("slow");
        }
        else
        {
            $("#plg_wc_sender_manual").show("slow");
        }
    })
    });', true);

// Navigation starts here
$gNavigation->addUrl(CURRENT_URL);
// add back link to module menu
$WC_Menu = $page->getMenu();
$WC_Menu->addItem('menu_item_back', $gNavigation->getPreviousUrl(), $gL10n->get('SYS_BACK'), 'back.png');
// show form
$form = new HtmlForm('plg_wc_form', 'written_communications_functions.php', $page);

$form->openGroupBox('plg_wc_template_choice', $gL10n->get('PLG_WC_CHOOSE_TEMPLATE'));
$form->addSelectBox('plg_wc_template', $gL10n->get('PLG_WC_CHOOSE_TEMPLATE'), $templateSelectionBox, array('property' => FIELD_REQUIRED));
$form->closeGroupBox();

$form->openGroupBox('plg_wc_selection', $gL10n->get('PLG_WC_SELECTION'));
$form->addCheckbox('sender_user', $gL10n->get('PLG_WC_ADDRESS_USER'), 1);
$form->addCheckbox('recipient_mode', $gL10n->get('PLG_WC_INDIVIDUAL_RECIPIENT'), 'recipient_mode');
$form->closeGroupBox();

$form->openGroupBox('plg_wc_sender_manual', $gL10n->get('SYS_SENDER'));
$form->addInput('plg_wc_sender_organization', $gL10n->get('SYS_ORGANIZATION'), '');
$form->addInput('plg_wc_sender_name', $gL10n->get('SYS_NAME'), '');
$form->addInput('plg_wc_sender_address', $gL10n->get('SYS_ADDRESS'), '');
$form->addInput('plg_wc_sender_postcode', $gL10n->get('SYS_POSTCODE'), '');
$form->addInput('plg_wc_sender_city', $gL10n->get('SYS_CITY'), '');
$form->closeGroupBox();

$form->openGroupBox('plg_wc_recipient_role', $gL10n->get('SYS_ROLE'));
$form->addStaticControl('plg_wc_recipient_role', $gL10n->get('SYS_ROLE'), FormElements::generateRoleSelectBox(0, 'role_select'));
$form->addStaticControl('plg_wc_recipient_role', $gL10n->get('SYS_MEMBER'), FormElements::generateDynamicSelectBox($selectBoxEntries, 0, 'show_members'));
$form->closeGroupBox();

$form->openGroupBox('plg_wc_recipient_manual', $gL10n->get('SYS_RECIPIENT'));
$form->addInput('plg_wc_recipient_organization', $gL10n->get('SYS_ORGANIZATION'), '');
$form->addInput('plg_wc_recipient_name', $gL10n->get('SYS_NAME'), '');
$form->addInput('plg_wc_recipient_address', $gL10n->get('SYS_ADDRESS'), '');
$form->addInput('plg_wc_recipient_postcode', $gL10n->get('SYS_POSTCODE'), '');
$form->addInput('plg_wc_recipient_city', $gL10n->get('SYS_CITY'), '');
$form->closeGroupBox();
// add editor for message
$form->openGroupBox('plg_wc_description', $gL10n->get('SYS_TEXT'));
$form->addInput('plg_wc_subject', $gL10n->get('MAI_SUBJECT'), '');
$form->addEditor('plugin_CKEditor', null, '', array('toolbar' => 'AdmidioPlugin_WC'));
$form->closeGroupBox();
 // add submit button
$form->addSubmitButton('btn_send', $gL10n->get('PLG_WC_DOWNLOAD_DOCUMENT'), array('icon' => THEME_PATH.'/icons/page_white_word.png'));
// add form to html page
$page->addHtml($form->show(false));
// show page
$page->show();
?>