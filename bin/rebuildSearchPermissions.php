<?php

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/security/Permission.inc');

$aDocuments = Document::getList();
foreach ($aDocuments as $oDocument) {
    Permission::updateSearchPermissionsForDocument($oDocument->getID());
}

?>
