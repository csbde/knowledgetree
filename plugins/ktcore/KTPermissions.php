<?php

class KTDocumentPermissionsAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'editDocumentPermissions';
    var $sDisplayName = 'Permissions';
    var $sName = 'ktcore.actions.document.permissions';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentPermissionsAction', 'ktcore.actions.document.permissions');

