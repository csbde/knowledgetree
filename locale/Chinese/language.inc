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

$lang_logout		= "(登出)";
$lang_login		= "登入";
$lang_successlogout 	= "你已經成功登出了";
$lang_loginfail		= "登入失敗";
$lang_logindisabled     = "Account has been DISABLED, contact the System Adminstrator";
$lang_sessinuse		= "目前有別的電腦以這個帳號登入 , 所以無法存取 , 請重新<A HREF='$default->owl_root_url/'>登入</A>";
$lang_invalidsess	= "錯誤的連線代碼 , 請重新";
$lang_sesstimeout	= "您閒置的時間太久了 , 請重新<BR>";

$lang_addingfolder 	= "新增目錄至 ";
$lang_addingfile 	= "新增檔案至 ";
$lang_modifying		= "修改 ";
$lang_reallydelete	= "真的要刪除";

$lang_title 		= "標題";
$lang_userlang          = "Language";
// begin Daphne change
$lang_held                      = "Held"; // for version control
$lang_ver                       = "Ver."; // for version control
// end Daphne change
$lang_file 		= "檔案";
$lang_size 		= "大小";
$lang_postedby 		= "張貼者";
$lang_modified 		= "修改日期";
$lang_actions 		= "動作";
$lang_ownership		= "所有人";
$lang_newpolicy		= "新的權限";
$lang_policy 		= "權限";

$lang_sendthisfile 	= "傳送這個檔案";
$lang_sendthisurl 	= "Send this URL";
$lang_sendfile 		= "傳送檔案";
$lang_keywords 		= "關鍵字";
$lang_permissions 	= "權限";
$lang_newpermissions    = "New Permissions";
$lang_description 	= "說明";
$lang_change		= "更改";
$lang_reset		= "重設";
$lang_create		= "建立";
// begin Daphne change
$lang_vertype           = "Revision type";  // for modify.php when uploading
$lang_vermajor          = "Major Revision";
$lang_verminor          = "Minor Revision";
$lang_verdescription = "Description<br>of changes";
// end Daphne change

$lang_everyoneread	= "全部的人可以讀取/下載";
$lang_everyonewrite	= "全部的人可以讀取/寫入/下載";
$lang_everyonewrite_nod = "Everyone can read/write/download, NO DELETE";
$lang_groupread		= "只有自己的群組可以讀取/下載";
$lang_groupwrite	= "只有自己的群組可以讀取/寫入/下載";
$lang_groupwrite_nod    = "Your group can read/write/download, NO DELETE";
$lang_onlyyou		= "只有自己可以讀取/寫入/下載";
$lang_groupwrite_worldread = "Your group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_nod = "Your group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread = "Your group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_nod = "Your group can upload files to this folder & everyone else can view contents, NO DELETE";

// BEGIN BUG FIX: #449394 add document policy bugs for admin

$lang_everyoneread_ad	= "全部的人可以讀取及下載";
$lang_everyonewrite_ad	= "全部的人可以讀取/寫入/下載";
$lang_everyonewrite_ad_nod      = "Everyone can read/write/download, NO DELETE";
$lang_groupread_ad	= "選擇的群組可以讀取/下載";
$lang_groupwrite_ad	= "選擇的群組可以讀取/寫入/下載";
$lang_groupwrite_ad_nod = "The selected group can read/write/download, NO DELETE";
$lang_onlyyou_ad	= "只有自己可以讀取/寫入/下載";
$lang_groupwrite_worldread_ad = "The selected group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_ad_nod = "The selected group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread_ad = "Selected group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_ad_nod = "Selected group can upload files to this folder & everyone else can view contents, NO DELETE";


// END BUG FIX: #449394 add document policy bugs for admin

$lang_geveryoneread	= "全部的人都可以讀取這個目錄";
$lang_geveryonewrite	= "全部的人可以上傳檔案及刪除這個目錄";
$lang_geveryonewrite_nod= "Everyone can upload files to this folder";
$lang_ggroupread	= "只有自己的群組可以讀取這個目錄";
$lang_ggroupwrite	= "只有自己的群組可以上傳檔案及刪除這個目錄";
$lang_ggroupwrite_nod   = "Your group can upload files to this folder";
$lang_gonlyyou		= "只有自己可以上傳檔案及刪除這個目錄";

$lang_geveryoneread_ad	= "全部的人可以讀取這個目錄";
$lang_geveryonewrite_ad	= "全部的人可以上傳檔案及刪除這個目錄";
$lang_geveryonewrite_ad_nod     = "Everyone can upload files to this folder";
$lang_ggroupread_ad	= "選擇的群組可以讀取這個目錄";
$lang_ggroupwrite_ad	= "選擇的群組可以上傳檔案及刪除目錄";
$lang_ggroupwrite_ad_nod= "The selected group can upload files to this folder";
$lang_gonlyyou_ad	= "只有自己可以上傳檔案及刪除目錄";

$lang_nofolderaccess	= "您沒有存取這個目錄的權限";
$lang_nofileaccess	= "您沒有存取這個檔案的權限";
$lang_nofilemod		= "您沒有修改這個檔案的權限";
$lang_nofiledelete	= "您沒有刪除這個檔案的權限";
$lang_noupload		= "您沒有上傳檔案到這個目錄的權限";
$lang_nosubfolder	= "您沒有建立目錄的權限";
$lang_nofoldermod	= "您沒有修改這個目錄的權限";
$lang_nofolderdelete	= "您沒有刪除這個目錄的權限";
$lang_noemail           = "You do not have access to email this file";
$lang_fileexists	= "這個檔案已經存在了";
$lang_specifyname	= "請回上頁修改名稱";

$lang_users		= "使用者";
$lang_groups		= "群組";
$lang_group		= "主要群組";
$lang_groupmember	= "Member Group";
$lang_username		= "使用者名稱";
$lang_password		= "登入密碼";
$lang_newpassword       = "New Password";
$lang_oldpassword       = "Old Password";
$lang_confpassword      = "Confirm New Password";
$lang_email             = "Email Address";
$lang_emailing          = "Emailing";
$lang_email_to          = "Mail To";
$lang_email_cc          = "CC";
$lang_email_reply_to    = "Reply To";
$lang_email_subject     = "Subject";
$lang_email_body        = "Message";
$lang_notification      = "Receive Notifications";
$lang_attach_file       = "Attach File";
$lang_change		= "修改";
$lang_deleteuser	= "刪除使用者";
$lang_deletegroup	= "刪除群組";
$lang_saved		= "儲存";
$lang_add		= "Add";
$lang_quota		= "配額";
$lang_disableuser       = "Disable User";
$lang_noprefaccess       = "Disable Pref";
$lang_toomanysessions   = "Maximum sessions for user reached, contact the System Administraor";
$lang_maxsessions       = "Max No. Sessions";
$lang_preference        = "Preferences";
$lang_moving            = "Moving";
$lang_select            = "Please select new folder:";
$lang_updating          = "Updating";
$lang_cancel_button     = "Cancel";

// Bozz Change Begin
$lang_email_alt         = "Email this File";
$lang_del_folder_alt    = "刪除目錄 (是/否)";
$lang_mod_folder_alt    = "修改目錄屬性";
$lang_move_folder_alt   = "移動目錄";

$lang_del_file_alt      = "刪除檔案 (是/否)";
$lang_mod_file_alt      = "修改檔案屬性";
$lang_move_file_alt     = "移動檔案";
$lang_upd_file_alt      = "更新檔案";
$lang_get_file_alt      = "下載檔案";
$lang_view_file_alt     = "檢視檔案";
$lang_newuser_alt       = "Create a new user";
$lang_newgroup_alt      = "Create a new group";
$lang_flush_sessions_alt	= "flush hanging sessions";
$lang_edthtml_alt	= "Edit Site HTML Preferences";
$lang_edprefs_alt	= "Edit Site Features and Settings";
$lang_backup_alt	= "Backup SQL Database";
$lang_backup_dis_alt	= "Backup Tool or gzip was not found";

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
$lang_owl_admin 	= "Owl Administration";
$lang_upg_MD5 		= "Upgrade Users to MD5 Passwords";
//janu's change END 

$lang_viewlog		= "Viewing Log For ";
$lang_viewing		= "Viewing ";
$lang_flushed		= "Session table flushed, user can now log in again.<BR>";

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
$lang_ht_main_hd_bg_cl	= "Main Header Background Color:";

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
$lang_owl_title_tools	= "DB BACKUP/GZIP TOOLS";
$lang_owl_dbdump_path	= "DB Dump Tool Path:";
$lang_owl_gzip_path	= "gzip Path:";
$lang_owl_tar_path     = "tar Path:";
?>
