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


require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

require_once(KT_LIB_DIR . "/widgets/forms.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");

// {{{ KTDocumentEditAction
class KTDocumentEditAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.edit';

    var $_sShowPermission = "ktcore.permissions.write";
    var $_bMutator = true;
    var $sIconClass = 'edit_metadata';    

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function getDisplayName() {
        return _kt('Edit Metadata');
    }
    
    function predispatch() {
        $this->persistParams(array('new_type'));
    }
    
    function form_edit() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt('Edit Metadata'),
            'submit_label' => _kt('Update Document'),
            'action' => 'update',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery("","",true),
        ));
    
        
        $oFReg =& KTFieldsetRegistry::getSingleton();
        
        $doctypeid = $this->oDocument->getDocumentTypeID();
        if ($_REQUEST['new_type']) {
            $oTestType = DocumentType::get($_REQUEST['new_type']);
            if (!PEAR::isError($oTestType)) {
                $doctypeid = $oTestType->getId();
            }
        }
        
        $widgets = array(
            array('ktcore.widgets.string', array(
                'label' => _kt("Document Title"),
                'description' => _kt("The document title is used as the main name of a document throughout KnowledgeTree."),
                'name' => 'document_title',
                'required' => true,
                'value' => $this->oDocument->getName(),
            )),
        );
        $validators = array(
            array('ktcore.validators.string',array(
                'test' => 'document_title',
                'output' => 'document_title',
            )),
        );
        $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->oDocument, $doctypeid);
        
        foreach ($fieldsets as $oFieldset) {
            $widgets = kt_array_merge($widgets, $oFReg->widgetsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
            $validators = kt_array_merge($validators, $oFReg->validatorsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));                
        }

        $oForm->setWidgets($widgets);
        $oForm->setValidators($validators);                
    
        return $oForm;
    }
    
    function do_main() {   
        $this->oPage->setBreadcrumbDetails(_kt("Edit Metadata"));    
        
        $oTemplate = $this->oValidator->validateTemplate('ktcore/document/edit');
        
        $doctypeid = $this->oDocument->getDocumentTypeID();
        $type = DocumentType::get($doctypeid);
        
        
        $oForm = $this->form_edit();
        
        $oTemplate->setData(array(
            'context' => $this,
            'form' => $oForm,
            'document' => $this->oDocument,
            'type_name' => $type->getName(),
        ));    
        return $oTemplate->render();
    }
    
    function do_update() {
        $oForm = $this->form_edit();
        
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        
        $data = $res['results'];

        // we need to format these in MDPack format
        // which is a little archaic:
        //
        //  array(
        //      array($oField, $sValue),
        //      array($oField, $sValue),
        //      array($oField, $sValue),                
        //  );
        //
        // we do this the "easy" way.
        $doctypeid = $this->oDocument->getDocumentTypeId();
        if ($_REQUEST['new_type']) {
            $oTestType = DocumentType::get($_REQUEST['new_type']);
            if (!PEAR::isError($oTestType)) {
                $doctypeid = $oTestType->getId();
            }
        }
                
        
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument, $doctypeid);
        
        $MDPack = array();
        foreach ($fieldsets as $oFieldset) {
            $fields = $oFieldset->getFields();
            $values = (array) KTUtil::arrayGet($data, 'fieldset_' . $oFieldset->getId());
            foreach ($fields as $oField) {
                $val = KTUtil::arrayGet($values, 'metadata_' . $oField->getId());        
                
                // FIXME "null" has strange meanings here.
                if (!is_null($val)) {    
                    $MDPack[] = array(
                        $oField,
                        $val
                    );
                }
                
            }
        } 

        $this->startTransaction();
        if ($this->oDocument->getDocumentTypeId() != $doctypeid) {
            $this->oDocument->setDocumentTypeId($doctypeid);
        }
        $this->oDocument->setName($data['document_title']);
        $res = $this->oDocument->update();
        if (PEAR::isError($res)) {
            $oForm->handleError(sprintf(_kt("Unexpected failure to update document title: %s"), $res->getMessage()));
        }
        $core_res = KTDocumentUtil::saveMetadata($this->oDocument, $MDPack);
        if (PEAR::isError($core_res)) {
            $oForm->handleError(sprintf(_kt("Unexpected validation failure: %s."), $core_res->getMessage()));
        }

        // post-triggers.
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('edit', 'postValidate');
                
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                "document" => $this->oDocument,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }
        
        $this->commitTransaction();
           
        redirect(KTBrowseUtil::getUrlForDocument($this->oDocument->getId()));
        exit(0);
    } 

    function form_changetype() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt("Change Document Type"),
            'description' => _kt("Changing the document type will allow different metadata to be associated with it."),
            'identifier' => 'ktcore.doc.edit.typechange',
            'submit_label' => _kt("Update Document"),
            'context' => $this,
            'cancel_action' => 'main',
            'action' => 'trytype',
        ));
        
        $type = DocumentType::get($this->oDocument->getDocumentTypeId());
        $current_type_name = $type->getName();
        $oFolder = Folder::get($this->oDocument->getFolderID());
        
        $oForm->setWidgets(array(
            array('ktcore.widgets.entityselection',array(
                'label' => _kt("New Document Type"),
                'description' => _kt("Please select the new type for this document."),
                'important_description' => sprintf(_kt("The document is currently of type \"%s\"."), $current_type_name),
                'value' => $type->getId(),
                'label_method' => 'getName',
                'vocab' => DocumentType::getListForUserAndFolder($this->oUser, $oFolder),
                'simple_select' => false,
                'required' => true,
                'name' => 'type'
            )),
        ));
        
        $oForm->setValidators(array(
            array('ktcore.validators.entity', array(
                'test' => 'type',
                'output' => 'type',
                'class' => 'DocumentType',
            )),
        ));
        
        return $oForm;
    }

    function do_selecttype() {
        $oForm = $this->form_changetype();
        return $oForm->renderPage(_kt("Change Document Type"));
    }   
    
    function do_trytype() {
        $oForm = $this->form_changetype();
        $res = $oForm->validate();
        $data = $res['results'];
        $errors = $res['errors'];
        
        if (!empty($errors)) {
            $oForm->handleError();
        }        
        
        $this->successRedirectToMain(sprintf(_kt("You have selected a new document type: %s.  Please note that this change has <strong>not</strong> yet been saved - please update the metadata below as necessary, and then save the document to make it a permanent change."), $data['type']->getName()), array('new_type' => $data['type']->getId()));
    }
}
// }}}

?>
