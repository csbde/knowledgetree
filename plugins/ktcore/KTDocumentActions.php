<?php

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

class KTDocumentViewAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'downloadDocument';
    var $sDisplayName = 'View';

    function customiseInfo($aInfo) {
        $aInfo['alert'] =  _("This will download a copy of the document and is not the same as Checking Out a document.  Changes to this downloaded file will not be managed in the DMS.");
        return $aInfo;
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentViewAction', 'ktcore.view');

class KTDocumentEmailAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'emailDocument';
    var $sDisplayName = 'Email';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentEmailAction', 'ktcore.email');

class KTDocumentCheckInOutAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'emailDocument';
    var $sDisplayName = 'Email';

    var $_sDisablePermission = "ktcore.permissions.write";

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            $this->sBuiltInAction = 'checkInDocument';
            $this->sDisplayName = 'Checkin';
            if ($this->oDocument->getCheckedOutUserID() != $_SESSION["userID"]) {
                $this->_bDisabled = true;
                $this->_sDisabledText = sprintf(_("The document can only be checked back in by %s"), $oUser->getName());
            }
        } else {
            $this->sBuiltInAction = 'checkOutDocument';
            $this->sDisplayName = 'Checkout';
        }
        return parent::getInfo();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentCheckInOutAction', 'ktcore.checkinout');

class KTDocumentDeleteAction extends KTBuiltInDocumentActionSingle {
    var $sBuiltInAction = 'deleteDocument';
    var $sDisplayName = 'Delete';

    var $_sDisablePermission = "ktcore.permissions.write";

    function _disable() {
         if ($this->oDocument->getIsCheckedOut()) {
             $this->_sDisabledText = _("This document can't be deleted because its checked out");
             return true;
         }
         return parent::_disable();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDeleteAction', 'ktcore.delete');

class KTDocumentMoveAction extends KTBuiltInDocumentActionSingle {
    var $sBuiltInAction = 'moveDocument';
    var $sDisplayName = 'Move';

    var $_sDisablePermission = "ktcore.permissions.write";

    function _disable() {
         if ($this->oDocument->getIsCheckedOut()) {
             $this->_sDisabledText = _("This document can't be deleted because its checked out");
             return true;
         }
         return parent::_disable();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentMoveAction', 'ktcore.move');

class KTDocumentHistoryAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'viewHistory';
    var $sDisplayName = 'History';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentHistoryAction', 'ktcore.history');

class KTDocumentSubscriptionAction extends KTBuiltInDocumentAction {
    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
            $this->sBuiltInAction = 'removeSubscription';
            $this->sDisplayName = 'Unsubscribe';
        } else {
            $this->sBuiltInAction = 'addSubscription';
            $this->sDisplayName = 'Subscribe';
        }
        return parent::getInfo();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentSubscriptionAction', 'ktcore.subscription');

class KTDocumentDiscussionAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'viewDiscussion';
    var $sDisplayName = 'Discussion';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDiscussionAction', 'ktcore.discussion');

class KTDocumentArchiveAction extends KTBuiltInDocumentAction {
    var $_sDisablePermission = "ktcore.permissions.write";
    var $sBuiltInAction = 'archiveDocument';
    var $sDisplayName = 'Archive';

    function _disable() {
        if ($this->oDocument->hasCollaboration() &&
            DocumentCollaboration::documentCollaborationStarted($this->oDocument->getID()) &&
            !DocumentCollaboration::documentCollaborationDone($this->oDocument->getID())) {
            $sDisabledText = _("This document is in collaboration and cannot be archived");
        }

        if ($this->oDocument->getIsCheckedOut()) {
            $this->_sDisabledText = _("This document is checked out and cannot be archived.");
            return true;
        }
        return parent::_disable();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentArchiveAction', 'ktcore.archive');

class KTDocumentDependentAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'createDependantDocument';
    var $sDisplayName = 'Link New Doc';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDependentAction', 'ktcore.dependent');


class KTDocumentPublishAction extends KTDocumentAction {
    var $_sDisablePermission = "ktcore.permissions.write";
    var $sDisplayName = 'Publish';

    function _disable() {
        $oDocument =& $this->oDocument;
        if ($oDocument->getIsCheckedOut()) {
            $this->_sDisabledText = _("This document is checked out and cannot be archived.");
            return true;
        }
        if (DocumentCollaboration::documentIsPublished($oDocument->getID())) {
            $this->_sDisabledText = _("This document is already published.");
            return true;
        }
        if (DocumentCollaboration::documentIsPendingWebPublishing($oDocument->getID())) {
            $this->_sDisabledText = _("This document has been marked as pending publishing and the web publisher has been notified.");
            return true;
        }
        if ($oDocument->hasCollaboration()) {
            if (!DocumentCollaboration::documentCollaborationDone($oDocument->getID())) {
                $this->_sDisabledText = _("You cannot publish this document until collaboration is complete");
                return true;
            }
        }
        return parent::_disable();
    }

    function getURL() {
        return sprintf("/control.php?action=%s&fDocumentID=%d&fForPublish=1", 'viewDocument', $this->oDocument->getID(), $this->oDocument->getID());
    }

}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentPublishAction', 'ktcore.publish');

?>
