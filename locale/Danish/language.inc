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

$lang_logout 		= "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
$lang_login		= "Log af";
$lang_successlogout 	= "Du er logget af";
$lang_loginfail		= "Der opstod en fejl under login";
$lang_logindisabled	= "Kontoen er blevet DEAKTIVERET, Kontakt systemadministrator";
$lang_sessinuse		= "Denne session er i brug på en anden computer, ingen adgang<BR><A HREF='$default->owl_root_url/'>Login</A>";
$lang_invalidsess	= "Sessions id er ikke gyldig, log på fra hovedsiden<BR>";
$lang_sesstimeout	= "Den session har forårsaget timeout, log på igen<BR>";

$lang_addingfolder 	= "Opretter en mappe i ";
$lang_addingfile 	= "Opretter en fil i ";
$lang_modifying		= "Ændrer ";
$lang_reallydelete	= "Er du sikker på at du vil slette?";
$lang_addfolder		= "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_add_folder.gif' BORDER=0>";
$lang_addfile		= "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_add_file.gif' BORDER=0>";

$lang_title 		= "Titel";
$lang_userlang		= "Sprog";
// begin Daphne change
$lang_held                      = "Lås"; // for version control
$lang_ver                       = "Ver."; // for version control
// end Daphne change
$lang_file 		= "Fil";
$lang_size 		= "Størrelse";
$lang_postedby 		= "Tilføjet af";
$lang_modified 		= "Ændret";
$lang_actions 		= "Valg";
$lang_ownership		= "Ejer";
$lang_newpolicy		= "Sæt nye sikkerhedsregler";
$lang_policy 		= "Sikkerhedsregler";

$lang_sendthisfile 	= "Send denne fil";
$lang_sendthisurl       = "Send this URL";
$lang_sendfile 		= "Send Fil";
$lang_keywords 		= "Nøgle ord";
$lang_newpermissions 	= "Ny tilladelse";
$lang_permissions 	= "Tilladelse";
$lang_description 	= "Beskrivelse";
$lang_change		= "Ændre";
$lang_reset		= "Nulstil";
$lang_create		= "Opret";
// begin Daphne change
$lang_vertype           = "Revision ";  // for modify.php when uploading
$lang_vermajor          = "Overordnet Revision";
$lang_verminor          = "UnderordnetvRevision";
$lang_verdescription = "Beskrivelse<br>af ændringer";
// end Daphne change

$lang_everyoneread	= "Alle kan læse/downloade";
$lang_everyonewrite	= "Alle kan læse/skrive/downloade";
$lang_everyonewrite_nod	= "Alle læse/skrive/downloade, IKKE SLETTE";
$lang_groupread		= "Din gruppe kan læse/downloade";
$lang_groupwrite	= "Din gruppe kan læse/skrive/downloade";
$lang_groupwrite_nod	= "Din gruppe kan læse/skrive/downloade, IKKE SLETTE";
$lang_onlyyou		= "Kun du kan læse/skrive/downloade";
$lang_groupwrite_worldread = "Din gruppe kan læse/skrive/downloade og alle kan læse";
$lang_groupwrite_worldread_nod = "Din gruppe kan læse/skrive/downloade (IKKE SLETTE) og alle kan læse";
$lang_ggroupwrite_worldread = "Din gruppe kan uploade filer og slette mappen og alle kan se indholdet";
$lang_ggroupwrite_worldread_nod = "Din gruppe kan uploade filer til mappen og alle kan se indhold, IKKE SLETTE";
// BEGIN BUG FIX: #449394 add document policy bugs for admin

$lang_everyoneread_ad	= "Alle kan læse/downloade";
$lang_everyonewrite_ad	= "Alle kan læse/skrive/downloade";
$lang_everyonewrite_ad_nod	= "Alle kan læse/skrive/downloade, IKKE SLETTE";
$lang_groupread_ad	= "Den valgte gruppe kan læse/downloade";
$lang_groupwrite_ad	= "Den valgte gruppe kan læse/skrive/downloade";
$lang_groupwrite_ad_nod	= "Den valgte gruppe kan læse/skrive/downloade, IKKE SLETTE";
$lang_onlyyou_ad	= "Kun du kan læse/skrive/downloade";
$lang_groupwrite_worldread_ad = "Den valgte gruppe kan læse/skrive/downloade og alle kan læse";
$lang_groupwrite_worldread_ad_nod = "Den valgte gruppe kan læse/skrive/downloade,(NO DELETE) og alle kan læse";
$lang_ggroupwrite_worldread_ad = "Den valgte gruppe kan uploade filer og slette denne mappen og alle kan se indholdet";
$lang_ggroupwrite_worldread_ad_nod = "Den valgte gruppe kan uploade filer til mappen og alle kan se indholdet, IKKE SLETTE";

// END BUG FIX: #449394 add document policy bugs for admin

$lang_geveryoneread	= "Alle kan læse denne mappe";
$lang_geveryonewrite	= "Alle kan uploade filer og slette denne mappe";
$lang_geveryonewrite_nod= "Alle kan uploade filer til denne mappe";
$lang_ggroupread	= "Din gruppe kan læse denne mappe";
$lang_ggroupwrite	= "Din gruppe kan uploade filer og slette denne mappe";
$lang_ggroupwrite_nod	= "Din gruppe kan uploade filer til denne mappe";
$lang_gonlyyou		= "Kun du kan uploade filer og slette denne mappe";

$lang_geveryoneread_ad	= "Alle kan læse denne mappe";
$lang_geveryonewrite_ad	= "Alle kan uploade filer og slette denne mappe";
$lang_geveryonewrite_ad_nod	= "Alle kan uploade filer til denne mappe";
$lang_ggroupread_ad	= "Den valgte gruppe kan læse filer i denne mappe";
$lang_ggroupwrite_ad    = "Den valgte gruppe kan uploade filer og slette denne mappe";
$lang_ggroupwrite_ad_nod= "Den valgte gruppe kan uploade filer til denne mappe";
$lang_gonlyyou_ad	= "Kun du kan uploade filer og slette denne mappe";

$lang_nofolderaccess	= "Du har ikke adgang til denne mappe";
$lang_nofileaccess	= "Du har ikke adgang til denne fil";
$lang_nofilemod		= "Du har ikke adgang til at modificere denne fil";
$lang_nofiledelete	= "Du har ikke adgang til at slette denne fil";
$lang_noupload		= "Du har ikke adgang til at uploade til denne mappe";
$lang_nosubfolder	= "Du har ikke adgang til at oprette undermapper her";
$lang_nofoldermod	= "Du har ikke adgang til at modificere denne mappe";
$lang_nofolderdelete	= "Du har ikke adgang til at slette denne mappe";
$lang_noemail		= "Du har ikke adgang til at sende denne fil via e-mail";
$lang_fileexists	= "Filen du prøver at uploade eksisterer allerede";
$lang_specifyname	= "Angiv venligst et navn";

$lang_users		= "Brugere";
$lang_groups		= "Grupper";
$lang_group		= "Hovedgruppe";
$lang_groupmember	= "Medlemsgruppe";
$lang_username		= "Brugernavn";
$lang_password		= "Password";
$lang_newpassword	= "Nyt Password";
$lang_oldpassword	= "Gammelt Password";
$lang_confpassword	= "Bekræft Nyt Password";
$lang_email             = "E-mail Adresse";
$lang_emailing		= "Sender e-mail";
$lang_email_to		= "Send til";
$lang_email_reply_to	= "Svar til";
$lang_email_cc          = "CC";
$lang_email_subject	= "Emne";
$lang_email_body	= "Besked";
$lang_notification      = "Modtag besked";
$lang_attach_file       = "Attach File";
$lang_change		= "Ændre";
$lang_deleteuser	= "Slet bruger";
$lang_deletegroup	= "Slet gruppe";
$lang_saved		= "Ændreinger er gemt";
$lang_add		= "Tilføj";
$lang_quota		= "Kvote";
$lang_disableuser	= "Deaktiver/Aktiver Bruger";
$lang_noprefaccess      = "Disable Pref";
$lang_toomanysessions   = "Maximum sessions for user reached, contact the System Administrator";
$lang_maxsessions       = "Max No. Sessions";
$lang_preference        = "Preferences";
$lang_moving            = "Moving";
$lang_select            = "Please select new folder:";
$lang_updating          = "Updating";
$lang_cancel_button     = "Cancel";

// Bozz Change Begin
$lang_email_alt		= "Send denne fil via e-mail";
$lang_del_folder_alt    = "Slet denne mappe (Y/N)";
$lang_mod_folder_alt    = "Modificer mappeegenskaber";
$lang_move_folder_alt   = "Flyt mappe";

$lang_del_file_alt      = "Slet fil (Y/N)";
$lang_mod_file_alt      = "Modificer filegenskaber";
$lang_move_file_alt     = "Flyt fil";
$lang_upd_file_alt      = "Opdater fil";
$lang_get_file_alt      = "download fil";
$lang_view_file_alt     = "Se fil";
$lang_newuser_alt       = "Create a new user";
$lang_newgroup_alt      = "Create a new group";
$lang_flush_sessions_alt        = "flush hanging sessions";
$lang_edthtml_alt       = "Edit Site HTML Preferences";
$lang_edprefs_alt       = "Edit Site Features and Settings";
$lang_backup_alt        = "Backup SQL Database";
$lang_backup_dis_alt    = "Backup Tool or gzip was not found";

// Bozz Change End
// begin Daphne change
$lang_log_file                  = "Fil Log";
$lang_lock_file                 = "Lås filen: til/fra";
// end Daphne Change
$lang_notif_subject_new = "Ny OWL Intranet Fil";
$lang_notif_subject_upd = "En OWL Intranet Fil blev opdateret";
$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";
;$lang_notif_msg         = "Dette er en automatiseret besked fra OWL Intranet\n\nTryk på linket nedenfor for at se den nye/opdaterede fil\n\n";

// Diego Bugfix
$lang_admin              = "Admin";
$lang_user               = "Bruger";
$lang_search             = "Søg";
$lang_search_results_for = "Søgeresultat for";
$lang_score              = "Point";
$lang_ownergroup         = "Ejer (Gruppe)";
$lang_name               = "Navn";
//Diego Bugfix end
$lang_query_empty       = "Tom søgestreng";

// Error Message Section
//
$lang_err_file_update	= "Opdatering af en fil kræver at filnavnet er uændret, prøv igen med at bruge det samme filnavn";
$lang_err_quota		= "Du har ikke nok plads nok tilbage i den kvote, til at uploade denne fil: ";
$lang_err_quota_needed	= " nødvendig ";
$lang_err_quota_avail	= " tilgængelig.";
$lang_err_quota_exceed  = "Du har overskridt over din kvote";
$lang_err_upload 	= "Upload fejl";
$lang_err_folder_exist	= "Denne mappe findes allerede";
$lang_err_folder_create = "Kunne ikke at oprette mappen";
//Begin Daphne Change - backup folder and file creation errors
$lang_err_backup_folder_create = "Kunne ikke oprette Sikkerhedskopimappe";
$lang_err_backup_file   = "Fejl under oprettelse af sikkerhedskopi for eksisterende fil";
$lang_err_file_lock     = "Du kan ikke låse denne fil, da den for øjeblikket er låst af";
// end Daphne change
$lang_err_unauthorized  = "Ikke tilladt";
$lang_err_unauth_area   = "Du har ikke tilladelse til at bruge dette område";
$lang_err_pass_wrong    = "Det gamle password er forkert";
$lang_err_pass_missmatch= "Nyt password og bekræftet password er ikke identiske";
$lang_err_user_exists	= "Brugernavn eksisterer allerede";
$lang_err_general 	= "Der er opstået en fejl";
$lang_err_lang_1        = "Sprog er blevet specifiseret, men ";
$lang_err_lang_2        = " er ikke blevet fundet";
$lang_err_lang_notfound = "Kunne ikke finde sprog, angivet i owl.lib.php";
$lang_err_sess_write	= "Der opstod en fejl ved skriving af sessionsinformation til databasen";
$lang_err_sess_notvalid = "Sessions id er ikke gyldig";
$lang_err_bad_version_1 = "Owl Intranet Kræver som en minimum PHP Version";
$lang_err_bad_version_2 = "Du kører Version";
$lang_err_bad_version_3 = "Installer en nyere version af php og prøv igen";

$lang_err_folderexists  = "Omdøb blev annuleret<BR>Mappe med det samme navn eksisterer.";
$lang_err_movecancel    = "Flyting blev annuleret<BR>";
$lang_err_email         = "Beskeden kunne ikke sendes";
$lang_err_fileexists    = "Flyting blev annuleret<BR>Fil med samme navn eksisterer allerede.";
$lang_err_nameempty     = "Folder name cannot be empty";

//janu's change BEGIN 
$lang_engine 		= "Owl Intranet Engine";
$lang_version 		= "Version";
$lang_username 		= "Brugernavn";
$lang_password 		= "Password";
$lang_owl_admin 	= "Owl Administration";
$lang_upg_MD5 		= "Opgrader brugere til MD5 Password";
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
$lang_owl_owl_timeout   = "Session Timeout in Seconds:";
$lang_owl_owl_expand    = "Default Browser View (Expand):";
$lang_owl_version_control= "Enable Version Control Feature:";
$lang_owl_restrict_view = "Enable Restricted View Feature:";
$lang_owl_title_tools   = "DB BACKUP/GZIP TOOLS";
$lang_owl_dbdump_path   = "DB Dump Tool Path:";
$lang_owl_gzip_path     = "gzip Path:";
$lang_owl_tar_path     = "tar Path:";
?>
