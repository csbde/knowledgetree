<?php

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

class KTDocumentDiscussionAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'viewDiscussion';
    var $sDisplayName = 'Discussion';
    var $sName = 'ktcore.actions.document.discussion';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDiscussionAction', 'ktcore.actions.document.discussion');

