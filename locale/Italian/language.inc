<?php

/*

  File: language.inc   Italian 
  Author: F. Malfatto <fmal@fmal.com>
  Date: 2001/05/14
	to steveb@bozzit.com
  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

/* LOCAL CHANGES for italian language
   $lang_current_dir  is used only in my italian version
      (I added it into browse.php (line 40) to describe the line
      showing the current directory)
*/
$lang_currentdir        = "Cartella attuale";
// END of local changes


//$lang_logout            = "<IMG SRC='$default->rootUrl/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
$lang_logout		= "(Logout)";
$lang_login             = "Login";
$lang_successlogout     = "Logout completato";
$lang_loginfail         = "Errore di login";
$lang_logindisabled     = "Account has been DISABLED, contact the System Adminstrator";
$lang_sessinuse         = "Sessione attiva su un altro computer, accesso negato";
$lang_invalidsess       = "Sessione non valida, collegarsi dalla pagina principale<BR>";
$lang_sesstimeout       = "Tempo scaduto, ricollegarsi ..<BR>";

$lang_addingfolder      = "Aggiungi cartella a ";
$lang_addingfile        = "Aggiungi file a ";
$lang_modifying         = "Modifica ";
$lang_reallydelete      = "Conferma cancellazione";

$lang_title             = "Titolo";
$lang_userlang          = "Language";
// begin Daphne change
$lang_held                      = "Held"; // for version control
$lang_ver                       = "Ver."; // for version control
// end Daphne change
$lang_file              = "File";
$lang_size              = "Dimensione";
$lang_postedby          = "Creato da";
$lang_modified          = "Modificato";
$lang_actions           = "Azioni";
$lang_ownership         = "Proprietario";
$lang_newpolicy         = "Nuovi permessi";
$lang_policy            = "Permessi";

$lang_sendthisfile      = "Manda questo file";
$lang_sendthisurl       = "Send this URL";
$lang_sendfile          = "Manda file";
$lang_keywords          = "Chiavi di ricerca";
$lang_permissions       = "Permessi";
$lang_newpermissions    = "Nuovi permessi";
$lang_description       = "Descrizione";
$lang_change            = "Cambia";
$lang_reset             = "Annulla";
$lang_create            = "Crea";
// begin Daphne change
$lang_vertype           = "Revision type";  // for modify.php when uploading
$lang_vermajor          = "Major Revision";
$lang_verminor          = "Minor Revision";
$lang_verdescription = "Description<br>of changes";
// end Daphne change

$lang_everyoneread      = "Tutti possono leggere/scaricare";
$lang_everyonewrite     = "Tutti possono leggere/scrivere/scaricare";
$lang_everyonewrite_nod = "Everyone can read/write/download, NO DELETE";
$lang_groupread         = "Il tuo gruppo puo' leggere/scaricare";
$lang_groupwrite        = "Il tuo gruppo puo' leggere/scrivere/scaricare";
$lang_groupwrite_nod    = "Your group can read/write/download, NO DELETE";
$lang_onlyyou           = "Personale (accesso solo al proprietario)";
$lang_groupwrite_worldread = "Your group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_nod = "Your group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread = "Your group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_nod = "Your group can upload files to this folder & everyone else can view contents, NO DELETE";

$lang_geveryoneread     = "Tutti possono leggere la cartella";
$lang_geveryonewrite    = "Tutti possono scrivere/cancellare files nella cartella";
$lang_geveryonewrite_nod= "Everyone can upload files to this folder";
$lang_ggroupread        = "Gli utenti del gruppo possono leggere";
$lang_ggroupwrite       = "Gli utenti del gruppo possono scrivere/cancellare";
$lang_ggroupwrite_nod   = "Your group can upload files to this folder";
$lang_gonlyyou          = "Accesso personale (solo il proprietario)";
$lang_groupwrite_worldread_ad = "The selected group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_ad_nod = "The selected group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread_ad = "Selected group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_ad_nod = "Selected group can upload files to this folder & everyone else can view contents, NO DELETE";


// BEGIN BUG FIX: #449394 add document policy bugs for admin
// Please translate and send back 
// bozz@bozzit.com

$lang_everyoneread_ad   = "Everyone can read/download";
$lang_everyonewrite_ad  = "Everyone can read/write/download";
$lang_everyonewrite_ad_nod      = "Everyone can read/write/download, NO DELETE";
$lang_groupread_ad      = "The selected group can read/download";
$lang_groupwrite_ad     = "The selected group can read/write/download";
$lang_groupwrite_ad_nod = "The selected group can read/write/download, NO DELETE";
$lang_onlyyou_ad        = "Only you can read/download/write";

// END BUG FIX: #449394 add document policy bugs for admin

$lang_geveryoneread_ad  = "Tutti possono leggere la cartella";
$lang_geveryonewrite_ad = "Tutti possono scrivere/cancellare files nella cartella";
$lang_geveryonewrite_ad_nod     = "Everyone can upload files to this folder";
$lang_ggroupread_ad     = "Gli utenti del gruppo possono leggere";
$lang_ggroupwrite_ad    = "Gli utenti del gruppo possono scrivere/cancellare";
$lang_ggroupwrite_ad_nod= "The selected group can upload files to this folder";
$lang_gonlyyou_ad       = "Accesso personale (solo il proprietario)";

$lang_nofolderaccess    = "Non hai permessi di accesso alla cartella";
$lang_nofileaccess      = "Non hai permessi di accesso al file";
$lang_nofilemod         = "Non hai permessi di modifica su questo file";
$lang_nofiledelete      = "Non hai permessi di cancellazione su questo file";
$lang_noupload          = "Non hai permessi di scrittura in questa cartella";
$lang_nosubfolder       = "Non hai permessi di scrittura in questa cartella";
$lang_nofoldermod       = "Non hai permessi di modifica su questa cartella";
$lang_nofolderdelete    = "Non hai permessi di cancellazione in questa cartella";
$lang_noemail           = "You do not have access to email this file";
$lang_fileexists        = "Il file che cerchi di caricare esiste gia'";
$lang_specifyname       = "Torna indietro e specifica un nome";

$lang_users             = "Utenti";
$lang_groups            = "Gruppi";
$lang_group             = "Gruppo";
$lang_username          = "Username";
$lang_password          = "Password";
$lang_newpassword       = "New Password";
$lang_oldpassword       = "Old Password";
$lang_confpassword      = "Confirm New Password";
$lang_email             = "Email Address";
$lang_emailing          = "Emailing";
$lang_email_to          = "Mail To";
$lang_email_reply_to    = "Reply To";
$lang_email_cc          = "CC";
$lang_email_subject     = "Subject";
$lang_email_body        = "Message";
$lang_notification      = "Receive Notifications";
$lang_attach_file       = "Attach File";
$lang_change            = "Cambia";
$lang_deleteuser        = "Cancella Utente";
$lang_deletegroup       = "Cancella Gruppo";
$lang_saved             = "Cambiamenti effettuati";
$lang_add               = "Aggiungi";
$lang_quota             = "Quota";
$lang_disableuser       = "Disable User";
$lang_noprefaccess       = "Disable Pref";
$lang_toomanysessions   = "Maximum sessions for user reached, contact the System Administrator";
$lang_maxsessions       = "Max No. Sessions";
$lang_preference        = "Preferences";
$lang_moving            = "Moving";
$lang_select            = "Please select new folder:";
$lang_updating          = "Updating";
$lang_cancel_button     = "Cancel";


// Bozz Change Begin
$lang_email_alt         = "Email this File";
$lang_del_folder_alt    = "Elimina Cartella (Y/N)";
$lang_mod_folder_alt    = "Modifica proprieta' Cartella";
$lang_move_folder_alt   = "Sposta Cartella";

$lang_del_file_alt      = "Elimina File (Y/N)";
$lang_mod_file_alt      = "Modifica proprieta' del File";
$lang_move_file_alt     = "Sposta File";
$lang_upd_file_alt      = "Aggiorna File";
$lang_get_file_alt      = "Scarica File";
$lang_view_file_alt     = "Visualizza File";
$lang_newuser_alt       = "Create a new user";
$lang_newgroup_alt      = "Create a new group";
$lang_flush_sessions_alt        = "Flush Hanging Sessions";
$lang_edthtml_alt       = "Edit Site HTML Preferences";
$lang_edprefs_alt       = "Edit Site Features and Settings";
$lang_backup_alt        = "Backup SQL Database";
$lang_backup_dis_alt    = "Backup Tool or gzip was not found";

// Bozz Change End
// begin Daphne change
$lang_log_file                  = "File Log";
$lang_lock_file                 = "Check file in/out";
// end Daphne Change

$lang_notif_subject_new = "New OWL Intranet File";
$lang_notif_subject_upd = "An OWL Intranet File was updated";
$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";
// Diego Bugfix
$lang_admin              = "Admin";
$lang_user               = "User";
$lang_search             = "Search";
$lang_search_results_for = "Search results for";
$lang_score              = "Score";
$lang_ownergroup         = "Owner (Group)";
$lang_name               = "Name";
//Diego Bugfix end
$lang_query_empty       = "Empty Search String";

$lang_err_file_update   = "Updating a file requires the filename to remain the same, please try again using the same filename";
$lang_err_quota         = "You do not have enough quota space to upload this file: ";
$lang_err_quota_needed  = " needed ";
$lang_err_quota_avail   = " available.";
$lang_err_quota_exceed  = "You have exceeded your quota";
$lang_err_upload        = "Upload error";
$lang_err_folder_exist  = "This folder already exists";
$lang_err_folder_create = "Folder Creation error";
//Begin Daphne Change - backup folder and file creation errors
$lang_err_backup_folder_create = "Backup Folder Creation error";
$lang_err_backup_file   = "Error backing up existing file";
$lang_err_file_lock     = "You cannot check this file out, it is currently held by";
// end Daphne change
$lang_err_unauthorized  = "Unauthorized";
$lang_err_unauth_area   = "You are not authorized to use this area";
$lang_err_pass_wrong    = "Old Password is wrong";
$lang_err_pass_missmatch= "New Password and Confirm Password don't Match";
$lang_err_user_exists   = "Username exists";
$lang_err_general       = "An Error has occured";
$lang_err_lang_1        = "Language has been spedified, but ";
$lang_err_lang_2        = " has not been found";
$lang_err_lang_notfound = "Unable to find language, please specify in owl.lib.php";
$lang_err_sess_write    = "Error writing session information to database";
$lang_err_sess_notvalid = "Session id is not valid";
$lang_err_bad_version_1 = "Owl Intranet Requires as a minimum PHP Version";
$lang_err_bad_version_2 = "You are running Version";
$lang_err_bad_version_3 = "Install a newer version of php and try again";

$lang_err_folderexists  = "Rename was Canceled<BR>Folder with the same name exists.";
$lang_err_movecancel    = "Move was Canceled<BR>";
$lang_err_email         = "Message could not be sent";
$lang_err_fileexists    = "Move was Canceled<BR>File with the same name already exists.";
$lang_err_nameempty     = "Folder name cannot be empty";

//janu's change BEGIN 
$lang_engine 		= "Owl Intranet Engine";
$lang_version 		= "Version";
$lang_username 		= "Username";
$lang_password 		= "Password";
$lang_owl_admin 		= "Owl Administration";
$lang_upg_MD5 		= "Upgrade Users to MD5 Passwords";
//janu's change END 

$lang_viewlog           = "Viewing Log For ";
$lang_viewing           = "Viewing ";
$lang_flushed           = "Session table flushed, user can now log in again.<BR>";

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
$lang_owl_sessionTimeout   = "Session Timeout in Seconds:";
$lang_owl_owl_expand    = "Default Browser View (Expand):";
$lang_owl_version_control= "Enable Version Control Feature:";
$lang_owl_restrict_view = "Enable Restricted View Feature:";
$lang_owl_title_tools   = "DB BACKUP/GZIP TOOLS";
$lang_owl_dbdump_path   = "DB Dump Tool Path:";
$lang_owl_gzip_path     = "gzip Path:";
$lang_owl_tar_path     = "tar Path:";
?>
