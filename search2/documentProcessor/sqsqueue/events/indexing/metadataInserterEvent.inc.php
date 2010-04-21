<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */
/**
 * Load simple event modeling class queueEvent
 */
require_once(realpath(dirname(__FILE__) . '/../../queueEvent.php'));

class metadataInserterEvent extends queueEvent 
{
	/**
	 * List of event dependencies
	 * @var array
	 */
	public $list_of_dependencies = array();
	/**
	 * Parameters to be passed with event
	 * @var array
	 */
	public $list_of_parameters = array();
	/**
	 * Callbacks to be envoked
	 * @var array
	 */
	public $list_of_callbacks = array();
	
    /**
    * Construct metadata insertion Event
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function __construct() 
	{
		parent::setName('metadataInserterEvent');
		parent::setMessage('metadataInserter.run');
	}
	
    /**
    * Create parameters needed by event
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function buildParameters() 
	{
		$this->addParameter('doc_id', $this->document->getId());
		$this->addParameter('src_file', $this->getSrcFile());
		$this->addParameter('dest_file', $this->getSrcFile());
		$this->addParameter('metadata', $this->getMetadata());
	}
	
	
    /**
    * Get document metadata formatted as key value pairs.
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	private function getMetadata() 
	{
		$kv_pairs = array();
		$metadata = $this->get_metadata();
		foreach ($metadata as $fieldsets) {
			$fieldset_name = $fieldsets['fieldset'];
			$fieldset_description = $fieldsets['description'];
			foreach ($fieldsets['fields'] as $key=>$field) {
				$field_name = $field['name'];
				$field_value = $field['value'];
				$kv_pairs[$field_name] = $field_value;
			}
		}

		return $kv_pairs;
	}
	
	/**
	 * This returns all metadata for the document.
	 *
	 * @author KnowledgeTree Team
	 * @access private
	 * @return array An array of metadata fieldsets and fields
	 */
	private function get_metadata()
	{
		 $doctypeid = $this->document->getDocumentTypeID();
		 $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->document, $doctypeid);
		 if (is_null($fieldsets) || PEAR::isError($fieldsets))
		 {
		     return array();
		 }

		 $results = array();

		 foreach ($fieldsets as $fieldset)
		 {
		    // this line caused conditional metadata to not be present, and it is there when this is commented out;
		    // if there are problems with conditional metadata in future, check here to make sure this is not the cause
//		 	if ($fieldset->getIsConditional()) {	/* this is not implemented...*/	continue;	}

		 	$fields = $fieldset->getFields();
		 	$result = array('fieldset' => $fieldset->getName(),
		 					'description' => $fieldset->getDescription());

		 	$fieldsresult = array();

            foreach ($fields as $field)
            {
                $value = '';

				$fieldvalue = DocumentFieldLink::getByDocumentAndField($this->document, $field);
                if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue)))
                {
                	$value = $fieldvalue->getValue();
                }

                // Old
                //$controltype = 'string';
                // Replace with true
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


                $fieldsresult[] = array(
                	'name' => $field->getName(),
                	'required' => $field->getIsMandatory(),
                    'value' => $value == '' ? 'n/a' : $value,
                    'blankvalue' => $value=='' ? '1' : '0',
                    'description' => $field->getDescription(),
                    'control_type' => $controltype,
                    'selection' => $selection,
                    'options' => $options,

                );

            }
            $result['fields'] = $fieldsresult;
            $results [] = $result;
		 }

		 return $results;
	}
}
?>