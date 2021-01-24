/*************************************************************************************
 * Plugin Written communications documentation
 *
 * Copyright    : (c) 2004-2021 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Author       : Thomas-RCV
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 * Version      : 3.3.3
 *
 ************************************************************************************/

 This Plugi generates letters and written communicatins. Based on a *.docx template with placeholders,
 the final document will be created and the download starts automatically.
 The document can be used with specific software supporting open xml format (docx) .
 Software MS Office, Open Office and Libre Office.



 1. System Requirements
 ----------------------

 - Admidio Version 2.4.4 odr higher




 2. Installation
 ---------------

 - no installation nescessary.
 - Copy the extracted plugi folder in adm_plugins.
 - Add the Link to the plugin to the Admidio Menue in the used theme.



   Example:
   ---------
   Add the link to the plugin at the end of the Admidio menue in the script "my_body_bottom.php"



   $moduleMenu->addItem('written_communications', '/adm_plugins/written_communications/written_communications.php',
        									'Brief erstellen', '/icons/page_white_word.png');



   Access to the plugin can be assigned to roles. Please use the Admidio function hasRole()

   Example for only access for role Administrator and chairman of the organization:
   ----------------------------------------------------------------------------

   if(hasRole('Administrator', 'Chairman')
   {
        $moduleMenu->addItem('written_communications', '/adm_plugins/written_communications/written_communications.php',
        									'Brief erstellen', '/icons/page_white_word.png');
   }





 3. Updates
 ----------

 - All scripts and folders are replaced if an update is released.

 NOTICE: Since Admidio Version 3.2 the role name "Webmaster" has changed to "Administrator" and will be removed in future.
         To avoid script errors, please update your config. php of the plugin and change the role access to $plg_wc_roleArray = array('Administrator');, if you use it.




 4. Creating own templates:
 ---------------------------------

Creating an using own templates, the plugin is connected to the Admdido download module.
It is not recommended using own templates in the demo template directory of the plugin.
Caused by an update the folder may be replaced, too.

 - Create a folder "MSWord_Templates" in the download module. If exists the plugin switches the path to it automatically.
 - Own Templates can be provided in this directory using the upload sequence. Also FTP transmitting is possible.
   The templates must have the *.docx format!
   Old formats like *.doc and also template formats are not supported!
 - Used placeholders can be seen in the demo templates demo_de.docx und demo_en.docx int the folder "templates" of the plugin.








