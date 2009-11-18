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
require_once(KT_LIB_DIR . "/util/sanitize.inc");
require_once(KT_LIB_DIR.'/permissions/permissiondynamiccondition.inc.php');

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
                'description' => sprintf(_kt("The document title is used as the main name of a document throughout %s."), APP_NAME),
                'name' => 'document_title',
                'required' => true,
                'value' => sanitizeForHTML($this->oDocument->getName()),
            )),
        );
        $validators = array(
            array('ktcore.validators.string', array(
                'test' => 'document_title',
                'output' => 'document_title',
            )),
        );
        $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->oDocument, $doctypeid);

        foreach ($fieldsets as $oFieldset) {
            $widgets = kt_array_merge($widgets, $oFReg->widgetsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
            $validators = kt_array_merge($validators, $oFReg->validatorsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
        }

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
            $widgets[] = array('ktcore.widgets.reason', array(
                    'label' => _kt('Reason'),
                    'name' => 'reason',
                    'required' => true
                ));

            $validators[] = array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oDocument->iId,
                'type' => 'document',
                'action' => 'ktcore.transactions.edit_metadata',
                'test' => 'info',
                'output' => 'info'
            ));
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
                
                // for html fields we want to do some stripping :)
                if ($oField->getIsHTML())
                {
                    // NOTE this works great...once the text is saved a first time
                    //      but the first time the <script> tags come through encoded, so decode first
                    // HOWEVER html_entity_decode decodes too much (e.g. &nbsp; - which causes a DB error for some reason)!  use this instead
                    // NOTE I considered a preg_replace_callback but str_replace is probably more efficient in this case as we only have 
                    //      two symbols to replace
                    $val = str_replace('&lt;', '<', $val);
                    $val = str_replace('&gt;', '>', $val);
                    //$val = preg_replace_callback('/&lt;([^&]*)&gt;/', create_function('$matches', 'return "<" . $matches[1] . ">";'), $val);
                    // in case of script which does not yet contain <!-- //--> around the actual code (i.e. first submission again)
                    // these will not be correctly removed by strip_tags
                    $val = preg_replace('/<script[^>]*>([^<]*)<\/script>/', '', $val);
                    // remove any attempts to call an onclick/onmouseover/onwhatever call
                    $val = preg_replace_callback('/on[^= ]*=[^; \/>]*;?"? *\/? *(>?)/', 
                                                 create_function('$matches', 'if (isset($matches[1])) return $matches[1]; else return null;'), 
                                                 $val);
                    // now strip remaining tags including script tags with code surrounded by <!-- //-->, 
					// which would not be stripped by the previous regex
                    $val = strip_tags($val, '<p><a><b><strong><ol><ul><li><p><br><i><em><u><span>');
                    // remove empty <p> tags?
                    $val = preg_replace('/<p><\/p>\r?\n?/', '', $val);
                }

                if($oField->getDataType() == "LARGE TEXT" && !is_null($oField->getMaxLength()))
                {
                    if(strlen(strip_tags($val)) > $oField->getMaxLength())
                    {
                        $oForm->handleError(sprintf(_kt("Value exceeds max allowed length of %d characters for %s. Current value is %d characters."), $oField->getMaxLength(), $oField->getName(), strlen(strip_tags($val))));
                    }
                }

                // FIXME "null" has strange meanings here.
                if (!is_null($val)) {
                    if(KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin') && is_array($val) && $oField->getHasInetLookup()) {
            $val = join(", ",$val);
            }
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
        $this->oDocument->setName(($data['document_title']));
        $this->oDocument->setLastModifiedDate(getCurrentDateTime());
        $this->oDocument->setModifiedUserId($this->oUser->getId());

        // Update the content version / document version
        global $default;
        if($default->updateContentVersion){
            $this->oDocument->startNewContentVersion($this->oUser);
            $this->oDocument->setMinorVersionNumber($this->oDocument->getMinorVersionNumber()+1);
        }else {
            $this->oDocument->startNewMetadataVersion($this->oUser);
        }

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
                "aOptions" => $MDPack,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }

        $this->commitTransaction();

        // create the document transaction record
        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, _kt('Document metadata updated'), 'ktcore.transactions.update');
        $oDocumentTransaction->create();

        // Check if there are any dynamic conditions / permissions that need to be updated on the document
        // If there are dynamic conditions then update the permissions on the document
        // The dynamic condition test fails unless the changes exists in the DB therefore update permissions after committing the transaction.
        $iPermissionObjectId = $this->oDocument->getPermissionObjectID();
        $dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($iPermissionObjectId);

        if(!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)){
            $res = KTPermissionUtil::updatePermissionLookup($this->oDocument);
        }

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

        $document_type = $data['type'];
        $doctypeid = $document_type->getId();

        // Get the current document type, fieldsets and metadata
        $iOldDocTypeID = $this->oDocument->getDocumentTypeID();
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument, $iOldDocTypeID);
        $mdlist = DocumentFieldLink::getByDocument($this->oDocument);

        $field_values = array();
        foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
        }

        DBUtil::startTransaction();

        // Update the document with the new document type id
        $this->oDocument->startNewMetadataVersion($this->oUser);
        $this->oDocument->setDocumentTypeId($doctypeid);
        $res = $this->oDocument->update();

        if (PEAR::isError($res))
        {
            DBUtil::rollback();
            return $res;
        }

        // Ensure all values for fieldsets common to both document types are retained
        $fs_ids = array();

        $doctype_fieldsets = KTFieldSet::getForDocumentType($doctypeid);
        foreach($doctype_fieldsets as $fieldset)
        {
            $fs_ids[] = $fieldset->getId();
        }

        $MDPack = array();
        foreach ($fieldsets as $oFieldset)
        {
            if ($oFieldset->getIsGeneric() || in_array($oFieldset->getId(), $fs_ids))
            {
                $fields = $oFieldset->getFields();

                foreach ($fields as $oField)
                {
                    $val = isset($field_values[$oField->getId()]) ? $field_values[$oField->getId()] : '';

                    if (!empty($val))
                    {
                        $MDPack[] = array($oField, $val);
                    }
                }
            }
        }

        $core_res = KTDocumentUtil::saveMetadata($this->oDocument, $MDPack, array('novalidate' => true));

        if (PEAR::isError($core_res)) {
            DBUtil::rollback();
            return $core_res;
        }
        DBUtil::commit();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('edit', 'postValidate');

        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
            "document" => $this->oDocument,
            "aOptions" => $MDPack,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }

        // Check if there are any dynamic conditions / permissions that need to be updated on the document
        // If there are dynamic conditions then update the permissions on the document
        // The dynamic condition test fails unless the changes exists in the DB therefore update permissions after committing the transaction.
        $iPermissionObjectId = $this->oDocument->getPermissionObjectID();
        $dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($iPermissionObjectId);

        if(!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)){
            $res = KTPermissionUtil::updatePermissionLookup($this->oDocument);
        }

        $this->successRedirectToMain(sprintf(_kt("You have selected a new document type: %s. "), $data['type']->getName()));
    }
}
// }}}

?>
