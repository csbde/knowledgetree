<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

error_reporting(E_ALL);

$oDocument = Document::get(28);
$oUser = User::get(1);

var_dump(KTDocumentUtil::rename($oDocument, 'bob1', $oUser));
var_dump(KTDocumentUtil::rename($oDocument, 'bob2', $oUser));
var_dump(KTDocumentUtil::rename($oDocument, 'bob3', $oUser));
var_dump(KTDocumentUtil::rename($oDocument, 'bob4', $oUser));

?>