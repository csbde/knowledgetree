<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

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
    var $sName = 'ktcore.actions.document.displaydetails';

    function do_main() {
        redirect(generateControllerLink('viewDocument',sprintf(_kt('fDocumentId=%d'),$this->oDocument->getId())));
        exit(0);
    }

    function getDisplayName() {
        return _kt('Display Details');
    }
}
// }}}

// {{{ KTDocumentViewAction
class KTDocumentViewAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.view';

    function getDisplayName() {
        return _kt('Download');
    }

    function customiseInfo($aInfo) {
        $aInfo['alert'] =  _kt("This will download a copy of the document and is not the same as Checking Out a document.  Changes to this downloaded file will not be managed in the DMS.");
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
    var $sName = 'ktcore.actions.document.checkout';

    var $_sShowPermission = "ktcore.permissions.write";

    function getDisplayName() {
        return _kt('Checkout');
    }

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
            $_SESSION['KTErrorMessage'][] = _kt("This document is already checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("checkout");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout');
        $checkout_fields = array();
        $checkout_fields[] = new KTStringWidget(_kt('Reason'), _('The reason for the checkout of this document for historical purposes, and to inform those who wish to check out this document.'), 'reason', "", $this->oPage, true);

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

        $this->startTransaction();
        $res = KTDocumentUtil::checkout($this->oDocument, $sReason, $this->oUser);
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(sprintf(_kt('Failed to check out the document: %s'), $res->getMessage()));
        }
        
        $this->commitTransaction();
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
    var $sName = 'ktcore.actions.document.checkin';

    var $_sShowPermission = "ktcore.permissions.write";

    function getDisplayName() {
        return _kt('Checkin');
    }

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
            $_SESSION['KTErrorMessage'][] = _kt("This document is not checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            $_SESSION['KTErrorMessage'][] = _kt("This document is checked out, but not by you");
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
        $checkin_fields[] = new KTFileUploadWidget(_kt('File'), _('The updated document.'), 'file', "", $this->oPage, true);
        $checkin_fields[] = new KTStringWidget(_kt('Description'), _('Describe the changes made to the document.'), 'reason', $sReason, $this->oPage, true);

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
            $this->errorRedirectToMain(_kt("No file was uploaded"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }

        // and that the filename matches
        global $default;
        $default->log->info("checkInDocumentBL.php uploaded filename=" . $_FILES['file']['name'] . "; current filename=" . $this->oDocument->getFileName());
        if ($this->oDocument->getFileName() != $_FILES['file']['name']) {
            $this->errorRedirectToMain(_kt("The file name of the uploaded file does not match the file name of the document in the system"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }

        $res = KTDocumentUtil::checkin($this->oDocument, $_FILES['file']['tmp_name'], $sReason, $this->oUser);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(_kt("An error occurred while trying to check in the document"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }
        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $this->oDocument->getID());
    }
}
// }}}


// {{{ KTDocumentCheckInAction
class KTDocumentCancelCheckOutAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.cancelcheckout';

    var $_sShowPermission = "ktcore.permissions.write";

    function getDisplayName() {
        return _kt('Cancel Checkout');
    }

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
            $_SESSION['KTErrorMessage'][] = _kt("This document is not checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            $_SESSION['KTErrorMessage'][] = _kt("This document is checked out, but not by you");
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
        $checkin_fields[] = new KTStringWidget(_kt('Reason'), _('Give a reason for cancelling this checkout.'), 'reason', $sReason, $this->oPage, true);

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
            return $this->errorRedirectToMain(_kt("Failed to force the document's checkin."),sprintf('fDocumentId=%d'),$this->oDocument->getId());
        }
        
        // checkout cancelled transaction
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, "Document checked out cancelled", 'ktcore.transactions.force_checkin');
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res) || ($res == false)) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_kt("Failed to force the document's checkin."),sprintf('fDocumentId=%d'),$this->oDocument->getId());
        }
        $this->commitTransaction(); // FIXME do we want to do this if we can't created the document-transaction?
        redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $this->oDocument->getID());
    }
}
// }}}


// {{{ KTDocumentEditAction
class KTDocumentEditAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.edit';

    var $_sShowPermission = "ktcore.permissions.write";

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function getDisplayName() {
        return _kt('Edit metadata');
    }

    function getURL() {
        return generateControllerLink("editDocument", sprintf("fDocumentId=%d", $this->oDocument->getID()));
    }
}
// }}}

// {{{ KTDocumentDeleteAction
class KTDocumentDeleteAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.delete';

    var $_sShowPermission = "ktcore.permissions.delete";

    function getDisplayName() {
        return _kt('Delete');
    }

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
            $_SESSION["KTErrorMessage"][]= _kt("This document can't be deleted because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("delete");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/delete');
        $delete_fields = array();
        $delete_fields[] = new KTStringWidget(_kt('Reason'), _('The reason for this document to be removed.'), 'reason', "", $this->oPage, true);

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
        
        $fFolderId = $this->oDocument->getFolderId();
        $res = KTDocumentUtil::delete($this->oDocument, $sReason);
        if (PEAR::isError($res)) {
            $_SESSION['KTErrorMessage'][] = $res->getMessage();
            controllerRedirect('viewDocument',sprintf('fDocumentId=%d', $this->oDocument->getId()));
        } else {
            $_SESSION['KTInfoMessage'][] = sprintf(_kt('Document "%s" Deleted.'),$this->oDocument->getName());
        }
        
        
        controllerRedirect('browse', 'fFolderId=' .  $fFolderId);
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
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fDocumentId=%d&fFolderId=%d', $this->oDocument->getId(), $aDataRow["folder"]->getId()));
    }
}

// {{{ KTDocumentMoveAction
class KTDocumentMoveAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.move';

    var $_sShowPermission = "ktcore.permissions.write";

    function getDisplayName() {
        return _kt('Move');
    }

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
            $_SESSION["KTErrorMessage"][]= _kt("This document can't be deleted because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', $this->oDocument->getFolderId());
        $this->oFolder = $this->oValidator->validateFolder($iFolderId);
        $this->oDocumentFolder = $this->oValidator->validateFolder($this->oDocument->getFolderId());
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("move"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/move');
        $move_fields = array();
        $aNames = $this->oDocumentFolder->getPathArray();
        $aNames[] = $this->oDocument->getName();
        $sDocumentName = join(" &raquo; ", $aNames);
        $move_fields[] = new KTStaticTextWidget(_kt('Document to move'), '', 'fDocumentId', $sDocumentName, $this->oPage, false);

        $collection = new DocumentCollection();
        $collection->addColumn(new KTDocumentMoveColumn("Test 1 (title)","title", $this->oDocument));
        $qObj = new FolderBrowseQuery($this->oFolder->getId());
        $collection->setQueryObject($qObj);

        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
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
        $folder_path_ids[] = $this->oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $id));
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
        $this->oPage->setBreadcrumbDetails(_kt("move"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/move_final');
        $sFolderPath = join(" &raquo; ", $this->oFolder->getPathArray());
        $aNames = $this->oDocumentFolder->getPathArray();
        $aNames[] = $this->oDocument->getName();
        $sDocumentName = join(" &raquo; ", $aNames);
        $move_fields = array();
        $move_fields[] = new KTStaticTextWidget(_kt('Document to move'), '', 'fDocumentId', $sDocumentName, $this->oPage, false);
        $move_fields[] = new KTStaticTextWidget(_kt('Target folder'), '', 'fFolderId', $sFolderPath, $this->oPage, false);
        $move_fields[] = new KTStringWidget(_kt('Reason'), _('The reason for this document to be moved.'), 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'move_fields' => $move_fields,
        ));
        return $oTemplate->render();
    }

    function do_move_final() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $aOptions = array(
            'message' => _kt("No reason given"),
            'redirect_to' => array('move', sprintf('fDocumentId=%d&fFolderId=%d', $this->oDocument->getId(), $this->oFolder->getId())),
        );
        $this->oValidator->notEmpty($sReason, $aOptions);

        if (!Permission::userHasFolderWritePermission($this->oFolder)) {
            $this->errorRedirectTo("main", _kt("You do not have permission to move a document to this location"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
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
            $this->errorRedirectTo("main", _kt("There was a problem updating the document's location in the database"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
        }


        //move the document on the file system
        $oStorage =& KTStorageManagerUtil::getSingleton();
        if (!$oStorage->moveDocument($this->oDocument, $this->oDocumentFolder, $this->oFolder)) {
            $this->oDocument->setFolderID($this->oDocumentFolder->getId());
            $this->oDocument->update(true);
            $this->errorRedirectTo("move", _kt("There was a problem updating the document's location in the repository storage"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
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


class KTDocumentCopyColumn extends TitleColumn {
    function KTDocumentCopyColumn($sLabel, $sName, $oDocument) {
        $this->oDocument = $oDocument;
        parent::TitleColumn($sLabel, $sName);
    }
    function buildFolderLink($aDataRow) {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fDocumentId=%d&fFolderId=%d', $this->oDocument->getId(), $aDataRow["folder"]->getId()));
    }
}

// {{{ KTDocumentMoveAction
class KTDocumentCopyAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.copy';

    var $_sShowPermission = "ktcore.permissions.read";

    function getDisplayName() {
        return _kt('Copy');
    }

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        // new code:  hide until 3.0.1 and appropriate testing.
        return null;
        
        //return parent::getInfo();
    }

    function check() {
        $res = parent::check();
        if ($res !== true) {
            return $res;
        }
        if ($this->oDocument->getIsCheckedOut()) {
            $_SESSION["KTErrorMessage"][]= _kt("This document can't be copied because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', $this->oDocument->getFolderId());
        $this->oFolder = $this->oValidator->validateFolder($iFolderId);
        $this->oDocumentFolder = $this->oValidator->validateFolder($this->oDocument->getFolderId());
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Copy"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/copy');
        $move_fields = array();
        $aNames = $this->oDocumentFolder->getPathArray();
        $aNames[] = $this->oDocument->getName();
        $sDocumentName = join(" &raquo; ", $aNames);
        $move_fields[] = new KTStaticTextWidget(_kt('Document to copy'), '', 'fDocumentId', $sDocumentName, $this->oPage, false);
        
        $collection = new DocumentCollection();
        $collection->addColumn(new KTDocumentMoveColumn("Test 1 (title)","title", $this->oDocument));
        $qObj = new FolderBrowseQuery($this->oFolder->getId());
        $collection->setQueryObject($qObj);
        
        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;
        
        $resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
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
        $folder_path_ids[] = $this->oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $id));
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

    function do_copy() {
        $this->oPage->setBreadcrumbDetails(_kt("Copy"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/copy_final');
        $sFolderPath = join(" &raquo; ", $this->oFolder->getPathArray());
        $aNames = $this->oDocumentFolder->getPathArray();
        $aNames[] = $this->oDocument->getName();
        $sDocumentName = join(" &raquo; ", $aNames);
        $move_fields = array();
        $move_fields[] = new KTStaticTextWidget(_kt('Document to move'), '', 'fDocumentId', $sDocumentName, $this->oPage, false);
        $move_fields[] = new KTStaticTextWidget(_kt('Target folder'), '', 'fFolderId', $sFolderPath, $this->oPage, false);
        $move_fields[] = new KTStringWidget(_kt('Reason'), _('The reason for this document to be moved.'), 'reason', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'move_fields' => $move_fields,
        ));
        return $oTemplate->render();
    }

    function do_copy_final() {
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $aOptions = array(
            'message' => _kt("No reason given"),
            'redirect_to' => array('move', sprintf('fDocumentId=%d&fFolderId=%d', $this->oDocument->getId(), $this->oFolder->getId())),
        );
        $this->oValidator->notEmpty($sReason, $aOptions);

        if (!Permission::userHasFolderWritePermission($this->oFolder)) {
            $this->errorRedirectTo("main", _kt("You do not have permission to move a document to this location"), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
            exit(0);
        }
        
        // FIXME agree on document-duplication rules re: naming, etc.
        
        $this->startTransaction();

        $oNewDoc = KTDocumentUtil::copy($this->oDocument, $this->oFolder);
        if (PEAR::isError($oNewDoc)) {
            $this->errorRedirectTo("main", _kt("Failed to move document: ") . $oNewDoc->getMessage(), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
            exit(0);
        }

        $this->commitTransaction();
        
        
        // FIXME do we need to refactor all trigger usage into the util function?
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('copyDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $oNewDocument,
                "old_folder" => $this->oDocumentFolder,
                "new_folder" => $this->oFolder,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }
        
        $aOptions = array('user' => $oUser);
        $oDocumentTransaction = & new DocumentTransaction($oNewDoc, "Document copied from old version.", 'ktcore.transactions.create', $aOptions);
        $res = $oDocumentTransaction->create();
        
        $_SESSION['KTInfoMessage'][] = _kt('Document copied.');
        
        controllerRedirect('viewDocument', 'fDocumentId=' .  $oNewDoc->getId());
        exit(0);
    }
}
// }}}

// {{{ KTDocumentHistoryAction
class KTDocumentTransactionHistoryAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.transactionhistory';

    function getDisplayName() {
        return _kt('Transaction History');
    }

    function getURL() {
        return generateControllerLink("viewDocument", sprintf("action=history&fDocumentId=%d", $this->oDocument->getID()));
    }
}
// }}}

// {{{ KTDocumentHistoryAction
class KTDocumentVersionHistoryAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.versionhistory';

    function getDisplayName() {
        return _kt('Version History');
    }

    function getURL() {
        return generateControllerLink("viewDocument", sprintf("action=versionhistory&fDocumentId=%d", $this->oDocument->getID()));
    }
}
// }}}

// {{{ KTDocumentArchiveAction
class KTDocumentArchiveAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.archive';
    var $_sShowPermission = "ktcore.permissions.write";

    function getDisplayName() {
        return _kt('Archive');
    }

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("archiving"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/archive');
        $fields = array();
        $fields[] = new KTStringWidget(_kt('Reason'), _('The reason for the archiving of this document.  This will be displayed when the archived document is to be displayed.'), 'reason', "", $this->oPage, true);

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
            $_SESSION['KTErrorMessage'][] = _kt("There was a database error while trying to archive this file");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, sprintf(_kt("Document archived: %s"), $sReason), 'ktcore.transactions.update');
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

        $_SESSION['KTInfoMessage'][] = _kt("Document archived.");
        controllerRedirect('browse', 'fFolderId=' .  $this->oDocument->getFolderID());
        exit(0);
    }
}
// }}}

// {{{ KTDocumentWorkflowAction
class KTDocumentWorkflowAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.workflow';
    var $_sShowPermission = "ktcore.permissions.write";
    
    var $sHelpPage = 'ktcore/workflow.html';    

    function getDisplayName() {
        return _kt('Workflow');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("workflow"));
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
            $transition_fields[] = new KTLookupWidget(_kt('Transition to perform'), 'The transition listed will cause the document to change from its current state to the listed destination state.', 'fTransitionId', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);
            $transition_fields[] = new KTTextWidget(
                _kt('Reason for transition'), _('Describe why this document qualifies to be changed from its current state to the destination state of the transition chosen.'), 
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
        $this->successRedirectToMain(_kt('Workflow started'),
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
        $this->successRedirectToMain(_kt('Transition performed'),
                array('fDocumentId' => $oDocument->getId()));
    }
}
// }}}

?>
