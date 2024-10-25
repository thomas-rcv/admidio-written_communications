<?php
/******************************************************************************
 * Plugin Written communications
 *
 * Homepage     : http://www.admidio.org
 * Author       : Thomas-RCV
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 * Version      : 3.4.1
 * Required     : Admidio Version 4.1
 *
 *****************************************************************************/

$rootPath = dirname(__DIR__, 2);
$pluginFolder = basename(__DIR__);

require_once($rootPath . '/adm_program/system/common.php');
require_once($rootPath . '/adm_program/system/login_valid.php');

// only include config file if it exists
if (is_file(__DIR__ . '/config.php'))
{
    require_once(__DIR__ . '/config.php');
}

// Check config parameters and define if not exists
if(!isset($plg_wc_roleAccess))
{
    // set to "0" if missing and enable plugin for all members of the organization
    $plg_wc_roleAccess = 0;
}
if(!isset($plg_wc_roleArray))
{
    $plg_wc_roleArray = array('Administrator');
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

// Initialize parameters
$getHeadline  = admFuncVariableIsValid($_GET, 'headline', 'string', array('defaultValue' => $gL10n->get('PLG_WC_CREATE_WRITTEN_COMMUNICATIONS')));
$getActiveRole  = admFuncVariableIsValid($_GET, 'active_role', 'bool', array('defaultValue' => true));
//* Check if own templates are available and set template path
if(is_dir(ADMIDIO_PATH . FOLDER_DATA . '/' . TableFolder::getRootFolderName() . '/MSWord_Templates'))
{
    $dir = ADMIDIO_PATH . FOLDER_DATA . '/' . TableFolder::getRootFolderName() . '/MSWord_Templates';
}
else
{
    $dir = 'templates';
}

// Define selectbox for membership condition
$selectBoxEntries = array(0 => $gL10n->get('SYS_ACTIVE_MEMBERS'), 1 => $gL10n->get('SYS_FORMER_MEMBERS'), 2 => $gL10n->get('SYS_ACTIVE_FORMER_MEMBERS'));
// read templates and create template array
$templateSelectionBox = array();
$folder = opendir($dir);
$i = 0;
while($file = readdir($folder))
{
    if ($file != "." && $file != "..")
    {
        // if docx template available assign to options
        if(preg_match('/\.docx/', $file))
        {
            $templateSelectionBox[$file] = substr($file, 0, -5);
        }
        $i++;
    }
}
closedir($folder);
// create html page object
$page = new HtmlPage('admidio-plugin-written-communication', $getHeadline);

// Javascript for select boxes
$page->addJavascript('
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
    })', true);

// Navigation starts here
$gNavigation->addUrl(CURRENT_URL);
// show form
$form = new HtmlForm('plg_wc_form', 'written_communications_functions.php', $page);

$form->openGroupBox('plg_wc_template_choice', $gL10n->get('PLG_WC_CHOOSE_TEMPLATE'));
$form->addSelectBox('plg_wc_template', $gL10n->get('PLG_WC_CHOOSE_TEMPLATE'), $templateSelectionBox, array('property' => HtmlForm::FIELD_REQUIRED));
$form->closeGroupBox();

$form->openGroupBox('plg_wc_selection', $gL10n->get('PLG_WC_SELECTION'));
$form->addCheckbox('sender_user', $gL10n->get('PLG_WC_ADDRESS_USER'), 1);
$form->addCheckbox('recipient_mode', $gL10n->get('PLG_WC_INDIVIDUAL_RECIPIENT'));
$form->closeGroupBox();

$form->openGroupBox('plg_wc_sender_manual', $gL10n->get('SYS_SENDER'));
$form->addInput('plg_wc_sender_organization', $gL10n->get('SYS_ORGANIZATION'), '');
$form->addInput('plg_wc_sender_name', $gL10n->get('SYS_NAME'), '');
$form->addInput('plg_wc_sender_street', $gL10n->get('SYS_STREET'), '');
$form->addInput('plg_wc_sender_postcode', $gL10n->get('SYS_POSTCODE'), '');
$form->addInput('plg_wc_sender_city', $gL10n->get('SYS_CITY'), '');
$form->closeGroupBox();

$form->openGroupBox('plg_wc_recipient_role', $gL10n->get('SYS_ROLE'));
// show all roles where the user has the right to see them
$sql = 'SELECT rol_id, rol_name, cat_name
          FROM '.TBL_ROLES.'
    INNER JOIN '.TBL_CATEGORIES.'
            ON cat_id = rol_cat_id
         WHERE rol_valid   = '.($getActiveRole === true ? 'true' : 'false').'
           AND (  cat_org_id  = '. $gCurrentOrganization->getValue('org_id'). '
               OR cat_org_id IS NULL )
      ORDER BY cat_sequence, rol_name';
$form->addSelectBoxFromSql('role_select', $gL10n->get('SYS_ROLE'), $gDb, $sql,
    array('property' => HtmlForm::FIELD_REQUIRED, 'defaultValue' => 0, 'multiselect' => false));
$showMembersSelection = array($gL10n->get('SYS_ACTIVE_MEMBERS'), $gL10n->get('SYS_FORMER_MEMBERS'), $gL10n->get('SYS_ACTIVE_FORMER_MEMBERS'));
$form->addSelectBox('show_members', $gL10n->get('SYS_CONFIGURATION'), $showMembersSelection,
    array('property' => HtmlForm::FIELD_REQUIRED, 'defaultValue' => $selectBoxEntries, 'showContextDependentFirstEntry' => false));
$form->closeGroupBox();

$form->openGroupBox('plg_wc_recipient_manual', $gL10n->get('SYS_RECIPIENT'));
$form->addInput('plg_wc_recipient_organization', $gL10n->get('SYS_ORGANIZATION'), '');
$form->addInput('plg_wc_recipient_name', $gL10n->get('SYS_NAME'), '');
$form->addInput('plg_wc_recipient_street', $gL10n->get('SYS_STREET'), '');
$form->addInput('plg_wc_recipient_postcode', $gL10n->get('SYS_POSTCODE'), '');
$form->addInput('plg_wc_recipient_city', $gL10n->get('SYS_CITY'), '');
$form->closeGroupBox();
// add editor for message
$form->openGroupBox('plg_wc_description', $gL10n->get('SYS_TEXT'));
$form->addInput('plg_wc_subject', $gL10n->get('SYS_SUBJECT'), '');
$form->addEditor('plugin_CKEditor', '', '', array('toolbar' => 'AdmidioPlugin_WC'));
$form->closeGroupBox();
 // add submit button
$form->addSubmitButton('btn_send', $gL10n->get('PLG_WC_DOWNLOAD_DOCUMENT'), array('icon' => 'fa-file-download'));
// add form to html page
$page->addHtml($form->show());
// show page
$page->show();
?>
