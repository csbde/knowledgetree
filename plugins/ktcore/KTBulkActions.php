<?php

/**
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/actions/bulkaction.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/compressionArchiveUtil.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');


class KTBulkDeleteAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.delete';
    var $_sPermission = 'ktcore.permissions.delete';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Delete');
    }

    function check_entity($oEntity) {
        if(is_a($oEntity, 'Document')) {
            if(!KTDocumentUtil::canBeDeleted($oEntity, $sError)) {
                if (PEAR::isError($sError))
                {
                    return $sError;
                }
                return PEAR::raiseError(_kt('Document cannot be deleted'));
            }
        }

        if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $aChildFolders = array();

            $oFolder = $oEntity;

            // Get folder id
            $sFolderId = $oFolder->getID();

            // Get documents in folder
            $sDocuments = $oFolder->getDocumentIDs($sFolderId);
            $aDocuments = (!empty($sDocuments)) ? explode(',', $sDocuments) : array();

            // Loop through documents and send to this function for checking
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocID){
                    $oDocument = Document::get($sDocID);
                    $res = $this->check_entity($oDocument);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be deleted
                        return PEAR::raiseError(_kt('Folder cannot be deleted'));
                    }
                }
            }

            // If all documents at the current level may be deleted, we can continue
            // Get any existing subfolders
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aChildFolders = $this->oFolder->getList($sWhereClause);

            // Loop through subfolders and check each in the same way as the parent
            if(!empty($aChildFolders)){
                foreach($aChildFolders as $oChild){
                    $res = $this->check_entity($oChild);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be deleted
                        return PEAR::raiseError(_kt('Folder cannot be deleted'));
                    }
                }
            }
        }

        return parent::check_entity($oEntity);
    }

    function form_collectinfo() {
        $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.delete.form',
            'label' => _kt('Delete Items'),
            'submit_label' => _kt('Delete'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_url' => $cancelUrl,
            'context' => $this,
        ));

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $widgets[] = array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                ));
            $widgets[] = array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                ));
            $widgets[] = array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                ));
        }

        $widgets[] = array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('The reason for the deletion of these documents and folders for historical purposes.'),
                'value' => null,
                'required' => true,
            ));

        $oForm->setWidgets($widgets);

        $validators[] = array('ktcore.validators.string', array(
                'test' => 'reason',
                'output' => 'reason',
            ));

        if($default->enableESignatures){
            $validators[] = array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getID(),
                'type' => 'bulk',
                'action' => 'ktcore.transactions.bulk_delete',
                'test' => 'info',
                'output' => 'info'
            ));
        }

        $oForm->setValidators($validators);

        return $oForm;
    }

    /**
     * build the confirmation form that is shown when symlinks are affected by this action.
     *
     * @return KTForm the form
     */
	function form_confirm() {
	    $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt('Are you sure?'),
            'description' => _kt('There are shortcuts linking to some of the documents or folders in your selection; continuing will automatically delete the shortcuts. Would you like to continue?'),
            'action' => 'collectinfo',
            'fail_action' => 'main',
            'cancel_url' => $cancelUrl,
            'submit_label' => _kt('Continue'),
            'context' => $this,
        ));

        $oForm->setWidgets(array(
        array('ktcore.widgets.hidden',array(
        	'name' => 'delete_confirmed',
        	'value' => '1'
        ))));

        return $oForm;
    }

    /**
     * Shows the confirmation form if symlinks are affected by the current action
     *
     * @return Template HTML
     */
	function do_confirm(){
		$this->store_lists();
        $this->get_lists();
    	$this->oPage->setBreadcrumbDetails(_kt('Confirm delete'));
    	$oTemplate =& $this->oValidator->validateTemplate('ktcore/bulk_action_confirm');
        $oForm = $this->form_confirm();
    	$oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();

     	//check if a the symlinks deletion confirmation has been passed yet
		if(KTutil::arrayGet($_REQUEST['data'],'delete_confirmed') != 1){
			//check if there are actually any symlinks involved.
        	if($this->symlinksLinkingToCurrentList()){
        		$this->redirectTo("confirm");
        	}
        }

        //render template
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

     /*
      * Bulk delete
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil/KTFolderUtil $oEntity
      *
      * Description :   Since its a bulk operation, the delete function needs to
      *                 know that. Added extra boolean param to the delete
      *                 function.
      */
    function perform_action($oEntity) {
        $sReason = $this->res['reason'];

        if(is_a($oEntity, 'Document')) {
            $res = KTDocumentUtil::delete($oEntity, $sReason, null, true);
            if (PEAR::isError($res)) {
                return $res;
            }
            return "RemoveChildDocument";
        } else if(is_a($oEntity, 'Folder')) {
            $res = KTFolderUtil::delete($oEntity, $this->oUser, $sReason, null, true);
            if (PEAR::isError($res)) {
                return $res;
            }
            return "RemoveChildFolder";
        }

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
        $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.move.form',
            'label' => _kt('Move Items'),
            'submit_label' => _kt('Move'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_url' => $cancelUrl,
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

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $oForm->addWidget(array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                )));
            $oForm->addWidget(array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                )));
            $oForm->addWidget(array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                )));
        }


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

        if($default->enableESignatures){
            $oForm->addValidator(array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getID(),
                'type' => 'bulk',
                'action' => 'ktcore.transactions.bulk_move',
                'test' => 'info',
                'output' => 'info'
            )));
        }


        return $oForm;
    }

    function check_entity($oEntity) {

        if(is_a($oEntity, 'Document')) {
            if(!KTDocumentUtil::canBeMoved($oEntity, $sError)) {
                if (PEAR::isError($sError))
                {
                    return $sError;
                }
                return PEAR::raiseError(_kt('Document cannot be moved'));
            }
        }

        if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $aChildFolders = array();

            $oFolder = $oEntity;

            // Get folder id
            $sFolderId = $oFolder->getID();

            // Get documents in folder
            $sDocuments = $oFolder->getDocumentIDs($sFolderId);
            $aDocuments = (!empty($sDocuments)) ? explode(',', $sDocuments) : array();

            // Loop through documents and send to this function for checking
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocID){
                    $oDocument = Document::get($sDocID);
                    $res = $this->check_entity($oDocument);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be moved
                        return PEAR::raiseError(_kt('Folder cannot be moved'));
                    }
                }
            }

            // If all documents at the current level may be moved, we can continue
            // Get any existing subfolders
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aChildFolders = $this->oFolder->getList($sWhereClause);

            // Loop through subfolders and check each in the same way as the parent
            if(!empty($aChildFolders)){
                foreach($aChildFolders as $oChild){
                    $res = $this->check_entity($oChild);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be moved
                        return PEAR::raiseError(_kt('Folder cannot be moved'));
                    }
                }
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
        // Store initial folder
        $_REQUEST['fOriginalFolderId'] = $this->oFolder->getId();
        // does it exists
        if(PEAR::isError($this->oTargetFolder)) {
            $this->errorRedirectTo('collectinfo', _kt('Invalid target folder selected'));
            exit(0);
        }

        if ($_REQUEST['fReturnAction'] != 'search2') {
            if($this->iTargetFolderId == $this->oFolder->getId()){
                $this->errorRedirectTo('collectinfo', _kt('Invalid target folder selected: Target folder is the same as the current folder.'));
                exit(0);
            }
        }

        // does the user have write permission
        if(!Permission::userHasFolderWritePermission($this->oTargetFolder)) {
            $this->errorRedirectTo('collectinfo', _kt('You do not have permission to move items to this location'));
            exit(0);
        }

        return parent::do_performaction();
    }

     /*
      * Bulk move
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil/KTFolderUtil $oEntity
      *
      * Description :   Since its a bulk operation, the move function needs to
      *                 know that. Added extra boolean param to the move
      *                 function.
      */
    function perform_action($oEntity) {
        if(is_a($oEntity, 'Document')) {
            $res = KTDocumentUtil::move($oEntity, $this->oTargetFolder, $this->oUser, $this->sReason, true);
        } else if(is_a($oEntity, 'Folder')) {
            $res = KTFolderUtil::move($oEntity, $this->oTargetFolder, $this->oUser, $this->sReason, true);
        }
        if (PEAR::isError($res))
            return $res;

        return 'MovedDocument';
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
        $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.copy.form',
            'label' => _kt('Copy Items'),
            'submit_label' => _kt('Copy'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_url' => $cancelUrl,
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

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $oForm->addWidget(array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                )));
            $oForm->addWidget(array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                )));
            $oForm->addWidget(array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                )));
        }

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

        if($default->enableESignatures){
            $oForm->addValidator(array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getID(),
                'type' => 'bulk',
                'action' => 'ktcore.transactions.bulk_copy',
                'test' => 'info',
                'output' => 'info'
            )));
        }


        return $oForm;
    }

    function check_entity($oEntity) {
        if(is_a($oEntity, 'Document')) {
            if(!KTDocumentUtil::canBeCopied($oEntity, $sError)) {
                if (PEAR::isError($sError))
                {
                    return $sError;
                }
                return PEAR::raiseError(_kt('Document cannot be copied'));
            }
        }

        if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $aChildFolders = array();

            $oFolder = $oEntity;

            // Get folder id
            $sFolderId = $oFolder->getID();

            // Get documents in folder
            $sDocuments = $oFolder->getDocumentIDs($sFolderId);
            $aDocuments = (!empty($sDocuments)) ? explode(',', $sDocuments) : array();

            // Loop through documents and send to this function for checking
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocID){
                    $oDocument = Document::get($sDocID);
                    $res = $this->check_entity($oDocument);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be copied
                        return PEAR::raiseError(_kt('Folder cannot be copied'));
                    }
                }
            }

            // If all documents at the current level may be copied, we can continue
            // Get any existing subfolders
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aChildFolders = $this->oFolder->getList($sWhereClause);

            // Loop through subfolders and check each in the same way as the parent
            if(!empty($aChildFolders)){
                foreach($aChildFolders as $oChild){
                    $res = $this->check_entity($oChild);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be copied
                        return PEAR::raiseError(_kt('Folder cannot be copied'));
                    }
                }
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

     /*
      * Bulk copy
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil/KTFolderUtil $oEntity
      *
      * Description :   Since its a bulk operation, the copy function needs to
      *                 know that. Added extra boolean param to the copy
      *                 function.
      */
    function perform_action($oEntity) {
        if(is_a($oEntity, 'Document')) {
            $res = KTDocumentUtil::copy($oEntity, $this->oTargetFolder, $this->sReason, null, true);
            if (PEAR::isError($res)) {
                return $res;
            }

        } else if(is_a($oEntity, 'Folder')) {
            $res = KTFolderUtil::copy($oEntity, $this->oTargetFolder, $this->oUser, $this->sReason, null, true);
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return 'CopiedDocument';
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
        $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.archive.form',
            'label' => _kt('Archive Items'),
            'submit_label' => _kt('Archive'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_url' => $cancelUrl,
            'context' => $this,
        ));

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $oForm->addWidget(array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                )));
            $oForm->addWidget(array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                )));
            $oForm->addWidget(array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                )));
        }

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

        if($default->enableESignatures){
            $oForm->addValidator(array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getID(),
                'type' => 'bulk',
                'action' => 'ktcore.transactions.bulk_archive',
                'test' => 'info',
                'output' => 'info'
            )));
        }


        return $oForm;
    }

    function check_entity($oEntity) {
        // NOTE: these checks don't have an equivalent in the delete and move functions.
        //       possibly they are no longer needed but I am leaving them here
        //       to avoid any potential problems I may not be aware of
        if((!is_a($oEntity, 'Document')) && (!is_a($oEntity, 'Folder'))) {
            return PEAR::raiseError(_kt('Document cannot be archived'));
        }

        if($oEntity->isSymbolicLink()){
        	return PEAR::raiseError(_kt("It is not possible to archive a shortcut. Please archive the target document or folder instead."));
        }

        if(is_a($oEntity, 'Document')) {
            if(!KTDocumentUtil::canBeArchived($oEntity, $sError)) {
                if (PEAR::isError($sError))
                {
                    return $sError;
                }
                return PEAR::raiseError(_kt('Document cannot be archived'));
            }
        }

        if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $aChildFolders = array();

            $oFolder = $oEntity;

            // Get folder id
            $sFolderId = $oFolder->getID();

            // Get documents in folder
            $sDocuments = $oFolder->getDocumentIDs($sFolderId);
            $aDocuments = (!empty($sDocuments)) ? explode(',', $sDocuments) : array();

            // Loop through documents and send to this function for checking
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocID){
                    $oDocument = Document::get($sDocID);
                    $res = $this->check_entity($oDocument);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be archived
                        return PEAR::raiseError(_kt('Folder cannot be archived'));
                    }
                }
            }

            // If all documents at the current level may be archived, we can continue
            // Get any existing subfolders
            $sWhereClause = "parent_folder_ids = '{$sFolderId}' OR
            parent_folder_ids LIKE '{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId},%' OR
            parent_folder_ids LIKE '%,{$sFolderId}'";
            $aChildFolders = $this->oFolder->getList($sWhereClause);

            // Loop through subfolders and check each in the same way as the parent
            if(!empty($aChildFolders)){
                foreach($aChildFolders as $oChild){
                    $res = $this->check_entity($oChild);
                    if (PEAR::isError($res))
                    {
                        // NOTE: we may want to append the document reason to this
                        // in order for the user to have some idea WHY the folder cannot be archived
                        return PEAR::raiseError(_kt('Folder cannot be archived'));
                    }
                }
            }
        }

        return parent::check_entity($oEntity);
    }

	/**
     * build the confirmation form that is shown when symlinks are affected by this action.
     *
     * @return KTForm the form
     */
	function form_confirm() {
	    $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt('Are you sure?'),
            'description' => _kt('There are shortcuts linking to some of the documents or folders in your selection; continuing will automatically delete the shortcuts. Would you like to continue?'),
            'action' => 'collectinfo',
            'fail_action' => 'main',
            'cancel_url' => $cancelUrl,
            'submit_label' => _kt('Continue'),
            'context' => $this,
        ));

        $oForm->setWidgets(array(
        array('ktcore.widgets.hidden',array(
        	'name' => 'archive_confirmed',
        	'value' => '1'
        ))));

        return $oForm;
    }

    /**
     * Shows the confirmation form if symlinks are affected by the current action
     *
     * @return Template HTML
     */
	function do_confirm(){
		$this->store_lists();
        $this->get_lists();
    	$this->oPage->setBreadcrumbDetails(_kt('Confirm archive'));
    	$oTemplate =& $this->oValidator->validateTemplate('ktcore/bulk_action_confirm');
        $oForm = $this->form_confirm();
    	$oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    // info collection step
    function do_collectinfo() {
        $this->store_lists();
        $this->get_lists();

        //check if a the symlinks deletion confirmation has been passed yet
		if(KTutil::arrayGet($_REQUEST['data'],'archive_confirmed') != 1){
			//check if there are actually any symlinks involved.
        	if($this->symlinksLinkingToCurrentList()){
        		$this->redirectTo("confirm");
        	}
        }

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

     /*
      * Bulk archive
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil/KTFolderUtil $oEntity
      *
      * Description :   Since its a bulk operation, the archive function needs
      *                 to know that. Added extra boolean param to the archive
      *                 function.
      */
    function perform_action($oEntity) {
        if(is_a($oEntity, 'Document')) {

            $res = KTDocumentUtil::archive($oEntity, $this->sReason, true);

            if(PEAR::isError($res)){
                return $res;
            }
        }else if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $aChildFolders = array();
            $oFolder = $oEntity;

            // Get folder id
            $sFolderId = $oFolder->getID();

            // Get documents in folder
            $sDocuments = $oFolder->getDocumentIDs($sFolderId);
            $aDocuments = (!empty($sDocuments)) ? explode(',', $sDocuments) : array();

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

                    if(PEAR::isError($oDocument)){
                        return $oDocument;
                    }

                    $res = KTDocumentUtil::archive($oDocument, $this->sReason, true);

                    if(PEAR::isError($res)){
                        return $res;
                    }
                }
            }else {
                return PEAR::raiseError(_kt('The folder contains no documents to archive.'));
            }

            
        }
        return "ArchivedDocument";
    }
}

// NOTE: None of the new code for folder recursion is implemented for this action.
class KTBrowseBulkExportAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.export';
    var $_sPermission = 'ktcore.permissions.read';
    var $_bMutator = true;
    var $bNotifications = true;

    function getDisplayName() {
        return _kt('Download All');
    }

    function check_entity($oEntity) {
        if((!is_a($oEntity, 'Document')) && (!is_a($oEntity, 'Folder'))) {
                return PEAR::raiseError(_kt('Document cannot be exported'));
        }
        //we need to do an extra folder permission check in case of a shortcut
        if(is_a($oEntity,'Folder') && $oEntity->isSymbolicLink()){
	    	if(!KTPermissionUtil::userHasPermissionOnItem($this->oUser, $this->_sPermission, $oEntity->getLinkedFolder())) {
	            return PEAR::raiseError(_kt('You do not have the required permissions'));
	        }
        }
        if(is_a($oEntity, 'Document')){
            if(!KTWorkflowUtil::actionEnabledForDocument($oEntity, 'ktcore.actions.document.view')){
                return PEAR::raiseError(_kt('Document cannot be exported as it is restricted by the workflow.'));
            }
        }

        return parent::check_entity($oEntity);
    }

    function do_performaction() {

        $config = KTConfig::getSingleton();
        $useQueue = $config->get('export/useDownloadQueue', true);

        // Create the export code
        $this->sExportCode = KTUtil::randomString();
        $_SESSION['exportcode'] = $this->sExportCode;

        // Save the return url in session so it is not lost when doing the download
        $folderurl = $this->getReturnUrl();
        $_SESSION['export_return_url'] = $folderurl;

        $download_url = KTUtil::addQueryStringSelf("action=downloadZipFile&fFolderId={$this->oFolder->getId()}&exportcode={$this->sExportCode}");

        if($useQueue){
            $result = parent::do_performaction();

            $url = KTUtil::kt_url() . '/presentation/lookAndFeel/knowledgeTree/bulkdownload/downloadTask.php';

          	$oTemplating =& KTTemplating::getSingleton();
          	$oTemplate = $oTemplating->loadTemplate('ktcore/action/bulk_download');

          	$aParams = array(
                    'folder_url' => $folderurl,
                    'url' => $url,
                    'code' => $this->sExportCode,
                    'download_url' => $download_url
                );
            return $oTemplate->render($aParams);
        }

        $this->oZip = new ZipFolder('', $this->sExportCode);
        $res = $this->oZip->checkConvertEncoding();

        if(PEAR::isError($res)){
            $this->addErrorMessage($res->getMessage());
            return $sReturn;
        }

        $this->startTransaction();

        $result = parent::do_performaction();

        $sExportCode = $this->oZip->createZipFile();

        if(PEAR::isError($sExportCode)){
            $this->addErrorMessage($sExportCode->getMessage());
            $this->rollbackTransaction();
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

        $str = '<p>'._kt('Creating zip file. Compressing and archiving in progress ...').'</p>';
        $str .= "<p style='margin-bottom: 10px;'><br /><b>".
                _kt('Warning! Please wait for archiving to complete before closing the page.').'</b><br />'.
                _kt('Note: Closing the page before the download link displays will cancel your Bulk Download.').'</p>';

        $str .= sprintf('<p>' . _kt('Once your download is complete, click <a href="%s">here</a> to return to the original folder') . "</p>\n", $folderurl);
        $str .= sprintf('<script language="JavaScript">
                function kt_bulkexport_redirect() {
                    document.location.href = "%s";
                }
                callLater(5, kt_bulkexport_redirect);
                </script>', $download_url);

        return $str;
    }

     /*
      * Bulk export
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil/KTFolderUtil $oEntity
      *
      * Description :   Since its a bulk operation, the export function needs
      *                 to know that. Added extra boolean param to the export
      *                 function.
      */
    function perform_action($oEntity) {
// TODO find a way to do bulk email
        $exportCode = $_SESSION['exportcode'];
        $this->oZip = ZipFolder::get($exportCode);

        $oQueue = new DownloadQueue();

        $config = KTConfig::getSingleton();
        $useQueue = $config->get('export/useDownloadQueue');

        if(is_a($oEntity, 'Document')) {

			$oDocument = $oEntity;
	        if($oDocument->isSymbolicLink()){
	    		$oDocument->switchToLinkedCore();
	    	}

	    	if($useQueue){
                DownloadQueue::addItem($this->sExportCode, $this->oFolder->getId(), $oDocument->iId, 'document');
	    	}else{
                $oQueue->addDocument($this->oZip, $oDocument->iId);
	    	}


        }else if(is_a($oEntity, 'Folder')) {
            $aDocuments = array();
            $oFolder = $oEntity;

            if($oFolder->isSymbolicLink()){
            	$oFolder = $oFolder->getLinkedFolder();
            }
            $sFolderId = $oFolder->getId();

            if($useQueue){
                DownloadQueue::addItem($this->sExportCode, $this->oFolder->getId(), $sFolderId, 'folder');
            }else{
                $oQueue->addFolder($this->oZip, $sFolderId);
            }
        }

        return "DownloadDocument";
    }

    function do_downloadZipFile() {
        $sCode = $this->oValidator->validateString($_REQUEST['exportcode']);

        $this->oZip = new ZipFolder('', $sCode);
        $res = $this->oZip->downloadZipFile($sCode);

        if(PEAR::isError($res)){
            $this->addErrorMessage($res->getMessage());
            $redirectUrl = $_SESSION['export_return_url'];
            unset($_SESSION['export_return_url']);
            redirect($redirectUrl);
        }
        exit(0);
    }
}

// NOTE: None of the new code for folder recursion is implemented for this action.
class KTBrowseBulkCheckoutAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.checkout';
    var $_sPermission = 'ktcore.permissions.write';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Checkout');
    }

    function check_entity($oEntity) {
        if(is_a($oEntity, 'Document')) {
            if($oEntity->getImmutable())
            {
            	return PEAR::raiseError(_kt('Document cannot be checked out as it is immutable'));
            }

            // Check that the document isn't already checked out
            if ($oEntity->getIsCheckedOut()) {
                $checkedOutUser = $oEntity->getCheckedOutUserID();
                $sUserId = $_SESSION['userID'];

                if($checkedOutUser != $sUserId){
                    $oCheckedOutUser = User::get($checkedOutUser);
                    return PEAR::raiseError($oEntity->getName().': '._kt('Document has already been checked out by ').$oCheckedOutUser->getName());
                }
            }

            // Check that the checkout action isn't restricted for the document
            if(!KTWorkflowUtil::actionEnabledForDocument($oEntity, 'ktcore.actions.document.checkout')){
                return PEAR::raiseError($oEntity->getName().': '._kt('Checkout is restricted by the workflow state.'));
            }
        }else if(!is_a($oEntity, 'Folder')) {
                return PEAR::raiseError(_kt('Document cannot be checked out'));
        }
    	//we need to do an extra folder permission check in case of a shortcut
        if(is_a($oEntity,'Folder') && $oEntity->isSymbolicLink()){
	    	if(!KTPermissionUtil::userHasPermissionOnItem($this->oUser, $this->_sPermission, $oEntity->getLinkedFolder())) {
	            return PEAR::raiseError(_kt('You do not have the required permissions'));
	        }
        }
        return parent::check_entity($oEntity);
    }

    function form_collectinfo() {
        $cancelUrl = $this->getReturnUrl();

        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.checkout.form',
            'label' => _kt('Checkout Items'),
            'submit_label' => _kt('Checkout'),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_url' => $cancelUrl,
            'context' => $this,
        ));

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $widgets[] = array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                ));
            $widgets[] = array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                ));
            $widgets[] = array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                ));
        }

        $widgets[] = array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt('Reason'),
                'description' => _kt('Please specify why you are checking out these documents. It will assist other users in understanding why you have locked these files.'),
                'value' => null,
                'required' => true,
                ));
        $widgets[] = array('ktcore.widgets.boolean', array(
                'label' => _kt('Download Files'),
                'description' => _kt('Indicate whether you would like to download these file as part of the checkout.'),
                'name' => 'download_file',
                'value' => true,
            ));

        $oForm->setWidgets($widgets);

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

        if($default->enableESignatures){
            $oForm->addValidator(array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getID(),
                'type' => 'bulk',
                'action' => 'ktcore.transactions.bulk_check_out',
                'test' => 'info',
                'output' => 'info'
            )));
        }


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

     /*
      * Bulk checkout
      * Author      :   Jarrett Jordaan
      * Modified    :   28/04/09
      *
      * @params     :   KTDocumentUtil/KTFolderUtil $oEntity
      *
      * Description :   Since its a bulk operation, the checkout function needs
      *                 to know that. Added extra boolean param to the checkout
      *                 function.
      */
    function perform_action($oEntity) {
        // checkout document
        $sReason = $this->sReason;

        if(is_a($oEntity, 'Document')) {

            if($oEntity->getImmutable())
            {
            	return PEAR::raiseError($oEntity->getName() .': '. _kt('Document cannot be checked out as it is immutable'));
            }

            if($oEntity->getIsCheckedOut()){
                $checkedOutUser = $oEntity->getCheckedOutUserID();
                $sUserId = $_SESSION['userID'];

                if($checkedOutUser != $sUserId){
                    $oCheckedOutUser = User::get($checkedOutUser);
                    return PEAR::raiseError($oEntity->getName().': '._kt('Document has already been checked out by ').$oCheckedOutUser->getName());
                }
            }else{
                $res = KTDocumentUtil::checkout($oEntity, $sReason, $this->oUser, true);

                if(PEAR::isError($res)) {
                    return PEAR::raiseError($oEntity->getName().': '.$res->getMessage());
                }
            }

            if($this->bDownload){
                if ($this->bNoisy) {
                    $oDocumentTransaction = new DocumentTransaction($oEntity, "Document part of bulk checkout", 'ktstandard.transactions.check_out', array());
                    $oDocumentTransaction->create();
                }

                $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
                $aTriggers = $oKTTriggerRegistry->getTriggers('checkoutDownload', 'postValidate');
                foreach ($aTriggers as $aTrigger) {
                    $sTrigger = $aTrigger[0];
                    $oTrigger = new $sTrigger;
                    $aInfo = array(
                        'document' => $oEntity,
                    );
                    $oTrigger->setInfo($aInfo);
                    $ret = $oTrigger->postValidate();
                    if (PEAR::isError($ret)) {
                        return $ret;
                    }
                }
                $this->oZip->addDocumentToZip($oEntity);
            }
            if(!PEAR::isError($res)) {
            }
        }else if(is_a($oEntity, 'Folder')) {
            // get documents and subfolders
            $aDocuments = array();
            $oFolder = $oEntity;

        	if($oFolder->isSymbolicLink()){
            	$oFolder = $oFolder->getLinkedFolder();
            }
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
			$aLinkingFolders = $this->getLinkingEntities($aFolderList);
            $aFolderList = array_merge($aFolderList,$aLinkingFolders);


            $aFolderObjects = array();
            $aFolderObjects[$sFolderId] = $oFolder;

            // Get the documents within the folder
            if(!empty($aFolderList)){
	                foreach($aFolderList as $k => $oFolderItem){
	                	if(Permission::userHasFolderReadPermission($oFolderItem)){
	                    // Get documents for each folder
	              		if($oFolderItem->isSymbolicLink()){
            				$oFolderItem = $oFolderItem->getLinkedFolder();
            			}
	                    $sFolderItemId = $oFolderItem->getID();
	                    $sFolderItemDocs = $oFolderItem->getDocumentIDs($sFolderItemId);

	                    if(!empty($sFolderItemDocs)){
	                        $aFolderDocs = explode(',', $sFolderItemDocs);
	                        $aDocuments = array_merge($aDocuments, $aFolderDocs);
	                    }

	                    // Add the folder to the zip file
	                    if($this->bDownload){
	                        $this->oZip->addFolderToZip($oFolderItem);
	                        $aFolderObjects[$oFolderItem->getId()] = $oFolderItem;
	                    }
	                }
            	}
            }

            // Checkout each document within the folder structure
            if(!empty($aDocuments)){
                foreach($aDocuments as $sDocId){
                    $oDocument = Document::get($sDocId);
                    if(PEAR::isError($oDocument)) {
                        // add message, skip document and continue
                        $this->addErrorMessage($oDocument->getName().': '.$oDocument->getMessage());
                        continue;
                    }
               		if($oDocument->isSymbolicLink()){
	    				$oDocument->switchToLinkedCore();
	    			}

                    if($oDocument->getImmutable())
                    {
                    	$this->addErrorMessage($oDocument->getName() .': '. _kt('Document cannot be checked out as it is immutable'));
                    	continue;
                    }

                    // Check if the action is restricted by workflow on the document
                    if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.checkout')){
                        $this->addErrorMessage($oDocument->getName().': '._kt('Checkout is restricted by the workflow state.'));
                        continue;
                    }

                    // Check if document is already checked out, check the owner.
                    // If the current user is the owner, then include to the download, otherwise ignore.
                    if($oDocument->getIsCheckedOut()){
                        $checkedOutUser = $oDocument->getCheckedOutUserID();
                        $sUserId = $_SESSION['userID'];

                        if($checkedOutUser != $sUserId){
                            $oCheckedOutUser = User::get($checkedOutUser);
                            $this->addErrorMessage($oDocument->getName().': '._kt('Document has already been checked out by ').$oCheckedOutUser->getName());
                            continue;
                        }
                    }else{
                        // Check out document
                        $res = KTDocumentUtil::checkout($oDocument, $sReason, $this->oUser, true);

                        if(PEAR::isError($res)) {
                            $this->addErrorMessage($oDocument->getName().': '._kt('Document could not be checked out. ').$res->getMessage());
                            continue;
                        }
                    }

                    // Add document to the zip file
                    if($this->bDownload){
                        if ($this->bNoisy) {
                            $oDocumentTransaction = new DocumentTransaction($oDocument, 'Document part of bulk checkout', 'ktstandard.transactions.check_out', array());
                            $oDocumentTransaction->create();
                        }

                        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
                        $aTriggers = $oKTTriggerRegistry->getTriggers('checkoutDownload', 'postValidate');
                        foreach ($aTriggers as $aTrigger) {
                            $sTrigger = $aTrigger[0];
                            $oTrigger = new $sTrigger;
                            $aInfo = array(
                                'document' => $oDocument,
                            );
                            $oTrigger->setInfo($aInfo);
                            $ret = $oTrigger->postValidate();
                            if (PEAR::isError($ret)) {
                                return $ret;
                            }
                        }

                        $sDocFolderId = $oDocument->getFolderID();
                        $oFolder = isset($aFolderObjects[$sDocFolderId]) ? $aFolderObjects[$sDocFolderId] : Folder::get($sDocFolderId);
                        $this->oZip->addDocumentToZip($oDocument, $oFolder);
                    }
                }
            }
        }

        return "CheckOutDocument";
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
