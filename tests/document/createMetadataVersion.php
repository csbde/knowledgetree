<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

error_reporting(E_ALL);

$oDocument = Document::get(2);
var_dump(DocumentUtil::createMetadataVersion($oDocument));

?>
