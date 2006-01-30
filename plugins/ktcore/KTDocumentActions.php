<?php

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/PhysicalDocumentManager.inc');

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

// {{{ KTDocumentDetailsAction 
class KTDocumentDetailsAction extends KTDocumentAction {
    var $sDisplayName = 'Display Details';
    var $sName = 'ktcore.actions.document.displaydetails';

    function do_main() {
        redirect(generateControllerLink('viewDocument',sprintf(_('fDocumentId=%d'),$this->oDocument->getId())));
        exit(0);
    }
}
// }}}

// {{{ KTDocumentViewAction
class KTDocumentViewAction extends KTDocumentAction {
    var $sDisplayName = 'Download';
    var $sName = 'ktcore.actions.document.view';

    function customiseInfo($aInfo) {
        $aInfo['alert'] =  _("This will download a copy of the document and is not the same as Checking Out a document.  Changes to this downloaded file will not be managed in the DMS.");
        return $aInfo;
    }

    function do_main() {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $aOptions = array();
        $iVersion = KTUtil::arrayGet($_REQUEST, 'version');
        if ($iVersion) {
            $oVersion = KTDocumentContentVersion::get($iVersion);
            $aOptions['version'] = sprintf("%d.%d", $oVersion->getMajorVersionNumber(), $oVersion->getMinorVersionNumber());;
            $oStorage->downloadVersion($this->oDocument, $iVersion);
        } else {
            $oStorage->download($this->oDocument);
        }
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, "Document downloaded", 'ktcore.transactions.download', $aOptions);
        $oDocumentTransaction->create();
        exit(0);
    }
}
// }}}

// {{{ KTDocumentCheckOutAction
class KTDocumentCheckOutAction extends KTDocumentAction {
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
        // since we actually check the doc out, then download it ...
        if (($_REQUEST[$this->event_var] == 'checkout_final') && ($this->oDocument->getCheckedOutUserID() == $_SESSION['userID'])) { 
             return true; 
        }
        
        // "normal".
        if ($this->oDocument->getIsCheckedOut()) {
            $_SESSION['KTErrorMessage'][] = _("This document is already checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("checkout");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout');
        $checkout_fields = array();
        $checkout_fields[] = new KTStringWidget(_('Reason'), _('The reason for the checkout of this document for historical purposes, and to inform those who wish to check out this document.'), 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'checkout_fields' => $checkout_fields,
        ));
        return $oTemplate->render();
    }

    function do_checkout() {
        $aErrorOptions = array(
            'redirect_to' => array('','fDocumentId=' . $this->oDocument->getId()),
            'message' => "You must provide a reason"
        );
        
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout_final');
        $sReason = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'reason'), $aErrorOptions);


        // flip the checkout status
        $this->oDocument->setIsCheckedOut(true);
        // set the user checking the document out
        $this->oDocument->setCheckedOutUserID($_SESSION["userID"]);
        // update it
        if (!$this->oDocument->update()) {
            $_SESSION['KTErrorMessage'][] = _("There was a problem checking out the document.");
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

        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, $sReason, 'ktcore.transactions.check_out');
        $oDocumentTransaction->create();

        $oTemplate->setData(array(
            'context' => &$this,
            'reason' => $sReason,
        ));
        return $oTemplate->render();
    }

    function do_checkout_final() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $this->oValidator->notEmpty($sReason);

        
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oStorage->download($this->oDocument);
        exit(0);
    }
}
// }}}

// {{{ KTDocumentCheckInAction
class KTDocumentCheckInAction extends KTDocumentAction {
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
            $_SESSION['KTErrorMessage'][] = _("This document is not checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            $_SESSION['KTErrorMessage'][] = _("This document is checked out, but not by you");
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
        $checkin_fields[] = new KTFileUploadWidget(_('File'), _('The updated document.'), 'file', "", $this->oPage, true);
        $checkin_fields[] = new KTStringWidget(_('Description'), _('Describe the changes made to the document.'), 'reason', $sReason, $this->oPage, true);

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
            $this->errorRedirectToMain(_("No file was uploaded"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }

        // and that the filename matches
        global $default;
        $default->log->info("checkInDocumentBL.php uploaded filename=" . $_FILES['file']['name'] . "; current filename=" . $this->oDocument->getFileName());
        if ($this->oDocument->getFileName() != $_FILES['file']['name']) {
            $this->errorRedirectToMain(_("The file name of the uploaded file does not match the file name of the document in the system"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }

        $res = KTDocumentUtil::checkin($this->oDocument, $_FILES['file']['tmp_name'], $sReason, $this->oUser);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(_("An error occurred while trying to check in the document"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }
        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $this->oDocument->getID());
    }
}
// }}}


// {{{ KTDocumentCheckInAction
class KTDocumentCancelCheckOutAction extends KTDocumentAction {
    var $sDisplayName = 'Cancel Checkout';
    var $sName = 'ktcore.actions.document.cancelcheckout';

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
            $_SESSION['KTErrorMessage'][] = _("This document is not checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            $_SESSION['KTErrorMessage'][] = _("This document is checked out, but not by you");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("cancel checkin");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/cancel_checkout');
        
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason', "");
        $checkin_fields = array();
        $checkin_fields[] = new KTStringWidget(_('Reason'), _('Give a reason for cancelling this checkout.'), 'reason', $sReason, $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'checkin_fields' => $checkin_fields,
            'document' => $this->oDocument,
        ));
        return $oTemplate->render();
    }

    function do_checkin() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $sReason = $this->oValidator->notEmpty($sReason);
        
        global $default;

        $this->startTransaction();
        // actually do the checkin.
        $this->oDocument->setIsCheckedOut(0);
        $this->oDocument->setCheckedOutUserID(-1);
        if (!$this->oDocument->update()) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_("Failed to force the document's checkin."),sprintf('fDocumentId=%d'),$this->oDocument->getId());
        }
        
        // checkout cancelled transaction
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, "Document checked out cancelled", 'ktcore.transactions.force_checkin');
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res) || ($res == false)) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_("Failed to force the document's checkin."),sprintf('fDocumentId=%d'),$this->oDocument->getId());
        }
        $this->commitTransaction(); // FIXME do we want to do this if we can't created the document-transaction?
        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $this->oDocument->getID());
    }
}
// }}}


// {{{ KTDocumentEditAction
class KTDocumentEditAction extends KTDocumentAction {
    var $sDisplayName = 'Edit metadata';
    var $sName = 'ktcore.actions.document.edit';

    function getURL() {
        return generateControllerLink("editDocument", sprintf("fDocumentId=%d", $this->oDocument->getID()));
    }
}
// }}}

// {{{ KTDocumentDeleteAction
class KTDocumentDeleteAction extends KTDocumentAction {
    var $sDisplayName = 'Delete';
    var $sName = 'ktcore.actions.document.delete';

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
            $_SESSION["KTErrorMessage"][]= _("This document can't be deleted because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("delete");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/delete');
        $delete_fields = array();
        $delete_fields[] = new KTStringWidget(_('Reason'), _('The reason for this document to be removed.'), 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'delete_fields' => $delete_fields,
        ));
        return $oTemplate->render();
    }

    function do_delete() {
        global $default;
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $this->oValidator->validateString($sReason, 
            array('redirect_to' => array('', sprintf('fDocumentId=%d', $this->oDocument->getId()))));
        
        
        $res = KTDocumentUtil::delete($this->oDocument, $sReason);
        if (PEAR::isError($res)) {
            $_SESSION['KTErrorMessage'][] = $res->getMessage();
            controllerRedirect('viewDocument',sprintf('fDocumentId=%d', $this->oDocument->getId()));
        } else {
            $_SESSION['KTInfoMessage'][] = sprintf(_('Document "%s" Deleted.'),$this->oDocument->getName());
        }
        
        
        controllerRedirect('browse', 'fFolderId=' .  $this->oDocument->getFolderId());
        exit(0);
    }
}
// }}}


class KTDocumentMoveColumn extends TitleColumn {
    function KTDocumentMoveColumn($sLabel, $sName, $oDocument) {
        $this->oDocument = $oDocument;
        parent::TitleColumn($sLabel, $sName);
    }
    function buildFolderLink($aDataRow) {
        $baseurl = KTUtil::arrayGet($this->aOptions, "folderurl", "");
        $kt_path_info = KTUtil::arrayGet($_REQUEST, 'kt_path_info');
        if (empty($kt_path_info)) {
            return sprintf('%s?fDocumentId=%d&fFolderId=%d', $baseurl, $this->oDocument->getId(), $aDataRow["folder"]->getId());
        } else {
            return sprintf('%s?kt_path_info=%s&fDocumentId=%d&fFolderId=%d', $baseurl, $kt_path_info, $this->oDocument->getId(), $aDataRow["folder"]->getId());
        }
    }
}

// {{{ KTDocumentMoveAction
class KTDocumentMoveAction extends KTDocumentAction {
    var $sDisplayName = 'Move';
    var $sName = 'ktcore.actions.document.move';

    var $_sShowPermission = "ktcore.permissions.write";

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
            $_SESSION["KTErrorMessage"][]= _("This document can't be deleted because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', $this->oDocument->getFolderId());
        $this->oFolder = $this->oValidator->validateFolder($iFolderId);
        $this->oDocumentFolder = $this->oValidator->validateFolder($this->oDocument->getFolderId());
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("move"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/move');
        $move_fields = array();
        $aNames = $this->oDocumentFolder->getPathArray();
        $aNames[] = $this->oDocument->getName();
        $sDocumentName = join(" &raquo; ", $aNames);
        $move_fields[] = new KTStaticTextWidget(_('Document to move'), '', 'fDocumentId', $sDocumentName, $this->oPage, false);

        $collection = new DocumentCollection();
        $collection->addColumn(new KTDocumentMoveColumn("Test 1 (title)","title", $this->oDocument));
        $qObj = new FolderBrowseQuery($this->oFolder->getId());
        $collection->setQueryObject($qObj);

        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = sprintf("?fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId());
        $collection->setBatching($resultURL, $batchPage, $batchSize);

        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");

        $collection->setSorting($displayControl, $displayOrder);

        $collection->getResults();

        $aBreadcrumbs = array();
        $folder_path_names = $this->oFolder->getPathArray();
        $folder_path_ids = explode(',', $this->oFolder->getParentFolderIds());
        if ($folder_path_ids[0] == 0) {
            $folder_path_ids = array();
        }
        $folder_path_ids[] = $this->oFolder->getId();

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = sprintf("?fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $id);
            $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
        }

        $oTemplate->setData(array(
            'context' => &$this,
            'move_fields' => $move_fields,
            'collection' => $collection,
            'collection_breadcrumbs' => $aBreadcrumbs,
        ));
        return $oTemplate->render();
    }

    function do_move() {
        $this->oPage->setBreadcrumbDetails(_("move"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/move_final');
        $sFolderPath = join(" &raquo; ", $this->oFolder->getPathArray());
        $aNames = $this->oDocumentFolder->getPathArray();
        $aNames[] = $this->oDocument->getName();
        $sDocumentName = join(" &raquo; ", $aNames);
        $move_fields = array();
        $move_fields[] = new KTStaticTextWidget(_('Document to move'), '', 'fDocumentId', $sDocumentName, $this->oPage, false);
        $move_fields[] = new KTStaticTextWidget(_('Target folder'), '', 'fFolderId', $sFolderPath, $this->oPage, false);
        $move_fields[] = new KTStringWidget(_('Reason'), _('The reason for this document to be moved.'), 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'move_fields' => $move_fields,
        ));
        return $oTemplate->render();
    }

    function do_move_final() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $aOptions = array(
            'message' => _("No reason given"),
            'redirect_to' => array('move', sprintf('fDocumentId=%d&fFolderId=%d', $this->oDocument->getId(), $this->oFolder->getId())),
        );
        $this->oValidator->notEmpty($sReason, $aOptions);

        if (!Permission::userHasFolderWritePermission($this->oFolder)) {
            $this->errorRedirectTo("main", _("You do not have permission to move a document to this location"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
            exit(0);
        }

        $this->startTransaction();

        $oOriginalFolder = Folder::get($this->oDocument->getFolderId());
        $iOriginalFolderPermissionObjectId = $oOriginalFolder->getPermissionObjectId();
        $iDocumentPermissionObjectId = $this->oDocument->getPermissionObjectId();

        if ($iDocumentPermissionObjectId === $iOriginalFolderPermissionObjectId) {
            $this->oDocument->setPermissionObjectId($this->oFolder->getPermissionObjectId());
        }

        //put the document in the new folder
        $this->oDocument->setFolderID($this->oFolder->getId());
        if (!$this->oDocument->update(true)) {
            $this->errorRedirectTo("main", _("There was a problem updating the document's location in the database"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
        }


        //move the document on the file system
        $oStorage =& KTStorageManagerUtil::getSingleton();
        if (!$oStorage->moveDocument($this->oDocument, $this->oDocumentFolder, $this->oFolder)) {
            $this->oDocument->setFolderID($this->oDocumentFolder->getId());
            $this->oDocument->update(true);
            $this->errorRedirectTo("move", _("There was a problem updating the document's location in the repository storage"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
        }

        $sMoveMessage = sprintf("Moved from %s/%s to %s/%s: %s",
            $this->oDocumentFolder->getFullPath(),
            $this->oDocumentFolder->getName(),
            $this->oFolder->getFullPath(),
            $this->oFolder->getName(),
            $sReason);

        // create the document transaction record
        
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, $sMoveMessage, 'ktcore.transactions.move');
        $oDocumentTransaction->create();

        $this->commitTransaction();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('moveDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $this->oDocument,
                "old_folder" => $this->oDocumentFolder,
                "new_folder" => $this->oFolder,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
            if (PEAR::isError($ret)) {
                $this->oDocument->delete();
                return $ret;
            }
        }
        
        controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
        exit(0);
    }
}
// }}}

// {{{ KTDocumentHistoryAction
class KTDocumentTransactionHistoryAction extends KTDocumentAction {
    var $sDisplayName = 'Transaction History';
    var $sName = 'ktcore.actions.document.transactionhistory';

    function getURL() {
        return generateControllerLink("viewDocument", sprintf("action=history&fDocumentId=%d", $this->oDocument->getID()));
    }
}
// }}}

// {{{ KTDocumentHistoryAction
class KTDocumentVersionHistoryAction extends KTDocumentAction {
    var $sDisplayName = 'Version History';
    var $sName = 'ktcore.actions.document.versionhistory';

    function getURL() {
        return generateControllerLink("viewDocument", sprintf("action=versionhistory&fDocumentId=%d", $this->oDocument->getID()));
    }
}
// }}}

// {{{ KTDocumentArchiveAction
class KTDocumentArchiveAction extends KTDocumentAction {
    var $sDisplayName = 'Archive';
    var $sName = 'ktcore.actions.document.archive';
    var $_sShowPermission = "ktcore.permissions.write";

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("archiving"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/archive');
        $fields = array();
        $fields[] = new KTStringWidget(_('Reason'), _('The reason for the archiving of this document.  This will be displayed when the archived document is to be displayed.'), 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_archive() {
    
        $aErrorOptions = array(
            'redirect_to' => array('','fDocumentId=' . $this->oDocument->getId()),
            'message' => "You must provide a reason"
        );
        
        $sReason = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'reason'), $aErrorOptions);
    
    
        $this->startTransaction();
        $this->oDocument->setStatusID(ARCHIVED);
        if (!$this->oDocument->update()) {
            $_SESSION['KTErrorMessage'][] = _("There was a database error while trying to archive this file");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, sprintf(_("Document archived: %s"), $sReason), 'ktcore.transactions.update');
        $oDocumentTransaction->create();
        
        $this->commitTransaction();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('archive', 'postValidate');
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

        $_SESSION['KTInfoMessage'][] = _("Document archived.");
        controllerRedirect('browse', 'fFolderId=' .  $this->oDocument->getFolderID());
        exit(0);
    }
}
// }}}

// {{{ KTDocumentWorkflowAction
class KTDocumentWorkflowAction extends KTDocumentAction {
    var $sDisplayName = 'Workflow';
    var $sName = 'ktcore.actions.document.workflow';
    var $_sShowPermission = "ktcore.permissions.write";

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("workflow"));
        $oTemplate =& $this->oValidator->validateTemplate("ktcore/workflow/documentWorkflow");
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);

        $oWorkflow = KTWorkflowUtil::getWorkflowForDocument($oDocument);
        $oWorkflowState = KTWorkflowUtil::getWorkflowStateForDocument($oDocument);

        $oUser =& User::get($_SESSION['userID']);
        $aTransitions = KTWorkflowUtil::getTransitionsForDocumentUser($oDocument, $oUser);
        $aWorkflows = KTWorkflow::getList('start_state_id IS NOT NULL');

        $fieldErrors = null;
        
        $transition_fields = array();
        if ($aTransitions) {
            $aVocab = array();
            foreach ($aTransitions as $oTransition) {
                $aVocab[$oTransition->getId()] = $oTransition->showDescription();
            }
            $fieldOptions = array("vocab" => $aVocab);
            $transition_fields[] = new KTLookupWidget(_('Transition to perform'), 'FIXME', 'fTransitionId', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);
            $transition_fields[] = new KTTextWidget(
                _('Reason for transition'), _('Describe the changes made to the document.'), 
                'fComments', "", 
                $this->oPage, true, null, null,
                array('cols' => 80, 'rows' => 4));
        }
        $aTemplateData = array(
            'oDocument' => $oDocument,
            'oWorkflow' => $oWorkflow,
            'oState' => $oWorkflowState,
            'aTransitions' => $aTransitions,
            'aWorkflows' => $aWorkflows,
            'transition_fields' => $transition_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_startWorkflow() {
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);
        $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        $res = KTWorkflowUtil::startWorkflowOnDocument($oWorkflow, $oDocument);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain($res->message, sprintf('fDocumentId=%s',$oDocument->getId()));
        }
        $this->successRedirectToMain(_('Workflow started'),
                array('fDocumentId' => $oDocument->getId()));
        exit(0);
    }

    function do_performTransition() {
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);
        $oTransition =& $this->oValidator->validateWorkflowTransition($_REQUEST['fTransitionId']);        

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fDocumentId=%d', $_REQUEST['fDocumentId'])),
            'message' => 'You must provide a reason for the transition'
        );

        $sComments =& $this->oValidator->validateString($_REQUEST['fComments'], $aErrorOptions);
        
        $oUser =& User::get($_SESSION['userID']);
        $res = KTWorkflowUtil::performTransitionOnDocument($oTransition, $oDocument, $oUser, $sComments);
        $this->successRedirectToMain(_('Transition performed'),
                array('fDocumentId' => $oDocument->getId()));
    }
}
// }}}

?>
