<?php
/******************************************************************************
 * Plugin Written communications
 *
 * Homepage     : http://www.admidio.org
 * Author       : Thomas-RCV
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 * Version      : 4.0.0
 * Required     : Admidio Version 5.0
 *
 *****************************************************************************/

use Admidio\Documents\Entity\Folder;
use Admidio\Infrastructure\Exception;
use Admidio\UI\Presenter\FormPresenter;
use Admidio\UI\Presenter\PagePresenter;

$rootPath = dirname(__DIR__, 2);
$pluginFolder = basename(__DIR__);

require_once($rootPath . '/system/common.php');
require_once(ADMIDIO_PATH . FOLDER_SYSTEM . '/login_valid.php');

try {
    // only include config file if it exists
    if (is_file(__DIR__ . '/config.php')) {
        require_once(__DIR__ . '/config.php');
    }

    // Check config parameters and define if not exists
    if (!isset($plg_wc_roleAccess)) {
        // set to "0" if missing and enable plugin for all members of the organization
        $plg_wc_roleAccess = 0;
    }
    if (!isset($plg_wc_roleArray)) {
        $plg_wc_roleArray = array('Administrator');
    }

    // Check current user for valid access to plugin
    if ($gValidLogin) {
        if ($plg_wc_roleAccess == 0) {
            $plg_wc_access = true;
        }

        if (!$plg_wc_access) {
            if ($plg_wc_roleAccess > 0 && count($plg_wc_roleArray) > 0) {
                foreach ($plg_wc_roleArray as $role) {
                    if (hasRole($role)) {
                        $plg_wc_access = true;
                    }
                }
            } else {
                throw new Exception('No roles defined in your configuration! Please check your parameters in the config.php!');
            }
        }
    }
    if (!$plg_wc_access) {
        // Access for defined users only!
        throw new Exception('SYS_NO_RIGHTS');
    }

    // Initialize parameters
    $getHeadline = admFuncVariableIsValid($_GET, 'headline', 'string', array('defaultValue' => $gL10n->get('PLG_WC_CREATE_WRITTEN_COMMUNICATIONS')));
    $getActiveRole = admFuncVariableIsValid($_GET, 'active_role', 'bool', array('defaultValue' => true));
    //* Check if own templates are available and set template path
    if (is_dir(ADMIDIO_PATH . FOLDER_DATA . '/' . Folder::getRootFolderName() . '/MSWord_Templates')) {
        $dir = ADMIDIO_PATH . FOLDER_DATA . '/' . Folder::getRootFolderName() . '/MSWord_Templates';
    } else {
        $dir = 'templates';
    }

    // Define selectbox for membership condition
    $selectBoxEntries = array(0 => $gL10n->get('SYS_ACTIVE_MEMBERS'), 1 => $gL10n->get('SYS_FORMER_MEMBERS'), 2 => $gL10n->get('SYS_ACTIVE_FORMER_MEMBERS'));
    // read templates and create template array
    $templateSelectionBox = array();
    $folder = opendir($dir);
    $i = 0;
    while ($file = readdir($folder)) {
        if ($file != "." && $file != "..") {
            // if docx template available assign to options
            if (preg_match('/\.docx/', $file)) {
                $templateSelectionBox[$file] = substr($file, 0, -5);
            }
            $i++;
        }
    }
    closedir($folder);
    // create html page object
    $page = PagePresenter::withHtmlIDAndHeadline('adm_plugin_written_communication', $getHeadline);
    $page->addTemplateFolder(ADMIDIO_PATH . FOLDER_PLUGINS . '/' . $pluginFolder . '/smarty');

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
    $gNavigation->addUrl(CURRENT_URL, $getHeadline);
    // show form
    $form = new FormPresenter('plg_wc_form', 'plugin.written-communication.edit.tpl', 'written_communications_functions.php', $page);

    $form->addSelectBox('plg_wc_template', $gL10n->get('PLG_WC_CHOOSE_TEMPLATE'), $templateSelectionBox, array('property' => HtmlForm::FIELD_REQUIRED));
    $form->addCheckbox('sender_user', $gL10n->get('PLG_WC_ADDRESS_USER'), 1);
    $form->addCheckbox('recipient_mode', $gL10n->get('PLG_WC_INDIVIDUAL_RECIPIENT'));

    $form->addInput('plg_wc_sender_organization', $gL10n->get('SYS_ORGANIZATION'), '');
    $form->addInput('plg_wc_sender_name', $gL10n->get('SYS_NAME'), '');
    $form->addInput('plg_wc_sender_street', $gL10n->get('SYS_STREET'), '');
    $form->addInput('plg_wc_sender_postcode', $gL10n->get('SYS_POSTCODE'), '');
    $form->addInput('plg_wc_sender_city', $gL10n->get('SYS_CITY'), '');

    // show all roles where the user has the right to see them
    $sql = 'SELECT rol_id, rol_name, cat_name
          FROM ' . TBL_ROLES . '
    INNER JOIN ' . TBL_CATEGORIES . '
            ON cat_id = rol_cat_id
         WHERE rol_valid   = ' . ($getActiveRole === true ? 'true' : 'false') . '
           AND rol_id IN (\'' . implode('\', \'', $gCurrentUser->getRolesViewMemberships()) . '\')
           AND (  cat_org_id  = ' . $gCurrentOrganization->getValue('org_id') . '
               OR cat_org_id IS NULL )
      ORDER BY cat_sequence, rol_name';
    $form->addSelectBoxFromSql('role_select', $gL10n->get('SYS_ROLE'), $gDb, $sql,
        array('property' => FormPresenter::FIELD_REQUIRED, 'defaultValue' => 0, 'multiselect' => false));
    $showMembersSelection = array($gL10n->get('SYS_ACTIVE_MEMBERS'), $gL10n->get('SYS_FORMER_MEMBERS'), $gL10n->get('SYS_ACTIVE_FORMER_MEMBERS'));
    $form->addSelectBox('show_members', $gL10n->get('SYS_CONFIGURATION'), $showMembersSelection,
        array('property' => FormPresenter::FIELD_REQUIRED, 'defaultValue' => $selectBoxEntries, 'showContextDependentFirstEntry' => false));

    $form->addInput('plg_wc_recipient_organization', $gL10n->get('SYS_ORGANIZATION'), '');
    $form->addInput('plg_wc_recipient_name', $gL10n->get('SYS_NAME'), '');
    $form->addInput('plg_wc_recipient_street', $gL10n->get('SYS_STREET'), '');
    $form->addInput('plg_wc_recipient_postcode', $gL10n->get('SYS_POSTCODE'), '');
    $form->addInput('plg_wc_recipient_city', $gL10n->get('SYS_CITY'), '');

    // add editor for message
    $form->addInput('plg_wc_subject', $gL10n->get('SYS_SUBJECT'), '');
    $form->addEditor('plugin_CKEditor', '', '', array('toolbar' => 'AdmidioNoMedia'));

    $form->addSubmitButton('adm_button_send', $gL10n->get('PLG_WC_DOWNLOAD_DOCUMENT'), array('icon' => 'fa-file-download'));

    $form->addToHtmlPage(false);
    $gCurrentSession->addFormObject($form);

    $page->show();
} catch (Exception $e) {
    $gMessage->show($e->getMessage());
}
