<?php

/*

  File: language.inc
  Author: Wen-ping Lee <liwenpin@tpts5.seed.net.tw>
  Date: 2001/08/13

  Owl: Copyright Chris Vincent <cvincent@project802.net>

  You should have received a copy of the GNU Public
  License along with this package; if not, write to the
  Free Software Foundation, Inc., 59 Temple Place - Suite 330,
  Boston, MA 02111-1307, USA.

*/

//$lang_logout 		= "<IMG SRC='$default->rootUrl/locale/$default->owl_lang/graphics/btn_logout.gif' BORDER=0>";
$lang_logout		= "(登出)";
$lang_login		= "登入";
$lang_successlogout 	= "您已經成功登出";
$lang_loginfail		= "登入失敗";
$lang_logindisabled     = "Account has been DISABLED, contact the System Adminstrator";
$lang_sessinuse		= "拒絕存取，這個連現正在被另一台電腦使用<BR><A HREF='$default->rootUrl/'>Login</A>";
$lang_invalidsess	= "無效的連線編號, 請從主頁登入<BR>";
$lang_sesstimeout	= "連線已經逾時, 請重新登入<BR>";

$lang_addingfolder 	= "新增一個資料夾到 ";
$lang_addingfile 	= "新增一個檔案到 ";
$lang_modifying		= "修改中 ";
$lang_reallydelete	= "真的要刪除";

$lang_title 		= "title";
$lang_userlang          = "Language";
// begin Daphne change
$lang_held                      = "Held"; // for version control
$lang_ver                       = "Ver."; // for version control
// end Daphne change
$lang_file 		= "檔案名稱";
$lang_size 		= "檔案大小";
$lang_postedby 		= "發表人";
$lang_modified 		= "修改";
$lang_actions 		= "動作";
$lang_ownership		= "擁有者";
$lang_newpolicy		= "新權限";
$lang_policy 		= "目前權限";

$lang_sendthisfile 	= "送出這個檔案";
$lang_sendthisurl       = "Send this URL";
$lang_sendfile 		= "送出";
$lang_keywords 		= "關鍵字";
$lang_permissions 	= "權限";
$lang_newpermissions    = "New Permissions";
$lang_description 	= "描述";
$lang_change		= "改變";
$lang_reset		= "重設";
$lang_create		= "創造";
// begin Daphne change
$lang_vertype           = "Revision type";  // for modify.php when uploading
$lang_vermajor          = "Major Revision";
$lang_verminor          = "Minor Revision";
$lang_verdescription = "Description<br>of changes";
// end Daphne change

$lang_everyoneread	= "每個人都可以讀/下載";
$lang_everyonewrite	= "每個人都可以讀/寫/下載";
$lang_everyonewrite_nod = "Everyone can read/write/download, NO DELETE";
$lang_groupread		= "你的群組可以讀/下載";
$lang_groupwrite	= "你的群組可以讀/寫/下載";
$lang_groupwrite_nod    = "Your group can read/write/download, NO DELETE";
$lang_onlyyou		= "只有你可以讀/寫/下載";
$lang_groupwrite_worldread = "Your group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_nod = "Your group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread = "Your group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_nod = "Your group can upload files to this folder & everyone else can view contents, NO DELETE";

$lang_everyoneread_ad   = "Everyone can read/download";
$lang_everyonewrite_ad  = "Everyone can read/write/download";
$lang_everyonewrite_ad_nod      = "Everyone can read/write/download, NO DELETE";
$lang_groupread_ad      = "The selected group can read/download";
$lang_groupwrite_ad     = "The selected group can read/write/download";
$lang_groupwrite_ad_nod = "The selected group can read/write/download, NO DELETE";
$lang_onlyyou_ad        = "Only you can read/download/write";
$lang_groupwrite_worldread_ad = "The selected group can read/write/download & everyone else can read";
$lang_groupwrite_worldread_ad_nod = "The selected group can read/write/download (NO DELETE) & everyone else can read";
$lang_ggroupwrite_worldread_ad = "Selected group can upload files and delete this folder & everyone else can view contents";
$lang_ggroupwrite_worldread_ad_nod = "Selected group can upload files to this folder & everyone else can view contents, NO DELETE";


$lang_geveryoneread	= "每個人都可以讀取這個資料夾";
$lang_geveryonewrite	= "每個人都可以上傳檔案和刪除這個資料夾";
$lang_geveryonewrite_nod= "Everyone can upload files to this folder";
$lang_ggroupread	= "你的群組可以讀取這個資料夾";
$lang_ggroupwrite	= "你的群組可以上傳檔案和刪除這個資料夾";
$lang_ggroupwrite_nod   = "Your group can upload files to this folder";
$lang_gonlyyou		= "只有你可以上傳檔案和刪除這個資料夾";

$lang_geveryoneread_ad	= "每個人都可以讀取這個資料夾";
$lang_geveryonewrite_ad	= "每個人都可以上傳檔案和刪除這個資料夾";
$lang_geveryonewrite_ad_nod     = "Everyone can upload files to this folder";
$lang_ggroupread_ad	= "所選擇的群組可以讀取這個資料夾";
$lang_ggroupwrite_ad	= "所選擇的群組可以上傳檔案和刪除這個資料夾";
$lang_ggroupwrite_ad_nod= "The selected group can upload files to this folder";
$lang_gonlyyou_ad	= "只有你可以上傳檔案和刪除這個資料夾";

$lang_nofolderaccess	= "你沒有權限存取這個資料夾";
$lang_nofileaccess	= "你沒有權限存取這個檔案";
$lang_nofilemod		= "你沒有權限存取這個檔案";
$lang_nofiledelete	= "你沒有權限刪除這個檔案";
$lang_noupload		= "你沒有權限上傳檔案到這個資料夾";
$lang_nosubfolder	= "你沒有權限在這裡新增資料夾";
$lang_nofoldermod	= "你沒有權限修改這個資料夾";
$lang_nofolderdelete	= "你沒有權限刪除這個資料夾";
$lang_noemail           = "You do not have access to email this file";
$lang_fileexists	= "您要上傳的檔案已經存在";
$lang_specifyname	= "請重新指定一個名稱";

$lang_users		= "使用者";
$lang_groups		= "群組";
$lang_group		= "主要群組";
$lang_groupmember	= "成員群組";
$lang_username		= "使用者名稱";
$lang_password		= "使用者密碼";
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

$lang_change		= "更改";
$lang_deleteuser	= "刪除使用者";
$lang_deletegroup	= "刪除群組";
$lang_saved		= "變更已經儲存";
$lang_add		= "增加";
$lang_quota		= "磁碟空間配額";
$lang_disableuser       = "Disable User";
$lang_noaccessuser      = "Disable Pref";
$lang_toomanysessions   = "Maximum sessions for user reached, contact the System Administrator";
$lang_maxsessions       = "Max No. Sessions";
$lang_preference        = "Preferences";
$lang_moving            = "Moving";
$lang_select            = "Please select new folder:";
$lang_updating          = "Updating";
$lang_cancel_button     = "Cancel";


// Bozz Change Begin
$lang_email_alt		= "Email this File";
$lang_del_folder_alt    = "刪除這個資料夾 (Y/N)";
$lang_mod_folder_alt    = "修改資料夾屬性";
$lang_move_folder_alt   = "移動資料夾";

$lang_del_file_alt      = "刪除這個檔案 (Y/N)";
$lang_mod_file_alt      = "變更檔案屬性";
$lang_move_file_alt     = "移動檔案";
$lang_upd_file_alt      = "更新檔案";
$lang_get_file_alt      = "下載檔案";
$lang_view_file_alt     = "檢視檔案內容";
$lang_newuser_alt       = "Create a new user";
$lang_newgroup_alt      = "Create a new group";
$lang_flush_sessions_alt        = "flush hanging sessions";
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

// Error Message Section
//
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

$lang_err_folderexists	= "Rename was Canceled<BR>Folder with the same name exists.";
$lang_err_movecancel  	= "Move was Canceled<BR>";
$lang_err_email		= "Message could not be sent";
$lang_err_fileexists	= "Move was Canceled<BR>File with the same name already exists.";
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
