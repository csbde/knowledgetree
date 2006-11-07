<?php

/**
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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

require_once(KT_LIB_DIR . "/widgets/forms.inc.php");

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


// {{{ KTDocumentHistoryAction
class KTDocumentTransactionHistoryAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.transactionhistory';

    function getDisplayName() {
        return _kt('Transaction History');
    }

    function do_main() {
        $this->oPage->setSecondaryTitle($this->oDocument->getName());

        $this->oPage->setBreadcrumbDetails(_kt("history"));

        $aTransactions = array();
        // FIXME create a sane "view user information" page somewhere.
        // FIXME do we really need to use a raw db-access here?  probably...
        $sQuery = "SELECT DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime " .
            "FROM " . KTUtil::getTableName("document_transactions") . " AS DT INNER JOIN " . KTUtil::getTableName("users") . " AS U ON DT.user_id = U.id " .
            "INNER JOIN " . KTUtil::getTableName("transaction_types") . " AS DTT ON DTT.namespace = DT.transaction_namespace " .
            "WHERE DT.document_id = ? ORDER BY DT.datetime DESC";
        $aParams = array($this->oDocument->getId());

        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
           var_dump($res); // FIXME be graceful on failure.
           exit(0);
        }

        $aTransactions = $res;


        // render pass.
        $this->oPage->setTitle(_kt("Document History"));

        $oTemplate = $this->oValidator->validateTemplate("ktcore/document/transaction_history");
        $aTemplateData = array(
              "context" => $this,
              "document_id" => $this->oDocument->getId(),
              "document" => $this->oDocument,
              "transactions" => $aTransactions,
        );
        return $oTemplate->render($aTemplateData);
    }
}
// }}}


// {{{ KTDocumentHistoryAction
class KTDocumentVersionHistoryAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.versionhistory';

    function getDisplayName() {
        return _kt('Version History');
    }

    function do_main() {

        $this->oPage->setSecondaryTitle($this->oDocument->getName());
        $this->oPage->setBreadcrumbDetails(_kt("Version History"));

        $aMetadataVersions = KTDocumentMetadataVersion::getByDocument($this->oDocument);
        $aVersions = array();
        foreach ($aMetadataVersions as $oVersion) {
            $aVersions[] = Document::get($this->oDocument->getId(), $oVersion->getId());
        }

        // render pass.
        $this->oPage->title = _kt("Document History");

        $oTemplate = $this->oValidator->validateTemplate("ktcore/document/metadata_history");

        $aActions = KTDocumentActionUtil::getDocumentActionsByNames(array('ktcore.actions.document.view'), 'documentinfo');
        $oAction = $aActions[0];

        $oAction->setDocument($this->oDocument);

        $aTemplateData = array(
              "context" => $this,
              "document_id" => $this->oDocument->getId(),
              "document" => $this->oDocument,
              "versions" => $aVersions,
              'downloadaction' => $oAction,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_startComparison() {
        $comparison_version = KTUtil::arrayGet($_REQUEST, 'fComparisonVersion');

        $oDocument =& Document::get($this->oDocument->getId(), $comparison_version);
        if (PEAR::isError($oDocument)) {
            return $this->redirectToMain(_kt("The document you selected was invalid"));
        }
        
        if (!Permission::userHasDocumentReadPermission($oDocument)) {
            return $this->errorRedirectToMain(_kt('You are not allowed to view this document'));
        }
        $this->oDocument =& $oDocument;
        $this->oPage->setSecondaryTitle($oDocument->getName());
        $this->oPage->setBreadcrumbDetails(_kt("Select Document Version to compare against"));

        $aMetadataVersions = KTDocumentMetadataVersion::getByDocument($oDocument);
        $aVersions = array();
        foreach ($aMetadataVersions as $oVersion) {
            $aVersions[] = Document::get($oDocument->getId(), $oVersion->getId());
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/document/comparison_version_select");
        $aTemplateData = array(
              "context" => $this,
              "document_id" => $this->oDocument->getId(),
              "document" => $oDocument,
              "versions" => $aVersions,
              'downloadaction' => $oAction,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_viewComparison() {
        // this is just a redirector
        $QS = array(
            'action' => 'viewComparison',
            'fDocumentId' => $this->oDocument->getId(),
            'fComparisonVersion' => $_REQUEST['fComparisonVersion'],
        );
        
        $frag = array();
        
        foreach ($QS as $k => $v) {
            $frag[] = sprintf("%s=%s", urlencode($k), urlencode($v));
        }
        
        redirect(KTUtil::ktLink('view.php',null,implode('&', $frag)));
    }
    
    
    function getUserForId($iUserId) {
        $u = User::get($iUserId);
        if (PEAR::isError($u) || ($u == false)) { return _kt('User no longer exists'); }
        return $u->getName();
    }    
}
// }}}


// {{{ KTDocumentViewAction
class KTDocumentViewAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.view';
    var $sIconClass = 'download';    

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
            $res = $oStorage->downloadVersion($this->oDocument, $iVersion);
        } else {
            $res = $oStorage->download($this->oDocument);
        }
        
        if ($res === false) {
            $this->addErrorMessage(_kt('The file you requested is not available - please contact the system administrator if this is incorrect.'));
            redirect(generateControllerLink('viewDocument',sprintf(_kt('fDocumentId=%d'),$this->oDocument->getId())));
            exit(0);  
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

    var $_bMutator = true;
    var $_bMutationAllowedByAdmin = false;
    var $sIconClass = 'checkout';

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

    function form_checkout() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Checkout"),
            'action' => 'checkout',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt("Checkout document"),
            'context' => &$this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please specify why you are checking out this document.  It will assist other users in understanding why you have locked this file.  Please bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
            array('ktcore.widgets.boolean', array(
                'label' => _kt("Download File"),
                'description' => _kt("Indicate whether you would like to download this file as part of the checkout."),
                'name' => 'download_file',
                'value' => true,
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'download_file',
                'output' => 'download_file',
            )),            
        ));
        
        return $oForm;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("checkout"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout');

        $oForm = $this->form_checkout();

        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_checkout() {
    
        $oForm = $this->form_checkout();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
    
        $data = $res['results'];

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkout_final');
        $sReason = $data['reason'];

        $this->startTransaction();
        $res = KTDocumentUtil::checkout($this->oDocument, $sReason, $this->oUser);
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(sprintf(_kt('Failed to check out the document: %s'), $res->getMessage()));
        }
        

        
        $this->commitTransaction();
        
        if (!$data['download_file']) {
            $this->addInfoMessage(_kt("Document checked out."));
            redirect(KTBrowseUtil::getUrlForDocument($this->oDocument));
            exit(0);
        }
        
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
        $oStorage->download($this->oDocument, true);
        exit(0);
    }
}
// }}}

// {{{ KTDocumentCheckInAction
class KTDocumentCheckInAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.checkin';

    var $_sShowPermission = "ktcore.permissions.write";
    var $sIconClass = 'checkin';

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


    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Checkin Document"),
            'action' => 'checkin',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt("Checkin"),
            'context' => &$this,
            'file_upload' => true,         // otherwise the post is not received.
        ));
        
        $major_inc = sprintf("%d.%d", $this->oDocument->getMajorVersionNumber()+1, 0);
        $minor_inc = sprintf("%d.%d", $this->oDocument->getMajorVersionNumber(), $this->oDocument->getMinorVersionNumber()+1);        
        
        $oForm->setWidgets(array(
            array('ktcore.widgets.file', array(
                'label' => _kt("File"),
                'description' => sprintf(_kt('Please specify the file you wish to upload.  Unless you also indicate that you are changing its filename (see "Force Original Filename" below), this will need to be called <strong>%s</strong>'), $this->oDocument->getFilename()),
                'name' => 'file',
                'basename' => 'file',
                'required' => true,
            )),
            array('ktcore.widgets.boolean',array(
                'label' => _kt('Major Update'), 
                'description' => sprintf(_kt('If this is checked, then the document\'s version number will be increased to %s.  Otherwise, it will be considered a minor update, and the version number will be %s.'), $major_inc, $minor_inc), 
                'name' => 'major_update', 
                'value' => false,
            )),            
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please describe the changes you made to the document.  Bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
            array('ktcore.widgets.boolean',array(
                'label' => _kt('Force Original Filename'), 
                'description' => sprintf(_kt('If this is checked, the uploaded document must have the same filename as the original: <strong>%s</strong>'), $this->oDocument->getFilename()), 
                'name' => 'forcefilename', 
                'value' => true,
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
            array('ktcore.validators.boolean', array(
                'test' => 'major_update',
                'output' => 'major_update',
            )),                                   
            array('ktcore.validators.file', array(
                'test' => 'file',
                'output' => 'file',
            )),           
            array('ktcore.validators.boolean', array(
                'test' => 'forcefilename',
                'output' => 'forcefilename',
            )),                       
        ));
        
        return $oForm;
    }


    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Checkin"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/checkin');
        
        $oForm = $this->form_main();

        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_checkin() {
        $oForm = $this->form_main();
        $res = $oForm->validate();
        $data = $res['results'];
        
        $extra_errors = array();
        
        if ($data['forcefilename'] && ($data['file']['name'] != $this->oDocument->getFilename())) {
            $extra_errors['file'] = sprintf(_kt('The file you uploaded was not called "%s". If you wish to change the filename, please set "Force Original Filename" below to false. '), $this->oDocument->getFilename());
        }
        
        if (!empty($res['errors']) || !empty($extra_errors)) {
            return $oForm->handleError(null, $extra_errors);
        }
    
        $sReason = $data['reason'];
        
        $sCurrentFilename = $this->oDocument->getFileName();
        $sNewFilename = $data['file']['name'];

        $aOptions = array();
        
        if ($data['major_update']) {
            $aOptions['major_update'] = true;
        }

        if ($sCurrentFilename != $sNewFilename) {
            $aOptions['newfilename'] = $sNewFilename;
        }
      
        $res = KTDocumentUtil::checkin($this->oDocument, $data['file']['tmp_name'], $sReason, $this->oUser, $aOptions);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(_kt("An error occurred while trying to check in the document"), 'fDocumentId=' . $this->oDocument->getId() . '&reason=' . $sReason);
        }
        redirect(KTBrowseUtil::getUrlForDocument($this->oDocument));
        exit(0);
    }
}
// }}}


// {{{ KTDocumentCancelCheckOutAction
class KTDocumentCancelCheckOutAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.cancelcheckout';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAllowInAdminMode = true;
    var $bInAdminMode = null;
    var $sIconClass = 'cancel_checkout';    

    function getDisplayName() {
        return _kt('Cancel Checkout');
    }

    function getInfo() {
        if (!$this->oDocument->getIsCheckedOut()) {
            return null;
        }
        if (is_null($this->bInAdminMode)) {
            $oFolder = Folder::get($this->oDocument->getFolderId());
            if (KTBrowseUtil::inAdminMode($this->oUser, $oFolder)) { 
                $this->bAdminMode = true;
                return parent::getInfo(); 
            }                   
        } else if ($this->bInAdminMode == true) {
            return parent::getInfo();
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
        // hard override if we're in admin mode for this doc.
        if (is_null($this->bInAdminMode)) {
            $oFolder = Folder::get($this->oDocument->getFolderId());
            if (KTBrowseUtil::inAdminMode($this->oUser, $oFolder)) { 
                $this->bAdminMode = true;
                return true; 
            }                   
        } else if ($this->bInAdminMode == true) {
            return true;
        }
        if ($this->oDocument->getCheckedOutUserID() != $this->oUser->getId()) {
            $_SESSION['KTErrorMessage'][] = _kt("This document is checked out, but not by you");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Cancel Checkout"),
            'action' => 'checkin',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt("Cancel Checkout"),
            'context' => &$this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please specify why you are cancelling this document's checked-out status.  Please bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
        ));
        
        return $oForm;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("cancel checkout"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/cancel_checkout');
        
        $oForm = $this->form_main();
        
        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
            'document' => $this->oDocument,
        ));
        return $oTemplate->render();
    }

    function do_checkin() {
        $oForm = $this->form_main();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        
        $data = $res['results'];
        
        $this->startTransaction();
        // actually do the checkin.
        $this->oDocument->setIsCheckedOut(0);
        $this->oDocument->setCheckedOutUserID(-1);
        $res = $this->oDocument->update();
        if (PEAR::isError($res) || ($res === false)) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_kt("Failed to force the document's checkin."),sprintf('fDocumentId=%d'),$this->oDocument->getId());
        }
        
        // checkout cancelled transaction
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, $data['reason'], 'ktcore.transactions.force_checkin');
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res) || ($res === false)) {
            $this->rollbackTransaction();
            return $this->errorRedirectToMain(_kt("Failed to force the document's checkin."),sprintf('fDocumentId=%d'),$this->oDocument->getId());
        }
        $this->commitTransaction(); 
        redirect(KTBrowseUtil::getUrlForDocument($this->oDocument));
    }
}
// }}}


// {{{ KTDocumentDeleteAction
class KTDocumentDeleteAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.delete';

    var $_sShowPermission = "ktcore.permissions.delete";
    var $_bMutator = true;

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


    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Delete Document"),
            'action' => 'delete',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt("Delete Document"),
            'context' => &$this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please specify why you are deleting this document.  Please bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
        ));
        
        return $oForm;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Delete"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/delete');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_delete() {
        $oForm = $this->form_main();
        $res = $oForm->validate();
        $data = $res['results'];
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        
        $sReason = $data['reason'];
        
        $fFolderId = $this->oDocument->getFolderId();
        $res = KTDocumentUtil::delete($this->oDocument, $sReason);
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt("Unexpected failure deleting document: %s"), $res->getMessage()));
        } 

        $_SESSION['KTInfoMessage'][] = sprintf(_kt('Document "%s" Deleted.'),$this->oDocument->getName());
                
        controllerRedirect('browse', 'fFolderId=' .  $fFolderId);
        exit(0);
    }
}
// }}}


// {{{ KTDocumentMoveAction
class KTDocumentMoveAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.move';

    var $_sShowPermission = "ktcore.permissions.write";
    var $_bMutator = true;

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
            $_SESSION["KTErrorMessage"][]= _kt("This document can't be moved because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        $this->persistParams(array('fFolderId'));
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', $this->oDocument->getFolderId());
        $this->oFolder = $this->oValidator->validateFolder($iFolderId);
        $this->oDocumentFolder = $this->oValidator->validateFolder($this->oDocument->getFolderId());
        return true;
    }

    function do_main() {
        $oForm = $this->form_move();
        return $oForm->renderPage();
    }

    function form_move() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => sprintf(_kt("Move Document \"%s\""), $this->oDocument->getName()),
            'submit_label' => _kt("Move"),
            'identifier' => 'ktcore.actions.movedoc',
            'action' => 'move',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'fail_action' => 'main',
            'context' => $this,
        ));    

        /*
         *  This is somewhat more complex than most forms, since the "filename"
         *  and title shouldn't appear unless there's a clash.
         *
         *  This is still not the most elegant solution.
         */   
    
        $oForm->setWidgets(array(
            array('ktcore.widgets.foldercollection', array(
                'label' => _kt('Target Folder'),
			    'description' => _kt('Use the folder collection and path below select the folder into which you wish to move the document.'),
			    'required' => true,
			    'name' => 'browse',
                'folder_id' => $this->oDocument->getFolderID(),
                )),
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please specify why you are moving this document.  Bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
        ));
 
         
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
            array('ktcore.validators.entity', array(
                'class' => 'Folder',
                'test' => 'browse',
                'output' => 'browse',
            )),
        ));        
 
        // here's the ugly bit.
        
        $err = $oForm->getErrors();
        if (!empty($err['name']) || !empty($err['filename'])) {
            $oForm->addWidget(
                array('ktcore.widgets.string', array(
                    'label' => _kt("Document Title"),
                    'value' => $this->oDocument->getName(),
                    'important_description' => _kt("Please indicate a new title to use to resolve any title conflicts."),
                    'name' => 'name',
                    'required' => true,
                ))
            );
            $oForm->addValidator(
                array('ktcore.validators.string', array(
                    'output' => 'name',
                    'test' => 'name'
                ))
            );

            $oForm->addWidget(
                array('ktcore.widgets.string', array(
                    'label' => _kt("Filename"),
                    'value' => $this->oDocument->getFilename(),
                    'important_description' => _kt("Please indicate a new filename to use to resolve any conflicts."),
                    'name' => 'filename',
                    'required' => true,
                ))
            );
            $oForm->addValidator(
                array('ktcore.validators.string', array(
                    'output' => 'filename',
                    'test' => 'filename'
                ))
            );
        }
        return $oForm;
    }
    
    function do_move() {
        $oForm = $this->form_move();
        $res = $oForm->validate();
        $errors = $res['errors'];
        $data = $res['results'];
        $extra_errors = array();

        if (!is_null($data['browse'])) {
            if ($data['browse']->getId() == $this->oDocument->getFolderID()) {
                $extra_errors['browse'] = _kt("You cannot move the document within the same folder.");
            } else {
                $bNameClash = KTDocumentUtil::nameExists($data['browse'], $this->oDocument->getName());        
                if ($bNameClash && isset($data['name'])) {
                    $name = $data['name'];
                    $bNameClash = KTDocumentUtil::nameExists($data['browse'], $name);                        
                } else {
                    $name = $this->oDocument->getName();
                }
                if ($bNameClash) {
                    $extra_errors['name'] = _kt("A document with this title already exists in your chosen folder.  Please choose a different folder, or specify a new title for the copied document.");
            }
            
                $bFileClash = KTDocumentUtil::fileExists($this->oFolder, $this->oDocument->getFilename());              
                if ($bFileClash && isset($data['filename'])) {
                    $filename = $data['filename'];
                    $bFileClash = KTDocumentUtil::fileExists($this->oFolder, $filename);              
                } else {
                    $filename = $this->oDocument->getFilename();
                }            
                if ($bFileClash) {
                    $extra_errors['filename'] = _kt("A document with this filename already exists in your chosen folder.  Please choose a different folder, or specify a new filename for the copied document.");
                }
                
                if (!Permission::userHasFolderWritePermission($data['browse'])) {
                    $extra_errors['browse'] = _kt("You do not have permission to create new documents in that folder.");
                }
            }
        }
        
        if (!empty($errors) || !empty($extra_errors)) {
            return $oForm->handleError(null, $extra_errors);   
        }
        
        $this->startTransaction();
        // now try update it.
        
        $res = KTDocumentUtil::move($this->oDocument, $data['browse'], $this->oUser, $sReason);
        if (PEAR::isError($oNewDoc)) {
            $this->errorRedirectTo("main", _kt("Failed to move document: ") . $oNewDoc->getMessage());
            exit(0);
        }
        
        $this->oDocument->setName($name);       // if needed.
        $this->oDocument->setFilename($filename);   // if needed.
                
        $res = $this->oDocument->update();
        if (PEAR::isError($res)) {
            return $this->errorRedirectTo("main", _kt("Failed to move document: ") . $res->getMessage());
        }

        $this->commitTransaction();
        
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
        
        return parent::getInfo();
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
    
    function form_copyselection() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => sprintf(_kt("Copy Document \"%s\""), $this->oDocument->getName()),
            'submit_label' => _kt("Copy"),
            'identifier' => 'ktcore.actions.copydoc',
            'action' => 'copy',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'fail_action' => 'main',
            'context' => $this,
        ));    

        /*
         *  This is somewhat more complex than most forms, since the "filename"
         *  and title shouldn't appear unless there's a clash.
         *
         *  This is still not the most elegant solution.
         */   
    
        $oForm->setWidgets(array(
            array('ktcore.widgets.foldercollection', array(
                'label' => _kt('Target Folder'),
			    'description' => _kt('Use the folder collection and path below to browse to the folder you wish to copy the documents into.'),
			    'required' => true,
			    'name' => 'browse',
                'folder_id' => $this->oDocument->getFolderID(),
                )),
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please specify why you are copying this document.  Bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
        ));
 
         
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
            array('ktcore.validators.entity', array(
                'class' => 'Folder',
                'test' => 'browse',
                'output' => 'browse',
            )),
        ));        
 
        // here's the ugly bit.
        
        $err = $oForm->getErrors();
        if (!empty($err['name']) || !empty($err['filename'])) {
            $oForm->addWidget(
                array('ktcore.widgets.string', array(
                    'label' => _kt("Document Title"),
                    'value' => $this->oDocument->getName(),
                    'important_description' => _kt("Please indicate a new title to use to resolve any title conflicts."),
                    'name' => 'name',
                    'required' => true,
                ))
            );
            $oForm->addValidator(
                array('ktcore.validators.string', array(
                    'output' => 'name',
                    'test' => 'name'
                ))
            );

            $oForm->addWidget(
                array('ktcore.widgets.string', array(
                    'label' => _kt("Filename"),
                    'value' => $this->oDocument->getFilename(),
                    'important_description' => _kt("Please indicate a new filename to use to resolve any conflicts."),
                    'name' => 'filename',
                    'required' => true,
                ))
            );
            $oForm->addValidator(
                array('ktcore.validators.string', array(
                    'output' => 'filename',
                    'test' => 'filename'
                ))
            );
        }
        return $oForm;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Copy"));
        $oForm = $this->form_copyselection();
        return $oForm->renderPage();
    }

    function do_copy() {   
        $oForm = $this->form_copyselection();
        $res = $oForm->validate();
        $errors = $res['errors'];
        $data = $res['results'];
        $extra_errors = array();


        if (!is_null($data['browse'])) {
            $bNameClash = KTDocumentUtil::nameExists($data['browse'], $this->oDocument->getName());        
            if ($bNameClash && isset($data['name'])) {
                $name = $data['name'];
                $bNameClash = KTDocumentUtil::nameExists($data['browse'], $name);                        
            } else {
                $name = $this->oDocument->getName();
            }
            if ($bNameClash) {
                $extra_errors['name'] = _kt("A document with this title already exists in your chosen folder.  Please choose a different folder, or specify a new title for the copied document.");
            }
        
            $bFileClash = KTDocumentUtil::fileExists($data['browse'], $this->oDocument->getFilename());              

            if ($bFileClash && isset($data['filename'])) {
                $filename = $data['filename'];
                $bFileClash = KTDocumentUtil::fileExists($data['browse'], $filename);              
            } else {
                $filename = $this->oDocument->getFilename();
            }            
            if ($bFileClash) {
                $extra_errors['filename'] = _kt("A document with this filename already exists in your chosen folder.  Please choose a different folder, or specify a new filename for the copied document.");
            }
            
            if (!Permission::userHasFolderWritePermission($data['browse'])) {
                $extra_errors['browse'] = _kt("You do not have permission to create new documents in that folder.");
            }
        }
        
        if (!empty($errors) || !empty($extra_errors)) {
            return $oForm->handleError(null, $extra_errors);   
        }
        
        // FIXME agree on document-duplication rules re: naming, etc.
        
        $this->startTransaction();
        // now try update it.
        
        $oNewDoc = KTDocumentUtil::copy($this->oDocument, $data['browse'], $sReason);
        if (PEAR::isError($oNewDoc)) {
            $this->errorRedirectTo("main", _kt("Failed to copy document: ") . $oNewDoc->getMessage(), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
            exit(0);
        }
        
        $oNewDoc->setName($name);
        $oNewDoc->setFilename($filename);
                
        $res = $oNewDoc->update();
        if (PEAR::isError($res)) {
            return $this->errorRedirectTo("main", _kt("Failed to copy document: ") . $res->getMessage(), sprintf("fDocumentId=%d&fFolderId=%d", $this->oDocument->getId(), $this->oFolder->getId()));
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
                "new_folder" => $data['browse'],
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }
        
        //$aOptions = array('user' => $oUser);
        //$oDocumentTransaction = & new DocumentTransaction($oNewDoc, "Document copied from old version.", 'ktcore.transactions.create', $aOptions);
        //$res = $oDocumentTransaction->create();
        
        $_SESSION['KTInfoMessage'][] = _kt('Document copied.');
        
        controllerRedirect('viewDocument', 'fDocumentId=' .  $oNewDoc->getId());
        exit(0);
    }
}
// }}}

// {{{ KTDocumentArchiveAction
class KTDocumentArchiveAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.archive';
    var $_sShowPermission = "ktcore.permissions.write";
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Archive');
    }

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Archive Document"),
            'action' => 'archive',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt("Archive Document"),
            'context' => &$this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Please specify why you are archiving this document.  Please bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),
        ));
        
        return $oForm;
    }
    
    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Archive Document"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/archive');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_archive() {
    
        $oForm = $this->form_main();
        $res = $oForm->validate();
        $data = $res['results'];
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        
        $sReason = $data['reason'];
    
        $this->startTransaction();
        $this->oDocument->setStatusID(ARCHIVED);
        $res = $this->oDocument->update();
        if (PEAR::isError($res) || ($res === false)) {
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
    var $_sShowPermission = "ktcore.permissions.read";
    
    var $sHelpPage = 'ktcore/user/workflow.html';    

    function predispatch() {
        $this->persistParams(array("fTransitionId"));    
    }

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

        $bHasPerm = false;
        if (KTPermissionUtil::userHasPermissionOnItem($oUser, 'ktcore.permissions.workflow', $oDocument)) {
            $bHasPerm = true;
        }

        $fieldErrors = null;
        
        $transition_fields = array();
        if ($aTransitions) {
            $aVocab = array();
            foreach ($aTransitions as $oTransition) {
                $aVocab[$oTransition->getId()] = $oTransition->showDescription();
            }
            $fieldOptions = array("vocab" => $aVocab);
            $transition_fields[] = new KTLookupWidget(_kt('Transition to perform'), _kt('The transition listed will cause the document to change from its current state to the listed destination state.'), 'fTransitionId', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);
            $transition_fields[] = new KTTextWidget(
                _kt('Reason for transition'), _kt('Describe why this document qualifies to be changed from its current state to the destination state of the transition chosen.'), 
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
            'bHasPerm' => $bHasPerm,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_startWorkflow() {
        $oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);
        if (!empty($_REQUEST['fWorkflowId'])) {
            $oWorkflow =& $this->oValidator->validateWorkflow($_REQUEST['fWorkflowId']);
        } else {
            $oWorkflow = null;
        }
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
            'message' => _kt('You must provide a reason for the transition'),
        );

        $sComments =& $this->oValidator->validateString($_REQUEST['fComments'], $aErrorOptions);
        
        $oUser =& User::get($_SESSION['userID']);
        $res = KTWorkflowUtil::performTransitionOnDocument($oTransition, $oDocument, $oUser, $sComments);

        if(!Permission::userHasDocumentReadPermission($oDocument)) {
            $this->commitTransaction();
            $_SESSION['KTInfoMessage'][] = _kt('Transition performed') . '. ' . _kt('You no longer have permission to view this document');
            controllerRedirect('browse', sprintf('fFolderId=%d', $oDocument->getFolderId()));
        } else {
            $this->successRedirectToMain(_kt('Transition performed'),
            array('fDocumentId' => $oDocument->getId()));
        }
    }
    
    function form_quicktransition() {

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.workflow.quicktransition',
            'label' => _kt("Perform Quick Transition"),
            'submit_label' => _kt("Perform Transition"),
            'context' => $this,
            'action' => 'performquicktransition',
            'fail_action' => 'quicktransition',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason', array(
                'label' => _kt("Reason"),
                'description' => _kt("Specify your reason for performing this action."),
                'important_description' => _kt("Please bear in mind that you can use a maximum of <strong>250</strong> characters."),
                'name' => 'reason',
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'max_length' => 250,
                'output' => 'reason',
            )),                     
        ));
        
        return $oForm;
    }

    function do_quicktransition() {
        // make sure this gets through.
        $this->persistParams(array('fTransitionId'));
        
        $transition_id = $_REQUEST['fTransitionId'];
        $oTransition = KTWorkflowTransition::get($transition_id);
        
        $oForm = $this->form_quicktransition();
        return $oForm->renderPage(sprintf(_kt("Perform Transition: %s"), $oTransition->getName()));
    }
    
    function do_performquicktransition() {
        $oForm = $this->form_quicktransition();
        $res = $oForm->validate();
        
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }    
        
        $this->startTransaction();
        
        $data = $res['results'];
        $oTransition = KTWorkflowTransition::get($_REQUEST['fTransitionId']);
        
        $res = KTWorkflowUtil::performTransitionOnDocument($oTransition, $this->oDocument, $this->oUser, $data['reason']);

        if(!Permission::userHasDocumentReadPermission($this->oDocument)) {
            $this->commitTransaction();
            $_SESSION['KTInfoMessage'][] = _kt('Transition performed') . '. ' . _kt('You no longer have permission to view this document');
            controllerRedirect('browse', sprintf('fFolderId=%d', $this->oDocument->getFolderId()));
        } else {
            $this->commitTransaction();        
            $_SESSION['KTInfoMessage'][] = _kt('Transition performed');
            controllerRedirect('viewDocument', sprintf('fDocumentId=%d', $this->oDocument->getId()));
        }        
    }

}
// }}}

class KTOwnershipChangeAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.ownershipchange';
    var $_sShowPermission = "ktcore.permissions.manageSecurity";

    function getDisplayName() {
        return _kt('Change Document Ownership');
    }
    
    function form_owner() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Change Document Ownership"),
            'description' => _kt('Changing document ownership allows you to keep the "owner" role relevant, even when the original user no longer is an appropriate choice.'),
            'action' => 'reown',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'fail_action' => 'main',
            'identifier' => 'ktcore.actions.document.owner',
            'context' => $this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.entityselection', array(
                'label' => _kt("New Owner"),
                'description' => _kt("The owner of a document is usually the person with ultimate responsibility for its contents.  It is initially set to the person who created the document, but can be changed to any other user."),
                'important_description' => _kt('Please note that changing the owner may affect access to this document.'),
                'label_method' => 'getName',
                'vocab' => User::getList('id > 0'),
                'value' => $this->oDocument->getOwnerID(),
                'name' => 'user_id'
            )),
        ));
        $oForm->setValidators(array(
            array('ktcore.validators.entity', array(
                'test' => 'user_id',
                'class' => 'User',
                'output' => 'user',
            )),
        ));
        
        return $oForm;
    }

    function do_main() { 
        $this->oPage->setBreadcrumbDetails(_kt("Changing Ownership"));
        $oTemplate =& $this->oValidator->validateTemplate("ktcore/document/ownershipchangeaction");
        
        $change_form = $this->form_owner();
        
        $oTemplate->setData(array(
            'context' => $this,
            'docname' => $this->oDocument->getName(),
            'form' => $change_form,
        ));     
        return $oTemplate->render();
    }
    
    function do_reown() {
        $oForm = $this->form_owner();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        
        if (!empty($errors)) {
            return $oForm->handleError();   
        }

        $oUser = $data['user'];
        
        $this->startTransaction();
        
        $this->oDocument->setOwnerID($oUser->getId());
        $res = $this->oDocument->update();
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt("Failed to update document: %s"), $res->getMessage()), sprintf('fDocumentId=%d', $this->oDocument->getId()));
        }
        
        $res = KTPermissionUtil::updatePermissionLookup($this->oDocument);
        
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt("Failed to update document: %s"), $res->getMessage()), sprintf('fDocumentId=%d', $this->oDocument->getId()));
        }
        
        $this->successRedirectToMain(_kt("Ownership changed."), sprintf('fDocumentId=%d', $this->oDocument->getId()));
    }
}

?>
