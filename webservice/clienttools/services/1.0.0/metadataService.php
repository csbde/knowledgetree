<?php

require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

// TODO Too much copy/paste going on.
//      Get rid of old code or if still used then abstract out common sections.
//      See plugins/ktcore/document/edit.php
//      and presentation/lookAndFeel/knowledgetree/widgets/updateMetadata.php.

// TODO Check the old tag saving code to ensure it left all tag words even when
//      no longer attached to a document.  Is so, note it, else new bug (fix.)

class metadataService extends client_service {

    /**
     * Save submitted tags.
     */
    public function saveTags($params)
    {
        $document = Document::get($params['documentId']);
        $origDocTypeId = $docTypeId = $document->getDocumentTypeId();

        // This is a cheat...should use something else to ensure the correct value.
        // Will work fine unless values are changed (which *should* never happen, but...)
        $fieldSetId = 2;
        $tagFieldSet = DocumentField::get($fieldSetId);
        $tagData = array($tagFieldSet, rtrim($params['tags'], ','));
        $metadataPack = $this->mergeMetadata($document, array($tagData));

        DBUtil::startTransaction();

        $user = User::get($_SESSION['userID']);
        $document->startNewMetadataVersion($user);

        $res = $document->update();
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            $this->addError(sprintf(_kt('Unexpected failure to update document tags: %s'), $res->getMessage()));
            return false;
        }

        $coreRes = KTDocumentUtil::saveMetadata($document, $metadataPack);
        if (PEAR::isError($coreRes)) {
            DBUtil::rollback();
            $this->addError(sprintf(_kt('Unexpected failure to update document tags: %s'), $res->getMessage()));
            return false;
        }

        // Post-triggers.
        // Do these have relevance to tag saving?
        $KTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $triggers = $KTTriggerRegistry->getTriggers('edit', 'postValidate');

        foreach ($triggers as $trigger) {
            $triggerName = $trigger[0];
            $trigger = new $triggerName;
            $info = array(
                'document' => $document,
                'aOptions' => $metadataPack,
                'docTypeId' => $docTypeId,
                'origDocTypeId' => $origDocTypeId
            );
            $trigger->setInfo($info);
            $ret = $trigger->postValidate();
        }

        DBUtil::commit();

        $documentTransaction = new DocumentTransaction(
                                                    $document,
                                                    _kt('Document metadata updated'),
                                                    'ktcore.transactions.update'
                                    );
        $documentTransaction->create();

        $response = array('saveTags' => 'Saved tags for document');
        $this->addResponse('saveTags', json_encode($response));

        return true;
    }

    /**
     * Merge existing metadata with submitted metadata.
     */
    private function mergeMetadata($document, $newMetadata = array())
    {
        $currentMetadata = (array)KTMetadataUtil::fieldsetsForDocument($document);
        $metadataPack = array();

        foreach ($currentMetadata as $currentFieldset) {
            $currentFields = $currentFieldset->getFields();
            foreach ($currentFields as $currentField) {
                $currentID = $currentField->getId();
                $newValue = '';

                $fieldValue = DocumentFieldLink::getByDocumentAndField($document, $currentField);
                if (!is_null($fieldValue) && (!PEAR::isError($fieldValue))) {
                    $newValue = $fieldValue->getValue();
                }

                foreach ($newMetadata as $fieldData) {
                    list($newField, $value) = $fieldData;
                    $newId = $newField->getId();
                    if ($currentID === $newId) {
                        $newValue = $value;
                    }
                }

                $metadataPack[] = array($currentField, $newValue);
            }
        }

        return $metadataPack;
    }

}

?>
