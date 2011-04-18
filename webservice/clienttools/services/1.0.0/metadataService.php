<?php

require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');

// TODO Too much copy/paste going on.
//	  Get rid of old code or if still used then abstract out common sections.
//	  See plugins/ktcore/document/edit.php
//	  and presentation/lookAndFeel/knowledgetree/widgets/updateMetadata.php.

// TODO Check the old tag saving code to ensure it left all tag words even when
//	  no longer attached to a document.  Is so, note it, else new bug (fix.)

class metadataService extends client_service {

	public function changeDocumentTitle($params)
	{
		//$GLOBALS['default']->log->debug('metadataService changeDocumentTitle params '.print_r($params, true));
		
		$iDocumentID = $params['documentID'];
		$sTitle = $params['documentTitle'];
		
		$response = array();
	
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
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentTitle packed '.print_r($packed, true));
		
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
		
		$response[] = $item;
		
		$this->addResponse('success', json_encode($response));

		return true;
	}
	
	function changeDocumentFilename($params)
	{
		$iDocumentID = $params['documentID'];
		$sFilename = $params['documentFilename'];
		
		$GLOBALS['default']->log->debug("metadataService changeDocumentFilename $iDocumentID $sFilename");
		
		$response = array();
		
		//TODO: validate if legal filename
		$oVF =& KTValidatorFactory::getSingleton();
		//$GLOBALS['default']->log->debug('metadataService changeDocumentFilename oVF '.print_r($oVF, true));
		
		$oValidator = $oVF->get('ktcore.validators.illegal_char', array(
			'test' => 'name',
			'output' => 'name',
		));
		
		//$GLOBALS['default']->log->debug('changeDocumentFilename oValidator '.print_r($oValidator, true));
		
		$res = $oValidator->validate(array('name'=>$sFilename));
		
		$GLOBALS['default']->log->debug('changeDocumentFilename validation result '.print_r($res, true));
		
		if (empty($res['errors']))
		{
			$GLOBALS['default']->log->debug('metadataService changeDocumentFilename validation I AM EMPTY');
			
			$oUser = User::get($_SESSION['userID']);
		
			if (PEAR::isError($oUser)) {
				$GLOBALS['default']->log->error("metadataService changeDocumentFilename User {$_SESSION['userID']}: {$oUser->getMessage()}");
				return false;
			}
			
			$oDocument = &Document::get($iDocumentID);
			
			$res = KTDocumentUtil::rename($oDocument, $sFilename, $oUser);
			
			if (PEAR::isError($res)) {
				$GLOBALS['default']->log->error("metadataService changeDocumentFilename User {$res->getMessage()}");
				return false;
			}
	
			//assemble the item to return
			$item['documentID'] = $iDocumentID;
			$item['documentFilename'] = $oDocument->getFileName();
			
			$response[] = $item;
			
			//$GLOBALS['default']->log->debug('metadataService changeDocumentFilename success item '.print_r($item, true));
		
			$this->addResponse('success', json_encode($response));
	
			return true;
		}
		else 
		{
			$GLOBALS['default']->log->debug('changeDocumentFilename validation I AM NOT EMPTY');
			
			//assemble the item to return
			$item['documentID'] = $iDocumentID;
			$item['documentFilename'] = $sFilename;
			$item['message'] = $res['errors']['name'];
			
			$response[] = $item;
			
			//$GLOBALS['default']->log->debug('metadataService changeDocumentFilename error item '.print_r($item, true));
		
			$this->addResponse('error', json_encode($response));
	
			return true;
		}
	}
	
	function changeDocumentType($params) 
	{
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType params '.print_r($params, true));

		$iDocumentID = $params['documentID'];
		$iDocumentTypeID = $params['documentTypeID'];
		
		$oDocument = &Document::get($iDocumentID);
		if (is_null($oDocument) || ($oDocument === false)) {
			$GLOBALS['default']->log->error('The Document does not exist.');
			return false;
		}
		
		$response = array();
		
		$newType =& DocumentType::get($iDocumentTypeID);
		if (is_null($newType) || ($newType === false)) {
			$GLOBALS['default']->log->error('The DocumentType does not exist.');
			return false;
		}

		$oldType = DocumentType::get($oDocument->getDocumentTypeID());
		$oDocument->setDocumentTypeID($iDocumentTypeID);

		// we need to find fieldsets that _were_ in the old one, and _delete_ those.
		$for_delete = array();
		
		$oldFieldsets = KTFieldset::getForDocumentType($oldType);
		$newFieldsets = KTFieldset::getForDocumentType($newType);

		// prune from MDPack.
		foreach ($oldFieldsets as $oFieldset) {
			$old_fields = $oFieldset->getFields();
			foreach ($old_fields as $oField) {
				$for_delete[$oField->getId()] = 1;
			}
		}

		foreach ($newFieldsets as $oFieldset) {
			$new_fields = $oFieldset->getFields();
			foreach ($new_fields as $oField) {
				unset($for_delete[$oField->getId()]);
			}
		}

		$newPack = array( );
		foreach ($field_values as $MDPack) {
			if (!array_key_exists($MDPack[0]->getId(), $for_delete)) {
				$newPack[] = $MDPack;
			}
		}
		$field_values = $newPack;
		
		//need to preserve the existing tags, so get them!
		$currentTags = array();
		
		$fieldId = 2;
		$tagField = DocumentField::get($fieldId);
			 
		$fieldValue = DocumentFieldLink::getByDocumentAndField($oDocument, $tagField);
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType fieldValue '.print_r($fieldValue, true));
		
		if (!is_null($fieldValue) && (!PEAR::isError($fieldValue))) 
		{
			$value = $fieldValue->getValue();
			//$GLOBALS['default']->log->debug("metadataService changeDocumentType tag value $value");
			
			$currentTags = explode(",", $value);
		}
		else if (PEAR::isError($fieldValue))
		{
			$GLOBALS['default']->log->debug('metadataService changeDocumentType fieldValue error '.$fieldValue->getMessage());
		}
				
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType field_values tags '.print_r($currentTags, true));
		
		$field_values = array_merge($field_values, $currentTags);		
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType field_values after getting tags '.print_r($field_values, true));

		//flatten the array to a string
		$field_values = implode(",", $field_values);		
		
		//$GLOBALS['default']->log->debug("metadataService changeDocumentType field_values flat $field_values");
		
		$metadataPack[] = array($tagField, $field_values);
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType document '.print_r($oDocument, true));
		$oDocumentTransaction = & new DocumentTransaction($oDocument, 'update metadata.', 'ktcore.transactions.update');
		if (PEAR::isError($oDocumentTransaction)) {
			$GLOBALS['default']->log->error('Failed to create transaction '.$oDocumentTransaction->getMessage());
			return false;
		}
	   	
		$res = $oDocumentTransaction->create();
		if (PEAR::isError( $res)) {
			$GLOBALS['default']->log->error('Failed to create transaction.');
			return false;
		}

		$res = $oDocument->update();
		if (PEAR::isError( $res)) {
			$this->rollbackTransaction( );
			$GLOBALS['default']->log->error('Failed to change basic details about the document...');
			return false;
		}

		$res = KTDocumentUtil::saveMetadata($oDocument, $metadataPack, array('novalidate'=>true));
		
		if(!PEAR::isError($res) || !is_null($res))	
		{
			$oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
			$aTriggers = $oKTTriggerRegistry->getTriggers('edit', 'postValidate');

			foreach ($aTriggers as $aTrigger)
			{
				$sTrigger = $aTrigger[0];
				$oTrigger = new $sTrigger;
				$aInfo = array(
				"document" => $oDocument,
				"aOptions" => $field_values,
				);
				$oTrigger->setInfo($aInfo);
				$ret = $oTrigger->postValidate();
			}
		} 
		else {
			$this->rollbackTransaction();
			$GLOBALS['default']->log->error('An Error occurred in _setTransitionWorkFlowState');
			
			$item['documentID'] = $iDocumentID;
			$item['documentTypeID'] = $oDocumentType->getId();
			$item['documentTypeName'] = $oDocumentType->getName();
			$item['message'] = $res->getMessage();
			
			$json['error'] = $item;
			
			echo(json_encode($json));
			exit(0);
		}
		
		$oDocumentType = DocumentType::get($iDocumentTypeID);
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType oDocumentType '.print_r($oDocumentType, true));
  
		$metadata = array();
		$fieldsetsresult = array();
		
		// first get generic ids
		$generic_fieldsets = KTFieldset::getGenericFieldsets(array('ids' => false));
		//$GLOBALS['default']->log->debug('update generic_fieldsets '.print_r($generic_fieldsets, true));
		
		$fieldsets = $oDocumentType->getFieldsets();
		
		$total_fieldsets = array_merge($fieldsets, $generic_fieldsets);
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType total_fieldsets '.print_r($total_fieldsets, true));
		
		foreach ($total_fieldsets as $fieldset) 
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
				
				$fieldsresult = array();
				
				foreach ($fields as $field)   
				{
					$value = '';
		
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
							
							//we need to get rid of values that we do not need else the JSON object we create will be incorrect!
							SimpleFieldsetDisplay::recursive_unset($selection, array('treeid', 'parentid', 'fieldid'));
							
							//now convert to JSON
							$selection = json_encode($selection);
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
						'value' => $value == '' ? null : $value,
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
		
		//$GLOBALS['default']->log->debug('metadataService changeDocumentType metadata '.print_r($metadata, true));
		
		//assemble the item to return
		$item['documentID'] = $iDocumentID;
		$item['documentTypeID'] = $oDocumentType->getId();
		$item['documentTypeName'] = $oDocumentType->getName();
		$item['metadata'] = $metadata;
		
		$response[] = $item;
	
		$this->addResponse('success', json_encode($response));
	}
	
	function updateMetadata($params)
	{
		//$GLOBALS['default']->log->debug('metadataService updateMetadata params '.print_r($params, true));
		
		$iDocumentID = $params['documentID'];
		
		//now remove docID from the array
		unset($params['documentID']);
		
		$response = array();
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata '.print_r($params, true));
		
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
			//$GLOBALS['default']->log->debug("metadataService updateMetadata param $key $field");
			
			$oField = DocumentField::get($key);
			
			if (is_null($oField) || PEAR::isError($oField) || $oField instanceof KTEntityNoObjects)
			{
				//$GLOBALS['default']->log->debug("Could not resolve field: $oField->getName() ");	//on fieldset $fieldsetname for document id: $this->documentid");
				// exit graciously
				continue;
			}
	
			if(is_array($field))
			{
				//$GLOBALS['default']->log->debug('metadataService updateMetadata I am an array');
				
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
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata packed '.print_r($packed, true));
		
		$packed = $this->mergeMetadata($oDocument, $packed);
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata packed after merge '.print_r($packed, true));
		
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
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata saveMetadata result '.print_r($result, true));
		
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
					//$GLOBALS['default']->log->debug('metadataService updateMetadata field '.print_r($field, true));
					
					$value = '';
					
					$fieldvalue = DocumentFieldLink::getByDocumentAndField($oDocument, $field);
					
					//$GLOBALS['default']->log->debug("metadataService updateMetadata fieldvalue $fieldvalue");
					
					if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue)))
					{
						//$GLOBALS['default']->log->debug('metadataService updateMetadata fieldvalue not null');
						$value = $fieldvalue->getValue();
						//$GLOBALS['default']->log->debug("metadataService updateMetadata value $value");
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
		
		//$GLOBALS['default']->log->debug('metadataService updateMetadata fieldsresult '.print_r($fieldsresult, true));
		
		//assemble the item to return
		$item['fields'] = $fieldsresult;
		$response[] = $item;
		
		$this->addResponse('success', json_encode($response));

		return true;
	}
	
	/**
	 * Save submitted tags.
	 */
	public function saveTags($params)
	{
		//$GLOBALS['default']->log->debug('metadataService saveTags params '.print_r($params, true));
		
		$document = Document::get($params['documentID']);
		$newTags = explode(",", rtrim($params['tagcloud'], ','));
		
		//$GLOBALS['default']->log->debug('metadataService saveTags newTags '.print_r($newTags, true));
		
		$response = array();
		
		$origDocTypeId = $docTypeId = $document->getDocumentTypeId();

		// This is a cheat...should use something else to ensure the correct value.
		// Will work fine unless values are changed (which *should* never happen, but...)
		$fieldId = 2;
		$tagField = DocumentField::get($fieldId);
		
		
		$fieldValue = DocumentFieldLink::getByDocumentAndField($document, $tagField);
		
		//$GLOBALS['default']->log->debug('metadataService saveTags fieldValue '.print_r($fieldValue, true));
		
		$existingTags = '';
		$currentTags = array();
		
		if (!is_null($fieldValue) && (!PEAR::isError($fieldValue))) 
		{
			$value = $fieldValue->getValue();
			
			if (!is_null($value) && (!PEAR::isError($value))) 
			{
				//$GLOBALS['default']->log->debug("metadataService saveTags existing tags $value");
				
				$currentTags = explode(",", $value);
				
				//iterate through the new tags and only add them if they don't already exist as a tag in the field			
				foreach($newTags as $newTag)
				{			
					$GLOBALS['default']->log->debug("metadataService saveTags checking $newTag");
					
					if (!in_array($newTag, $currentTags))
					{
						$currentTags[] = $newTag;
					}
				}
			}
			else 
			{
				$GLOBALS['default']->log->error('metadataService saveTags existing tags value is null or error '.$value->getMessage());
				$currentTags = $newTags;
			}
		}		
		else 
		{
			$GLOBALS['default']->log->error('metadataService saveTags fieldValue is null or error '.$fieldValue->getMessage());
			$currentTags = $newTags;
		}
		
		//$GLOBALS['default']->log->debug('metadataService saveTags currentTags '.print_r($currentTags, true));
		
		//flatten the array to a string
		$totalTags = implode(",", $currentTags);
		
		//$totalTags = $existingTags.','.$newTags;
		
		//$GLOBALS['default']->log->debug("metadataService saveTags updated tags $totalTags");
		
		$tagData = array($tagField, $totalTags);
		
		$metadataPack = $this->mergeMetadata($document, array($tagData));
		
		//$GLOBALS['default']->log->debug('metadataService saveTags merged metadata '.print_r($metadataPack, true));

		DBUtil::startTransaction();

		$user = User::get($_SESSION['userID']);
		$document->startNewMetadataVersion($user);

		$res = $document->update();
		if (PEAR::isError($res)) {
			DBUtil::rollback();
			$GLOBALS['default']->log->error(sprintf(_kt('Unexpected failure to update document tags: %s'), $res->getMessage()));
			$response = array('saveTags' => $res->getMessage());
			$this->addResponse('error', json_encode($response));
			
			return false;
		}
		
		//$GLOBALS['default']->log->debug('metadataService saveTags metadataPack '.print_r($metadataPack, true));

		$coreRes = KTDocumentUtil::saveMetadata($document, $metadataPack, array('novalidate'=>true));
		if (PEAR::isError($coreRes)) {
			DBUtil::rollback();
			$GLOBALS['default']->log->error(sprintf(_kt('Unexpected failure to update document tags: %s'), $coreRes->getMessage()));
			$response = array('saveTags' => $coreRes->getMessage());
			$this->addResponse('error', json_encode($response));
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
		
		$item['tags'] = $newTags;		
		$response[] = $item;
		$this->addResponse('success', json_encode($response));

		return true;
	}
	
	public function deleteTag($params)
	{
		//$GLOBALS['default']->log->debug('metadataService deleteTag '.print_r($params, true));
		
		$document = Document::get($params['documentID']);
		$tagToDelete = $params['tag'];
		
		// This is a cheat...should use something else to ensure the correct value.
		// Will work fine unless values are changed (which *should* never happen, but...)
		$fieldId = 2;
		$tagField = DocumentField::get($fieldId);
		
		//$GLOBALS['default']->log->debug('metadataService deleteTag tagField '.print_r($tagField, true));
			 
		$fieldValue = DocumentFieldLink::getByDocumentAndField($document, $tagField);
				
		//$GLOBALS['default']->log->debug("metadataService deleteTag fieldValue ".print_r($fieldValue, true));
		
		if (!is_null($fieldValue) && (!PEAR::isError($fieldValue))) 
		{
			$value = $fieldValue->getValue();
			//$GLOBALS['default']->log->debug("metadataService deleteTag value $value");
			
			$currentTags = explode(",", $value);
			
			//$GLOBALS['default']->log->debug('metadataService deleteTag current tags '.print_r($currentTags, true));
			
			$newTags = array();
			
			//iterate through the existing tags, and build new array of tags without the deleted one
			foreach($currentTags as $currentTag)
			{				
				//$GLOBALS['default']->log->debug("metadataService deleteTag current tag $currentTag");
				
				if($currentTag !== $tagToDelete)
				{
					//$GLOBALS['default']->log->debug('metadataService deleteTag NOT FOUND');
					$newTags[] = $currentTag;
				}
			}
			
			//$GLOBALS['default']->log->debug('metadataService deleteTag new tags '.print_r($newTags, true));
			
			$tags = implode(",", $newTags);
			
			//$GLOBALS['default']->log->debug("metadataService deleteTag new tags: $tags");
			
			$tagData = array($tagField, $tags);
			
			//now update the new tags to the db
			$origDocTypeId = $docTypeId = $document->getDocumentTypeId();
			$metadataPack = $this->mergeMetadata($document, array($tagData));

			DBUtil::startTransaction();
	
			$user = User::get($_SESSION['userID']);
			$document->startNewMetadataVersion($user);
	
			$res = $document->update();
			if (PEAR::isError($res)) {
				DBUtil::rollback();
				$GLOBALS['default']->log->error(sprintf(_kt('Unexpected failure to update document tags: %s'), $res->getMessage()));
				$response = array('saveTags' => $res->getMessage());
				$this->addResponse('error', json_encode($response));
				
				return false;
			}
	
			$coreRes = KTDocumentUtil::saveMetadata($document, $metadataPack);
			if (PEAR::isError($coreRes)) {
				DBUtil::rollback();
				$GLOBALS['default']->log->error(sprintf(_kt('Unexpected failure to update document tags: %s'), $coreRes->getMessage()));
				$response = array('saveTags' => $coreRes->getMessage());
				$this->addResponse('error', json_encode($response));
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
		else 
		{
			//$GLOBALS['default']->log->debug('metadataService deleteTag fieldValue error '.$fieldValue->getMessage());
			
			//TODO
			
		}
	}

	/**
	 * Merge existing metadata with submitted metadata.
	 */
	private function mergeMetadata($document, $newMetadata = array())
	{		
		$currentMetadata = (array)KTMetadataUtil::fieldsetsForDocument($document);
		$metadataPack = array();

		foreach ($currentMetadata as $currentFieldset) 
		{
			$currentFields = $currentFieldset->getFields();
			
			foreach ($currentFields as $currentField) 
			{				
				$currentID = $currentField->getId();
				
				$newValue = '';

				$fieldValue = DocumentFieldLink::getByDocumentAndField($document, $currentField);
				
				if (!is_null($fieldValue) && (!PEAR::isError($fieldValue))) 
				{
					$newValue = $fieldValue->getValue();
				}

				foreach ($newMetadata as $fieldData) 
				{
					list($newField, $value) = $fieldData;
					$newId = $newField->getId();
					if ($currentID === $newId) 
					{
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
