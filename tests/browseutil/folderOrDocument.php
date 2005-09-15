<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

error_reporting(E_ALL);

var_dump(KTBrowseUtil::folderOrDocument("/Root Folder/test.sxw"));
var_dump(KTBrowseUtil::folderOrDocument("/Root Folder/test.sxw/ktcore.delete"));
var_dump(KTBrowseUtil::folderOrDocument("/Root Folder/test.sxw/ktcore.delete", true));
var_dump(KTBrowseUtil::folderOrDocument("/Root Folder/Default Unit"));
var_dump(KTBrowseUtil::folderOrDocument("/Root Folder/Default Unit/ktcore.delete"));
var_dump(KTBrowseUtil::folderOrDocument("/Root Folder/Default Unit/ktcore.delete", true));

?>
