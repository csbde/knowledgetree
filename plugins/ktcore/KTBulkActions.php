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

require_once(KT_LIB_DIR . '/actions/bulkaction.php');
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");


class KTBulkDeleteAction extends KTBulkAction {
    var $sName = 'ktcore.actions.bulk.delete';
    var $_sPermission = "ktcore.permissions.delete";
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Delete');
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.delete.form',
            'label' => _kt("Delete Items"),
            'submit_label' => _kt("Delete"),
            'action' => 'performaction',
            'fail_action' => 'collectinfo',
            'cancel_action' => 'main',
            'context' => $this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt("Reason"),
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
    var $_sPermission = "ktcore.permissions.write";
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Move');
    }

    function form_collectinfo() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.actions.bulk.move.form',
            'label' => _kt("Move Items"),
            'submit_label' => _kt("Move"),
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

        $qObj = new FolderBrowseQuery(1);
        $collection->setQueryObject($qObj);

        $aOptions = $collection->getEnvironOptions();
        $aOptions['result_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'], 
                                                         array('fFolderId' => '1',
                                                               'action' => 'collectinfo'));

        $collection->setOptions($aOptions);

	$oWF =& KTWidgetFactory::getSingleton();
	$oWidget = $oWF->get('ktcore.widgets.collection', 
			     array('label' => _kt('Target Folder'),
				   'description' => _kt('Use the folder collection and path below to browse to the folder you wish to move the documents into.'),
				   'required' => true,
				   'name' => 'fFolderId',
				   'broken_name' => true,
                                   'folder_id' => 1,
				   'collection' => $collection));



        $oForm->addInitializedWidget($oWidget);
        $oForm->addWidget(
            array('ktcore.widgets.reason',array(
                'name' => 'reason',
                'label' => _kt("Reason"),
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
        
        // does it exists
        if(PEAR::isError($this->oTargetFolder)) {
            return PEAR::raiseError(_kt('Invalid target folder selected'));
        }        
        
        // does the user have write permission
        if(!Permission::userHasFolderWritePermission($this->oTargetFolder)) {
            $this->errorRedirectTo('collectinfo', _kt("You do not have permission to move items to this location"));
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

?>