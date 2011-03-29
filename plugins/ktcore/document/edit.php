<?php

/**
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldsetregistry.inc.php');
require_once(KT_LIB_DIR . '/util/sanitize.inc');
require_once(KT_LIB_DIR . '/permissions/permissiondynamiccondition.inc.php');

class KTDocumentEditAction extends KTDocumentAction {

    var $sName = 'ktcore.actions.document.edit';
    var $_sShowPermission = 'ktcore.permissions.write';
    var $_bMutator = true;
    var $sIconClass = 'edit_metadata';

    function getInfo()
    {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }

        return parent::getInfo();
    }

    function getDisplayName()
    {
        return _kt('Edit properties');
    }

    function predispatch()
    {
        $this->persistParams(array('new_type'));
    }

    function form_edit()
    {
        $form = new KTForm;
        $form->setOptions(array(
            'submit_label' => _kt('Update Document'),
            'action' => 'update',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery('', '', true),
        ));

        $fieldsetRegistry =& KTFieldsetRegistry::getSingleton();

        $doctypeId = $this->oDocument->getDocumentTypeID();
        if ($_REQUEST['new_type']) {
            $testType = DocumentType::get($_REQUEST['new_type']);
            if (!PEAR::isError($testType)) {
                $doctypeId = $testType->getId();
            }
        }

        $widgets = array(
            array('ktcore.widgets.string',
                array(
                    'label' => _kt('Document Title'),
                    'description' => sprintf(_kt('The document title is used as the main name of a document throughout %s.'), APP_NAME),
                    'name' => 'document_title',
                    'required' => true,
                    'value' => sanitizeForHTML($this->oDocument->getName())
                )
            ),
        );

        $validators = array(
            array('ktcore.validators.string', array(
                'test' => 'document_title',
                'output' => 'document_title',
            )),
        );

        $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->oDocument, $doctypeId);

        foreach ($fieldsets as $fieldset) {
            $widgets = kt_array_merge($widgets, $fieldsetRegistry->widgetsForFieldset($fieldset, 'fieldset_' . $fieldset->getId(), $this->oDocument));
            $validators = kt_array_merge($validators, $fieldsetRegistry->validatorsForFieldset($fieldset, 'fieldset_' . $fieldset->getId(), $this->oDocument));
        }

        // Electronic Signature if enabled
        global $default;
        if ($default->enableESignatures) {
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
                    'label' => _kt('Note'),
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

        $form->setWidgets($widgets);
        $form->setValidators($validators);

        return $form;
    }

    function do_main()
    {
        $this->oPage->setBreadcrumbDetails(_kt('Edit properties'));

        $template = $this->oValidator->validateTemplate('ktcore/document/edit');

        $doctypeId = $this->oDocument->getDocumentTypeID();
        $type = DocumentType::get($doctypeId);

        $template->setData(array(
            'context' => $this,
            'form' => $this->form_edit(),
            'document' => $this->oDocument,
            'type_name' => $type->getName(),
        ));

        return $template->render();
    }

    function do_update()
    {
        $form = $this->form_edit();

        $res = $form->validate();
        if (!empty($res['errors'])) {
            return $form->handleError();
        }

        $data = $res['results'];

        // we need to format these in MDPack format
        // which is a little archaic:
        //
        //  array(
        //      array($field, $sValue),
        //      array($field, $sValue),
        //      array($field, $sValue),
        //  );
        //
        // we do this the 'easy' way.
        $doctypeId = $this->oDocument->getDocumentTypeId();
        $origDocTypeId = $doctypeId;
        if ($_REQUEST['new_type']) {
            $testType = DocumentType::get($_REQUEST['new_type']);
            if (!PEAR::isError($testType)) {
                $doctypeId = $testType->getId();
            }
        }

        $metadataPack = $this->getMetadataPack($doctypeId);

        $this->startTransaction();

        if ($this->oDocument->getDocumentTypeId() != $doctypeId) {
            $this->oDocument->setDocumentTypeId($doctypeId);
        }

        $this->oDocument->setName($data['document_title']);
        $this->oDocument->setLastModifiedDate(getCurrentDateTime());
        $this->oDocument->setModifiedUserId($this->oUser->getId());

        // Update the content version / document version
        global $default;
        if ($default->updateContentVersion) {
            $this->oDocument->startNewContentVersion($this->oUser);
            $this->oDocument->setMinorVersionNumber($this->oDocument->getMinorVersionNumber()+1);
        }
        else {
            $this->oDocument->startNewMetadataVersion($this->oUser);
        }

        $res = $this->oDocument->update();
        if (PEAR::isError($res)) {
            $form->handleError(sprintf(_kt('Unexpected failure to update document title: %s'), $res->getMessage()));
        }

        $coreRes = KTDocumentUtil::saveMetadata($this->oDocument, $metadataPack);
        if (PEAR::isError($coreRes)) {
            $form->handleError(sprintf(_kt('Unexpected validation failure: %s.'), $coreRes->getMessage()));
        }

        // post-triggers.
        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('edit', 'postValidate');

        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                'document' => $this->oDocument,
                'aOptions' => $metadataPack,
                'docTypeId' => $doctypeId,
                'origDocTypeId' => $origDocTypeId
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();
        }

        $this->commitTransaction();

        // create the document transaction record
        $documentTransaction = & new DocumentTransaction($this->oDocument, _kt('Document metadata updated'), 'ktcore.transactions.update');
        $documentTransaction->create();

        // Check if there are any dynamic conditions / permissions that need to be updated on the document
        // If there are dynamic conditions then update the permissions on the document
        // The dynamic condition test fails unless the changes exists in the DB therefore update permissions after committing the transaction.
        $permissionObjectId = $this->oDocument->getPermissionObjectID();
        $dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($permissionObjectId);

        if (!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)) {
            $res = KTPermissionUtil::updatePermissionLookup($this->oDocument);
        }

        redirect(KTBrowseUtil::getUrlForDocument($this->oDocument->getId()));
        exit(0);
    }

    private function getMetadataPack($doctypeId)
    {
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument, $doctypeId);
        $metadataPack = array();
        foreach ($fieldsets as $fieldset) {
            $fields = $fieldset->getFields();
            $values = (array)KTUtil::arrayGet($data, 'fieldset_' . $fieldset->getId());
            foreach ($fields as $field) {
                $val = KTUtil::arrayGet($values, 'metadata_' . $field->getId());
                // For html fields we want to do some stripping.
                if ($field->getIsHTML()) {
                    $val = $this->prepareHTMLFieldValue($val);
                }

                if ($field->getDataType() == 'LARGE TEXT' && !is_null($field->getMaxLength())) {
                    if (strlen(strip_tags($val)) > $field->getMaxLength()) {
                        $form->handleError(sprintf(_kt('Value exceeds max allowed length of %d characters for %s. Current value is %d characters.'), $field->getMaxLength(), $field->getName(), strlen(strip_tags($val))));
                    }
                }

                // FIXME 'null' has strange meanings here.
                // WHAT STRANGE MEANINGS?
                if (!is_null($val)) {
                    if (KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin') && is_array($val) && $field->getHasInetLookup()) {
            $val = join(', ', $val);
                    }

                    $metadataPack[] = array($field, $val);
                }

            }
        }

        return $metadataPack;
    }

    /**
     * This works great...once the text is saved a first time.
     * The first time the <script> tags come through encoded, so decode first.
     *
     * HOWEVER html_entity_decode decodes too much (e.g. &nbsp; - which causes a DB error for some reason)!
     * Use this instead.
     */
    private function prepareHTMLFieldValue($val)
    {
        $val = str_replace('&lt;', '<', $val);
        $val = str_replace('&gt;', '>', $val);
        // In case of script which does not yet contain <!-- //-->
        // around the actual code (i.e. first submission again):
        // these will not be correctly removed by strip_tags.
        $val = preg_replace('/<script[^>]*>([^<]*)<\/script>/', '', $val);
        // Remove any attempts to call an onclick/onmouseover/onwhatever call.
        $val = preg_replace_callback('/on[^= ]*=[^; \/>]*;?"? *\/? *(>?)/',
        create_function('$matches', 'if (isset($matches[1])) return $matches[1]; else return null;'),
        $val);
        // Now strip remaining tags including script tags with code surrounded by <!-- //-->,
        // which would not be stripped by the previous regex.
        $val = strip_tags($val, '<p><a><b><strong><ol><ul><li><p><br><i><em><u><span>');
        // Remove empty <p> tags.
        $val = preg_replace('/<p><\/p>\r?\n?/', '', $val);

        return $val;
    }

    function form_changetype()
    {
        $form = new KTForm;
        $form->setOptions(array(
            'description' => _kt('Changing the document type will allow different metadata to be associated with it.'),
            'identifier' => 'ktcore.doc.edit.typechange',
            'submit_label' => _kt('Update Document'),
            'context' => $this,
            'cancel_action' => 'main',
            'action' => 'trytype',
        ));

        $type = DocumentType::get($this->oDocument->getDocumentTypeId());
        $currentTypeName = $type->getName();
        $folder = Folder::get($this->oDocument->getFolderID());

        $form->setWidgets(array(
            array('ktcore.widgets.entityselection',array(
                'label' => _kt('New Document Type'),
                'description' => _kt('Please select the new type for this document.'),
                'important_description' => sprintf(_kt('The document is currently of type "%s".'), $currentTypeName),
                'value' => $type->getId(),
                'label_method' => 'getName',
                'vocab' => DocumentType::getListForUserAndFolder($this->oUser, $folder),
                'simple_select' => false,
                'required' => true,
                'name' => 'type'
            )),
        ));

        $form->setValidators(array(
            array('ktcore.validators.entity', array(
                'test' => 'type',
                'output' => 'type',
                'class' => 'DocumentType',
            )),
        ));

        return $form;
    }

    function do_selecttype()
    {
        $form = $this->form_changetype();
        return $form->renderPage(_kt('Change Document Type'));
    }

    function do_trytype()
    {
        $form = $this->form_changetype();
        $res = $form->validate();
        $data = $res['results'];
        $errors = $res['errors'];

        if (!empty($errors)) {
            $form->handleError();
        }

        $documentType = $data['type'];
        $doctypeId = $documentType->getId();

        // Get the current document type, fieldsets and metadata
        $oldDocTypeID = $this->oDocument->getDocumentTypeID();
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument, $oldDocTypeID);
        $metadataList = DocumentFieldLink::getByDocument($this->oDocument);

        $fieldValues = array();
        foreach ($metadataList as $fieldLink) {
            $fieldValues[$fieldLink->getDocumentFieldID()] = $fieldLink->getValue();
        }

        DBUtil::startTransaction();

        // Update the document with the new document type id
        $this->oDocument->startNewMetadataVersion($this->oUser);
        $this->oDocument->setDocumentTypeId($doctypeId);
        $res = $this->oDocument->update();

        if (PEAR::isError($res)) {
            DBUtil::rollback();
            return $res;
        }

        // Ensure all values for fieldsets common to both document types are retained
        $fieldsetIds = array();

        $doctype_fieldsets = KTFieldSet::getForDocumentType($doctypeId);
        foreach($doctype_fieldsets as $fieldset) {
            $fieldsetIds[] = $fieldset->getId();
        }

        $metadataPack = array();
        foreach ($fieldsets as $fieldset) {
            if ($fieldset->getIsGeneric() || in_array($fieldset->getId(), $fieldsetIds)) {
                $fields = $fieldset->getFields();
                foreach ($fields as $field) {
                    $val = isset($fieldValues[$field->getId()]) ? $fieldValues[$field->getId()] : '';
                    if (!empty($val)) {
                        $metadataPack[] = array($field, $val);
                    }
                }
            }
        }

        $coreRes = KTDocumentUtil::saveMetadata($this->oDocument, $metadataPack, array('novalidate' => true));
        if (PEAR::isError($coreRes)) {
            DBUtil::rollback();
            return $coreRes;
        }

        DBUtil::commit();

        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('edit', 'postValidate');

        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                'document' => $this->oDocument,
                'aOptions' => $metadataPack,
                'docTypeId' => $doctypeId,
                'origDocTypeId' => $oldDocTypeID
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();
        }

        // Check if there are any dynamic conditions / permissions that need to be updated on the document
        // If there are dynamic conditions then update the permissions on the document
        // The dynamic condition test fails unless the changes exists in the DB therefore update permissions after committing the transaction.
        $permissionObjectId = $this->oDocument->getPermissionObjectID();
        $dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($permissionObjectId);

        if (!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)) {
            $res = KTPermissionUtil::updatePermissionLookup($this->oDocument);
        }

        $this->successRedirectToMain(sprintf(_kt('You have selected a new document type: %s. '), $data['type']->getName()));
    }

}

?>
