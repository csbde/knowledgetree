<?php

require_once('../../../../config/dmsDefaults.php');
require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');
	
	// HTTP headers for no cache etc
	header('Content-type: text/plain; charset=UTF-8');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	//$GLOBALS['default']->log->debug('update documentID '.$_POST['documentID']);
	//$GLOBALS['default']->log->debug('update documentTypeID '.$_POST['documentTypeID']);
	
	$iDocumentID = (int)$_REQUEST['documentID'];
	$iDocumentTypeID = (int)$_POST['documentTypeID'];
	
	//now update the document type
	updateDocumentType($iDocumentID, $iDocumentTypeID);
	
	$oDocumentType = DocumentType::get($_POST['documentTypeID']);
  
	$metadata = array();
	$fieldsetsresult = array();
	
	// first get generic ids
    $generic_fieldsets = KTFieldset::getGenericFieldsets(array('ids' => false));
    //$GLOBALS['default']->log->debug('update generic_fieldsets '.print_r($generic_fieldsets, true));
	
	$fieldsets = $oDocumentType->getFieldsets();
	
	$total_fieldsets = array_merge($fieldsets, $generic_fieldsets);
	
	//$GLOBALS['default']->log->debug('update total_fieldsets '.print_r($total_fieldsets, true));
	
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
						$selection = $selection[-1]['fields'][0];
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
	
	//$GLOBALS['default']->log->debug('update metadata '.print_r($metadata, true));
	
	//assemble the item to return
	$item['documentID'] = $iDocumentID;
	$item['documentTypeID'] = $oDocumentType->getId();
	$item['documentTypeName'] = $oDocumentType->getName();
	$item['metadata'] = $metadata;
	
	$json['success'] = $item;
	
	echo(json_encode($json));
	exit(0);
	
	function updateDocumentType($iDocumentID, $iDocumentTypeID) 
	{
		//$GLOBALS['default']->log->debug("changeDocumentType updateDocumentType $iDocumentID $iDocumentTypeID");
		
        $oDocument =& Document::get($iDocumentID);
        if (is_null($oDocument) || ($oDocument === false)) {
            $GLOBALS['default']->log->error('The Document does not exist.');
            return false;
        }
        
        //only change the doctype if a new doctype has been selected!
        if ($oDocument->getDocumentTypeID() != $iDocumentTypeID)
		{
	        $newType =& DocumentType::get($iDocumentTypeID);
	        if (is_null($newType) || ($newType === false)) {
	            //$GLOBALS['default']->log->error('The DocumentType does not exist.');
	            return false;
	        }
	
	        $oldType = DocumentType::get($oDocument->getDocumentTypeID());
	        $oDocument->setDocumentTypeID($iDocumentTypeID);
	
	        // we need to find fieldsets that _were_ in the old one, and _delete_ those.
	        $for_delete = array( );
	        
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
	            $new_fields = $oFieldset->getFields( );
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
	
	        $oDocumentTransaction = & new DocumentTransaction($oDocument, 'update metadata.', 'ktcore.transactions.update');
	        
	        $res = $oDocumentTransaction->create( );
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
	
	        $res = KTDocumentUtil::saveMetadata($oDocument, $field_values);
	
	        //$GLOBALS['default']->log->debug("update setDocumentType result $res");
	        
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
	
	            return true;
	        } else {
	            $this->rollbackTransaction();
	            $GLOBALS['default']->log->error('An Error occurred in _setTransitionWorkFlowState');
	            return false;
	        }
		}
    }

?>