<?php

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

// {{{ KTDocumentViewAction
class KTDocumentViewAction extends KTDocumentAction {
    var $sBuiltInAction = 'downloadDocument';
    var $sDisplayName = 'Download';
    var $sName = 'ktcore.actions.document.view';

    function customiseInfo($aInfo) {
        $aInfo['alert'] =  _("This will download a copy of the document and is not the same as Checking Out a document.  Changes to this downloaded file will not be managed in the DMS.");
        return $aInfo;
    }

    function do_main() {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument->getId(), "Document downloaded", DOWNLOAD);
        $oDocumentTransaction->create();
        $oStorage->download($this->oDocument);
        exit(0);
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentViewAction', 'ktcore.actions.document.view');
// }}}

// {{{ KTDocumentCheckOutAction
class KTDocumentCheckOutAction extends KTDocumentAction {
    var $sBuiltInAction = 'checkOutDocument';
    var $sDisplayName = 'Checkout';
    var $sName = 'ktcore.actions.document.checkout';

    var $_sDisablePermission = "ktcore.permissions.write";

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function check() {
        $res = parent::check();
        if ($res !== true) {
            return $res;
        }
        if ($this->oDocument->getIsCheckedOut()) {
            $_SESSION['KTErrorMessage'][] = "This document is already checked out";
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("checkout");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout');
        $checkout_fields = array();
        $checkout_fields[] = new KTStringWidget('Reason', 'The reason for the checkout of this document for historical purposes, and to inform those who wish to check out this document.', 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'checkout_fields' => $checkout_fields,
        ));
        return $oTemplate->render();
    }

    function do_checkout() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $this->oValidator->notEmpty($sReason);
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout_final');
        $oTemplate->setData(array(
            'context' => &$this,
            'reason' => $sReason,
        ));
        return $oTemplate->render();
    }

    function do_checkout_final() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $this->oValidator->notEmpty($sReason);

        // flip the checkout status
        $this->oDocument->setIsCheckedOut(true);
        // set the user checking the document out
        $this->oDocument->setCheckedOutUserID($_SESSION["userID"]);
        // update it
        if (!$this->oDocument->update()) {
            $_SESSION['KTErrorMessage'][] = "There was a problem checking out the document.";
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
        }

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('checkout', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $this->oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                $this->oDocument->delete();
                return $ret;
            }
        }

        $oDocumentTransaction = & new DocumentTransaction($this->oDocument->getID(), $sReason, CHECKOUT);
        $oDocumentTransaction->create();
        
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oStorage->download($this->oDocument);
        exit(0);
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentCheckOutAction', 'ktcore.actions.document.checkout');
// }}}

class KTDocumentCheckInAction extends KTDocumentAction {
    var $sBuiltInAction = 'checkInDocument';
    var $sDisplayName = 'Checkin';
    var $sName = 'ktcore.actions.document.checkin';

    var $_sShowPermission = "ktcore.permissions.write";

    function getInfo() {
        if (!$this->oDocument->getIsCheckedOut()) {
            return null;
        }

        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            return null;
        }
        return parent::getInfo();
    }

    function check() {
        $res = parent::check();
        if ($res !== true) {
            return $res;
        }
        if (!$this->oDocument->getIsCheckedOut()) {
            $_SESSION['KTErrorMessage'][] = "This document is not checked out";
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            $_SESSION['KTErrorMessage'][] = "This document is checked out, but not by you";
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("checkin");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkin');
        
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason', "");
        $checkin_fields = array();
        $checkin_fields[] = new KTFileUploadWidget('File', 'The updated document.', 'file', "", $this->oPage, true);
        $checkin_fields[] = new KTStringWidget('Description', 'Describe the changes made to the document.', 'reason', $sReason, $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'checkin_fields' => $checkin_fields,
        ));
        return $oTemplate->render();
    }

    function do_checkin() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $sReason = $this->oValidator->notEmpty($sReason);

        // make sure the user actually selected a file first
        if (strlen($_FILES['file']['name']) == 0) {
            $this->errorRedirectToMain("No file was uploaded", 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }

        // and that the filename matches
        global $default;
        $default->log->info("checkInDocumentBL.php uploaded filename=" . $_FILES['file']['name'] . "; current filename=" . $this->oDocument->getFileName());
        if ($this->oDocument->getFileName() != $_FILES['file']['name']) {
            $this->errorRedirectToMain("The file name of the uploaded file does not match the file name of the document in the system", 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }

        $res = KTDocumentUtil::checkin($this->oDocument, $_FILES['file']['tmp_name'], $sReason, $this->oUser);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain("An error occurred while trying to check in the document", 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }
        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $this->oDocument->getID());
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentCheckInAction', 'ktcore.actions.document.checkin');

class KTDocumentDeleteAction extends KTBuiltInDocumentActionSingle {
    var $sBuiltInAction = 'deleteDocument';
    var $sDisplayName = 'Delete';
    var $sName = 'ktcore.actions.document.delete';

    var $_sDisablePermission = "ktcore.permissions.write";

    function _disable() {
         if ($this->oDocument->getIsCheckedOut()) {
             $this->_sDisabledText = _("This document can't be deleted because its checked out");
             return true;
         }
         return parent::_disable();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDeleteAction', 'ktcore.actions.document.delete');

class KTDocumentMoveAction extends KTBuiltInDocumentActionSingle {
    var $sBuiltInAction = 'moveDocument';
    var $sDisplayName = 'Move';
    var $sName = 'ktcore.actions.document.move';

    var $_sDisablePermission = "ktcore.permissions.write";

    function _disable() {
         if ($this->oDocument->getIsCheckedOut()) {
             $this->_sDisabledText = _("This document can't be deleted because its checked out");
             return true;
         }
         return parent::_disable();
    }

    function getURL() {
        return sprintf("/control.php?action=%s&fDocumentIDs[]=%d&fReturnDocumentID=%d&fFolderID=%d", $this->sBuiltInAction, $this->oDocument->getID(), $this->oDocument->getID(), $this->oDocument->getFolderID());
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentMoveAction', 'ktcore.actions.document.move');

class KTDocumentHistoryAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'viewHistory';
    var $sDisplayName = 'History';
    var $sName = 'ktcore.actions.document.history';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentHistoryAction', 'ktcore.actions.document.history');

class KTDocumentDiscussionAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'viewDiscussion';
    var $sDisplayName = 'Discussion';
    var $sName = 'ktcore.actions.document.discussion';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDiscussionAction', 'ktcore.actions.document.discussion');

class KTDocumentArchiveAction extends KTBuiltInDocumentAction {
    var $_sDisablePermission = "ktcore.permissions.write";
    var $sBuiltInAction = 'archiveDocument';
    var $sDisplayName = 'Archive';
    var $sName = 'ktcore.actions.document.archive';

    function _disable() {
        /*
        if ($this->oDocument->hasCollaboration() &&
            DocumentCollaboration::documentCollaborationStarted($this->oDocument->getID()) &&
            !DocumentCollaboration::documentCollaborationDone($this->oDocument->getID())) {
            $sDisabledText = _("This document is in collaboration and cannot be archived");
        }
        */

        if ($this->oDocument->getIsCheckedOut()) {
            $this->_sDisabledText = _("This document is checked out and cannot be archived.");
            return true;
        }
        return parent::_disable();
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentArchiveAction', 'ktcore.actions.document.archive');

class KTDocumentDependentAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'createDependantDocument';
    var $sDisplayName = 'Link New Doc';
    var $sName = 'ktcore.actions.document.dependent';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDependentAction', 'ktcore.actions.document.dependent');


class KTDocumentPublishAction extends KTDocumentAction {
    var $_sDisablePermission = "ktcore.permissions.write";
    var $sDisplayName = 'Publish';
    var $sName = 'ktcore.actions.document.publish';

    function _disable() {
        $oDocument =& $this->oDocument;
        if ($oDocument->getIsCheckedOut()) {
            $this->_sDisabledText = _("This document is checked out and cannot be archived.");
            return true;
        }
        /*
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
        */
        return parent::_disable();
    }

    function getURL() {
        return sprintf("/control.php?action=%s&fDocumentID=%d&fForPublish=1", 'viewDocument', $this->oDocument->getID(), $this->oDocument->getID());
    }

}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentPublishAction', 'ktcore.actions.document.publish');

class KTDocumentPermissionsAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'editDocumentPermissions';
    var $sDisplayName = 'Permissions';
    var $sName = 'ktcore.actions.document.permissions';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentPermissionsAction', 'ktcore.actions.document.permissions');

class KTDocumentWorkflowAction extends KTBuiltInDocumentAction {
    var $sBuiltInAction = 'documentWorkflow';
    var $sDisplayName = 'Workflow';
    var $sName = 'ktcore.actions.document.workflow';
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentWorkflowAction', 'ktcore.actions.document.workflow');

?>
