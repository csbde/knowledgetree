<?php

/*

  File: language.inc
  Author: Chris
  Modified: Alvaro Muñoz-Aycuens
  Date: 2002/10/23

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

//$lang_logout = "<IMG SRC='$default->rootUrl/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
$lang_logout = "(Salir)";
$lang_login = "Ingresar";
$lang_successlogout = "Se ha desconectado correctamente";
$lang_loginfail = "Error al ingresar";
$lang_logindisabled = "La cuenta ha sido DESHABILITADA, contacte con el Administrador del sistema";
$lang_sessinuse = "La sesión esta activa en otra maquina, acceso denegado";
$lang_invalidsess = "Identificador de sesión invalido, por favor intente acceder nuevamente en la página principal<BR>";
$lang_sesstimeout = "Sú sesión ha expirado, por favor acceda nuevamente<BR>";

$lang_addingfolder = "Anadir la carpeta a ";
$lang_addingfile = "Añadir el archivo a ";
$lang_modifying = "Modificando ";
$lang_reallydelete = "¿Esta seguro que desea borrar";

$lang_title = "Título";
$lang_userlang = "Idioma";
// begin Daphne change
$lang_held = "Bloqueado por";
// for version control
$lang_ver = "Ver.";
// for version control 
// end Daphne change
$lang_file = "Archivo";
$lang_size = "Tamaño";
$lang_postedby = "Añadida por";
$lang_modified = "Modificado";
$lang_actions = "Acciones";
$lang_ownership = "Propiedad de";
$lang_newpolicy = "Nueva Politica";
$lang_policy = "Politicas";
$lang_sendthisfile = "Mandar este archivo";
$lang_sendthisurl       = "Send this URL";
$lang_sendfile = "Mandar archivo";
$lang_keywords = "Palabras clave";
$lang_newpermissions = "Nuevos Permisos";
$lang_permissions = "Permisos";
$lang_description = "Descripción";
$lang_change = "Actualizar";
$lang_reset = "Restaurar";
$lang_create = "Crear";
// begin Daphne change
$lang_vertype = "Tipo de cambio";
// for modify.php when uploading
$lang_vermajor = "Cambios extensivos";
$lang_verminor = "Cambios mínimos";
$lang_verdescription = "Descripción de cambios (Revision)";
// end Daphne change
$lang_everyoneread = "Cualquiera puede leer/descargar";
$lang_everyonewrite = "Cualquiera puede leer/escribir/descargar";
$lang_everyonewrite_nod = "Cualquiera puede leer/escribir/descargar, NO BORRAR";
$lang_groupread = "Tú grupo puede leer/descargar";
$lang_groupwrite = "Tú grupo puede leer/escribir/descargar";
$lang_groupwrite_nod = "Tú grupo puede leer/escribir/descargar, NO BORRAR";
$lang_onlyyou = "Solamente tú puedes leer/escribir/descargar";
$lang_groupwrite_worldread = "El grupo seleccionado puede leer/escribir/descargar y cualquiera puede leer";
$lang_groupwrite_worldread_nod = "El grupo seleccionado leer/escribir/descargar (NO BORRAR) y cualquiera puede leer";
$lang_ggroupwrite_worldread = "El grupo seleccionado puede agregar archivos y borrar esta carpeta y cualquiera puede ver sus contenidos";
$lang_ggroupwrite_worldread_nod = "El grupo seleccionado puede agregar archivos a esta carpeta y cualquiera puede ver sus contenidos, NO BORRAR";
// BEGIN BUG FIX: #449394 add document policy bugs for admin
$lang_everyoneread_ad = "Cualquiera puede leer/descargar";
$lang_everyonewrite_ad = "Cualquiera puede leer/escribir/descargar";
$lang_everyonewrite_ad_nod = "Cualquiera puede leer/escribir/descargar, NO BORRAR";
$lang_groupread_ad = "Tú grupo puede leer/descargar";
$lang_groupwrite_ad = "Tú grupo puede leer/escribir/descargar";
$lang_groupwrite_ad_nod = "Tú grupo puede leer/escribir/descargar, NO BORRAR";
$lang_onlyyou_ad = "Solamente tú puedes leer/escribir/descargar";
$lang_groupwrite_worldread_ad = "El grupo seleccionado puede leer/escribir/descargar y cualquiera puede leer";
$lang_groupwrite_worldread_ad_nod = "El grupo seleccionado puede leer/escribir/descargar (NO BORRAR) y cualquiera puede leer";
$lang_ggroupwrite_worldread_ad = "El grupo seleccionado puede agregar y borrar archivos de esta carpeta y cualquiera puede ver su contenido";
$lang_ggroupwrite_worldread_ad_nod = "El grupo seleccionado puede agregar y borrar archivos de esta carpeta y cualquiera puede ver su contenido, NO BORRAR";
// END BUG FIX: #449394 add document policy bugs for admin
$lang_geveryoneread = "Cualquiera puede leer esta carpeta";
$lang_geveryonewrite = "Cualquiera puede agregar archivos y borrar esta carpeta";
$lang_geveryonewrite_nod= "Cualquiera puede agregar archivos a esta carpeta";
$lang_ggroupread = "Tú grupo puede leer esta carpeta";
$lang_ggroupwrite = "Tú grupo puede agregar archivos y borrar esta carpeta";
$lang_ggroupwrite_nod = "Tú grupo puede agregar archivos a esta carpeta";
$lang_gonlyyou = "Solamente tú puedes agregar archivos y borrar esta carpeta";
$lang_geveryoneread_ad = "Cualquiera puede leer esta carpeta";
$lang_geveryonewrite_ad = "Cualquiera puede agregar archivos y borrar esta carpeta";
$lang_geveryonewrite_ad_nod = "Cualquiera puede agregar archivos a esta carpeta";
$lang_ggroupread_ad = "El grupo seleccionado puede leer esta carpeta";
$lang_ggroupwrite_ad = "El grupo seleccionado puede agregar archivos y borrar esta carpeta";
$lang_ggroupwrite_ad_nod= "El grupo seleccionado puede agregar archivos a esta carpeta";
$lang_gonlyyou_ad = "Solamente tú puedes agregar archivos y borrar esta carpeta";
$lang_nofolderaccess = "No tienes acceso a esta carpeta";
$lang_nofileaccess = "No tienes acceso a este archivo";
$lang_nofilemod = "No tienes permiso para modificar este archivo";
$lang_nofiledelete = "No tienes permiso para borrar este archivo";
$lang_noupload = "No tienes permiso para agregar archivos a esta carpeta";
$lang_nosubfolder = "No tienes permisos para crear carpetas subordinadas aqui";
$lang_nofoldermod = "No tienes permiso para modificar esta carpeta";
$lang_nofolderdelete = "No tienes permiso para borrar esta carpeta";
$lang_noemail = "No tienes permiso para mandar por correo este archivo";
$lang_fileexists = "El archivo que intentas agregar ya existe";
$lang_specifyname = "Por favor vuelve a la página anterior y específica un nombre";

$lang_users = "Usuarios";
$lang_groups = "Grupos";
$lang_group = "Grupo Principal";
$lang_groupmember = "Miembro del Grupo";
$lang_username = "Nombre de Usuario";
$lang_password = "Contraseña";
$lang_newpassword = "Nueva Contraseña";
$lang_oldpassword = "Contraseña Anterior";
$lang_confpassword = "Confirma la Nueva Contraseña";
$lang_email = "Dirección de Correo Electrónico";
$lang_emailing = "Enviando Correo";
$lang_email_to = "Para:";
$lang_email_reply_to = "Responder a (Reply To):";
$lang_email_cc          = "CC:";
$lang_email_subject = "Asunto:";
$lang_email_body = "Mensaje";
$lang_notification = "Recibir Notificaciones";
$lang_attach_file       = "Attach File";
$lang_change = "Cambiar";
$lang_deleteuser = "Borrar Usuario";
$lang_deletegroup = "Borrar Grupo";
$lang_saved = "Cambios Guardados";
$lang_add = "Agregar";
$lang_quota = "Cuota";
$lang_disableuser = "Deshabilitar Usuario";
$lang_noprefaccess = "Deshabilitar Preferencias";
$lang_toomanysessions   = "Ha alcanzado el máximo número de sesiones permitidas, por favor contacte con el Administrador del Sistema";
$lang_maxsessions       = "Máximo Num.  de Sesiones";

// Bozz Change Begin
$lang_email_alt = "Mandar este Archivo por Correo";
$lang_del_folder_alt = "Borrar Carpeta (S/N)";
$lang_mod_folder_alt = "Modificación de las propiedades de la carpeta";
$lang_move_folder_alt = "Mover la Carpeta";
$lang_del_file_alt = "Borrar Archivo (S/N)";
$lang_mod_file_alt = "Modificar las Propiedades del Archivo";
$lang_move_file_alt = "Mover el Archivo";
$lang_upd_file_alt = "Actualizar el Archivo";
$lang_get_file_alt = "Descargar el Archivo";
$lang_view_file_alt = "Ver el Archivo";
$lang_newuser_alt = "Crear un nuevo usuario";
$lang_newgroup_alt = "Crear un nuevo groupo";
$lang_flush_sessions_alt        = "Flush Fanging Fessions";
$lang_edthtml_alt       = "Edit Site HTML Preferences";
$lang_edprefs_alt       = "Edit Site Features and Settings";
$lang_backup_alt        = "Backup SQL Database";
$lang_backup_dis_alt    = "Backup Tool or gzip was not found";

// Bozz Change End 
// begin Daphne change
$lang_log_file = "Histórico del Archivo";
$lang_lock_file = "Bloquear/Desbloquear el Archivo";
// end Daphne Change
$lang_notif_subject_new = "Nuevo archivo en OWL Intranet";
$lang_notif_subject_upd = "Un archivo de OWL Intranet fue actualizado";
$lang_notif_msg         = "THIS IS AN AUTOMATED MESSAGE FROM OWL Intranet\n\nFind the new/updated file attached to this Email\n\n";
//$lang_notif_msg = "ESTE ES UN MENSAJE GENERADO AUTOMATICAMENTE DESDE OWL Intranet\n\nHaz Click en el enlace para ver el archivo nuevo/actualizado.\n\n";
// Diego Bugfix
$lang_admin = "Administrador";
$lang_user = "Usuario";
$lang_search = "Buscar";
$lang_search_results_for = "Resultados de la busqueda sobre";
$lang_score = "Relevancia";
$lang_ownergroup = "Propietario (Grupo)";
$lang_name = "Nombre";
//Diego Bugfix end
$lang_query_empty = "Cadena de búsqueda vacia";
// Error Message Section //
$lang_err_file_update = "Para actualizar un archivo es necesario usar el mismo nombre, por favor intentelo nuevamente usando el mismo nombre de archivo";
$lang_err_quota = "No tienes suficente espacio en disco asignado para subir este archivo: ";
$lang_err_quota_needed = " Necesario ";
$lang_err_quota_avail = " Disponible.";
$lang_err_quota_exceed = "Has excedido tu cuota";
$lang_err_upload = "Error al agregar el archivo";
$lang_err_folder_exist = "Esta carpeta ya existe";
$lang_err_folder_create = "Error al crear la carpeta";
//Begin Daphne Change - backup folder and file creation errors
$lang_err_backup_folder_create = "Error al crear la carpeta de copias de seguridad";
$lang_err_backup_file = "Error al crear una copia de seguridad de un archivo ya existente";
$lang_err_file_lock = "En este momento no puede bajar este archivo, El archivo se encuentra bloqueado por";
// end Daphne change
$lang_err_unauthorized = "Acceso no autorizado";
$lang_err_unauth_area = "No estas autorizado para acceder a esta área";
$lang_err_pass_wrong = "La contraseña anterior es incorrecta";
$lang_err_pass_missmatch= "La contraseña nueva y su confirmación no coinciden";
$lang_err_user_exists = "El usuario ya existe";
$lang_err_general = "Ha ocurrido un error";
$lang_err_lang_1 = "El idioma ha sido especificado, pero ";
$lang_err_lang_2 = " este no ha sido encontrado";
$lang_err_lang_notfound = "Imposible encontrar el idioma, por favor especifiquelo en el archivo owl.lib.php";
$lang_err_sess_write = "Error al escribir la información de la sesión en la base de datos";
$lang_err_sess_notvalid = "Identificador de sesión no valido";
$lang_err_bad_version_1 = "Owl Intranet requiere como mínimo la versión de PHP";
$lang_err_bad_version_2 = "Tú ejecutas la Versión";
$lang_err_bad_version_3 = "Instala una nueva versión de php e intentalo nuevamente";

$lang_err_folderexists = "El renombramiento fue cancelado Existe una carpeta con el mismo nombre.";
$lang_err_movecancel = "La operacion mover fue cancelada";
$lang_err_email = "El mensaje no pudo ser enviado";
$lang_err_fileexists = "La operacion mover fue cancelada Un Archivo con el mismo nombre ya existe.";
$lang_err_nameempty     = "El nombre de la carpeta no puede estar en blanco";

//janu's change BEGIN
$lang_engine = "Owl Intranet Engine";
$lang_version = "Versión";
$lang_username = "Usuario";
$lang_password = "Contraseña";
$lang_owl_admin = "Administración de Owl";
$lang_upg_MD5 = "Actualizar los passwords de los usuarios a MD5";
//janu's change END 

$lang_viewlog           = "Mostrando el historico de ";
$lang_viewing           = "Mostrando ";
$lang_flushed           = "La tabla de las sessiones a sido purgada, el usuario puede volver a ingresar de nuevo.<BR>";

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
