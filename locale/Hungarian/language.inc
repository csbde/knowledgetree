<?php



/*



  File: language.inc      

  Author: Chris

  Date: 2000/12/14



  Owl: Copyright Chris Vincent <cvincent@project802.net>



  You should have received a copy of the GNU Public

  License along with this package; if not, write to the

  Free Software Foundation, Inc., 59 Temple Place - Suite 330,

  Boston, MA 02111-1307, USA.



*/



//$lang_logout 		= "<IMG SRC='$default->rootUrl/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";

$lang_logout		= "(Kilépés)";

$lang_login		= "Belépés";

$lang_successlogout 	= "Ön kilépett";

$lang_loginfail		= "Belépési hiba";

$lang_logindisabled     = "Account has been DISABLED, contact the System Adminstrator";

$lang_sessinuse		= "Ezt a kapcsolatot már más géprõl használják<BR><A HREF='$default->rootUrl/'>Belépés</A>";

$lang_invalidsess	= "Tiltott kapcsolat azonosító, lépjen be a fõlapról<BR>";

$lang_sesstimeout	= "A kapcsolat ideje lejárt, lépjen be újra<BR>";



$lang_addingfolder 		= "Könyvtár hozzáadás: ";

$lang_addingfile 		= "Dokumentum hozzáadás: ";

$lang_modifying		= "Módosítás ";

$lang_reallydelete		= "Biztosan törli :";



$lang_title 		= "Megnevezés";

$lang_userlang          = "Language";

// begin Daphne change

$lang_held                      	= "Zárolás"; // for version control

$lang_ver                       	= "Verzió"; // for version control

// end Daphne change

$lang_file 			= "Állomány";

$lang_size 		= "Méret";

$lang_postedby 		= "Postázta";

$lang_modified 		= "Módosítva";

$lang_actions 		= "Akciók";

$lang_ownership		= "Tulajdonos";

$lang_newpolicy		= "Új szabály";

$lang_policy 		= "Szabály";



$lang_sendthisfile 	= "Küldendõ állomány";

$lang_sendthisurl       = "Send this URL";

$lang_sendfile 		= "Állomány küldés";

$lang_keywords 		= "Kulcsszavak";

$lang_permissions 	= "Jogok";

$lang_newpermissions    = "New Permissions";

$lang_description 	= "Leírás";

$lang_change		= "Változtat";

$lang_reset		= "Alapállapotba";

$lang_create		= "Kész";

// begin Daphne change

$lang_vertype           		= "A módosítás típusa";  // for modify.php when uploading

$lang_vermajor          	= "Alapvetõ módosítás (X.0)";

$lang_verminor          	= "Kis módosítás (0.X)";

$lang_verdescription 	= "A módosítás <br>leírása";

// end Daphne change



$lang_everyoneread		= "Mindenki olvashatja/letöltheti";

$lang_everyonewrite		= "Mindenki olvashatja/írhatja/letöltheti";

$lang_everyonewrite_nod	= "Mindenki olvashatja/írhatja/letöltheti, NEM TÖRÖLHETÕ";

$lang_groupread		= "Az Ön csoportja olvashatja/töltheti le";

$lang_groupwrite		= "Az Ön csoportja olvashatja/írhatja/töltheti le";

$lang_groupwrite_nod	= "Az Ön csoportja olvashatja/írhatja/töltheti le, NEM TÖRÖLHETÕ";

$lang_onlyyou		= "Csak Ön olvashatja/írhatja/töltheti le";

$lang_groupwrite_worldread = "Your group can read/write/download & everyone else can read";

$lang_groupwrite_worldread_nod = "Your group can read/write/download (NO DELETE) & everyone else can read";

$lang_ggroupwrite_worldread = "Your group can upload files and delete this folder & everyone else can view contents";

$lang_ggroupwrite_worldread_nod = "Your group can upload files to this folder & everyone else can view contents, NO DELETE";



// BEGIN BUG FIX: #449394 add document policy bugs for admin



$lang_everyoneread_ad	= "Mindenki olvashatja/letöltheti";

$lang_everyonewrite_ad	= "Mindenki olvashatja/írhatja/letöltheti";

$lang_everyonewrite_ad_nod	= "Mindenki olvashatja/írhatja/letöltheti, NEM TÖRÖLHETÕ";

$lang_groupread_ad		= "A kiválasztott csoport olvashatja/töltheti le";

$lang_groupwrite_ad		= "A kiválasztott csoport olvashatja/írhatja/töltheti le";

$lang_groupwrite_ad_nod	= "A kiválasztott csoport olvashatja/írhatja/töltheti le, NEM TÖRÖLHETÕ";

$lang_onlyyou_ad		= "Csak Ön olvashatja/írhatja/töltheti le";

$lang_groupwrite_worldread_ad = "The selected group can read/write/download & everyone else can read";

$lang_groupwrite_worldread_ad_nod = "The selected group can read/write/download (NO DELETE) & everyone else can read";

$lang_ggroupwrite_worldread_ad = "Selected group can upload files and delete this folder & everyone else can view contents";

$lang_ggroupwrite_worldread_ad_nod = "Selected group can upload files to this folder & everyone else can view contents, NO DELETE";





// END BUG FIX: #449394 add document policy bugs for admin



$lang_geveryoneread	= "Mindenki olvashatja ezt a könyvtárat";

$lang_geveryonewrite	= "Mindenki feltölthet és törölhet ebben a könyvtárban";

$lang_geveryonewrite_nod= "Mindenki feltölthet ebbe a könyvtárba";

$lang_ggroupread	= "Az Ön csoportja olvashatja ezt a könyvtárat";

$lang_ggroupwrite	= "Az Ön csoportja feltölthet és törölhet ebben a könyvtárban";

$lang_ggroupwrite_nod	= "Az Ön csoportja tölhet fel ebbe a könyvtárba";

$lang_gonlyyou		= "Csak Ön tölthet fel és törölhet ebben a könyvtárban";



$lang_geveryoneread_ad	= "Mindenki olvashatja ezt a könyvtárat";

$lang_geveryonewrite_ad	= "Mindenki feltölthet és törölhet ebben a könyvtárban";

$lang_geveryonewrite_ad_nod	= "Mindenki feltölthet ebbe a könyvtárba";

$lang_ggroupread_ad	= "A kiválasztott csoport olvashatja ezt a könyvtárat";

$lang_ggroupwrite_ad   	="A kiválasztott csoport feltölthet és törölhet ebben a könyvtárban";

$lang_ggroupwrite_ad_nod= "A kiválasztott csoport tölhet fel ebbe a könyvtárba";

$lang_gonlyyou_ad	= "Csak Ön tölthet fel és törölhet ebben a könyvtárban";



$lang_nofolderaccess	= "Önnek nincs joga erre erre könyvtárra";

$lang_nofileaccess	= "Önnek nincs joga erre az állományra";

$lang_nofilemod		= "Önnek nincs joga az állomány módosítására";

$lang_nofiledelete	= "Önnek nincs joga az állomány törlésére";

$lang_noupload		= "Önnek nincs joga feltölteni ebbe a könyvtárba";

$lang_nosubfolder	= "Önnek nincs joga itt alkönyvtár létrehozására";

$lang_nofoldermod	= "Önnek nincs joga ezt a könyvtárat módosítani";

$lang_nofolderdelete	= "Önnek nincs joga ezt a könyvtárat törölni";

$lang_noemail           = "You do not have access to email this file";

$lang_fileexists	= "A feltölteni kívánt állomány már létezik";

$lang_specifyname	= "Lépjen vissza, és adjon meg másik nevet";



$lang_users		= "Felhasználók";

$lang_groups		= "Csoportok";

$lang_group		= "Elsõdleges csoport";

$lang_groupmember	= "Csoporttagság";

$lang_username		= "Felhasználó";

$lang_password		= "Jelszó";

$lang_newpassword	= "Új jelszó";

$lang_oldpassword	= "Régi jelszó";

$lang_confpassword	= "Új jelszó mégegyszer";

$lang_email             = "Email cím";

$lang_emailing          = "Emailing";

$lang_email_to          = "Mail To";

$lang_email_reply_to    = "Reply To";

$lang_email_cc          = "CC";

$lang_email_subject     = "Subject";

$lang_email_body        = "Message";

$lang_notification      = "Értesítést kap";

$lang_attach_file       = "Attach File";

$lang_change		= "Változtat";

$lang_deleteuser	= "Felhasználó törlés";

$lang_deletegroup	= "Csoport törlés";

$lang_saved		= "Változások elmentve";

$lang_add		= "Hozzáadás";

$lang_quota		= "Tárolóhely limit";

$lang_disableuser       = "Disable User";

$lang_noprefaccess       = "Disable Pref";

$lang_toomanysessions   = "Maximum sessions for user reached, contact the System Administrators";

$lang_maxsessions       = "Max No. Sessions";

$lang_preference        = "Preferences";

$lang_moving            = "Moving";

$lang_select            = "Please select new folder:";

$lang_updating          = "Updating";

$lang_cancel_button     = "Cancel";





// Bozz Change Begin

$lang_email_alt         = "Email this File";

$lang_del_folder_alt   	= "Könyvtár törlés (I/N)";

$lang_mod_folder_alt   	= "Könyvtár tulajdonságainak módósítása";

$lang_move_folder_alt  	= "Könyvtár mozgatása";



$lang_del_file_alt     	= "Állomány törlés (I/N)";

$lang_mod_file_alt     	= "Állomány tulajdonságainak módósítása";

$lang_move_file_alt    	= "Állomány mozgatása";

$lang_upd_file_alt     	= "Állomány módosítása";

$lang_get_file_alt     	= "Állomány letöltése";

$lang_view_file_alt    	= "Állomány megtekintése";

$lang_newuser_alt       = "Create a new user";

$lang_newgroup_alt      = "Create a new group";

$lang_flush_sessions_alt        = "Flush Hanging Sessions";

$lang_edthtml_alt       = "Edit Site HTML Preferences";

$lang_edprefs_alt       = "Edit Site Features and Settings";

$lang_backup_alt        = "Backup SQL Database";

$lang_backup_dis_alt    = "Backup Tool or gzip was not found";



// Bozz Change End

// begin Daphne change

$lang_log_file          = "Állomány napló";

$lang_lock_file         = "Zárolás BE/KI";

// end Daphne Change

$lang_notif_subject_new = "OWL Intranet Dokumentum";

$lang_notif_subject_upd = "Karbantartott OWL Intranet Dokumentum";

$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";

;;$lang_notif_msg         = "EZ EGY AUTOMATIKUS ÜZENET \nAZ OWL Intranet Dokumentumkezelõtõl. \nKattintson a linkre az új/módosult állomány megtekintéséhez.\n\n";



// Diego Bugfix

$lang_admin             = "Adminisztrátor";

$lang_user              = "Felhasználó";

$lang_search            = "Keresés";

$lang_search_results_for= "A keresett szöveg :";

$lang_score             = "Helyezés";

$lang_ownergroup        = "Tulajdonos (Csoport)";

$lang_name              = "Név";

//Diego Bugfix end

$lang_query_empty       	= "Üres a keresendõ szöveg";



// Error Message Section

//

$lang_err_file_update	= "Az állomány neve módosítás esetén nem változtatható, próbálja újra";

$lang_err_quota		= "Tárolóhely limit túllépés miatt nem lehet feltölteni: ";

$lang_err_quota_needed	= " szükséges ";

$lang_err_quota_avail	= " rendelkezésre áll.";

$lang_err_quota_exceed  	= "Az Ön tárolóhelye elfogyott";

$lang_err_upload 		= "Feltöltési hiba";

$lang_err_folder_exist	= "A könyvtár már létezik";

$lang_err_folder_create 	= "Könyvtár létrehozási hiba";

//Begin Daphne Change - backup folder and file creation errors

$lang_err_backup_folder_create = "Mentés könyvtár létrehozási hiba";

$lang_err_backup_file   	= "Hiba az állomány mentésekor";

$lang_err_file_lock     	= "Ezt az állományt már zárolták";

// end Daphne change

$lang_err_unauthorized  	= "Jogtalan";

$lang_err_unauth_area   	= "Ehez a területhez nincs joga";

$lang_err_pass_wrong    	= "A régi jelszó helytelen";

$lang_err_pass_missmatch	= "Az új jelszó két példánya nem azonos";

$lang_err_user_exists	= "A felhasználó már létezik";

$lang_err_general 		= "Hiba következett be";

$lang_err_lang_1        	= "Olyan nyelvet jelölt meg ";

$lang_err_lang_2        	= " amely nem létezik";

$lang_err_lang_notfound 	= "A nyelvet nem találom, módosítsa az owl.lib.php állományt";

$lang_err_sess_write		= "Hiba a kapcsolat információk adatbázisba írásakor";

$lang_err_sess_notvalid 	= "Hibás kapcsolat azonosító";

$lang_err_bad_version_1 	= "Az Owl Intranethez szükséges minimális PHP verzió";

$lang_err_bad_version_2 	= "Az Ön által használt verzió";

$lang_err_bad_version_3 	= "Telepítsen egy újabb verziót, és próbálja újra";



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

$lang_owl_admin 	= "Owl Administration";

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

