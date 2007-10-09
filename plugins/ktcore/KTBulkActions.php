<?php

/**
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/actions/bulkaction.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/compressionArchiveUtil.inc.php');


class KTBulkDeleteAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.delete';
    var $_sPermission = 'ktcore.permissions.delete';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Delete');
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.delete.form',
            'label' => _kt('Delete Items'),
            'submit_label' => _kt('Delete'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_action' => 'main',
            'context' => $this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('The reason for the deletion of these documents and folders for historical purposes.'),
                'value' => null,
                'required' => true,
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'output' => 'reason',
            )),
        ));

        return $oForm;
    }

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();
	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_info');
        return $oTemplate->render(array('context' => $this,
                                        'form' => $this->form_collectinfo()));
    }


    function do_performaction() {
        $this->store_lists();
        $this->get_lists();

        $oForm = $this->form_collectinfo();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }

        $this->res = $res['results'];
        return parent::do_performaction();
    }

    function perform_action($oEntity) {
        $sReason = $this->res['reason'];

        if(is_a($oEntity, 'Document')) {
            $res = KTDocumentUtil::delete($oEntity, $sReason);
        } else if(is_a($oEntity, 'Folder')) {
            $res = KTFolderUtil::delete($oEntity, $this->oUser, $sReason);
        }

        return $res;
    }
}


class KTBulkMoveAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.move';
    var $_sPermission = 'ktcore.permissions.write';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Move');
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.move.form',
            'label' => _kt('Move Items'),
            'submit_label' => _kt('Move'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_action' => 'main',
            'context' => $this,
        ));

        // Setup the collection for move display.
        require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
        $collection = new AdvancedCollection();

        $oCR =& KTColumnRegistry::getSingleton();
        $col = $oCR->getColumn('ktcore.columns.title');
        //$col->setOptions(array('qs_params'=>array('fMoveCode'=>$sMoveCode,
        //                                          'fFolderId'=>$oFolder->getId(),
        //                                          'action'=>'startMove')));
        $collection->addColumn($col);

        $qObj = new FolderBrowseQuery($this->oFolder->iId);

        $exclude=array();

        foreach( $this->oEntityList->aFolderIds as $folderid)
        {
        	$exclude[] = $folderid+0;
        }

       	$qObj->exclude_folders = $exclude;



        $collection->setQueryObject($qObj);

        $aOptions = $collection->getEnvironOptions();
        $aOptions['result_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'],
                                                         array('fFolderId' => $this->oFolder->iId,
                                                               'action' => 'collectinfo'));

        $collection->setOptions($aOptions);

	$oWF =& KTWidgetFactory::getSingleton();
	$oWidget = $oWF->get('ktcore.widgets.collection',
			     array('label' => _kt('Target Folder'),
				   'description' => _kt('Use the folder collection and path below to browse to the folder you wish to move the documents into.'),
				   'required' => true,
				   'name' => 'fFolderId',
				   'broken_name' => true,
                                   'folder_id' => $this->oFolder->iId,
				   'collection' => $collection));



        $oForm->addInitializedWidget($oWidget);
        $oForm->addWidget(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('The reason for moving these documents and folders, for historical purposes.'),
                'value' => null,
                'required' => true,
                )
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'output' => 'reason',
            )),
        ));

        return $oForm;
    }

    function check_entity($oEntity) {
        if(is_a($oEntity, 'Document')) {
            if(!KTDocumentUtil::canBeMoved($oEntity)) {
                return PEAR::raiseError(_kt('Document cannot be moved'));
            }
        }
        return parent::check_entity($oEntity);
    }

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();
	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_info');
        return $oTemplate->render(array('context' => $this,
                                        'form' => $this->form_collectinfo()));
    }

    function do_performaction() {
        $this->store_lists();
        $this->get_lists();

        $oForm = $this->form_collectinfo();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }

        $this->sReason = $_REQUEST['data']['reason'];
        $this->iTargetFolderId = $_REQUEST['data']['fFolderId'];
        $this->oTargetFolder = Folder::get($this->iTargetFolderId);
        $_REQUEST['fReturnData'] = '';
        $_REQUEST['fFolderId'] = $this->iTargetFolderId;

        // does it exists
        if(PEAR::isError($this->oTargetFolder)) {
            return PEAR::raiseError(_kt('Invalid target folder selected'));
        }

        // does the user have write permission
        if(!Permission::userHasFolderWritePermission($this->oTargetFolder)) {
            $this->errorRedirectTo('collectinfo', _kt('You do not have permission to move items to this location'));
        }

        return parent::do_performaction();
    }

    function perform_action($oEntity) {
        if(is_a($oEntity, 'Document')) {
            return KTDocumentUtil::move($oEntity, $this->oTargetFolder, $this->oUser, $this->sReason);
        } else if(is_a($oEntity, 'Folder')) {
            return KTFolderUtil::move($oEntity, $this->oTargetFolder, $this->oUser, $this->sReason);
        }
    }
}

class KTBulkCopyAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.copy';
    var $_sPermission = 'ktcore.permissions.read';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Copy');
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.copy.form',
            'label' => _kt('Copy Items'),
            'submit_label' => _kt('Copy'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_action' => 'main',
            'context' => $this,
        ));

        // Setup the collection for move display.
        require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
        $collection = new AdvancedCollection();

        $oCR =& KTColumnRegistry::getSingleton();
        $col = $oCR->getColumn('ktcore.columns.title');
        //$col->setOptions(array('qs_params'=>array('fMoveCode'=>$sMoveCode,
        //                                          'fFolderId'=>$oFolder->getId(),
        //                                          'action'=>'startMove')));
        $collection->addColumn($col);

        $qObj = new FolderBrowseQuery($this->oFolder->iId);

        $exclude=array();

        foreach( $this->oEntityList->aFolderIds as $folderid)
        {
        	$exclude[] = $folderid+0;
        }

       	$qObj->exclude_folders = $exclude;



        $collection->setQueryObject($qObj);

        $aOptions = $collection->getEnvironOptions();
        $aOptions['result_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'],
                                                         array('fFolderId' => $this->oFolder->iId,
                                                               'action' => 'collectinfo'));

        $collection->setOptions($aOptions);

	$oWF =& KTWidgetFactory::getSingleton();
	$oWidget = $oWF->get('ktcore.widgets.collection',
			     array('label' => _kt('Target Folder'),
				   'description' => _kt('Use the folder collection and path below to browse to the folder you wish to copy the documents into.'),
				   'required' => true,
				   'name' => 'fFolderId',
				   'broken_name' => true,
                                   'folder_id' => $this->oFolder->iId,
				   'collection' => $collection));



        $oForm->addInitializedWidget($oWidget);
        $oForm->addWidget(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('The reason for copying these documents and folders, for historical purposes.'),
                'value' => null,
                'required' => true,
                )
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'output' => 'reason',
            )),
        ));

        return $oForm;
    }

    function check_entity($oEntity) {
        if(is_a($oEntity, 'Document')) {
            if(!KTDocumentUtil::canBeMoved($oEntity)) {
                return PEAR::raiseError(_kt('Document cannot be copied'));
            }
        }
        return parent::check_entity($oEntity);
    }

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();
	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_info');
        return $oTemplate->render(array('context' => $this,
                                        'form' => $this->form_collectinfo()));
    }

    function do_performaction() {
        $this->store_lists();
        $this->get_lists();

        $oForm = $this->form_collectinfo();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }

        $this->sReason = $_REQUEST['data']['reason'];
        $this->iTargetFolderId = $_REQUEST['data']['fFolderId'];
        $this->oTargetFolder = Folder::get($this->iTargetFolderId);
        $_REQUEST['fReturnData'] = '';
        $_REQUEST['fFolderId'] = $this->iTargetFolderId;

        // does it exists
        if(PEAR::isError($this->oTargetFolder)) {
            return PEAR::raiseError(_kt('Invalid target folder selected'));
        }

        // does the user have write permission
        if(!Permission::userHasFolderWritePermission($this->oTargetFolder)) {
            $this->errorRedirectTo('collectinfo', _kt('You do not have permission to move items to this location'));
        }

        return parent::do_performaction();
    }

    function perform_action($oEntity) {
        if(is_a($oEntity, 'Document')) {
            return KTDocumentUtil::copy($oEntity, $this->oTargetFolder, $this->sReason);
        } else if(is_a($oEntity, 'Folder')) {
            return KTFolderUtil::copy($oEntity, $this->oTargetFolder, $this->oUser, $this->sReason);
        }
    }
}

class KTBulkArchiveAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.archive';
    var $_sPermission = 'ktcore.permissions.write';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Archive');
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.archive.form',
            'label' => _kt('Archive Items'),
            'submit_label' => _kt('Archive'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_action' => 'main',
            'context' => $this,
        ));

        $oForm->addWidget(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('The reason for archiving these documents and folders, for historical purposes.'),
                'value' => null,
                'required' => true,
                )
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'reason',
                'output' => 'reason',
            )),
        ));

        return $oForm;
    }

    function check_entity($oEntity) {
        if((!is_a($oEntity, 'Document')) && (!is_a($oEntity, 'Folder'))) {
                return PEAR::raiseError(_kt('Document cannot be archived'));
        }
        return parent::check_entity($oEntity);
    }

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();
	$oTemplating =& KTTemplating::getSingleton();
	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_info');
        return $oTemplate->render(array('context' => $this,
                                        'form' => $this->form_collectinfo()));
    }

    function do_performaction() {
        $this->store_lists();
        $this->get_lists();

        $oForm = $this->form_collectinfo();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }

        $this->sReason = $_REQUEST['data']['reason'];


        return parent::do_performaction();
    }

    function perform_action($oEntity) {
        if(is_a($oEntity, 'Document')) {
        	DBUtil::startTransaction();

        	$document = $oEntity;

            $document->setStatusID(ARCHIVED);
            $res = $document->update();
            if (($res === false) || PEAR::isError($res)) {
               DBUtil::rollback();
               return false;
            }

            $oDocumentTransaction = & new DocumentTransaction($document, sprintf(_kt('Document archived: %s'),  $this->sReason), 'ktcore.transactions.update');
            $oDocumentTransaction->create();

            DBUtil::commit();
            return true;
        }else if(is_a($oEntity, 'Folder')) {
        	DBUtil::startTransaction();

            $aDocuments = array();
            $aChildFolders = array();
            $oFolder = $oEntity;

            // Get folder id
            $sFolderId = $oFolder->getID();

            // Get documents in folder
            $sDocuments = $oFolder->getDocumentIDs($sFolderId);
            $aDocuments = explode(',', $sDocuments);

            // Get all the folders within the folder
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aChildFolders = $this->oFolder->getList($sWhereClause);

            // Loop through folders and get documents
            if(!empty($aChildFolders)){
                foreach($aChildFolders as $oChild){
                    $sChildId = $oChild->getID();
                    $sChildDocs = $oChild->getDocumentIDs($sChildId);
                    if (PEAR::isError($res)) {
                       DBUtil::rollback();
                       return false;
                    }

                    if(!empty($sChildDocs)){
                        $aChildDocs = explode(',', $sChildDocs);
                        $aDocuments = array_merge($aDocuments, $aChildDocs);
                    }
                }
            }

            // Archive all documents
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocumentId){
                    $oDocument = Document::get($sDocumentId);

                    $oDocument->setStatusID(ARCHIVED);
                    $res = $oDocument->update();
                    if (($res === false) || PEAR::isError($res)) {
                       DBUtil::rollback();
                       return false;
                    }

                    $oDocumentTransaction = & new DocumentTransaction($oDocument, sprintf(_kt('Document archived: %s'),  $this->sReason), 'ktcore.transactions.update');
                    $oDocumentTransaction->create();
                }
            }
            DBUtil::commit();
            return true;
        }
    }
}

class KTBrowseBulkExportAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.export';
    var $_sPermission = 'ktcore.permissions.read';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Export');
    }



    function check_entity($oEntity) {
        if((!is_a($oEntity, 'Document')) && (!is_a($oEntity, 'Folder'))) {
                return PEAR::raiseError(_kt('Document cannot be exported'));
        }
        return parent::check_entity($oEntity);
    }


    function do_performaction() {

        $folderName = $this->oFolder->getName();
        $this->oZip = new ZipFolder($folderName);
        $res = $this->oZip->checkConvertEncoding();

        $folderurl = KTBrowseUtil::getUrlForFolder($this->oFolder);
        $sReturn = sprintf('<p>' . _kt('Return to the original <a href="%s">folder</a>') . "</p>\n", $folderurl);

        if(PEAR::isError($res)){
            $this->addErrorMessage($res->getMessage());
            return $sReturn;
        }

        $this->startTransaction();
        $oKTConfig =& KTConfig::getSingleton();
        $this->bNoisy = $oKTConfig->get("tweaks/noisyBulkOperations");

        $result = parent::do_performaction();
        $sExportCode = $this->oZip->createZipFile();

        if(PEAR::isError($sExportCode)){
            $this->addErrorMessage($sExportCode->getMessage());
            return $sReturn;
        }

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Bulk export",
            'transactionNS' => 'ktstandard.transactions.bulk_export',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));

        $this->commitTransaction();
        
        $url = KTUtil::addQueryStringSelf(sprintf('action=downloadZipFile&fFolderId=%d&exportcode=%s', $this->oFolder->getId(), $sExportCode));
        $str = sprintf('<p>' . _kt('Go <a href="%s">here</a> to download the zip file if you are not automatically redirected there') . "</p>\n", $url);
        $folderurl = KTBrowseUtil::getUrlForFolder($this->oFolder);
        $str .= sprintf('<p>' . _kt('Once downloaded, return to the original <a href="%s">folder</a>') . "</p>\n", $folderurl);
        //$str .= sprintf("</div></div></body></html>\n");
        $str .= sprintf('<script language="JavaScript">
                function kt_bulkexport_redirect() {
                document.location.href = "%s";
                }
                callLater(1, kt_bulkexport_redirect);
    
                </script>', $url);
                
        return $str;
    }

    function perform_action($oEntity) {

        if(is_a($oEntity, 'Document')) {

			$oDocument = $oEntity;

            if ($this->bNoisy) {
                $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                $oDocumentTransaction->create();
            }
            $this->oZip->addDocumentToZip($oDocument);

        }else if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $oFolder = $oEntity;
            $sFolderId = $oFolder->getId();
            $sFolderDocs = $oFolder->getDocumentIDs($sFolderId);

            if(!empty($sFolderDocs)){
                $aDocuments = explode(',', $sFolderDocs);
            }

            // Get all the folders within the current folder
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aFolderList = $this->oFolder->getList($sWhereClause);

            // Export the folder structure to ensure the export of empty directories
            if(!empty($aFolderList)){
                foreach($aFolderList as $k => $oFolderItem){
                    // Get documents for each folder
                    $sFolderItemId = $oFolderItem->getID();
                    $sFolderItemDocs = $oFolderItem->getDocumentIDs($sFolderItemId);

                    if(!empty($sFolderItemDocs)){
                        $aFolderDocs = explode(',', $sFolderItemDocs);
                        $aDocuments = array_merge($aDocuments, $aFolderDocs);
                    }
                    $this->oZip->addFolderToZip($oFolderItem);
                }
            }

            // Add all documents to the export
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocumentId){
                    $oDocument = Document::get($sDocumentId);

                    if ($this->bNoisy) {
                        $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                        $oDocumentTransaction->create();
                    }
                    $this->oZip->addDocumentToZip($oDocument);
                }
            }
        }
        return true;
    }

    function do_downloadZipFile() {
        $sCode = $this->oValidator->validateString($_REQUEST['exportcode']);
        
        $folderName = $this->oFolder->getName();
        $this->oZip = new ZipFolder($folderName);
        
        $res = $this->oZip->downloadZipFile($sCode);
        
        if(PEAR::isError($res)){
            $this->addErrorMessage($res->getMessage());
            redirect(generateControllerUrl("browse", "fBrowseType=folder&fFolderId=" . $this->oFolder->getId()));
        }
        exit(0);
    }
}

class KTBrowseBulkCheckoutAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.checkout';
    var $_sPermission = 'ktcore.permissions.write';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Checkout');
    }

    function check_entity($oEntity) {
        if(is_a($oEntity, 'Document')) {
            if ($oEntity->getIsCheckedOut()) {
                return PEAR::raiseError(_kt('Document is already checked out'));
            }
        }else if(!is_a($oEntity, 'Folder')) {
                return PEAR::raiseError(_kt('Document cannot be checked out'));
        }
        return parent::check_entity($oEntity);
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.checkout.form',
            'label' => _kt('Checkout Items'),
            'submit_label' => _kt('Checkout'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_action' => 'main',
            'context' => $this,
        ));

        $oForm-> setWidgets(array(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('Please specify why you are checking out these documents. It will assist other users in understanding why you have locked these files.'),
                'value' => null,
                'required' => true,
                )),
            array('ktcore.widgets.boolean', array(
                'label' => _kt('Download Files'),
                'description' => _kt('Indicate whether you would like to download these file as part of the checkout.'),
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

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();
      	$oTemplating =& KTTemplating::getSingleton();
      	$oTemplate = $oTemplating->loadTemplate('ktcore/bulk_action_info');
        return $oTemplate->render(array('context' => $this,
                                        'form' => $this->form_collectinfo()));
    }

    function do_performaction() {
        // Get reason for checkout & check if docs must be downloaded
        $this->store_lists();
        $this->get_lists();

        $oForm = $this->form_collectinfo();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }

        $this->sReason = $_REQUEST['data']['reason'];
        $this->bDownload = $_REQUEST['data']['download_file'];

        $oKTConfig =& KTConfig::getSingleton();
        $this->bNoisy = $oKTConfig->get("tweaks/noisyBulkOperations");
        
        $folderurl = KTBrowseUtil::getUrlForFolder($this->oFolder);
        $sReturn = sprintf('<p>' . _kt('Return to the original <a href="%s">folder</a>') . "</p>\n", $folderurl);

        $this->startTransaction();

        // if files are to be downloaded - create the temp directory for the bulk export
        if($this->bDownload){
            $folderName = $this->oFolder->getName();
            $this->oZip = new ZipFolder($folderName);
            $res = $this->oZip->checkConvertEncoding();
                        
            if(PEAR::isError($res)){
                $this->addErrorMessage($res->getMessage());
                return $sReturn;
            }
        }


        $result = parent::do_performaction();

        if(PEAR::isError($result)){
            $this->addErrorMessage($result->getMessage());
            return $sReturn;
        }

        if($this->bDownload){
            $sExportCode = $this->oZip->createZipFile();

            if(PEAR::isError($sExportCode)){
                $this->addErrorMessage($sExportCode->getMessage());
                return $sReturn;
            }
        }

        $this->commitTransaction();

        if($this->bDownload){
        
            $url = KTUtil::addQueryStringSelf(sprintf('action=downloadZipFile&fFolderId=%d&exportcode=%s', $this->oFolder->getId(), $sExportCode));
            $str = sprintf('<p>' . _kt('Go <a href="%s">here</a> to download the zip file if you are not automatically redirected there') . "</p>\n", $url);
            $folderurl = KTBrowseUtil::getUrlForFolder($this->oFolder);
            $str .= sprintf('<p>' . _kt('Once downloaded, return to the original <a href="%s">folder</a>') . "</p>\n", $folderurl);
            $str .= sprintf("</div></div></body></html>\n");
            $str .= sprintf('<script language="JavaScript">
                    function kt_bulkexport_redirect() {
                        document.location.href = "%s";
                    }
                    callLater(1, kt_bulkexport_redirect);
    
                    </script>', $url);
                    
            return $str;
        }
        return $result;
    }

    function perform_action($oEntity) {
        // checkout document
        $sReason = $this->sReason;

        if(is_a($oEntity, 'Document')) {
            $res = KTDocumentUtil::checkout($oEntity, $sReason, $this->oUser);

            if(PEAR::isError($res)) {
                return PEAR::raiseError($oEntity->getName().': '.$res->getMessage());
            }
            if($this->bDownload){
                if ($this->bNoisy) {
                    $oDocumentTransaction = new DocumentTransaction($oEntity, "Document part of bulk checkout", 'ktstandard.transactions.check_out', array());
                    $oDocumentTransaction->create();
                }
                $this->oZip->addDocumentToZip($oEntity);
            }

        }else if(is_a($oEntity, 'Folder')) {
            // get documents and subfolders
            $aDocuments = array();
            $oFolder = $oEntity;

            $sFolderId = $oFolder->getId();
            $sFolderDocs = $oFolder->getDocumentIDs($sFolderId);

            // get documents directly in the folder
            if(!empty($sFolderDocs)){
                $aDocuments = explode(',', $sFolderDocs);
            }

            // Get all the folders within the current folder
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aFolderList = $this->oFolder->getList($sWhereClause);

            // Get the documents within the folder
            if(!empty($aFolderList)){
                foreach($aFolderList as $k => $oFolderItem){
                    // Get documents for each folder
                    $sFolderItemId = $oFolderItem->getID();
                    $sFolderItemDocs = $oFolderItem->getDocumentIDs($sFolderItemId);

                    if(!empty($sFolderItemDocs)){
                        $aFolderDocs = explode(',', $sFolderItemDocs);
                        $aDocuments = array_merge($aDocuments, $aFolderDocs);
                    }

                    // Add the folder to the zip file
                    if($this->bDownload){
                        $this->oZip->addFolderToZip($oFolderItem);
                    }
                }
            }

            // Checkout each document within the folder structure
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocId){
                    $oDocument = Document::get($sDocId);
                    if(PEAR::isError($oDocument)) {
                        return PEAR::raiseError(_kt('Folder documents cannot be checked out'));
                    }

                    $res = KTDocumentUtil::checkout($oDocument, $sReason, $this->oUser);
                    if(PEAR::isError($res)) {
                        return PEAR::raiseError($oDocument->getName().': '.$res->getMessage());
                    }

                    // Add document to the zip file
                    if($this->bDownload){
                        if ($this->bNoisy) {
                            $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk checkout", 'ktstandard.transactions.check_out', array());
                            $oDocumentTransaction->create();
                        }
                        $this->oZip->addDocumentToZip($oDocument);
                    }
                }
            }
        }
        return true;
    }
    
    function do_downloadZipFile() {
        $sCode = $this->oValidator->validateString($_REQUEST['exportcode']);
        
        $folderName = $this->oFolder->getName();
        $this->oZip = new ZipFolder($folderName);
        
        $res = $this->oZip->downloadZipFile($sCode);
        
        if(PEAR::isError($res)){
            $this->addErrorMessage($res->getMessage());
            redirect(generateControllerUrl("browse", "fBrowseType=folder&fFolderId=" . $this->oFolder->getId()));
        }
        exit(0);
    }
}

?>