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

$lang_logout		= "(Logg ut)";

$lang_login		= "Logg inn";

$lang_successlogout 	= "Du har logget deg ut";

$lang_loginfail		= "Det skjedde en feil ved innloggingen";

$lang_logindisabled	= "Kontoen har blitt DEAKTIVERT, Kontakt system administrator";

$lang_sessinuse		= "Denne sesjonen er i bruk på en annen datamaskin, ingen tilgang<BR><A HREF='$default->rootUrl/'>Login</A>";

$lang_invalidsess	= "Sesjons id er ikke valid, logg inn fra hovedsiden<BR>";

$lang_sesstimeout	= "Den sesjon har gått ut på tid, logg inn på nytt<BR>";



$lang_addingfolder 	= "Lager en mappe i ";

$lang_addingfile 	= "Lager en fil i ";

$lang_modifying		= "Forandrer ";

$lang_reallydelete	= "Er du sikker på at du vil slette?";



$lang_title 		= "Tittel";

$lang_userlang		= "Språk";

// begin Daphne change

$lang_held                      = "Holdt"; // for version control

$lang_ver                       = "Ver."; // for version control

// end Daphne change

$lang_file 		= "Fil";

$lang_size 		= "Størrelse";

$lang_postedby 		= "Lagt inn av";

$lang_modified 		= "Forandret";

$lang_actions 		= "Valg";

$lang_ownership		= "Eier";

$lang_newpolicy		= "Sett nye sikkerhets regler";

$lang_policy 		= "Sikkerhets regler";



$lang_sendthisfile 	= "Send denne filen";

$lang_sendthisurl       = "Send this URL";

$lang_sendfile 		= "Send Fil";

$lang_keywords 		= "Nøkkel ord";

$lang_newpermissions 	= "Ny tillatelse";

$lang_permissions 	= "Tillatelse";

$lang_description 	= "Beskrivelse";

$lang_change		= "Forandre";

$lang_reset		= "Nullstill";

$lang_create		= "Lag";

// begin Daphne change

$lang_vertype           = "Revisjon type";  // for modify.php when uploading

$lang_vermajor          = "Stor Revisjon";

$lang_verminor          = "Liten Revisjon";

$lang_verdescription = "Beskrivelse<br>av forandringer";

// end Daphne change



$lang_everyoneread	= "Alle kan lese/laste ned";

$lang_everyonewrite	= "Alle kan lese/skrive/laste ned";

$lang_everyonewrite_nod	= "Alle lese/skrive/laste ned, IKKE SLETTE";

$lang_groupread		= "Din gruppe kan lese/laste ned";

$lang_groupwrite	= "Din gruppe kan lese/skrive/laste ned";

$lang_groupwrite_nod	= "Din gruppe kan lese/skrive/laste ned, IKKE SLETTE";

$lang_onlyyou		= "Bare du kan lese/skrive/laste ned";

$lang_groupwrite_worldread = "Din gruppe kan lese/skrive/laste ned og alle kan lese";

$lang_groupwrite_worldread_nod = "Din gruppe kan lese/skrive/laste ned (IKKE SLETTE) og alle kan lese";

$lang_ggroupwrite_worldread = "Din gruppe kan laste opp filer og slette mappen og alle kan se innholdet";

$lang_ggroupwrite_worldread_nod = "Din gruppe kan laste opp filer til mappen og alle kan se innhold, IKKE SLETTE";

// BEGIN BUG FIX: #449394 add document policy bugs for admin



$lang_everyoneread_ad	= "Alle kan lese/laste ned";

$lang_everyonewrite_ad	= "Alle kan lese/skrive/laste ned";

$lang_everyonewrite_ad_nod	= "Alle kan lese/skrive/laste ned, IKKE SLETTE";

$lang_groupread_ad	= "Den valgte gruppen kan lese/laste ned";

$lang_groupwrite_ad	= "Den valgte gruppen kan lese/skrive/laste ned";

$lang_groupwrite_ad_nod	= "Den valgte gruppen kan lese/skrive/laste ned, IKKE SLETTE";

$lang_onlyyou_ad	= "Bare du kan lese/skrive/laste ned";

$lang_groupwrite_worldread_ad = "Den valgte gruppen kan lese/skrive/laste ned og alle kan lese";

$lang_groupwrite_worldread_ad_nod = "Den valgte gruppen kan lese/skrive/laste ned,(NO DELETE) og alle kan lese";

$lang_ggroupwrite_worldread_ad = "Den valgte gruppen kan laste opp filer og slette denne mappen og alle kan se innholdet";

$lang_ggroupwrite_worldread_ad_nod = "Den valgte gruppen kan laste opp filer til mappen og alle kan se innholdet, IKKE SLETTE";



// END BUG FIX: #449394 add document policy bugs for admin



$lang_geveryoneread	= "Alle kan lese denne mappen";

$lang_geveryonewrite	= "Alle kan laste opp filer og slette denne mappen";

$lang_geveryonewrite_nod= "Alle kan laste opp filer til denne mappen";

$lang_ggroupread	= "Din gruppe kan lese denne mappen";

$lang_ggroupwrite	= "Din gruppe kan laste opp filer og slette denne mappen";

$lang_ggroupwrite_nod	= "Din gruppe kan laste opp filer til denne mappen";

$lang_gonlyyou		= "Bare du kan laste opp filer og slette denne mappen";



$lang_geveryoneread_ad	= "Alle kan lese denne mappen";

$lang_geveryonewrite_ad	= "Alle kan laste opp filer og slette denne mappen";

$lang_geveryonewrite_ad_nod	= "Alle kan laste opp filer til denne mappen";

$lang_ggroupread_ad	= "Den valgte gruppen kan lese filer i denne mappen";

$lang_ggroupwrite_ad    = "Den valgte gruppen kan laste opp filer og slette denne mappen";

$lang_ggroupwrite_ad_nod= "Den valgte gruppen kan laste opp filer til denne mappen";

$lang_gonlyyou_ad	= "Bare du kan laste opp filer og slette denne mappen";



$lang_nofolderaccess	= "Du har ikke tilgang til denne mappen";

$lang_nofileaccess	= "Du har ikke tilgang til denne filen";

$lang_nofilemod		= "Du har ikke tilgang til å modifisere denne filen";

$lang_nofiledelete	= "Du har ikke tilgang til å slette denne filen";

$lang_noupload		= "Du har ikke tilgang til å laste opp til denne mappen";

$lang_nosubfolder	= "Du har ikke tilgang til å lage undermapper her";

$lang_nofoldermod	= "Du har ikke tilgang til å modifisere denne mappen";

$lang_nofolderdelete	= "Du har ikke tilgang til å slette denne mappen";

$lang_noemail		= "Du har ikke tilgang til å sende denne filen via e-post";

$lang_fileexists	= "Filen du prøver å laste opp eksisterer allerede";

$lang_specifyname	= "Vennligst gå tilbake å spesifiser et navn";



$lang_users		= "Brukere";

$lang_groups		= "Grupper";

$lang_group		= "Hoved gruppe";

$lang_groupmember	= "Medlems gruppe";

$lang_username		= "Brukernavn";

$lang_password		= "Passord";

$lang_newpassword	= "Nytt Passord";

$lang_oldpassword	= "Gammelt Passord";

$lang_confpassword	= "Bekreft Nytt Passord";

$lang_email             = "E-post Addresse";

$lang_emailing		= "Sender e-post";

$lang_email_to		= "Send til";

$lang_email_reply_to	= "Svar til";

$lang_email_cc          = "CC";

$lang_email_subject	= "Emne";

$lang_email_body	= "Beskjed";

$lang_notification      = "Motta varsel";

$lang_attach_file       = "Attach File";

$lang_change		= "Forandre";

$lang_deleteuser	= "Slett bruker";

$lang_deletegroup	= "Slett gruppe";

$lang_saved		= "Forandringer er lagret";

$lang_add		= "Legg til";

$lang_quota		= "Kvote";

$lang_disableuser	= "Deaktiver Bruker";

$lang_noprefaccess	= "Deaktiver Pref";

$lang_toomanysessions   = "Maximum sessions for user reached, contact the System Administrator";

$lang_maxsessions       = "Max No. Sessions";

$lang_preference        = "Preferences";

$lang_moving            = "Moving";

$lang_select            = "Please select new folder:";

$lang_updating          = "Updating";

$lang_cancel_button     = "Cancel";





// Bozz Change Begin

$lang_email_alt		= "Send denne filen via e-post";

$lang_del_folder_alt    = "Slett denne mappen (Y/N)";

$lang_mod_folder_alt    = "Modifiser mappe egenskaper";

$lang_move_folder_alt   = "Flytt mappe";



$lang_del_file_alt      = "Slett fil (Y/N)";

$lang_mod_file_alt      = "Modifiser fil egenskaper";

$lang_move_file_alt     = "Flytt fil";

$lang_upd_file_alt      = "Oppdater fil";

$lang_get_file_alt      = "Last ned fil";

$lang_view_file_alt     = "Se fil";

$lang_newuser_alt       = "Create a new user";

$lang_newgroup_alt      = "Create a new group";

$lang_flush_sessions_alt        = "Flush Fanging Fessions";

$lang_edthtml_alt       = "Edit Site HTML Preferences";

$lang_edprefs_alt       = "Edit Site Features and Settings";

$lang_backup_alt        = "Backup SQL Database";

$lang_backup_dis_alt    = "Backup Tool or gzip was not found";



// Bozz Change End

// begin Daphne change

$lang_log_file                  = "Fil Logg";

$lang_lock_file                 = "Sjekk filen inn/ut";

// end Daphne Change

$lang_notif_subject_new = "Ny OWL Intranet Fil";

$lang_notif_subject_upd = "En OWL Intranet Fil ble oppdatert";

$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";

//$lang_notif_msg         = "Dette er en automatisert beskjed fra OWL Intranet\n\nTrykk på linken nedenfor for å se den nye/oppdaterte filen\n\n";



// Diego Bugfix

$lang_admin              = "Admin";

$lang_user               = "Bruker";

$lang_search             = "Søk";

$lang_search_results_for = "Søk resultat for";

$lang_score              = "Poeng";

$lang_ownergroup         = "Eier (Gruppe)";

$lang_name               = "Navn";

//Diego Bugfix end

$lang_query_empty       = "Tom søke streng";



// Error Message Section

//

$lang_err_file_update	= "Oppdatering av en fil krever at filnavnet er likt, prøv igjen med å bruke det samme filnavnet";

$lang_err_quota		= "Du har ikke nok igjen av plass kvoten din for å laste opp denne filen: ";

$lang_err_quota_needed	= " nødvendig ";

$lang_err_quota_avail	= " tilgjengelig.";

$lang_err_quota_exceed  = "Du har gått over din kvote";

$lang_err_upload 	= "Opplastnings feil";

$lang_err_folder_exist	= "Denne mappen finnes allerede";

$lang_err_folder_create = "Klarte ikke å lage mappen";

//Begin Daphne Change - backup folder and file creation errors

$lang_err_backup_folder_create = "Klarte ikke å lage Sikkerhetskopi mappe";

$lang_err_backup_file   = "Feil under laging av sikkerhetskopi for eksisterende fil";

$lang_err_file_lock     = "Du kan ikke sjekke ut denne filen, den er for øyeblikket holdt av";

// end Daphne change

$lang_err_unauthorized  = "Ikke tillatt";

$lang_err_unauth_area   = "Du har ikke tillatelse til å bruke dette området";

$lang_err_pass_wrong    = "Gammelt passord er feil";

$lang_err_pass_missmatch= "Nytt passord og bekrift passord stemmer ikke overens";

$lang_err_user_exists	= "Brukernavn eksisterer allerede";

$lang_err_general 	= "En feil har oppstått";

$lang_err_lang_1        = "Språk har blitt spesifisert, men ";

$lang_err_lang_2        = " har ikke blitt funnet";

$lang_err_lang_notfound = "Klarer ikke å finne språl, spesifiser i owl.lib.php";

$lang_err_sess_write	= "Det oppstå en feil ved skriving av sesjons informasjon til databasen";

$lang_err_sess_notvalid = "Sesjons id er ikke valid";

$lang_err_bad_version_1 = "Owl Intranet Krever som en minimum PHP Versjon";

$lang_err_bad_version_2 = "Du kjører Versjon";

$lang_err_bad_version_3 = "Installer en nyere versjon av php og prøv igjen";



$lang_err_folderexists  = "Gi nytt navn ble kanselert<BR>Mappe med det samme navnet eksisterer.";

$lang_err_movecancel    = "Flytting ble kanselert<BR>";

$lang_err_email         = "Beskjeden kunne ikke bli sendt";

$lang_err_fileexists    = "Flytting ble kanselert<BR>Fil med samme navn eksisterer allerede.";

$lang_err_nameempty     = "Folder name cannot be empty";



//janu's change BEGIN 

$lang_engine 		= "Owl Intranet Motor";

$lang_version 		= "Versjon";

$lang_username 		= "Brukernavn";

$lang_password 		= "Passord";

$lang_owl_admin 	= "Owl Administrasjon";

$lang_upg_MD5 		= "Oppgrader brukere til MD5 Passord";

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

