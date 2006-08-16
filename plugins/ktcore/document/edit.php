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

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function getDisplayName() {
        return _kt('Edit Metadata');
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
        $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->oDocument);
        
        foreach ($fieldsets as $oFieldset) {
            $widgets = kt_array_merge($widgets, $oFReg->widgetsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
            $validators = kt_array_merge($validators, $oFReg->validatorsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));                
        }
        
        $oForm->setWidgets($widgets);
        $oForm->setValidators($validators);                
    
        return $oForm;
    }
    
    function do_main() {
        $this->addErrorMessage("Doctype changing regressed.");
    
        $this->oPage->setBreadcrumbDetails("Edit Metadata");    
        
        $oTemplate = $this->oValidator->validateTemplate('ktcore/document/edit');
    
        $oForm = $this->form_edit();
        
        $oTemplate->setData(array(
            'context' => $this,
            'form' => $oForm,
            'document' => $this->oDocument,
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
        
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument);
        
        $MDPack = array();
        foreach ($fieldsets as $oFieldset) {
            $fields = $oFieldset->getFields();
            $values = (array) KTUtil::arrayGet($data, 'fieldset_' . $oFieldset->getId());
            foreach ($fields as $oField) {
                $val = KTUtil::arrayGet($values, 'metadata_' . $oField->getId());            
                $MDPack[] = array(
                    $oField,
                    $val
                );
            }
        } 

        $this->startTransaction();
        $this->oDocument->setName($data['document_title']);
        $res = $this->oDocument->update();
        if (PEAR::isError($res)) {
            $oForm->handleError(sprintf(_kt("Unexpected failure to update document title: %s"), $res->getMessage()));
        }
        $core_res = KTDocumentUtil::saveMetadata($this->oDocument, $MDPack);
        if (PEAR::isError($core_res)) {
            $oForm->handleError(_kt("Unexpected validation failure."));
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

}
// }}}

?>
