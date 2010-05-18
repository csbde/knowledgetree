<?php
/**
 * Bulk Actions API for KnowledgeTree
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
 */

/**
 * @copyright 2008-2010, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 */

require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");

/**
 * API for the getting Conditional Metadata
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_ConditionalMetadata
{
    /**
     * Instance of the KTAPI object
     *
     * @access private
     */
    private $ktapi;
	
	/**
     * Flag whether the rules have been run or not
     *
     * @access private
     */
	private $rulesRun = FALSE;
	
	/**
     * List of Connections
     *
     * @access private
     */
	private $conditionalMetadataConnections;

    /**
     * Constructs the bulk actions object
     *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi Instance of the KTAPI object
     */
    public function __construct(&$ktapi)
    {
        $this->ktapi = $ktapi;
    }
	
	public function getConditionalMetadataRules()
	{
		return $this->_getConditionalMetadataRules();
	}
	
	public function getConditionalMetadataConnections()
	{
		if (!$this->rulesRun) {
			$this->_getConditionalMetadataRules();
		}
		
		return $this->conditionalMetadataConnections;
	}
	
	/**
	 * Method to get the Conditional Metadata Rules
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 */
	private function _getConditionalMetadataRules()
	{
		$oFReg =& KTFieldsetRegistry::getSingleton();
		$oFieldSets = KTFieldset::getConditionalFieldsets();
        
        $fieldSetFields = array();
        $fieldChanges = array();
        $betterFieldChanges = array();
        
		$aConnections = array();
		
		foreach ($oFieldSets as $oFieldset)
        {
            
            // step 1 - create array of fields
            foreach($oFieldset->getFields() as $oField) {
                
                $fieldSetFields['F_'.$oField->getID()] = $oField->getName();
    	    }
            
			// step 2 - now convert data into rules
    	    foreach($oFieldset->getFields() as $oField) {
				
				$c = array();
                
                foreach($oField->getEnabledValues() as $oMetadata) {
                    
                    $nvals = KTMetadataUtil::getNextValuesForLookup($oMetadata->getId());
                    if($nvals) {
                        foreach($nvals as $i=>$aVals) {
                            foreach($aVals as $id) {
								
                                $fieldId = $this->_getFieldIdForMetadataId($id);
                                $fieldValue = $this->_getFieldValueForMetadataId($id);
								
								if(!in_array($fieldId, $c)) {
		                        	$c[] = $fieldId;
		    			      	}
                                
								// Check if Source Field is in Array
                                if (!isset($betterFieldChanges[$oField->getId()])) {
                                    $betterFieldChanges[$oField->getId()] = array();
                                }
                                
								// Check if Select Value in Source Field
                                if (!isset($betterFieldChanges[$oField->getId()][$oMetadata->getName()])) {
                                    $betterFieldChanges[$oField->getId()][$oMetadata->getName()] = array();
                                }
                                
								// Check if Target Field is in Array
                                if (!isset($betterFieldChanges[$oField->getId()][$oMetadata->getName()][$fieldId])) {
                                    $betterFieldChanges[$oField->getId()][$oMetadata->getName()][$fieldId] = array();
                                }
                                
								// Add Target Value into Field
                                $betterFieldChanges[$oField->getId()][$oMetadata->getName()][$fieldId][] = $fieldValue;
                                
								/*
                                // $fieldChanges is for debug
                                $fieldChanges[] = array(
                                    'field'=>$oField->getName(),
                                    'value'=>$oMetadata->getName(),
                                    'valueid'=>$oMetadata->getId(),
                                    'field2'=>$fieldSetFields['F_'.$fieldId],
                                    'value2'=>$fieldValue,
                                    'value2id'=>$id,
                                );*/
                            }
                        }
                    }
                }
				
				
				$aConnections[$oField->getId()] = $c;
				
    	    }
            
            
		}
		
		
		/*
		 // for debug
		foreach ($fieldChanges as $change)
		{
			echo '<p>';
			echo ' if '.$change['field'].' = '.$change['value'].'('.$change['valueid'].') then '.$change['field2'].' can have a value of '.$change['value2'].' ('.$change['value2id'].')';
			echo '</p>';
		}*/
		
		$this->rulesRun = TRUE;
		
		
		$newConnections = array();
		
		foreach ($aConnections as $id=>$sub) {
			//$newConnections[$fieldSetFields['F_'.$id]] = $fieldSetFields['F_'.$sub[0]];
			$newConnections[$id] = $sub;
		}
		
		
		$this->conditionalMetadataConnections = $newConnections;
        
        return $betterFieldChanges;
	}

	/**
	 * Method to get the Field Id of a Metadata Lookup Item
	 *
	 * @author KnowledgeTree Team
	 * @access private
	 */
    private function _getFieldIdForMetadataId($iMetadata) {
        $sTable = 'metadata_lookup';
        $sQuery = "SELECT document_field_id FROM " . $sTable . " WHERE id = ?";
        $aParams = array($iMetadata);
    
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'document_field_id');
        if (PEAR::isError($res)) {
            return false;
        }
        return $res;
    }
    
	/**
	 * Method to get the Value of a Metadata Lookup Item
	 *
	 * @author KnowledgeTree Team
	 * @access private
	 */
    private function _getFieldValueForMetadataId($iMetadata) {
        $sTable = 'metadata_lookup';
        $sQuery = "SELECT name FROM " . $sTable . " WHERE id = ?";
        $aParams = array($iMetadata);
    
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'name');
        if (PEAR::isError($res)) {
            return false;
        }
        return $res;
    }
}
?>
