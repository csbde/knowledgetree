<?php

//require_once('config/dmsDefaults.php');
require_once('ktapi/ktapi.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');
require_once(KT_LIB_DIR . '/widgets/FieldsetDisplayRegistry.inc.php');

	function getID()
	{
		echo 'getID';
		
		//$GLOBALS['default']->log->debug("update entire request ".print_r($_REQUEST, true));

	    if(isset($_REQUEST['documentID'])){
	       return (int)$_REQUEST['documentID'];
	    }

	    /*$id = 1;
	    $uri = $_REQUEST['cleanFolderID'];

		// Check for slash
		if (substr($uri, 0, 1) == '/') {
		    $uri = substr($uri, 1);
		}

		// Remove Query String
		$uri = preg_replace('/(\?.*)/i', '', $uri);

		if (substr($uri, 0, 2) == '00') {
			$id = KTUtil::decodeId(substr($uri, 2));
		}*/

		return $id;
	}
	
	// HTTP headers for no cache etc
	header('Content-type: text/plain; charset=UTF-8');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	
	//file_put_contents('update.txt', 'update.php', FILE_APPEND);
	//file_put_contents('update.txt', 'document type ID '.$_POST['documenttype'], FILE_APPEND);
	//$GLOBALS['default']->log->debug('update.php '.print_r($_POST, true));
	
	$oDocument = Document::get($_POST['documentID']);
	$oDocument->setDocumentTypeID($_POST['documentTypeID']);
	
	$oDocumentType = DocumentType::get($_POST['documentTypeID']);
	
	//$GLOBALS['default']->log->debug('update oDocument '.print_r($oDocument, true));
	
	$fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
  
	$fieldsets = array();
	
	//$aDocFieldsets = KTMetadataUtil::fieldsetsForDocument($oDocument);
	$aDocFieldsets = $oDocumentType->getFieldsets();
	$GLOBALS['default']->log->debug('update fieldsets '.print_r($aDocFieldsets, true));
	
	foreach ($aDocFieldsets as $oFieldset) 
	{		
		/*//$GLOBALS['default']->log->debug('update fieldset '.print_r($oFieldset, true));
		//$GLOBALS['default']->log->debug('update fieldset namespace '.$oFieldset->getNamespace());
		$displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
		//$GLOBALS['default']->log->debug('update fieldset displayClass '.print_r($displayClass, true));
		$fieldsetdisplay = new $displayClass($oFieldset);
		//$GLOBALS['default']->log->debug('update fieldsetdisplay '.print_r($fieldsetdisplay, true));
		
		array_push($fieldsets, new $displayClass($oFieldset));*/
		
		$GLOBALS['default']->log->debug('update fieldset '.print_r($oFieldset, true));
		
		$fields = $oFieldset->getFields();
		
		$GLOBALS['default']->log->debug('update fields '.print_r($fields, true));
		
		$fieldsresult = array();
		
		foreach ($fields as $field)   
		 {
			$value = '';
	
			$fieldvalue = DocumentFieldLink::getByDocumentAndField($oDocument, $field);

				if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue)))
                {
                	$value = $fieldvalue->getValue();
                }

                // Old
                //$controltype = 'string';
                // Replace with true
                $controltype = strtolower($field->getDataType());
                
                $GLOBALS['default']->log->debug("update SimpleFieldsetDisplay field controltype $controltype");

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
                
                $GLOBALS['default']->log->debug("update SimpleFieldsetDisplay field controltype2 $controltype");

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


                $fieldsresult[] = array(
                	'fieldid' => $field->getId(),
                	'name' => $field->getName(),
                	'required' => $field->getIsMandatory(),
                    'value' => $value == '' ? null : $value,
                    'blankvalue' => $value=='' ? '1' : '0',
                    'description' => $field->getDescription(),
                    'control_type' => $controltype,
                    'selection' => $selection,
                    'options' => $options,

                );
		}
		
		$GLOBALS['default']->log->debug('update SimpleFieldsetDisplay fieldsresult '.print_r($fieldsresult, true));
		//$fieldset_values[] = array('fieldset' => $oFieldset, 'fields' => $fieldsresult);
		$fieldset_values[] = $fieldsresult;
		
		//$fieldset_values['fieldset'] = $oFieldset;
		//$fieldset_values['fields'] = $fieldsresult;
	}
	
	$GLOBALS['default']->log->debug('fieldset_values '.print_r($fieldset_values, true));
		
	
	
	//$document_types = & DocumentType::getList("disabled=0");
	
	//$GLOBALS['default']->log->debug('update oDocumentType '.print_r($oDocumentType, true));  
	/*file_put_contents('update.txt', 'documentType '.print_r($oDocumentType, true), FILE_APPEND);  
	if (PEAR::isError($oDocumentType)) {   		
		$GLOBALS['default']->log->error("update DocumentType: {$oDocumentType->getMessage()}");   	
		//file_put_contents('update.txt', "update DocumentType: {$oDocumentType->getMessage()}", FILE_APPEND);	
		exit(0);
	}*/
	
	//$oDocument = Document::get(15928);
	/*$oOwner = User::get($oDocument->getOwnerID());
	$oCreator = User::get($oDocument->getCreatorID());
	$oModifier = User::get($oDocument->getModifiedUserId());*/
	
	/*$fieldsets = $oDocumentType->getFieldsets();	
	$GLOBALS['default']->log->debug('update fieldsets '.print_r($fieldsets, true));
	$fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
	foreach ($fieldsets as $fieldset) {
		$GLOBALS['default']->log->debug('update fieldset '.print_r($fieldset, true));
		$GLOBALS['default']->log->debug('update fieldset namespace '.$fieldset->getNamespace());
		$displayClass = $fieldsetDisplayReg->getHandler($fieldset->getNamespace());
		$GLOBALS['default']->log->debug('update fieldset displayClass '.print_r($displayClass, true));
		$fieldsetdisplay = new $displayClass($fieldset);
		$GLOBALS['default']->log->debug('update fieldsetdisplay '.print_r($fieldsetdisplay, true));
	}*/
	
	//assemble the item
	$item['documentTypeID'] = $oDocumentType->getId();
	$item['documentTypeName'] = $oDocumentType->getName();
	$item['fieldsets'] = $fieldsets;
	$item['fieldsetValues'] = $fieldset_values;
	//$item['document_types'] = $document_types;
	
	$json['success'] = $item;
	
	echo(json_encode($json));
	exit(0);
	
	//parse_str(file_get_contents("php://input"),$post_vars);
    //file_put_contents('update.txt', print_r($post_vars, true), FILE_APPEND);
	
	file_put_contents('update.txt', 'post specific '.$_POST['documenttype'], FILE_APPEND);
	file_put_contents('update.txt', 'whole post '.print_r($_POST, true), FILE_APPEND);
	//file_put_contents('update.txt', 'whole get'.print_r($_GET, true), FILE_APPEND);
	file_put_contents('update.txt', 'whole request '.print_r($_REQUEST, true), FILE_APPEND);

	//$GLOBALS['default']->log->debug("update documentID resolves to $documentID");

?>