<?php



/*



  File: language.inc

  Author: Chris

  Date: 2000/12/14

  Last changed by: Oliver Stein 2002/11/27



  Owl: Copyright Chris Vincent <cvincent@project802.net>



  You should have received a copy of the GNU Public

  License along with this package; if not, write to the

  Free Software Foundation, Inc., 59 Temple Place - Suite 330,

  Boston, MA 02111-1307, USA.



*/



$lang_logout             = "<IMG SRC='$default->rootUrl/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";

//$lang_logout             = "(Abmelden)";

$lang_login              = "Anmelden";

$lang_successlogout      = "Sie haben sich erfolgreich abgemeldet";

$lang_loginfail          = "Falscher Benutzername oder falsches Passwort";

$lang_logindisabled      = "Der Benutzername wurde deaktiviert, bitte kontaktieren Sie den Systemadministrator";

$lang_sessinuse          = "Diese Sitzung ist bereits auf einem anderen Computer aktiv.  Zugang verweigert.";

$lang_invalidsess        = "Ung&uuml;ltige Anmeldung. Gehen Sie bitte zur Hauptseite<BR>";

$lang_sesstimeout        = "Zeit&uuml;berschreitung! Bitte melden Sie sich neu an<BR>";



$lang_addingfolder       = "Hinzuf&uuml;gen eines Verzeichnisses zu ";

$lang_addingfile         = "Hinzuf&uuml;gen einer Datei zu ";

$lang_modifying          = "&Auml;ndern ";

$lang_reallydelete       = "Wirklich l&ouml;schen";



$lang_title              = "Titel";

$lang_userlang           = "Sprache";

// begin Daphne change

$lang_held               = "Ausgecheckt"; // for version control

$lang_ver                = "Ver."; // for version control

// end Daphne change

$lang_file               = "Datei";

$lang_size               = "Gr&ouml;sse";

$lang_postedby           = "Eingestellt von";

$lang_modified           = "Ver&auml;ndert";

$lang_actions            = "Aktionen";

$lang_ownership          = "Eigentum";

$lang_newpolicy          = "Neue Berechtigung";

$lang_policy             = "Berechtigung"; 



// BEGIN extend Scott Tigr

$lang_folder             = "Verzeichnis";

$lang_currentdir         = "aktuelles Verzeichnis";

$lang_create_file        = "Datei erstellen";

// begin Daphne change

$lang_vertype            = "Art der &Uuml;berarbeitung";  // for modify.php when uploading

$lang_vermajor           = "Komplette &Uuml;berarbeitung";

$lang_verminor           = "Kleinere &Auml;nderungen";

$lang_verdescription     = "Beschreibung<br>der &Auml;nderungen";

// end Daphne change



// END extend Scott Tigr



$lang_sendthisfile       = "Diese Datei senden";

$lang_sendthisurl        = "Diese URL senden";

$lang_sendfile           = "Datei senden";

$lang_keywords           = "Schl&uuml;sselworte";

$lang_permissions        = "Berechtigungen";

$lang_newpermissions     = "Neue Berechtigungen";

$lang_description        = "Beschreibung";

$lang_change             = "Ver&auml;ndern";

$lang_reset              = "Zur&uuml;cksetzen";

$lang_create             = "Anlegen";



$lang_everyoneread       = "Jeder kann lesen/herunterladen";

$lang_everyonewrite      = "Jeder kann lesen/schreiben/herunterladen";

$lang_everyonewrite_nod  = "Jeder kann lesen/schreiben/herunterladen, aber NICHT L&ouml;schen";

$lang_groupread          = "Ihre Gruppe kann lesen/herunterladen";

$lang_groupwrite         = "Ihre Gruppe kann lesen/schreiben/herunterladen";

$lang_groupwrite_nod     = "Ihre Gruppe kann lesen/schreiben/herunterladen, aber NICHT l&ouml;schen";

$lang_onlyyou            = "Nur Sie k&ouml;nnen lesen/schreiben/herunterladen";

$lang_groupwrite_worldread = "Ihre Gruppe kann lesen/schreiben/herunterladen & jeder kann lesen";

$lang_groupwrite_worldread_nod = "Ihre Gruppe kann lesen/schreiben/herunterladen, aber NICHT l&ouml;schen & jeder kann lesen";

$lang_ggroupwrite_worldread = "Ihre Gruppe kann Dateien hochladen und dieses Verzeichnis l&ouml;schen & jeder kann den Inhalt lesen";

$lang_ggroupwrite_worldread_nod = "Ihre Gruppe kann Dateien in dieses Verzeichnis hochladen & jeder kann den Inhalt lesen, aber NICHT l&ouml;schen";



// BEGIN BUG FIX: #449394 add document policy bugs for admin

// Please translate and send back 

// bozz@bozzit.com

$lang_everyoneread_ad    = "Jeder kann lesen/herunterladen";

$lang_everyonewrite_ad   = "Jeder kann lesen/schreiben/herunterladen";

$lang_everyonewrite_ad_nod = "Jeder kann lesen/schreiben/herunterladen, aber NICHT l&ouml;schen";

$lang_groupread_ad       = "Die ausgew&auml;hlte Gruppe kann lesen/herunterladen";

$lang_groupwrite_ad      = "Die ausgew&auml;hlte Gruppe kann lesen/schreiben/herunterladen";

$lang_groupwrite_ad_nod  = "Die ausgew&auml;hlte Gruppe kann lesen/schreiben/herunterladen, aber NICHT l&ouml;schen";

$lang_onlyyou_ad         = "Nur der Eigent&uuml;mer (!) kann lesen/schreiben/herunterladen";

$lang_groupwrite_worldread_ad = "Die ausgew&auml;hlte Gruppe kann lesen/schreiben/herunterladen & jeder kann lesen";

$lang_groupwrite_worldread_ad_nod = "Die ausgew&auml;hlte Gruppe kann lesen/schreiben/herunterladen, aber NICHT l&ouml;schen & jeder kann lesen";

$lang_ggroupwrite_worldread_ad = "Die ausgew&auml;hlte Gruppe kann Dateien hochladen und dieses Verzeichnis l&ouml;schen & jeder kann den Inhalt lesen";

$lang_ggroupwrite_worldread_ad_nod = "Die ausgew&auml;hlte Gruppe kann Dateien in dieses Verzeichnis hochladen & jeder kann den Inhalt lesen, aber NICHT l&ouml;schen";



// END BUG FIX: #449394 add document policy bugs for admin



$lang_geveryoneread_ad   = "Jeder kann dieses Verzeichnis lesen";

$lang_geveryonewrite_ad  = "Jeder kann Dateien einstellen und das Verzeichnis l&ouml;schen";

$lang_geveryonewrite_ad_nod = "Jeder kann Dateien in dieses Verzeichnis speichern";

$lang_ggroupread_ad      = "Ihre Gruppe kann dieses Verzeichnis lesen";

$lang_ggroupwrite_ad     = "Ihre Gruppe kann Dateien einstellen und das Verzeichnis l&ouml;schen";

$lang_ggroupwrite_ad_nod = "Die ausgew&auml;hlte Gruppe kann Dateien in dieses Verzeichnis speichern";

$lang_gonlyyou_ad        = "Nur Sie k&ouml;nnen Dateien erstellen und das Verzeichnis l&ouml;schen";



$lang_geveryoneread      = "Jeder kann dieses Verzeichnis lesen";

$lang_geveryonewrite     = "Jeder kann Dateien einstellen und das Verzeichnis l&ouml;schen";

$lang_geveryonewrite_nod = "Jeder kann Dateien in dieses Verzeichnis speichern";

$lang_ggroupread         = "Ihre Gruppe kann dieses Verzeichnis lesen";

$lang_ggroupwrite        = "Ihre Gruppe kann Dateien einstellen und das Verzeichnis l&ouml;schen";

$lang_ggroupwrite_nod    = "Ihre Gruppe kann Dateien in dieses Verzeichnis speichern";

$lang_gonlyyou           = "Nur Sie k&ouml;nnen Dateien erstellen und das Verzeichnis l&ouml;schen";



$lang_nofolderaccess     = "Sie haben keinen Zugriff auf das Verzeichnis";

$lang_nofileaccess       = "Sie haben keinen Zugriff auf die Datei";

$lang_nofilemod          = "Sie haben keine Berechtigung zum &Auml;ndern dieser Datei";

$lang_nofiledelete       = "Sie haben keine Berechtigung zum L&ouml;schen dieser Datei";

$lang_noupload           = "Sie haben keine Berechtigung, Dateien in dieses Verzeichnis einzustellen";

$lang_nosubfolder        = "Sie haben hier keine Berechtigung, Unterverzeichnisse zu erstellen";

$lang_nofoldermod        = "Sie haben keine Berechtigung, dieses Verzeichnis zu ver&auml;ndern";

$lang_nofolderdelete     = "Sie haben keine Berechtigung, dieses Verzeichnis zu l&ouml;schen";

$lang_noemail            = "Sie haben keine Berechtigung, diese Datei zu versenden";

$lang_fileexists         = "Die Datei, die Sie versuchen einzustellen, existiert bereits";

$lang_specifyname        = "Bitte gehen Sie zur&uuml;ck und vergeben einen Namen";



$lang_users              = "Benutzer";

$lang_groups             = "Gruppen";

$lang_group              = "Gruppe";

$lang_username           = "Benutzername";

$lang_password           = "Passwort";

$lang_newpassword        = "Neues Passwort";

$lang_oldpassword        = "Altes Passwort";

$lang_confpassword       = "Neues Passwort best&auml;tigen";

$lang_email              = "Email-Adresse";

$lang_emailing           = "Emailing";

$lang_email_to           = "Email an";

$lang_email_reply_to     = "Antwort an";

$lang_email_cc           = "Kopie an";

$lang_email_subject      = "Betreff";

$lang_email_body         = "Nachricht";

$lang_notification       = "Benachrichtigungen versenden";

$lang_attach_file        = "Datei anh&auml;ngen";

$lang_change             = "&Auml;ndern";

$lang_deleteuser         = "Benutzer l&ouml;schen";

$lang_deletegroup        = "Gruppe l&ouml;schen";

$lang_saved              = "&Auml;nderungen speichern";

$lang_add                = "Hinzuf&uuml;gen";

$lang_quota              = "Mengenbegrenzung";

$lang_disableuser        = "Benutzer deaktivieren";

$lang_noprefaccess       = "Einstellungen deaktivieren ";

$lang_toomanysessions    = "Maximale Anzahl an Sitzungen f&uuml;r diesen Benutzer erreicht, kontaktieren Sie den Systemadministrator";

$lang_maxsessions        = "Maximale Anzahl an Sitzungen";

$lang_preference         = "Einstellungen";

$lang_moving             = "Verschieben";

$lang_select             = "W&auml;hlen Sie ein neues Verzeichnis:";

$lang_updating           = "Aktualiseren";

$lang_cancel_button      = "Abbrechen";

//$lang_return             = "Zur&uuml;ck zum Dateiverzeichnis";





// Bozz Change Begin

$lang_email_alt          = "Datei per Email versenden";

$lang_del_folder_alt     = "Verzeichnis l&ouml;schen (J/N)";

$lang_mod_folder_alt     = "Verzeichniseigenschaften &auml;ndern";

$lang_move_folder_alt    = "Verzeichnis verschieben";



$lang_del_file_alt       = "Datei l&ouml;schen (J/N)";

$lang_mod_file_alt       = "Dateieigenschaften &auml;ndern";

$lang_move_file_alt      = "Datei verschieben";

$lang_upd_file_alt       = "Datei aktualisieren";

$lang_get_file_alt       = "Datei herunterladen";

$lang_view_file_alt      = "Datei ansehen";

$lang_newuser_alt        = "Benutzer anlegen";

$lang_newgroup_alt       = "Gruppe anlegen";

$lang_flush_sessions_alt = "h&auml;ngengebliebene Sessions l&ouml;schen";

$lang_edthtml_alt        = "HTML-Voreinstellungen der Site bearbeiten";

$lang_edprefs_alt        = "Features und Einstellungen der Site bearbeiten";

$lang_backup_alt         = "SQL-Datenbank sichern";

$lang_backup_dis_alt     = "Backup-Tool oder gzip nicht gefunden";



// Bozz Change End

// begin Daphne change

$lang_log_file           = "Log-Datei";

$lang_lock_file          = "Datei aus-/einchecken";

// end Daphne Change

$lang_notif_subject_new  = "Neue OWL Intranet Datei";

$lang_notif_subject_upd  = "Eine OWL Intranet Datei wurde aktualisiert";

$lang_notif_msg          = "DIES IST EINE AUTOMATISCHE MITTEILUNG VON  OWL Intranet.\n\nDie neue oder ge&auml;nderte Datei ist angeh&auml;ngt.\n\n";

// $lang_notif_msg         = "DIESE MAIL WURDE AUTOMATISCH VON OWL Intranet GENERIERT\n\nUm die neuen/ ge&auml;nderten Dateien zu sehen, klicken Sie bitte auf den Link unten.\n\n";



// Diego Bugfix

$lang_admin              = "Administration";

$lang_user               = "Benutzer";

$lang_search             = "Suchen";

$lang_search_results_for = "Suchergebnisse f&uuml;r";

$lang_score              = "Treffer";

$lang_ownergroup         = "Besitzer (Gruppe)";

$lang_name               = "Name";

//Diego Bugfix end

$lang_query_empty        = "Bitte geben Sie einen Suchbegriff ein.";





$lang_err_file_update    = "Um eine Datei zu aktualisieren, m&uuml;ssen die Dateinamen &uuml;bereinstimmen. Benutzen Sie bitte den in OWL Intranet gespeicherten Dateinamen";

$lang_err_quota          = "Sie haben nicht genug Speicherplatz, um die Datei hochzuladen: ";

$lang_err_quota_needed   = " ben&ouml;tigt ";

$lang_err_quota_avail    = " verf&uuml;gbar.";

$lang_err_quota_exceed   = "Sie haben keinen Speicherplatz mehr zur Verf&uuml;gung ";

$lang_err_upload         = "Fehler beim Hochladen der Datei";

$lang_err_folder_exist   = "Das Verzeichnis existiert bereits";

$lang_err_folder_create  = "Verzeichnis konnte nicht angelegt werden";

//Begin Daphne Change - backup folder and file creation errors

$lang_err_backup_folder_create = "Fehler beim Erzeugen des Backup-Verzeichnisses";

$lang_err_backup_file    = "Fehler beim Erzeugen der Backup-Datei";

$lang_err_file_lock      = "Sie k&ouml;nnen diese Datei nicht auchecken, da sie bereits verwendet wird von ";

// end Daphne change

$lang_err_unauthorized   = "Nicht autorisiert";

$lang_err_unauth_area    = "Sie haben keine Berechtigung f&uuml;r diesen Bereich";

$lang_err_pass_wrong     = "Das alte Passwort ist falsch";

$lang_err_pass_missmatch = "Das neue und das Best&auml;tigungspasswort stimmen nicht &uuml;berein.";

$lang_err_user_exists    = "Benutzername existiert bereits";

$lang_err_general        = "Ein Fehler ist aufgetreten";

$lang_err_lang_1         = "Spracheinstellung wurde gefunden, aber ";

$lang_err_lang_2         = " wurde nicht gefunden";

$lang_err_lang_notfound  = "Konnte Spracheinstellung nicht feststellen, bitte in owl.lib.php spezifizieren";

$lang_err_sess_write     = "Konnte Sessioninformationen nicht in die Datenbank schreiben";

$lang_err_sess_notvalid  = "Sitzungs-ID ist ung&uuml;ltig";

$lang_err_bad_version_1  = "Owl Intranet ben&ouml;tigt zumindest PHP Version";

$lang_err_bad_version_2  = "Sie verwenden Version";

$lang_err_bad_version_3  = "Installieren Sie eine neuere PHP Version und versuchen Sie es erneut";



$lang_err_folderexists   = "Umbenennen abgebrochen.<BR>Ein Verzeichnis mit diesem Namen existiert bereits!";

$lang_err_movecancel     = "Verschieben wurde abgebrochen.<BR>";

$lang_err_email          = "Die Nachricht konnte nicht verschickt werden.";

$lang_err_fileexists     = "Verschieben abgebrochen.<BR>Eine Datei mit diesem Namen existiert bereits!";

$lang_err_nameempty      = "Der Verzeichnisname darf nicht leer sein.";



//janu's change BEGIN 

$lang_engine             = "Owl Intranet Engine";

$lang_version            = "Version";

$lang_username           = "Benutzername";

$lang_password           = "Passwort";

$lang_owl_admin          = "Owl Administration";

$lang_upg_MD5            = "Passw&ouml;rter nach MD5 konvertieren";

//janu's change END 



$lang_viewlog            = "Zeige Log f&uuml;r ";

$lang_viewing            = "Anzeige ";

$lang_flushed            = "Die Sitzungstabelle wurde bereinigt. Der Nutzer kann sich wieder anmelden!<BR>";



// Edit HTML Properties Messages



$lang_ht_tbl_border_sz   = "Tabellengr&ouml;sse:";

$lang_ht_tbl_hd_bg       = "Hintergrundfarbe des Tabellenkopfs:";

$lang_ht_tbl_cell_bg_cl  = "Erste Hintergrundfarbe der Tabellenfelder:";

$lang_ht_tbl_cell_bg_al  = "Zweite Hintergrundfarbe der Tabellenfelder:";

$lang_ht_tbl_bg_cl       = "Hintergrundfarbe der Tabelle:";

$lang_ht_expand_width    = "Breite Erweiterte Ansicht:";

$lang_ht_collapse_width  = "Breite Normalansicht:";

$lang_ht_bd_bg_cl        = "Hintergrundfarbe:";

$lang_ht_bd_txt_cl       = "Textfarbe:";

$lang_ht_bd_lnk_cl       = "Textfarbe f&uuml;r Links:";

$lang_ht_bd_vlnk_cl      = "Textfarbe f&uuml;r benutzte Links:";

$lang_ht_main_hd_bg_cl   = "Hintergrundfarbe des Kopfbereichs:";



// Edit Prefs Properties Messages



$lang_owl_title_email    = "EMAIL-EINSTELLUNGEN";

$lang_owl_email_from     = "Absenderadresse:";

$lang_owl_email_fromname = "Absendername:";

$lang_owl_email_replyto  = "Antwortadresse:";

$lang_owl_email_server   = "Email-Serveradresse:";

$lang_owl_title_HD       = "EINSTELLUNGEN FšR NEUE DATEIEN AUS DATEISYSTEM &UUML;BERNEHMEN";

$lang_owl_lookAtHD       = "Einstellungen im Dateisystem nicht beachten:";

$lang_owl_def_file_security = "Standard-Dateisicherheit:";

$lang_owl_def_file_group_owner = "Standardbesitzer f&uuml;r Dateigruppen:";

$lang_owl_def_file_owner = "Standardbesitzer f&uuml;r Dateien:";

$lang_owl_def_file_title = "Standard-Dateibezeichnung:";

$lang_owl_def_file_meta  = "Standard-Metadaten f&uuml;r Dateien:";

$lang_owl_def_fold_sec   = "Standard-Verzeichnissicherheit:";

$lang_owl_def_fold_group_owner  = "Standardbesitzer f&uuml;r Verzeichnisgruppen:";

$lang_owl_def_fold_owner        = "Standardbesitzer f&uuml;r Verzeichnisse:";

$lang_owl_title_other    = "ANDERE EINSTELLUNGEN";

$lang_owl_max_filesize   = "Maximale Dateigr&ouml;sse f&uuml;r Upload:";

$lang_owl_sessionTimeout    = "Session-Timeout in Sekunden:";

$lang_owl_owl_expand     = "Standardansicht(Erweitert):";

$lang_owl_version_control = "Versionskontrolle einschalten:";

$lang_owl_restrict_view  = "Eingeschr&auml;nkte Ansichten erlauben:";

$lang_owl_title_tools    = "DB BACKUP/GZIP-TOOLS";

$lang_owl_dbdump_path    = "Pfad zum DB-Dump-Tool:";

$lang_owl_gzip_path      = "Pfad zu gzip:";

$lang_owl_tar_path     	 = "Pfad zu tar";

?>

