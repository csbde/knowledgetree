<?php

/*

  File: language.inc
  Author: Diego Rodrigo Neufert
  Date: 2001/10/05

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

//$lang_logout          = "<IMG SRC='$default->owl_root_url/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
$lang_logout            = "(Sair)";
$lang_login             = "Acessar";
$lang_successlogout     = "Voc&ecirc; saiu do sistema.";
$lang_loginfail         = "Acesso falhou";
$lang_logindisabled     = "Account has been DISABLED, contact the System Adminstrator";
$lang_sessinuse         = "Esta sess&atilde;o esta sendo acessada de outro computador. Acesso negado.<BR><A HREF='$default->owl_root_url/'>Acessar</A>";
$lang_invalidsess       = "Sess&atilde;o inv&aacute;lida, favor clicar em sair pela p&aacute;gina principal.<BR>";
$lang_sesstimeout       = "Sess&atilde;o expirou, acesse o sistema novamente.<BR>";

$lang_addingfolder      = "Adicionando um folder a ";
$lang_addingfile        = "Adicionando um arquivo a ";
$lang_modifying         = "Modificando ";
$lang_reallydelete      = "Deseja remover? ";

$lang_title             = "Titulo";
$lang_userlang          = "Language";
// begin Daphne change
$lang_held                      = "Held"; // for version control
$lang_ver                       = "Ver."; // for version control
// end Daphne change
$lang_file              = "Arquivo";
$lang_size              = "Tamanho";
$lang_postedby          = "Adicionado por";
$lang_modified          = "Modificado";
$lang_actions           = "A&ccedil;&otilde;es";
$lang_ownership         = "Criador por";
$lang_newpolicy         = "Nova politica de acesso";
$lang_policy            = "Politica de acesso";

$lang_sendthisfile      = "Enviar este arquivo";
$lang_sendthisurl       = "Send this URL";
$lang_sendfile          = "Enviar aquivo";
$lang_keywords          = "Palavras chaves";
$lang_permissions       = "Permiss&otilde;es";
$lang_newpermissions    = "New Permissions";
$lang_description       = "Descrição";
$lang_change            = "Alterar";
$lang_reset             = "Reiniciar";
$lang_create            = "Criar";
// begin Daphne change
$lang_vertype           = "Revision type";  // for modify.php when uploading
$lang_vermajor          = "Major Revision";
$lang_verminor          = "Minor Revision";
$lang_verdescription = "Description<br>of changes";
// end Daphne change

$lang_everyoneread      = "Todos podem ler/baixar";
$lang_everyonewrite     = "Todos podem ler/escrever/baixar";
$lang_everyonewrite_nod = "Everyone can read/write/download, NO DELETE";
$lang_groupread         = "Seu grupo pode ler/baixar";
$lang_groupwrite        = "Seu grupo pode ler/escrever/baixar";
$lang_groupwrite_nod    = "Your group can read/write/download, NO DELETE";
$lang_onlyyou           = "Apenas voc&ecirc; pode ler/baixar/escrever";
$lang_groupwrite_worldread = "Your group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_nod = "Your group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread = "Your group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_nod = "Your group can upload files to this folder & everyone else can view contents, NO DELETE";

// BEGIN BUG FIX: #449394 add document policy bugs for admin

$lang_everyoneread_ad   = "Todos podem ler/baixar";
$lang_everyonewrite_ad  = "Todos podem ler/escrever/baixar";
$lang_everyonewrite_ad_nod      = "Everyone can read/write/download, NO DELETE";
$lang_groupread_ad      = "O grupo selecionado pode ler/baixar";
$lang_groupwrite_ad     = "O grupo selecionado pode ler/escrever/baixar";
$lang_groupwrite_ad_nod = "The selected group can read/write/download, NO DELETE";
$lang_onlyyou_ad        = "Apenas voc&ecirc; pode ler/escrever/baixar";
$lang_groupwrite_worldread_ad = "The selected group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_ad_nod = "The selected group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread_ad = "Selected group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_ad_nod = "Selected group can upload files to this folder & everyone else can view contents, NO DELETE";


// END BUG FIX: #449394 add document policy bugs for admin

$lang_geveryoneread     = "Todos podem ler esta pasta";
$lang_geveryonewrite    = "Todos pode enviar arquivos e apagar esta pasta";
$lang_geveryonewrite_nod= "Everyone can upload files to this folder";
$lang_ggroupread        = "Seu groupo pode ler esta pasta";
$lang_ggroupwrite       = "Seu grupo pode enviar arquivos e apagar esta pasta.";
$lang_ggroupwrite_nod   = "Your group can upload files to this folder";
$lang_gonlyyou          = "Apenas voc&ecirc; pode enviar arquivos e apagar esta pasta";

$lang_geveryoneread_ad  = "Todos podem ler esta pasta";
$lang_geveryonewrite_ad = "Todos podem enviar arquivos e apagar esta pasta";
$lang_geveryonewrite_ad_nod     = "Everyone can upload files to this folder";
$lang_ggroupread_ad     = "O grupo selecionado pode ler esta pasta";
$lang_ggroupwrite_ad    = "O grupo selecionado pode enviar arquivos e apagar esta pasta";
$lang_ggroupwrite_ad_nod= "The selected group can upload files to this folder";
$lang_gonlyyou_ad       = "Apenas voc&ecirc; pode enviar arquivos e apagar esta pasta";

$lang_nofolderaccess    = "Voc&ecirc; n&atilde;o tem acesso a esta pasta";
$lang_nofileaccess      = "Voc&ecirc; n&atilde;o tem acesso a este arquivo";
$lang_nofilemod         = "Voc&ecirc; n&atilde;o pode modificar este arquivo";
$lang_nofiledelete      = "Voc&ecirc; n&atilde;o pode apagar este arquivo";
$lang_noupload          = "Voc&ecirc; n&atilde;o pode enviar arquivos para esta pasta";
$lang_nosubfolder       = "Voc&ecirc; n&atilde;o pode criar sub-pastas aqui";
$lang_nofoldermod       = "Voc&ecirc; n&atilde;o pode modificar esta pasta";
$lang_nofolderdelete    = "Voc&ecirc; n&atilde;o pode apagar esta pasta";
$lang_noemail           = "You do not have access to email this file";
$lang_fileexists        = "O arquivo que voc&ecirc; est&aacute; tentando enviar j&aacute; existe";
$lang_specifyname       = "Favor votar e epecificar um nome";

$lang_users             = "Usu&aacute;rios";
$lang_groups            = "Grupos";
$lang_group             = "Grupo Prim&aacute;mario";
$lang_groupmember       = "Membro do Grupo";
$lang_username          = "Usu&aacute;ario";
$lang_password          = "Senha";
$lang_password          = "Password";
$lang_newpassword       = "Nova Password";
$lang_oldpassword       = "Password Antiga";
$lang_confpassword      = "Confirmar Nova Password";
$lang_email             = "Email";
$lang_emailing          = "Emailing";
$lang_email_to          = "Mail To";
$lang_email_reply_to    = "Reply To";
$lang_email_cc          = "CC";
$lang_email_subject     = "Subject";
$lang_email_body        = "Message";
$lang_notification      = "Receber Alertas";
$lang_attach_file       = "Attach File";
$lang_change            = "Alterar";
$lang_deleteuser        = "Remover Usu&aacute;rio";
$lang_deletegroup       = "Remover Grupo";
$lang_saved             = "Altera&ccedil;&otilde;es Salvas";
$lang_add               = "Adicionar";
$lang_quota             = "Cota";
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
$lang_del_folder_alt    = "Remover Pasta? (S/N)";
$lang_mod_folder_alt    = "Modificar propriedades da pasta";
$lang_move_folder_alt   = "Mover pasta";

$lang_del_file_alt      = "Remover Arquivo (S/N)";
$lang_mod_file_alt      = "Modificar propriedades do arquivo";
$lang_move_file_alt     = "Mover arquivo";
$lang_upd_file_alt      = "Atualizar arquivo";
$lang_get_file_alt      = "Baixar arquivo";
$lang_view_file_alt     = "Ver arquivo";
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

$lang_notif_subject_new = "Novo Arquivo";
$lang_notif_subject_upd = "Alteração em arquivo";
$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";
//$lang_notif_msg         = "Esta mensagem é do sistema kb.interage.com.br\n\nClick no link abaixo para ver o arquivo\n\n";

// Diego Bugfix
$lang_admin              = "Interface Administrativa";
$lang_user               = "Usu&aacute;rio";
$lang_search             = "Busca";
$lang_search_results_for = "Resultados da busca por ";
$lang_score              = "Score";
$lang_ownergroup         = "Grupo";
$lang_name               = "Nome";
//Diego Bugfix end
$lang_query_empty       = "Empty Search String";

$lang_err_file_update   = "Requer o mesmo nome de arquivo, tente novamente utilizando o mesmo nome";
$lang_err_quota         = "Insuficiente espa&ccedil;o em disco";
$lang_err_quota_needed  = " para gravar este arquivo ";
$lang_err_quota_avail   = "";
$lang_err_quota_exceed  = "Voc&ecirc; excedeu sua quota";
$lang_err_upload        = "Erro de envio";
$lang_err_folder_exist  = "Este folder j&aacute; existe";
$lang_err_folder_create = "Erro na cria&ccedil;o do folder";
$lang_err_unauthorized  = "N&atilde;o autorizado";
$lang_err_unauth_area   = "Voc&ecirc; n&atilde;o esta autorizado a utilizar esta &aacute;rea";
$lang_err_pass_wrong    = "Password antiga esta errada";
$lang_err_pass_missmatch= "Passwords n&atilde;o s&atilde;o iguais";
$lang_err_user_exists   = "Username n&atilde;o existe";
$lang_err_general       = "Um erro foi encontrado";
$lang_err_lang_1        = "Linguagem n&atilde;o foi especificada";
$lang_err_lang_2        = "";
$lang_err_lang_notfound = "Imposs&iacute;vel determinar linguagem";
$lang_err_sess_write    = "Erro escrevendo sess&atilde;o no banco de dados";
$lang_err_sess_notvalid = "Sess&atilde;o n&atilde;o &eacute; v&aacute;lida";
//Begin Daphne Change - backup folder and file creation errors
$lang_err_backup_folder_create = "Backup Folder Creation error";
$lang_err_backup_file   = "Error backing up existing file";
$lang_err_file_lock     = "You cannot check this file out, it is currently held by";

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
$lang_owl_owl_timeout   = "Session Timeout in Seconds:";
$lang_owl_owl_expand    = "Default Browser View (Expand):";
$lang_owl_version_control= "Enable Version Control Feature:";
$lang_owl_restrict_view = "Enable Restricted View Feature:";
$lang_owl_title_tools   = "DB BACKUP/GZIP TOOLS";
$lang_owl_dbdump_path   = "DB Dump Tool Path:";
$lang_owl_gzip_path     = "gzip Path:";
$lang_owl_tar_path     = "tar Path:";
?>
