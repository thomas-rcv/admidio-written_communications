<?php
/******************************************************************************
 * Plugin Written communications
 *
 * Copyright    : (c) 2004 - 2014 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Author       : Thomas-RCV
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Version      : 1.1 
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
require_once('../../adm_program/system/login_valid.php');
require_once('../../adm_program/system/classes/form_elements.php');
require_once('../../adm_program/system/classes/ckeditor_special.php');
require_once('config.php');

$plg_wc_access = false;

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
            throw new Exception('No roles defined in your configuration! Please check your parameters in config.php!');
        }
    }
}
if(!$plg_wc_access)
{
    // Access for users only!
    $gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
    exit();
}
// Register plugin language files
$gL10n->addLanguagePath(PLUGIN_PATH. '/'.$plugin_folder.'/languages');

// Intitialize parameter
$getHeadline  = admFuncVariableIsValid($_GET, 'headline', 'string', $gL10n->get('PLG_WC_CREATE_WRITTEN_COMMUNICATIONS'),false);

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

// read templates and create selectbox
$templateSelectionBox = '<select name="plg_wc_template" size="" style="width: 150px;">';
$folder = opendir($dir);
while($file = readdir($folder))
{
    if ($file != "." && $file != "..") 
    { 
        // if docx template available assign to options
        if(preg_match('/.docx/', $file))
        {
            $templateSelectionBox .= '<option value="'.$file.'">'.substr($file, 0, -5).'</option>';       
        }
    }
}
closedir($folder);
$templateSelectionBox .= '</select>';

$gLayout['header'] =  '
<script type="text/javascript"><!--
$(document).ready(function () {
    $("#plg_wc_recipient_manual").hide();
    $("input[name=recipient_mode]:checkbox").change(function (){
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
    $("input[name=sender_user]:checkbox").change(function () {
        if ($(this).is(":checked")) 
        {
            $("#plg_wc_sender_manual").hide("slow");
        }
        else
        {
            $("#plg_wc_sender_manual").show("slow");
        }
    })
});
//--></script>';

// Navigation starts here                        
$gNavigation->addUrl(CURRENT_URL);
$ckEditor = new CKEditorSpecial();

require(SERVER_PATH. '/adm_program/system/overall_header.php');

// Start html output
echo '
<form action="written_communications_functions.php" method="post">
    <div class="formLayout" id="plg_wc_create">
        <div class="formHead">'. $getHeadline. '</div>
        <div class="formBody">
    		<div class="groupBox" id="plg_wc_settings">
    			<div class="groupBoxHeadline" id="plg_wc_settings_head">
    				<a class="iconShowHide" href="javascript:showHideBlock(\'plg_wc_settings_body\', \''.$gL10n->get('SYS_FADE_IN').'\', \''.$gL10n->get('SYS_HIDE').'\')"><img
    				id="plg_wc_settings_BodyImage" src="'. THEME_PATH. '/icons/triangle_open.gif" alt="'.$gL10n->get('SYS_HIDE').'" title="'.$gL10n->get('SYS_HIDE').'" /></a>'.$gL10n->get('PLG_WC_SELECTION').'
    			</div>
                    <div class="groupBoxBody" id="plg_wc_settings_body">
        				<ul class="formFieldList">
        					<li>
        						<dl>
        							<dt><label for="plg_wc_template">'.$gL10n->get('PLG_WC_CHOOSE_TEMPLATE').'</label></dt>
        							<dd>
        								'.$templateSelectionBox.'
        							</dd>
        						</dl>
        					</li>
        					<li>
        					    <dd>
        						<input type="checkbox" id="s1" name="sender_user" value="user" checked /> '.$gL10n->get('PLG_WC_ADDRESS_USER').'
                                </dd>
                                <dd>
        						<input type="checkbox" id="r2" name="recipient_mode" value="single" /> '.$gL10n->get('PLG_WC_INDIVIDUAL_RECIPIENT').'
        					    </dd>
                            </li>
                        </ul> 
                    </div>
            </div>
            <div class="groupBox" id="plg_wc_sender_manual">
    			<div class="groupBoxHeadline" id="plg_wc_sender_head">
    				<a class="iconShowHide" href="javascript:showHideBlock(\'plg_wc_sender_body\', \''.$gL10n->get('SYS_FADE_IN').'\', \''.$gL10n->get('SYS_HIDE').'\')"><img
    				id="plg_wc_sender_manual_BodyImage" src="'. THEME_PATH. '/icons/triangle_open.gif" alt="'.$gL10n->get('SYS_HIDE').'" title="'.$gL10n->get('SYS_HIDE').'" /></a> '.$gL10n->get('SYS_SENDER').'
    			</div>
                    <div class="groupBoxBody" id="plg_wc_sender_body">
        				<ul class="formFieldList">
                            <li>
        						<dl>
        							<dt><label for="plg_wc_sender_organization">'.$gL10n->get('SYS_ORGANIZATION').'</label></dt>
                                         <dd>
        						            <input type="text" name="plg_wc_sender_organization" value="" size="42" maxlength="50" />
                                        </dd>
                                    <dt><label for="plg_wc_sender_name">'.$gL10n->get('SYS_NAME').'</label></dt>
                                         <dd>
        						            <input type="text" name="plg_wc_sender_name" value="" size="42" maxlength="50" />
                                        </dd>
                                    <dt><label for="plg_wc_sender_address">'.$gL10n->get('SYS_ADDRESS').'</label></dt>
                                         <dd>
        						            <input type="text" name="plg_wc_sender_address" value="" size="42" maxlength="50" />
                                        </dd>
                                    <dt><label for="plg_wc_sender_city">'.$gL10n->get('SYS_CITY').'</label></dt>
                                         <dd>
                                            <span>
        						                <input type="text" name="plg_wc_sender_postcode" value="" size="5" maxlength="50" />
        						            </span>
                                            <span style="margin-left: 14px;">
        						                <input type="text" name="plg_wc_sender_city" value="" size="30" maxlength="50" />
        						            </span>
                                        </dd>
                                </dl>        
                           </li>
                        </ul>
                    </div>             
            </div>
            <div class="groupBox" id="plg_wc_recipient">
    			<div class="groupBoxHeadline" id="plg_wc_recipient_head">
    				<a class="iconShowHide" href="javascript:showHideBlock(\'plg_wc_recipient_body\', \''.$gL10n->get('SYS_FADE_IN').'\', \''.$gL10n->get('SYS_HIDE').'\')"><img
    				id="plg_wc_recipient_BodyImage" src="'. THEME_PATH. '/icons/triangle_open.gif" alt="'.$gL10n->get('SYS_HIDE').'" title="'.$gL10n->get('SYS_HIDE').'" /></a>'.$gL10n->get('SYS_RECIPIENT').'
    			</div>
                    <div class="groupBoxBody" id="plg_wc_recipient_body">
                        <ul class="formFieldList" id="plg_wc_recipient_role">
                            <li>
        						<dl>
        							<dt><label for="plg_wc_recipient_role">'.$gL10n->get('SYS_ROLE').'</label></dt>
        							    <dd>
        							        <span>
        						                '.FormElements::generateRoleSelectBox(0, 'role_select').'
        						            </span>
        						            <span style="margin-left: 15;">
        						                '.FormElements::generateDynamicSelectBox($selectBoxEntries, 0, 'show_members').'
        						            </span>
                                        </dd>
                                </dl>
                            </li>
                        </ul>
                        <ul class="formFieldList" id="plg_wc_recipient_manual">
                            <li>
        						<dl>
        							<dt><label for="plg_wc_recipient_organization">'.$gL10n->get('SYS_ORGANIZATION').'</label></dt>
                                         <dd>
        						            <input type="text" name="plg_wc_recipient_organization" value="" size="42" maxlength="50" />
                                        </dd>
                                    <dt><label for="plg_wc_recipient_name">'.$gL10n->get('SYS_NAME').'</label></dt>
                                         <dd>
        						            <input type="text" name="plg_wc_recipient_name" value="" size="42" maxlength="50" />
                                        </dd>
                                    <dt><label for="plg_wc_recipient_address">'.$gL10n->get('SYS_ADDRESS').'</label></dt>
                                         <dd>
        						            <input type="text" name="plg_wc_recipient_address" value="" size="42" maxlength="50" />
                                        </dd>
                                    <dt><label for="plg_wc_recipient_city">'.$gL10n->get('SYS_CITY').'</label></dt>
                                         <dd>
                                            <span>
        						                <input type="text" name="plg_wc_recipient_postcode" value="" size="5" maxlength="50" />
        						            </span>
                                            <span style="margin-left: 14px;">
        						                <input type="text" name="plg_wc_recipient_city" value="" size="30" maxlength="50" />
        						            </span>
                                        </dd>
                                </dl>
                           </li>
                        </ul>
                    </div>
            </div>
            <div class="groupBox" id="plg_wc_subject">
                <div class="groupBoxHeadline" id="plg_wc_subject_head">
    				<a class="iconShowHide" href="javascript:showHideBlock(\'plg_wc_subject_body\', \''.$gL10n->get('SYS_FADE_IN').'\', \''.$gL10n->get('SYS_HIDE').'\')"><img
    				id="plg_wc_subject_BodyImage" src="'. THEME_PATH. '/icons/triangle_open.gif" alt="'.$gL10n->get('SYS_HIDE').'" title="'.$gL10n->get('SYS_HIDE').'" /></a>'.$gL10n->get('MAI_SUBJECT').'
    		    </div>
                <div class="groupBoxBody" id="plg_wc_subject_body">
    				<ul class="formFieldList">
    					<li>
                            <input type="text" id="plg_wc_subject" name="plg_wc_subject" style="width: 99%;" maxlength="100" value="" />
                        </li>
    				</ul>
                </div>
            </div>
            <div class="groupBox" id="plg_wc_description">
    			<div class="groupBoxHeadline" id="plg_wc_description_head">
    				<a class="iconShowHide" href="javascript:showHideBlock(\'plg_wc_DescriptionBody\', \''.$gL10n->get('SYS_FADE_IN').'\', \''.$gL10n->get('SYS_HIDE').'\')"><img
    				id="plg_wc_description_BodyImage" src="'. THEME_PATH. '/icons/triangle_open.gif" alt="'.$gL10n->get('SYS_HIDE').'" title="'.$gL10n->get('SYS_HIDE').'" /></a>'.$gL10n->get('SYS_DESCRIPTION').'
    			</div>
                <div class="groupBoxBody" id="plg_wc_DescriptionBody">
                    <ul class="formFieldList">
                        <li>'.$ckEditor->createEditor('plugin_CKEditor', '', 'AdmidioPlugin_WC').'</li>
                    </ul>
                </div>
            </div> 
        </div>
        <div class="formSubmit">
            <button type="submit" name="submit"><img src="'.THEME_PATH.'/icons/page_white_word.png" alt="" title="" />'.$gL10n->get('PLG_WC_DOWNLOAD_DOCUMENT').'</button>
        </div>                                
    </div>
</form>';
require(SERVER_PATH. '/adm_program/system/overall_footer.php');
?>