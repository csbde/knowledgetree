<?php

require_once('../../../../config/dmsDefaults.php');
require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . '/validation/validatorfactory.inc.php');
//require_once(KT_PLUGIN_DIR . '/ktcore/KTValidators.php');

	
	// HTTP headers for no cache etc
	header('Content-type: text/plain; charset=UTF-8');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	//$GLOBALS['default']->log->debug('update documentID '.$_POST['documentID']);
	//$GLOBALS['default']->log->debug('update documentTypeID '.$_POST['documentTypeID']);
	//$GLOBALS['default']->log->debug('update function '.$_POST['func']);
	
	$sFunction = $_REQUEST['func'];	
	$iDocumentID = (int)$_REQUEST['documentID'];
	
	$GLOBALS['default']->log->debug("updateMetadata $sFunction $iDocumentID");
	
	switch($sFunction)
	{
		case 'doctype':			
			$iDocumentTypeID = (int)$_POST['documentTypeID'];
			changeDocumentType($iDocumentID, $iDocumentTypeID);
		break;		
		case 'metadata':
			$aFields = $_POST;
			persistMetadata($iDocumentID, $aFields);
		break;
		case 'title':
			$sTitle = $_POST['documentTitle'];
			changeDocumentTitle($iDocumentID, $sTitle);
		break;
		case 'filename':
			$sFilename = $_POST['documentFilename'];
			changeDocumentFilename($iDocumentID, $sFilename);
		break;
	}
	
	function changeDocumentTitle($iDocumentID, $sTitle)
	{
		$GLOBALS['default']->log->debug("changeDocumentTitle $iDocumentID $sTitle");
	
		$oUser = User::get($_SESSION['userID']);
		
		if (PEAR::isError($oUser)) {
			$GLOBALS['default']->log->error("persistMetadata User {$_SESSION['userID']}: {$oUser->getMessage()}");
			return false;
		}
		
		DBUtil::startTransaction();
		
		$oDocument = &Document::get($iDocumentID);
		$oDocument->setName($sTitle);
	    $oDocument->setLastModifiedDate(getCurrentDateTime());
	    $oDocument->setModifiedUserId($oUser->getId());
	
	    $packed = mergeMetadata($oDocument, array());
		
		$GLOBALS['default']->log->debug('changeDocumentTitle packed '.print_r($packed, true));
	    
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
	
	    DBUtil::commit();
	
	    // create the document transaction record
	    $oDocumentTransaction = & new DocumentTransaction($oDocument, _kt('Document metadata updated'), 'ktcore.transactions.update');
	    $oDocumentTransaction->create();
		
		//assemble the item to return
		$item['documentID'] = $iDocumentID;
		$item['documentTitle'] = $sTitle;
		
		$json['success'] = $item;
		
		echo(json_encode($json));
		exit(0);
	}
	
	function changeDocumentFilename($iDocumentID, $sFilename)
	{
		$GLOBALS['default']->log->debug("changeDocumentFilename $iDocumentID $sFilename");
		
		//TODO: validate if legal filename
		$oVF =& KTValidatorFactory::getSingleton();
		$GLOBALS['default']->log->debug('changeDocumentFilename oVF '.print_r($oVF, true));
		
		$oValidator = $oVF->get('ktcore.validators.illegal_char', array(
            'test' => 'name',
            'output' => 'name',
        ));
        
        //$GLOBALS['default']->log->debug('changeDocumentFilename oValidator '.print_r($oValidator, true));
        
        $res = $oValidator->validate(array('name'=>$sFilename));
        
        //$GLOBALS['default']->log->debug('changeDocumentFilename validation result '.print_r($res, true));
        
        if (empty($res['errors']))
        {
        	//$GLOBALS['default']->log->debug('changeDocumentFilename validation I AM EMPTY');
        	
        	$oUser = User::get($_SESSION['userID']);
		
			if (PEAR::isError($oUser)) {
				$GLOBALS['default']->log->error("changeDocumentFilename User {$_SESSION['userID']}: {$oUser->getMessage()}");
				return false;
			}
			
			$oDocument = &Document::get($iDocumentID);
			
			$res = KTDocumentUtil::rename($oDocument, $sFilename, $oUser);
			
			if (PEAR::isError($res)) {
				$GLOBALS['default']->log->error("changeDocumentFilename User {$res->getMessage()}");
				return false;
	        }
	
	        //assemble the item to return
			$item['documentID'] = $iDocumentID;
			$item['documentFilename'] = $oDocument->getFileName();
			
			$json['success'] = $item;
			
			echo(json_encode($json));
			exit(0);
        }
        else 
        {
        	//$GLOBALS['default']->log->debug('changeDocumentFilename validation I AM NOT EMPTY');
        	
        	//assemble the item to return
			$item['documentID'] = $iDocumentID;
			$item['documentFilename'] = $sFilename;
			$item['message'] = $res['errors']['name'];
			
			$json['error'] = $item;
			
			echo(json_encode($json));
			exit(0);
        }
		
		
	}
	
	
	function changeDocumentType($iDocumentID, $iDocumentTypeID) 
	{		
		$GLOBALS['default']->log->debug("changeDocumentType $iDocumentID $iDocumentTypeID");
		
        $oDocument = &Document::get($iDocumentID);
        if (is_null($oDocument) || ($oDocument === false)) {
            $GLOBALS['default']->log->error('The Document does not exist.');
            return false;
        }
        
        $GLOBALS['default']->log->debug('changeDocumentType oDocument '.print_r($oDocument, true));
        
        $newType =& DocumentType::get($iDocumentTypeID);
        if (is_null($newType) || ($newType === false)) {
            //$GLOBALS['default']->log->error('The DocumentType does not exist.');
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
        
        $GLOBALS['default']->log->debug('changeDocumentType field_values '.print_r($field_values, true));

        $oDocumentTransaction = & new DocumentTransaction($oDocument, 'update metadata.', 'ktcore.transactions.update');
        
        $res = $oDocumentTransaction->create();
        if ( PEAR::isError( $res)) {
            $GLOBALS['default']->log->error('Failed to create transaction.');
            return false;
        }

        $res = $oDocument->update( );
        if ( PEAR::isError( $res)) {
            $this->rollbackTransaction( );
            $GLOBALS['default']->log->error('Failed to change basic details about the document...');
            return false;
        }

        $res = KTDocumentUtil::saveMetadata($oDocument, $field_values, array('novalidate'=>true));
        //$result = KTDocumentUtil::saveMetadata($oDocument, $packed, array('novalidate'=>true));

        $GLOBALS['default']->log->debug("changeDocumentType result $res");
        
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
		
		$GLOBALS['default']->log->debug('changeDocumentType oDocumentType '.print_r($oDocumentType, true));
  
		$metadata = array();
		$fieldsetsresult = array();
		
		// first get generic ids
	    $generic_fieldsets = KTFieldset::getGenericFieldsets(array('ids' => false));
	    //$GLOBALS['default']->log->debug('update generic_fieldsets '.print_r($generic_fieldsets, true));
		
		$fieldsets = $oDocumentType->getFieldsets();
		
		$total_fieldsets = array_merge($fieldsets, $generic_fieldsets);
		
		$GLOBALS['default']->log->debug('changeDocumentType total_fieldsets '.print_r($total_fieldsets, true));
		
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
							//remove the outer elements of the array as we don't need them!
							$selection = $selection[0];
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
		
		$GLOBALS['default']->log->debug('changeDocumentType metadata '.print_r($metadata, true));
		
		//assemble the item to return
		$item['documentID'] = $iDocumentID;
		$item['documentTypeID'] = $oDocumentType->getId();
		$item['documentTypeName'] = $oDocumentType->getName();
		$item['metadata'] = $metadata;
		
		$json['success'] = $item;
		
		echo(json_encode($json));
		exit(0);
    }
    
    function persistMetadata($iDocumentID, $aFields)
    {
    	$oDocument = Document::get($iDocumentID);
		if (PEAR::isError($oDocument)) {
	   		$GLOBALS['default']->log->error("persistMetadata Document {$_REQUEST['documentID']}: {$oDocument->getMessage()}");
	   		return false;
	    }
		    
		//$GLOBALS['default']->log->debug('persistMetadata REQUEST '.print_r($_REQUEST, true));
	
		//$GLOBALS['default']->log->debug('persistMetadata POST '.print_r($_POST, true));
		
		$fields = array();
		
		//cycle through the POST variables and get all the fields
		foreach($aFields as $key => $field)
		{
			//$GLOBALS['default']->log->debug("persistMetadata postVar $key $postVar");
			
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
		
		if (is_null($result) || PEAR::isError($result))    
		{
			DBUtil::rollback();
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
    }
    
    /**
	 * Take the new metadata fields, and merge them into the document's existing metadata
	 *
	 * @param unknown_type $oDocument
	 * @param unknown_type $aNewMetadata
	 * @return unknown
	 */
	function mergeMetadata($oDocument, $aNewMetadata = array())
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
				$GLOBALS['default']->log->debug('mergeMetadata CurrentField '.print_r($oCurrentField, true));

				$iCurrentID = $oCurrentField->getId();
				
				$sNewValue = '';
				
				$sFieldValue = DocumentFieldLink::getByDocumentAndField($oDocument, $oCurrentField);
				$GLOBALS['default']->log->debug('mergeMetadata CurrentField field value '.print_r($sFieldValue, true));
				if (!is_null($sFieldValue) && (!PEAR::isError($sFieldValue)))
				{
					$sNewValue = $sFieldValue->getValue();
				}
				
				$GLOBALS['default']->log->debug("mergeMetadata CurrentField ID $iCurrentID Value $sNewValue");

				foreach($aNewMetadata as $aInfo)
				{
					$GLOBALS['default']->log->debug('mergeMetadata foreach');
					
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