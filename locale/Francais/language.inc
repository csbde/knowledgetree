<?php

/*

  File: language.inc
  Author: Bozz
  Email: bozz@bozzit.com
  Date: 2001/10/16

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/
//$lang_logout            = "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
$lang_logout            = "(Déconnexion)";
$lang_login             = "Connexion";
$lang_successlogout     = "Vous êtes bien déconnecté";
$lang_loginfail         = "Erreur de connexion";
$lang_logindisabled     = "Account has been DISABLED, contact the System Adminstrator";
$lang_sessinuse         = "Accès refusé, ce compte est en cours d'utilisation<BR><A HREF='$default->owl_root_url/'>Connexion</A>";
$lang_invalidsess       = "Index de session invalide, connectez vous depuis la page d'accueil<BR>";
$lang_sesstimeout       = "Votre session s'est terminée, veuillez vous reconnecter<BR>";

$lang_addingfolder      = "Ajout d'un dossier à ";
$lang_addingfile        = "Ajout d'un fichier à ";
$lang_modifying         = "Modification ";
$lang_reallydelete      = "Suppression effective";

$lang_title 		= "Titre";
$lang_userlang          = "Langue";
// begin Daphne change
$lang_held                      = "Held"; // for version control
$lang_ver                       = "Ver."; // for version control
// end Daphne change
$lang_file 		= "Fichier";
$lang_size 		= "Taille";
$lang_postedby 		= "Envoyé par";
$lang_modified 		= "Modifié";
$lang_actions 		= "Actions";
$lang_ownership		= "Propriétaire";
$lang_newpolicy		= "Nouveaux droits";
$lang_policy 		= "Droits";

$lang_sendthisfile 	= "Envoyer ce fichier";
$lang_sendthisurl       = "Envoyer ce URL";
$lang_sendfile 		= "Envoi fichier";
$lang_keywords 		= "Mots Clés";
$lang_permissions 	= "Permissions";
$lang_newpermissions    = "Nouvelle Permissions";
$lang_description 	= "Description";
$lang_change		= "Changement";
$lang_reset		= "Initialiser";
$lang_create		= "Créer";
// begin Daphne change
$lang_vertype           = "Revision type";  // for modify.php when uploading
$lang_vermajor          = "Major Revision";
$lang_verminor          = "Minor Revision";
$lang_verdescription = "Description<br>of changes";
// end Daphne change


$lang_everyoneread      = "Lecture/Téléchargement par tous";
$lang_everyonewrite     = "Lecture/Ecriture/Téléchargement par tous";
$lang_everyonewrite_nod = "Lecture/Ecriture/Téléchargement par tous, SANS SUPPRESSION";
$lang_groupread         = "Lecture/Téléchargement par votre groupe";
$lang_groupwrite        = "Lecture/Ecriture/Téléchargement par votre groupe";
$lang_groupwrite_nod    = "Lecture/Ecriture/Téléchargement par votre groupe, SANS SUPPRESSION";
$lang_onlyyou           = "Lecture/Ecriture/Téléchargement par vous seul";
$lang_groupwrite_worldread = "Your group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_nod = "Your group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread = "Your group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_nod = "Your group can upload files to this folder & everyone else can view contents, NO DELETE";

// BEGIN BUG FIX: #449394 add document policy bugs for admin
$lang_everyoneread_ad   = "Lecture/Téléchargement par tous";
$lang_everyonewrite_ad  = "Lecture/Ecriture/Téléchargement par tous";
$lang_everyonewrite_ad_nod      = "Lecture/Ecriture/Téléchargement par tous, SANS SUPPRESSION";
$lang_groupread_ad      = "Lecture/Téléchargement par le groupe sélectionné";
$lang_groupwrite_ad     = "Lecture/Ecriture/Téléchargement par le groupe sélectionné";
$lang_groupwrite_ad_nod = "Lecture/Ecriture/Téléchargement par le groupe sélectionné, SANS SUPPRESSION";
$lang_onlyyou_ad        = "Lecture/Ecriture/Téléchargement par vous seul";
$lang_groupwrite_worldread_ad = "The selected group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_ad_nod = "The selected group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread_ad = "Selected group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_ad_nod = "Selected group can upload files to this folder & everyone else can view contents, NO DELETE";

// END BUG FIX: #449394 add document policy bugs for admin

$lang_geveryoneread     = "Lecture du dossier par tous";
$lang_geveryonewrite    = "Téléchargement et suppression du dossier par tous";
$lang_geveryonewrite_nod= "Téléchargement par tous";
$lang_ggroupread        = "Lecture du dossier par votre groupe";
$lang_ggroupwrite       = "Téléchargement et suppression du dossier par votre groupe";
$lang_ggroupwrite_nod   = "Téléchargement du dossier par votre groupe";
$lang_gonlyyou          = "Téléchargement et suppression du dossier par vous seul";

$lang_geveryoneread_ad  = "Lecture du dossier par tous";
$lang_geveryonewrite_ad = "Téléchargement et suppression du dossier par tous";
$lang_geveryonewrite_ad_nod     = "Téléchargement du dossier par tous";
$lang_ggroupread_ad     = "Lecture du dossier par le groupe sélectionné";
$lang_ggroupwrite_ad    = "Téléchargement et suppression du dossier par le groupe sélectionné";
$lang_ggroupwrite_ad_nod= "Téléchargement du dossier par le groupe sélectionné";
$lang_gonlyyou_ad       = "Téléchargement et suppression du dossier par vous seul";

$lang_nofolderaccess    = "Vous n'avez pas accès à ce dossier";
$lang_nofileaccess      = "Vous n'avez pas accès à ce fichier";
$lang_nofilemod         = "Vous n'êtes pas autorisé à modifier ce fichier";
$lang_nofiledelete      = "Vous n'êtes pas autorisé à supprimer ce fichier";
$lang_noupload          = "Vous n'êtes pas autorisé à insérer de fichier à ce dossier";
$lang_nosubfolder       = "Vous n'êtes pas autorisé à créer des sous-dossiers";
$lang_nofoldermod       = "Vous n'êtes pas autorisé à modifier ce dossier";
$lang_nofolderdelete    = "Vous n'êtes pas autorisé à supprimer ce dossier";
$lang_noemail           = "Vous n'êtes pas autorisé à envoyer ce fichier par couriel";
$lang_fileexists        = "Le fichier que vous essayez d'insérer existe déjà";
$lang_specifyname       = "Faites un retour en arrière et précisez le nom";

$lang_users		= "Utilisateurs";
$lang_groups		= "Groupes";
$lang_group		= "Groupe Primaire";
$lang_groupmember	= "Groupe Membre";
$lang_username          = "Identifiant";
$lang_password		= "Mot de passe";
$lang_newpassword	= "Nouveau mot de passe";
$lang_oldpassword	= "Ancien mot de passe";
$lang_confpassword      = "Confirmez le nouveau mot de passe";
$lang_email             = "Couriel";
$lang_emailing          = "Emailing";
$lang_email_to          = "Mail To";
$lang_email_reply_to    = "Reply To";
$lang_email_cc          = "CC";
$lang_email_subject     = "Subject";
$lang_email_body        = "Message";
$lang_notification      = "Recevoir des notifications";
$lang_attach_file       = "Attach File";
$lang_change            = "Changer";
$lang_deleteuser        = "Supprimer un utilisateur";
$lang_deletegroup       = "Supprimer un groupe";
$lang_saved             = "Modifications sauvegardées";
$lang_add               = "Ajouter";
$lang_quota		= "Quota";
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
$lang_email_alt         = "Envoyer par couriel";
$lang_del_folder_alt    = "Supression du dossier (Y/N)";
$lang_mod_folder_alt    = "Modifier les propriétés du dossier";
$lang_move_folder_alt   = "Déplacer le dossier";

$lang_del_file_alt      = "Supprimer fichier (Y/N)";
$lang_mod_file_alt      = "Modifier les propriétés du fichier";
$lang_move_file_alt     = "Déplacer le fichier";
$lang_upd_file_alt      = "Mise à jour du fichier";
$lang_get_file_alt      = "Télécharger le fichier";
$lang_view_file_alt     = "Voir le fichier";
$lang_newuser_alt       = "Créer un Nouvel Utilisateur";
$lang_newgroup_alt      = "Créer un Nouveau Groupe";
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
$lang_notif_subject_new = "Insertion d'un nouveau fichier sur le serveur";
$lang_notif_subject_upd = "Mise à jour d'un fichier sur le serveur";
$lang_notif_msg         = "CECI EST UN MESSAGE AUTOMATIQUE\n\nTrouver ci joint le fichier ajouté ou mis à jour\n\n";

// Diego Bugfix
$lang_admin              = "Administration";
$lang_user               = "Utilisateur";
$lang_search             = "Recherche";
$lang_search_results_for = "Résultats de recherche pour";
$lang_score              = "Score";
$lang_ownergroup         = "Groupe Propriétaire";
$lang_name               = "Nom";
//Diego Bugfix end
$lang_query_empty       = "Champ de recherche est vide";

// Error Message Section
//
$lang_err_file_update   = "La mise à jour d'un fichier nécessite le même nom de fichier";
$lang_err_quota         = "Vous n'avez pas d'espace suffisant pour insérer ce fichier: ";
$lang_err_quota_needed  = " necessaire ";
$lang_err_quota_avail   = " disponible.";
$lang_err_quota_exceed  = "Vous avez dépassé votre quota";
$lang_err_upload        = "Erreur de téléchargement";
$lang_err_folder_exist  = "Ce dossier existe déjà";
$lang_err_folder_create = "Erreur dans la création du dossier";
//Begin Daphne Change - backup folder and file creation errors
$lang_err_backup_folder_create = "Backup Folder Creation error";
$lang_err_backup_file   = "Error backing up existing file";
$lang_err_file_lock     = "You cannot check this file out, it is currently held by";
// end Daphne change
$lang_err_unauthorized  = "Non autorisé";
$lang_err_unauth_area   = "Vous n'êtes pas autorisé à utiliser cet espace";
$lang_err_pass_wrong    = "L'ancien mot de passe est incorrect";
$lang_err_pass_missmatch= "Les mots de passe ne correspondent pas";
$lang_err_user_exists   = "Utilisateur déjà existant";
$lang_err_general       = "Une erreur s'est produite";
$lang_err_lang_1        = "La langue est bien spécifiée, mais ";
$lang_err_lang_2        = " n'a pas été trouvé";
$lang_err_lang_notfound = "Langue non trouvée, complétez les paramètres dans owl.lib.php";
$lang_err_sess_write    = "Erreur d'écriture dans la base de donnée";
$lang_err_sess_notvalid = "L'identifiant de session n'est pas valide";
$lang_err_bad_version_1 = "Owl Intranet a besion d'une version minimum de PHP ";
$lang_err_bad_version_2 = "Votre serveur a";
$lang_err_bad_version_3 = "L'administrateur du système doit installer une nouvelle version de PHP";

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
$lang_owl_owl_timeout   = "Session Timeout in Seconds:";
$lang_owl_owl_expand    = "Default Browser View (Expand):";
$lang_owl_version_control= "Enable Version Control Feature:";
$lang_owl_restrict_view = "Enable Restricted View Feature:";
$lang_owl_title_tools   = "DB BACKUP/GZIP TOOLS";
$lang_owl_dbdump_path   = "DB Dump Tool Path:";
$lang_owl_gzip_path     = "gzip Path:";
$lang_owl_tar_path     = "tar Path:";
?>
