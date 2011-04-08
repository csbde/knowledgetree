<?php

require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

// TODO Too much copy/paste going on.
//      Get rid of old code or if still used then abstract out common sections.
//      See plugins/ktcore/document/edit.php
//      and presentation/lookAndFeel/knowledgetree/widgets/updateMetadata.php.

// TODO Check the old tag saving code to ensure it left all tag words even when
//      no longer attached to a document.  Is so, note it, else new bug (fix.)

class metadataService extends client_service {

	public function changeDocumentTitle($params)
	{
		$GLOBALS['default']->log->debug('metadataService changeDocumentTitle params '.print_r($params, true));
		
		$iDocumentID = $params['documentID'];
		$sTitle = $params['documentTitle'];
		
		$GLOBALS['default']->log->debug("metadataService changeDocumentTitle $iDocumentID $sTitle");
		
		$returnResponse = array();
	
		$oUser = User::get($_SESSION['userID']);
		
		if (PEAR::isError($oUser)) {
			$GLOBALS['default']->log->error("metadataService updateMetadata User {$_SESSION['userID']}: {$oUser->getMessage()}");
			return false;
		}
		
		DBUtil::startTransaction();
		
		$oDocument = &Document::get($iDocumentID);
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentTitle document '.print_r($oDocument, true));
		
		$oDocument->setName($sTitle);
	    $oDocument->setLastModifiedDate(getCurrentDateTime());
	    $oDocument->setModifiedUserId($oUser->getId());
	
	    $packed = $this->mergeMetadata($oDocument, array());
		
		$GLOBALS['default']->log->debug('metadataService changeDocumentTitle packed '.print_r($packed, true));
	    
	    // Update the content version / document version
		$oDocument->startNewMetadataVersion($oUser);
	
		$res = $oDocument->update();    
		if (PEAR::isError($res))
		{
			DBUtil::rollback();
			//return new KTAPI_Error('Unexpected failure updating document', $res);
		}
				
		$result = KTDocumentUtil::saveMetadata($oDocument, $packed, array('novalidate'=>true));
		
		if (is_null($result) || PEAR::isError($result))    
		{
			DBUtil::rollback();
		}
		    
		DBUtil::commit();
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata committed');
		
		$oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
		$aTriggers = $oKTTriggerRegistry->getTriggers('edit', 'postValidate');
	
		foreach ($aTriggers as $aTrigger) {
			$sTrigger = $aTrigger[0];
			$oTrigger = new $sTrigger;
			$aInfo = array(
				"document" => $oDocument,
				"aOptions" => $packed,
			);
	
			$oTrigger->setInfo($aInfo);
			$ret = $oTrigger->postValidate();
		}
	
	    DBUtil::commit();
	
	    // create the document transaction record
	    $oDocumentTransaction = & new DocumentTransaction($oDocument, _kt('Document metadata updated'), 'ktcore.transactions.update');
	    $oDocumentTransaction->create();
		
		//assemble the item to return
		$item['documentID'] = $iDocumentID;
		$item['documentTitle'] = $sTitle;
		
		//$json['success'] = $item;
		
		//echo(json_encode($json));
		//exit(0);
		
		$returnResponse[] = $item;
		
		//$this->addResponse('addedDocuments', $returnResponse);
        $this->addResponse('success', json_encode($returnResponse));

        return true;
	}
	
	function updateMetadata($params)
    {
    	$GLOBALS['default']->log->debug('metadataService updateMetadata params '.print_r($params, true));
    	
    	$iDocumentID = $params['documentID'];
    	
    	unset($params['documentID']);
    	
    	$GLOBALS['default']->log->debug('metadataService updateMetadata '.print_r($params, true));
    	
    	$oDocument = Document::get($iDocumentID);
		if (PEAR::isError($oDocument)) {
	   		$GLOBALS['default']->log->error("metadataService updateMetadata Document {$_REQUEST['documentID']}: {$oDocument->getMessage()}");
	   		return false;
	    }
		    
		//$GLOBALS['default']->log->debug('metadataService updateMetadata REQUEST '.print_r($_REQUEST, true));
	
		//$GLOBALS['default']->log->debug('metadataService updateMetadata POST '.print_r($_POST, true));
		
		$fields = array();
		
		//cycle through the params and get all the fields
		foreach($params as $key => $field)
		{
			$GLOBALS['default']->log->debug("metadataService updateMetadata postVar $key $field");
			
			$oField = DocumentField::get($key);
			
	 		if (is_null($oField) || PEAR::isError($oField) || $oField instanceof KTEntityNoObjects)
	 		{
	 			//$GLOBALS['default']->log->debug("Could not resolve field: $oField->getName() ");	//on fieldset $fieldsetname for document id: $this->documentid");
	 			// exit graciously
	 			continue;
	 		}
	
			if(is_array($field))
			{
				$value = '';
				
				foreach($field as $f)
				{
						$value .= $f.',';
				}
				
				//chop off trailing comma
				$value = rtrim($value, ",");
							
				$packed[] = array($oField, $value);
			}
			else
			{
				$packed[] = array($oField, $field);
			}
		}
		
		$GLOBALS['default']->log->debug('metadataService updateMetadata packed '.print_r($packed, true));
		
		$packed = $this->mergeMetadata($oDocument, $packed);
		
		$GLOBALS['default']->log->debug('metadataService updateMetadata packed after merge'.print_r($packed, true));
		
		DBUtil::startTransaction();
	
		$oUser = User::get($_SESSION['userID']);
		
		if (PEAR::isError($oUser)) {
			$GLOBALS['default']->log->error("metadataService updateMetadata User {$_SESSION['userID']}: {$oUser->getMessage()}");
			return false;
		}
		    
		$oDocument->setLastModifiedDate(getCurrentDateTime());
		$oDocument->setModifiedUserId($oUser->getId());
		
		// Update the content version / document version
		$oDocument->startNewMetadataVersion($oUser);
	
		$res = $oDocument->update();    
		if (PEAR::isError($res))
		{
			DBUtil::rollback();
			//return new KTAPI_Error('Unexpected failure updating document', $res);
		}
	
		$result = KTDocumentUtil::saveMetadata($oDocument, $packed, array('novalidate'=>true));
		
		$GLOBALS['default']->log->debug('metadataService updateMetadata saveMetadata result '.print_r($result, true));
		
		if (is_null($result) || PEAR::isError($result))    
		{
			DBUtil::rollback();
		}
		    
		DBUtil::commit();
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata committed');
		
		$oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
		$aTriggers = $oKTTriggerRegistry->getTriggers('edit', 'postValidate');
	
		foreach ($aTriggers as $aTrigger) {
			$sTrigger = $aTrigger[0];
			$oTrigger = new $sTrigger;
			$aInfo = array(
				"document" => $oDocument,
				"aOptions" => $packed,
			);
	
			$oTrigger->setInfo($aInfo);
			$ret = $oTrigger->postValidate();
		}
	
		// update document object with additional fields / data from the triggers
		$oDocument = Document::get($oDocument->getId());
		$oFolder = Folder::get($oDocument->getFolderID());
	
		// Check if there are any dynamic conditions / permissions that need to be updated on the document
		// If there are dynamic conditions then update the permissions on the document
		// The dynamic condition test fails unless the document exists in the DB therefore update permissions after committing the transaction.
		include_once(KT_LIB_DIR.'/permissions/permissiondynamiccondition.inc.php');
		$iPermissionObjectId = $oFolder->getPermissionObjectID();
		$dynamicCondition = KTPermissionDynamicCondition::getByPermissionObjectId($iPermissionObjectId);
		
		if(!PEAR::isError($dynamicCondition) && !empty($dynamicCondition)){
			$res = KTPermissionUtil::updatePermissionLookup($oDocument);
			KTPermissionUtil::clearCache();
		}
		
		
		//now get the fields again so that we can send back the updated data
		$fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($oDocument, $oDocument->getDocumentTypeID());
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata fieldsets '.print_r($fieldsets, true));
		
		$fieldsresult = array();
		
		foreach ($fieldsets as $fieldset) 
		{	
			//Tag Cloud displayed elsewhere
			if ($fieldset->getNamespace() !== 'tagcloud')
			{		
				//assemble the fieldset values		
				$fieldsetsresult = array(
					'fieldsetid' => $fieldset->getId(),
					'name' => $fieldset->getName(),
					'description' => $fieldset->getDescription()
				);
				
				$fields = $fieldset->getFields();
				
				foreach ($fields as $field)   
				{
					$GLOBALS['default']->log->debug('metadataService updateMetadata field '.print_r($field, true));
					
					$value = '';
					
					$fieldvalue = DocumentFieldLink::getByDocumentAndField($oDocument, $field);
					
					$GLOBALS['default']->log->debug("metadataService updateMetadata fieldvalue $fieldvalue");
					
	                if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue)))
	                {
	                	$GLOBALS['default']->log->debug('metadataService updateMetadata fieldvalue not null');
	                	$value = $fieldvalue->getValue();
	                	$GLOBALS['default']->log->debug("metadataService updateMetadata value $value");
	                }
		
					$controltype = strtolower($field->getDataType());
		
					if ($field->getHasLookup())
					{
						$controltype = 'lookup';
						if ($field->getHasLookupTree())
						{
							$controltype = 'tree';
						}
					}
		
					// Options - Required for Custom Properties
					$options = array();
		
					if ($field->getInetLookupType() == 'multiwithcheckboxes' || $field->getInetLookupType() == 'multiwithlist') {
						$controltype = 'multiselect';
					}
		
					switch ($controltype)
					{
						case 'lookup':
							$selection = KTAPI::get_metadata_lookup($field->getId());
						break;
						case 'tree':
							$selection = KTAPI::get_metadata_tree($field->getId());
						break;
						case 'large text':
							$options = array(
								'ishtml' => $field->getIsHTML(),
								'maxlength' => $field->getMaxLength()
							);
		
							$selection= array();
						break;
						case 'multiselect':
							$selection = KTAPI::get_metadata_lookup($field->getId());
							$options = array(
								'type' => $field->getInetLookupType()
							);
						break;
						default:
							$selection= array();	                
					}
		
					//assemble the field values
					$fieldsresult[] = array(
						'fieldid' => $field->getId(),
						'name' => $field->getName(),
						'required' => $field->getIsMandatory(),
						'value' => $value == '' ? 'no value' : $value,
						'blankvalue' => $value=='' ? '1' : '0',
						'description' => $field->getDescription(),
						'control_type' => $controltype,
						'selection' => $selection,
						'options' => $options
					);
				}
				
				$fieldsetsresult['fields'] = $fieldsresult;
				$metadata[] = $fieldsetsresult;
			}
		}
		
		$GLOBALS['default']->log->debug('metadataService updateMetadata fieldsresult '.print_r($fieldsresult, true));
		
		//assemble the item to return
		$item['fields'] = $fieldsresult;
		
		//$json['success'] = $item;
		
		//echo(json_encode($json));
		//exit(0);
		
		$returnResponse[] = $item;
		
		//$this->addResponse('addedDocuments', $returnResponse);
        $this->addResponse('success', json_encode($returnResponse));

        return true;
    }
	
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
    	$GLOBALS['default']->log->debug('metadataService mergeMetadata '.print_r($document, true).' '.$newMetadata);
    	
        $currentMetadata = (array)KTMetadataUtil::fieldsetsForDocument($document);
        $metadataPack = array();
        
        $GLOBALS['default']->log->debug('metadataService mergeMetadata currentMetadata '.print_r($currentMetadata, true));

        foreach ($currentMetadata as $currentFieldset) {
            $currentFields = $currentFieldset->getFields();
            
            $GLOBALS['default']->log->debug('metadataService mergeMetadata currentFields '.print_r($currentFields, true));
            
            foreach ($currentFields as $currentField) {
            	
            	$GLOBALS['default']->log->debug('metadataService mergeMetadata currentField '.print_r($currentField, true));
            	
                $currentID = $currentField->getId();
                
                $GLOBALS['default']->log->debug("metadataService mergeMetadata currentID $currentID");
                
                $newValue = '';

                $fieldValue = DocumentFieldLink::getByDocumentAndField($document, $currentField);
                
                $GLOBALS['default']->log->debug("metadataService mergeMetadata fieldValue $fieldValue");
                
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
