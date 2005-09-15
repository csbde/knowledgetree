<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

class KTFolderAddDocumentAction extends KTBuiltInFolderAction {
    var $sBuiltInAction = 'addDocument';
    var $sDisplayName = 'Add Document';
}
$oKTActionRegistry->registerAction('folderaction', 'KTDocumentViewAction', 'ktcore.actions.folder.addDocument');

?>
