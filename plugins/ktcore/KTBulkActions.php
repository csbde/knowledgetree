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

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");
    	$this->sTmpPath= $sTmpPath = tempnam($sBasedir, 'kt_export');

		$this->oStorage =& KTStorageManagerUtil::getSingleton();

		unlink($sTmpPath);
        mkdir($sTmpPath, 0700);

        $this->startTransaction();
        $this->bNoisy = $oKTConfig->get("tweaks/noisyBulkOperations");

        $this->sTmpPath = $sTmpPath;
        $this->aPaths = array();

        $result = parent::do_performaction();


        $sManifest = sprintf("%s/%s", $this->sTmpPath, "MANIFEST");
        file_put_contents($sManifest, join("\n", $this->aPaths));
        $sZipFile = sprintf("%s/%s.zip", $this->sTmpPath, $this->oFolder->getName());
        $sZipFile = str_replace('<', '', str_replace('</', '', str_replace('>', '', $sZipFile)));
        $_SESSION['bulkexport'] = KTUtil::arrayGet($_SESSION, 'bulkexport', array());
        $sExportCode = KTUtil::randomString();
        $_SESSION['bulkexport'][$sExportCode] = array(
            'file' => $sZipFile,
            'dir' => $this->sTmpPath,
        );
        $sZipCommand = KTUtil::findCommand("export/zip", "zip");
        $aCmd = array(
            $sZipCommand,
            "-r",
            $sZipFile,
            ".",
            "-i@MANIFEST",
        );
        $sOldPath = getcwd();
        chdir($this->sTmpPath);
        // Note that the popen means that pexec will return a file descriptor

        $fh = KTUtil::pexec($aCmd);

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Bulk export",
            'transactionNS' => 'ktstandard.transactions.bulk_export',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));

        $this->commitTransaction();


		header("Content-Type: application/zip");
        header("Content-Length: ". filesize($sZipFile));
        header("Content-Disposition: attachment; filename=\"" . $this->oFolder->getName() . ".zip" . "\"");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate");
        readfile($sZipFile);
        $sTmpDir = $aData['dir'];
        KTUtil::deleteDirectory($sTmpDir);

        return $result;
    }

    function perform_action($oEntity) {
        $aReplace = array(
            "[" => "[[]",
            " " => "[ ]",
            "*" => "[*]",
            "?" => "[?]",
        );
    
        $aReplaceKeys = array_keys($aReplace);
        $aReplaceValues = array_values($aReplace);
            
        if(is_a($oEntity, 'Document')) {

			$oDocument = $oEntity;

            if ($this->bNoisy) {
                $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                $oDocumentTransaction->create();
            }

            $sParentFolder = str_replace('<', '', str_replace('</', '', str_replace('>', '', sprintf('%s/%s', $this->sTmpPath, $oDocument->getFullPath()))));
            $newDir = $this->sTmpPath;
            $sFullPath = str_replace('<', '', str_replace('</', '', str_replace('>', '', $this->_convertEncoding($oDocument->getFullPath(), true))));
            foreach (split('/', $sFullPath) as $dirPart) {
                $newDir = sprintf("%s/%s", $newDir, $dirPart);
                if (!file_exists($newDir)) {
                    mkdir($newDir, 0700);
                }
            }
            $sOrigFile = str_replace('<', '', str_replace('</', '', str_replace('>', '', $this->oStorage->temporaryFile($oDocument))));
            $sFilename = sprintf("%s/%s", $sParentFolder, str_replace('<', '', str_replace('</', '', str_replace('>', '', $oDocument->getFileName()))));
            $sFilename = $this->_convertEncoding($sFilename, true);
            copy($sOrigFile, $sFilename);
            $sPath = str_replace('<', '', str_replace('</', '', str_replace('>', '', sprintf("%s/%s", $oDocument->getFullPath(), $oDocument->getFileName()))));
            $sPath = str_replace($aReplaceKeys, $aReplaceValues, $sPath);
            $sPath = $this->_convertEncoding($sPath, true);
            $this->aPaths[] = $sPath;


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
            foreach($aFolderList as $k => $oFolderItem){
                // Get documents for each folder
                $sFolderItemId = $oFolderItem->getID();
                $sFolderItemDocs = $oFolderItem->getDocumentIDs($sFolderItemId);
                        
                if(!empty($sFolderItemDocs)){
                    $aFolderDocs = explode(',', $sFolderItemDocs);
                    $aDocuments = array_merge($aDocuments, $aFolderDocs);
                }
                
                $sFolderPath = $oFolderItem->getFullPath().'/'.$oFolderItem->getName().'/';
                $sParentFolder = str_replace('<', '', str_replace('</', '', str_replace('>', '', sprintf('%s/%s', $this->sTmpPath, $sFolderPath))));
                $newDir = $this->sTmpPath;
                $sFullPath = str_replace('<', '', str_replace('</', '', str_replace('>', '', $this->_convertEncoding($sFolderPath, true))));
                foreach (split('/', $sFullPath) as $dirPart) {
                    $newDir = sprintf("%s/%s", $newDir, $dirPart);
                    if (!file_exists($newDir)) {
                        mkdir($newDir, 0700);
                    }
                }
                $sPath = str_replace('<', '', str_replace('</', '', str_replace('>', '', sprintf("%s", $sFolderPath))));
                $sPath = str_replace($aReplaceKeys, $aReplaceValues, $sPath);
                $sPath = $this->_convertEncoding($sPath, true);
                $this->aPaths[] = $sPath;
            }
            
            // Add all documents to the export
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocumentId){
                    $oDocument = Document::get($sDocumentId);
                    
                    if ($this->bNoisy) {
                        $oDocumentTransaction = new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                        $oDocumentTransaction->create();
                    }
        
                    $sParentFolder = str_replace('<', '', str_replace('</', '', str_replace('>', '', sprintf('%s/%s', $this->sTmpPath, $oDocument->getFullPath()))));
                    $newDir = $this->sTmpPath;
                    $sFullPath = str_replace('<', '', str_replace('</', '', str_replace('>', '', $this->_convertEncoding($oDocument->getFullPath(), true))));
                    foreach (split('/', $sFullPath) as $dirPart) {
                        $newDir = sprintf("%s/%s", $newDir, $dirPart);
                        if (!file_exists($newDir)) {
                            mkdir($newDir, 0700);
                        }
                    }
                    $sOrigFile = str_replace('<', '', str_replace('</', '', str_replace('>', '', $this->oStorage->temporaryFile($oDocument))));
                    $sFilename = sprintf("%s/%s", $sParentFolder, str_replace('<', '', str_replace('</', '', str_replace('>', '', $oDocument->getFileName()))));
                    $sFilename = $this->_convertEncoding($sFilename, true);
                    copy($sOrigFile, $sFilename);
                    $sPath = str_replace('<', '', str_replace('</', '', str_replace('>', '', sprintf("%s/%s", $oDocument->getFullPath(), $oDocument->getFileName()))));
                    $sPath = str_replace($aReplaceKeys, $aReplaceValues, $sPath);
                    $sPath = $this->_convertEncoding($sPath, true);
                    $this->aPaths[] = $sPath;
                }
            }                    
        }
        return true;
    }

    function _convertEncoding($sMystring, $bEncode) {
    	if (strcasecmp($this->sOutputEncoding, "UTF-8") === 0) {
    		return $sMystring;
    	}
    	if ($bEncode) {
    		return iconv("UTF-8", $this->sOutputEncoding, $sMystring);
    	} else {
    		return iconv($this->sOutputEncoding, "UTF-8", $sMystring);
    	}
    }

}
?>