<?php

/*

  File: language.inc (dutch)
  Author: Sander E. Robijns
  Date: 2001/03/04
  Updated by Fred Stuurman
  Date: 2002/10/24

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

$lang_logout 		= "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
//$lang_logout		= "(Afmelden)";
$lang_login		= "Aanmelden";
$lang_successlogout 	= "U heeft zich succesvol afgemeld";
$lang_loginfail		= "Er is een fout opgetreden bij het aanmelden";
$lang_logindisabled = "Gebruiker is GEBLOKKEERD, neem contact op met de System Adminstrator";
$lang_sessinuse		= "Deze sessie is in gebruik op een andere computer, toegang geweigerd<BR><A HREF='$default->owl_root_url/'>Aanmelden</A>";
$lang_invalidsess	= "Verkeerde sessie, meldt u zich aan op de hoofdpagina a.u.b.<BR></A>";
$lang_sesstimeout	= "Uw sessie is verlopen, meldt u zich opnieuw aan op de hoofdpagina a.u.b.<BR>";

$lang_addingfolder 	= "Een map toevoegen aan ";
$lang_addingfile 	= "Een bestand toevoegen aan ";
$lang_modifying		= "Wijzigen van ";
$lang_reallydelete	= "Werkelijk verwijderen";
$lang_currentdir    = "Huidige directory: ";
$lang_defpass           = "derf"; 
$lang_resetpass         = "Reset Wachtwoord"; 

$lang_title 		= "Titel";
$lang_userlang          = "Taal";
// begin Daphne change
$lang_held                      = "Held"; // for version control
$lang_ver                       = "Versie"; // for version control
// end Daphne change
$lang_file 		= "Bestand";
$lang_size 		= "Grootte";
$lang_postedby 		= "Eigenaar";
$lang_modified 		= "Gewijzigd";
$lang_actions 		= "Acties";
$lang_ownership		= "Eigendom";
$lang_newpolicy		= "Nieuw beleid";
$lang_policy 		= "Beleid";

$lang_folder		= "Map";

$lang_sendthisfile 	= "Bestandsnaam";
$lang_sendthisurl       = "Send this URL";
$lang_sendfile 		= "Verstuur bestand";
$lang_keywords 		= "Sleutelwoorden";
$lang_permissions 	= "Permissies";
$lang_newpermissions    = "Nieuwe Permissies";
$lang_description 	= "Beschrijving";
$lang_change		= "Wijzig";
$lang_reset		    = "Reset";
$lang_create		= "Maak";
// begin Daphne change
$lang_vertype           = "Revisie type";  // for modify.php when uploading
$lang_vermajor          = "Major Revisie";
$lang_verminor          = "Minor Revisie";
$lang_verdescription = "Beschrijving van de<br>wijzigingen";
// end Daphne change

$lang_everyoneread	= "Iedereen kan lezen/downloaden";
$lang_everyonewrite	= "Iedereen kan lezen/schrijven/downloaden";
$lang_everyonewrite_nod = "Iedereen kan lezen/schrijven/downloaden, NIET VERWIJDEREN";
$lang_groupread		= "Uw groep kan lezen/downloaden";
$lang_groupwrite	= "Uw groep kan lezen/schrijven/downloaden";
$lang_groupwrite_nod  = "Uw groep kan lezen/schrijven/downloaden, NIET VERWIJDEREN";
$lang_onlyyou		= "Alleen u kan lezen/schrijven/downloaden";
$lang_groupwrite_worldread = "Uw groep kan lezen/schrijven/downloaden & iedereen kan lezen";
$lang_groupwrite_worldread_nod = "Uw groep kan lezen/schrijven/downloaden (NIET VERWIJDEREN) & iedereen kan lezen";
$lang_ggroupwrite_worldread = "Uw groep kan bestanden uploaden and verwijderen deze folder & iedereen kan inhoud lezen";
$lang_ggroupwrite_worldread_nod = "Uw groep kan bestanden uploaden naar deze folder & iedereen kan inhoud lezen, NIET VERWIJDREN";

// BEGIN BUG FIX: #449394 add document policy bugs for admin
// Please translate and send back 
// bozz@bozzit.com

$lang_everyoneread_ad   = "Iedereen kan lezen/downloaden";
$lang_everyonewrite_ad  = "Iedereen kan lezen/schrijven/downloaden";
$lang_everyonewrite_ad_nod  = "Iedereen kan lezen/schrijven/downloaden, NIET VERWIJDEREN";
$lang_groupread_ad      = "Uw groep kan lezen/downloaden";
$lang_groupwrite_ad     = "Uw groep kan lezen/schrijven/downloaden";
$lang_groupwrite_ad_nod = "Uw groep kan lezen/schrijven/downloaden, NIET VERWIJDEREN";
$lang_onlyyou_ad        = "Alleen u kan lezen/schrijven/downloaden";
$lang_onlyyou_ad        = "Alleen u kan lezen/schrijven/downloaden";
$lang_groupwrite_worldread_ad = "De geselecteerde groep kan lezen/schrijven/downloaden & iedereen kan lezen";
$lang_groupwrite_worldread_ad_nod = "De geselecteerde groep kan lezen/schrijven/downloaden (NIET VERWIJDREN) & iedereen kan lezen";
$lang_ggroupwrite_worldread_ad = "De geselecteerde groep upload bestanden and verwijdern deze folder & iedereen kan inhoud lezen";
$lang_ggroupwrite_worldread_ad_nod = "De geselecteerde groep kan bestanden uploaden naar deze folder & iedereen kan inhoud lezen, NIET VERWIJDREN";


// END BUG FIX: #449394 add document policy bugs for admin

$lang_geveryoneread	= "Iedereen kan deze map lezen";
$lang_geveryonewrite	= "Iedereen kan bestanden toevoegen en deze map verwijderen";
$lang_geveryonewrite_nod= "Iedereen kan bestanden toevoegen";
$lang_ggroupread	= "Uw groep kan deze map lezen";
$lang_ggroupwrite	= "Uw groep kan bestanden toevoegen en deze map verwijderen";
$lang_ggroupwrite_nod   = "Uw groep kan bestanden toevoegen in deze map";
$lang_gonlyyou		= "Alleen u kan bestanden toevoegen en deze map verwijderen";

$lang_geveryoneread_ad	= "Iedereen kan deze map lezen";
$lang_geveryonewrite_ad	= "Iedereen kan bestanden toevoegen en deze map verwijderen";
$lang_geveryonewrite_ad_nod = "Iedereen kan bestanden toevoegen aan de map";
$lang_ggroupread_ad	= "Uw groep kan deze map lezen";
$lang_ggroupwrite_ad	= "Uw groep kan bestanden toevoegen en deze map verwijderen";
$lang_ggroupwrite_ad_nod   = "Uw groep kan bestanden toevoegen in deze map";
$lang_gonlyyou_ad	= "Alleen u kan bestanden toevoegen en deze map verwijderen";

$lang_nofolderaccess	= "U heeft geen toegang tot deze map";
$lang_nofileaccess	= "U heeft geen toegang tot dit bestand";
$lang_nofilemod		= "U heeft geen toegang om dit bestand te wijzigen";
$lang_nofiledelete	= "U heeft geen toegang om dit bestand te verwijderen";
$lang_noupload		= "U heeft geen toegang om bestanden toe te voegen aan deze map";
$lang_nosubfolder	= "U heeft geen toegang om hier mappen toe te voegen";
$lang_nofoldermod	= "U heeft geen toegang om deze map te wijzigen";
$lang_nofolderdelete 	= "U heeft geen toegang om deze map te verwijderen";
$lang_noemail           = "U mag deze file niet emailen";
$lang_fileexists	= "Het bestand dat u probeert toe te voegen bestaat al";
$lang_specifyname	= "Ga terug en vul een naam in a.u.b.";

$lang_users		= "Gebruikers";
$lang_groups		= "Groepen";
$lang_group		= "Groep";
$lang_username		= "Gebruikersnaam";
$lang_password		= "Wachtwoord";
$lang_newpassword   	= "Nieuw Wachtwoord";
$lang_oldpassword   	= "Oud Wachtwoord";
$lang_confpassword  	= "Nieuw Wachtwoord opnieuw";
$lang_email         	= "Email Adres";
$lang_emailing        = "Versturen van:";
$lang_email_to        = "Email naar";
$lang_email_reply_to  = "Antwoord naar";
$lang_email_cc          = "CC";
$lang_email_subject   = "Onderwerp";
$lang_email_body      = "Boodschap";
$lang_notification  	= "Ontvang Notificaties";
$lang_attach_file       = "Attach File";
$lang_change		= "Wijzig";
$lang_deleteuser	= "Verwijder gebruiker";
$lang_deletegroup	= "Verwijder groep";
$lang_saved		= "Wijzigingen zijn opgeslagen";
$lang_add		= "Toevoegen";
$lang_quota		= "Quotum";
$lang_disableuser       = "Disable Gebruiker";
$lang_noprefaccess	= "Disable Eigenschappen";
$lang_toomanysessions   = "Maximum sessies voor gebruiker bereikt, neem contact op met de System Administrator";
$lang_maxsessions     = "Maximum aantal Sessies";

$lang_groupmember   	= "Lid van groep";
$lang_preference        = "Eigenschappen";
$lang_moving            = "Verplaatsen van";
$lang_select            = "Geef een nieuwe folder:";
$lang_updating          = "Wijzigen";
$lang_cancel_button     = "Annuleer";

// Bozz Change Begin
$lang_email_alt         = "Email dit bestand";
$lang_del_folder_alt    = "Verwijder Folder (J/N)";
$lang_mod_folder_alt    = "Wijzig Folder Eigenschappen";
$lang_move_folder_alt   = "Verplaats Folder";

$lang_del_file_alt      = "Verwijder bestand (J/N)";
$lang_mod_file_alt      = "Wijzig Bestand Eigenschappen";
$lang_move_file_alt     = "Verplaats Bestand";
$lang_upd_file_alt      = "Update Bestand";
$lang_get_file_alt      = "Download Bestand";
$lang_view_file_alt     = "Bekijk Bestand";
$lang_newuser_alt       = "Create a new user";
$lang_newgroup_alt      = "Create a new group";
$lang_flush_sessions_alt        = "flush hanging sessions";
$lang_edthtml_alt       = "Edit Site HTML Preferences";
$lang_edprefs_alt       = "Edit Site Features and Settings";
$lang_backup_alt        = "Backup SQL Database";
$lang_backup_dis_alt    = "Backup Tool or gzip was not found";

// Bozz Change End
// begin Daphne change
$lang_log_file                  = "Bestand Log";
$lang_lock_file                 = "Check file in/out";
// end Daphne Change

$lang_notif_subject_new = "Nieuw OWL Intranet Bestand";
$lang_notif_subject_upd = "Een OWL Intranet Bestand is gewijzigd";
$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";
//$lang_notif_msg         = "Dit is een automatische email van OWL Intranet\n\nClick op de link hieronder om het nieuw/gewijzigde bestand te bekijken\n\n";
// Diego Bugfix
$lang_admin              = "Administratie scherm";
$lang_user               = "Gebruiker";
$lang_search             = "Zoeken";
$lang_search_results_for = "Zoek resultaten voor";
$lang_score              = "Score";
$lang_ownergroup         = "Groeps Eigenaar";
$lang_name               = "Naam";
//Diego Bugfix end
$lang_query_empty       = "Zoek string is leeg";

$lang_err_file_update   = "Bij het wijzigen van een bestand moet de Bestandsnaam hetzelfde blijven, probeer opnieuw met dezelfde bestandsnaam";
$lang_err_quota         = "U heeft niet voldoende ruimte quota om dit bestand te uploaden: ";
$lang_err_quota_needed  = " nodig ";
$lang_err_quota_avail   = " beschikbaar.";
$lang_err_quota_exceed  = "U bent boven uw gestelde quota";
$lang_err_upload        = "Er heeft zich een fout voorgedaan met het uploaden";
$lang_err_folder_exist  = "Deze map bestaat al";
$lang_err_folder_create = "Fout bij het creeren van de Map";
//Begin Daphne Change - backup folder and file creation errors
$lang_err_backup_folder_create = "Fout bij creereen vanBackup Map";
$lang_err_backup_file   = "Fout bij maken van backup van bestaand bestand";
$lang_err_file_lock     = "U kunt dit bestand niet benaderen, het wordt gebruikt door";
// end Daphne change
$lang_err_unauthorized  = "niet geauthoriseerd";
$lang_err_unauth_area   = "U bent niet geauthoriseerd om dit te gebruiken";
$lang_err_pass_wrong    = "Oude Wachtwoord is fout";
$lang_err_pass_missmatch= "Nieuw Wachtwoord en het herhaalde wachtwoord zijn niet hetzelfde";
$lang_err_user_exists   = "Usernaam betaat al";
$lang_err_general       = "Er is een fout opgetreden";
$lang_err_lang_1        = "Taal is gespecificeerd, maar";
$lang_err_lang_2        = " is niet gevonden";
$lang_err_lang_notfound = "Taal niet gevonden, specificeer owl.lib.php";
$lang_err_sess_write    = "Fout bij wegschrijven van session informatie in de database";
$lang_err_sess_notvalid = "Session id is niet geldig";
$lang_err_bad_version_1 = "Owl Intranet vereist een minimaal PHP Versie";
$lang_err_bad_version_2 = "U gebruikt Versie";
$lang_err_bad_version_3 = "Installeer een nieuwere versie van php en probeer opnieuw";

$lang_err_folderexists  = "Rename is geannuleerd<BR>Folder met dezelfde naam bestaat al.";
$lang_err_movecancel    = "Verplaatsen is geannuleerd<BR>";
$lang_err_email         = "Email kon niet worden verzonden";
$lang_err_fileexists    = "Move is geannuleerd<BR>Bestand met dezelfde naam bestaat al.";
$lang_err_nameempty     = "Folder naam kan niet leeg zijn";

//janu's change BEGIN 
$lang_engine                = "Owl Intranet Engine";
$lang_version               = "Versie";
$lang_username              = "Usernaam";
$lang_password              = "Wachtwoord";
$lang_owl_admin             = "Owl Administratie";
$lang_upg_MD5               = "Upgrade Gebruikers naar MD5 Wachtwoorden";
//janu's change END 

$lang_viewlog           = "Bekijk Log voor ";
$lang_viewing           = "Bekijken ";
$lang_flushed           = "Sessie tabel is geleegd, gebruiker kan zich weer aanmelden.<BR>";

// Edit HTML Properties Messages

$lang_ht_tbl_border_sz  = "Table Border Size:";
$lang_ht_tbl_hd_bg      = "Header Background:";
$lang_ht_tbl_cell_bg_cl = "Table Cell Background Colour:";
$lang_ht_tbl_cell_bg_al = "Table Cell Background Colour Alternate:";
$lang_ht_tbl_bg_cl      = "Table Background Colour:";
$lang_ht_expand_width   = "Expanded View Width:";
$lang_ht_collapse_width = "Collapse View Width:";
$lang_ht_bd_bg_cl       = "Body Background Color:";
$lang_ht_bd_txt_cl      = "Body Text Colour:";
$lang_ht_bd_lnk_cl      = "Body Link Colour:";
$lang_ht_bd_vlnk_cl     = "Body Visited Link Colour:";
$lang_ht_main_hd_bg_cl  = "Main Header Background Color:";

// Edit Prefs Properties Messages

$lang_owl_title_email   = "EMAIL SETTINGS";
$lang_owl_email_from    = "Email From Address:";
$lang_owl_email_fromname= "Email From Name:";
$lang_owl_email_replyto = "Email Reply To Address:";
$lang_owl_email_server  = "Email Delivery Server Address:";
$lang_owl_title_HD      = "LOOK AT HD FOR NEW FILES SETINGS";
$lang_owl_lookAtHD      = "Disable Look at hd:";
$lang_owl_def_file_security= "Default File Security:";
$lang_owl_def_file_group_owner= "Default File Group Owner:";
$lang_owl_def_file_owner= "Default File Owner:";
$lang_owl_def_file_title= "Default File Title:";
$lang_owl_def_file_meta = "Default File Meta Data:";
$lang_owl_def_fold_sec  = "Default Folder Security:";
$lang_owl_def_fold_group_owner  = "Default Folder Group Owner:";
$lang_owl_def_fold_owner        = "Default Folder Owner:";
$lang_owl_title_other   = "OTHER SETINGS";
$lang_owl_max_filesize  = "Maximum Upload File Size:";
$lang_owl_owl_timeout   = "Session Timeout in Seconds:";
$lang_owl_owl_expand    = "Default Browser View (Expand):";
$lang_owl_version_control= "Enable Version Control Feature:";
$lang_owl_restrict_view = "Enable Restricted View Feature:";
$lang_owl_title_tools   = "DB BACKUP/GZIP TOOLS";
$lang_owl_dbdump_path   = "DB Dump Tool Path:";
$lang_owl_gzip_path     = "gzip Path:";
$lang_owl_tar_path     = "tar Path:";
?>
