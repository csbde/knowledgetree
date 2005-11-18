<?php

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

class KTDocumentEmailAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'emailDocument';
    var $sDisplayName = 'Email';
    var $sName = 'ktcore.actions.document.email';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentEmailAction', 'ktcore.actions.document.email');

