# Admidio Plugin Written Communications
This Plugin for Admidio allows you to create letters and form letters. Based on a *.docx template with placeholders (template) this plugin generates a complete * .docx document and start downloading the file you created. The resulting document can be processed with corresponding software on the PC. MS Office, OpenOffice and LibreOffice are supported. 

# Requirements
Admidio Version 2.4.4 or higher 

#Restrict access to roles
Access rights can be linked to roles. The limitation on roles can be configured in the config.php file of the plugin. To this end, the role of monitoring must match the parameter $plg_wc_roleAccess = 1; be activated. If this parameter is set (default: 0 for all members of the organization) then the access is limited to the roles that were defined in the config.php in the array $plg_wc_roleArray. Please use the Admidio function hasRole () to make the link only visible for defined roles. 

#Register profile fields in the plugin
Starting with version 1.1 custom profile fields can be additionally registered as desired, then they can be used automatically in a template as wildcard. If you need to provide additional profile fields they are needed to be registered in the config.php of the plugin in the array $plg_wc_arrCustomProfileFields. Please purpose defining the profile field, which is to be used, with the desired placeholder in the template, as a key / value pair. Note that the database fields must be written in capital letters! 

#Create your own templates and letterheads
To create your own templates the plugin is linked to the Download module of Admidio. Own letter templates should not be created in the example folder “Templates” of the Plugin, because this folder is possibly replaced with updates. In the download module you should create a folder “MSWord_Templates”. Such a directory once exists, this folder is to be choosen automatically and templates are loaded only from this directory. Now your own templates can be easily provided on the upload. The templates must have the format *.docx! 
The old *.doc format and presentation formats are not permitted! 
To be used, all placeholders are included in the demo templates demo_de.docx and demo_en.docx in the folder “templates” of the Plugin. The placeholders are all optional and can be removed at any time if a detail, eg, the date “${LetterDate}” is not required in the template. 
