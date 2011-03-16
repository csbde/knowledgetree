<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');

	// HTTP headers for no cache etc
	header('Content-type: text/plain; charset=UTF-8');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	//first get the document object
	$oDocument = Document::get($_REQUEST['documentID']);
	if (PEAR::isError($oDocument)) {
   		$GLOBALS['default']->log->error("persistMetadata Document {$_REQUEST['documentID']}: {$oDocument->getMessage()}");
   		return false;
    }
	    
	//$GLOBALS['default']->log->debug('persistMetadata REQUEST '.print_r($_REQUEST, true));

	//$GLOBALS['default']->log->debug('persistMetadata POST '.print_r($_POST, true));
	
	$fields = array();
	
	//cycle through the POST variables and get all the fields
	foreach($_POST as $key => $postVar)
	{
		//$GLOBALS['default']->log->debug("persistMetadata postVar $key $postVar");
		
		$oField = DocumentField::get($key);
		
 		if (is_null($oField) || PEAR::isError($oField) || $oField instanceof KTEntityNoObjects)
 		{
 			//$GLOBALS['default']->log->debug("Could not resolve field: $oField->getName() ");	//on fieldset $fieldsetname for document id: $this->documentid");
 			// exit graciously
 			continue;
 		}

		if(is_array($postVar))
		{
			$value = '';
			
			foreach($postVar as $var)
			{
					$value .= $var.',';
			}
			
			//chop off trailing comma
			$value = rtrim($value, ",");
						
			$packed[] = array($oField, $value);
		}
		else
		{
			$packed[] = array($oField, $postVar);
		}
	}
	
	//$GLOBALS['default']->log->debug('persistMetadata packed '.print_r($packed, true));
	
	$packed = mergeMetadata($oDocument, $packed);
	
	//$GLOBALS['default']->log->debug('persistMetadata packed after merge'.print_r($packed, true));
	
	DBUtil::startTransaction();

	$oUser = User::get($_SESSION['userID']);
	
	if (PEAR::isError($oUser)) {
		$GLOBALS['default']->log->error("persistMetadata User {$_SESSION['userID']}: {$oUser->getMessage()}");
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
	
	if (is_null($result))    
	{
		DBUtil::rollback();
		//return new PEAR_Error(KTAPI_ERROR_INTERNAL_ERROR . ': Null result returned but not expected.');
	}
	    
	if (PEAR::isError($result))
	{
		DBUtil::rollback();
		//return new KTAPI_Error('Unexpected validation failure', $result);
	}
	    
	DBUtil::commit();
	
	//$GLOBALS['default']->log->debug('persistMetadata committed');
	
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
	
	//$GLOBALS['default']->log->debug('persistMetadata fieldsets '.print_r($fieldsets, true));
	
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
				$value = '';
				
				$fieldvalue = DocumentFieldLink::getByDocumentAndField($oDocument, $field);
				
                if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue)))
                {
                	$value = $fieldvalue->getValue();
                	//$GLOBALS['default']->log->debug("persistMetadata fieldsresult fieldvalue $value");
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
	
	//$GLOBALS['default']->log->debug('persistMetadata fieldsresult '.print_r($fieldsresult, true));
	
	//assemble the item to return
	$item['fields'] = $fieldsresult;
	
	$json['success'] = $item;
	
	echo(json_encode($json));
	exit(0);
	
	/**
	 * Take the new metadata fields, and merge them into the document's existing metadata
	 *
	 * @param unknown_type $oDocument
	 * @param unknown_type $aNewMetadata
	 * @return unknown
	 */
	function mergeMetadata($oDocument, $aNewMetadata)
	{
		$aCurrentMetadata = (array) KTMetadataUtil::fieldsetsForDocument($oDocument);
    	
    	//$GLOBALS['default']->log->debug('mergeMetadata aCurrentMetadata '.print_r($aCurrentMetadata, true));
    	
    	$aMDPacked = array();
    	
    	foreach($aCurrentMetadata as $oCurrentFieldset)
    	{
    		//$GLOBALS['default']->log->debug('mergeMetadata oCurrentFieldset '.print_r($oCurrentFieldset, true));
    		
    		$oCurrentFields = $oCurrentFieldset->getFields();
			
			foreach ($oCurrentFields as $oCurrentField)   
			{
				//$GLOBALS['default']->log->debug('mergeMetadata oCurrentField '.print_r($oCurrentField, true));

				$iCurrentID = $oCurrentField->getId();
				
				$sNewValue = '';
				
				$sFieldValue = DocumentFieldLink::getByDocumentAndField($oDocument, $oCurrentField);
				if (!is_null($sFieldValue) && (!PEAR::isError($sFieldValue)))
				{
					$sNewValue = $sFieldValue->getValue();
				}
				
				//$GLOBALS['default']->log->debug("mergeMetadata oCurrentField ID $iCurrentID Value $sNewValue");

				foreach($aNewMetadata as $aInfo)
				{
					list($oNewField, $sValue) = $aInfo;
					
					$iNewID = $oNewField->getId();
					
					if($iCurrentID === $iNewID)
					{
						//use this value as the 'packed' value
						$sNewValue = $sValue;
					}
				}
				
				$aMDPacked[] = array($oCurrentField, $sNewValue);
			}
		}
		
		//$GLOBALS['default']->log->debug('mergeMetadata aMDPacked '.print_r($aMDPacked, true));
		
		return $aMDPacked;
	}

?>