<?php
/**
 * $Id$
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

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');

require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");


/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class AjaxConditionalDispatcher extends KTStandardDispatcher {
    
    function do_main() {
        return "AJAX Error";
    }

    function handleOutput($data) {
        print $data;
    }

    function do_verifyAndUpdate() {
         header('Content-Type: text/xml');
         $oTemplating =& KTTemplating::getSingleton();

        $oTemplate = $oTemplating->loadTemplate("ktcore/conditional_ajax_verifyAndUpdate");
        $aTemplateData = array(
            
        );
        return $oTemplate->render($aTemplateData);
 
    }

    function do_updateFieldset() {
        global $main;
        $GLOBALS['default']->log->error(print_r($_REQUEST, true));
        header('Content-Type: application/xml');
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fieldset']);

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aValues[$matches[1]] = $v;
            }
        }

        $aNextFieldValues =& KTMetadataUtil::getNext($oFieldset, $aValues);
        
        $sWidgets = '';
        // convert these into widgets using the ever-evil...
        // function getWidgetForMetadataField($field, $current_value, $page, $errors = null, $vocab = null) 
        foreach ($aNextFieldValues as $aFieldInfo) {
            $vocab = array();
            $vocab[''] = 'Unset';
            foreach ($aFieldInfo['values'] as $md_v) { $vocab[$md_v->getName()] = $md_v->getName(); }
            $req = $aFieldInfo['field']->getIsMandatory();
            $oWidget = getWidgetForMetadataField($aFieldInfo['field'], null, $main, null, $vocab, array('required' => $req)) ;
            $sWidgets .= $oWidget->render();
        }
        
        return $sWidgets;
    }

    
    
    
    function _getFieldIdForMetadataId($iMetadata) {
	$sTable = 'metadata_lookup';
	$sQuery = "SELECT document_field_id FROM " . $sTable . " WHERE id = ?";
	$aParams = array($iMetadata);

	$res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'document_field_id');
	if (PEAR::isError($res)) {
	    return false;
	}
	return $res;
    }
    
    //http://localhost/knowledgetree/presentation/lookAndFeel/knowledgeTree/ajaxConditional.php?action=getConditionalData&masterid=6&type=connections
    //http://localhost/knowledgetree/presentation/lookAndFeel/knowledgeTree/ajaxConditional.php?action=getConditionalData&masterid=6&type=lookups
    //$type can be lookups or connections
    function do_getConditionalData(){
        $iMetadata = $_GET['masterid'];
        $type = $_GET['type'];
        
		$oFReg =& KTFieldsetRegistry::getSingleton();
		$oFieldSets = KTFieldset::getConditionalFieldsets();

		foreach ($oFieldSets as $oFieldset) {
            
    	    $aLookups = array();
    	    $aConnections = array();
    
    	    foreach($oFieldset->getFields() as $oField) {
    		$c = array();
    
    		foreach($oField->getEnabledValues() as $oMetadata) {
    		    $a = array();
    		    // print '<pre>';
    
    		    $nvals = KTMetadataUtil::getNextValuesForLookup($oMetadata->getId());
    		    if($nvals) {
    			foreach($nvals as $i=>$aVals) {
    			    $a = array_merge($a, $aVals);
    
    			    foreach($aVals as $id) {
    			      $field = $this->_getFieldIdForMetadataId($id);
    			      // print 'id ' . $id . ' is in field ' . $field . "<br/>";
    			      if(!in_array($field, $c)) {
    				$c[] = $field;
    			      }
    			    }
    			}
    		    }
    
    		    $aLookups[$oMetadata->getId()] = $a;
    		}
    		$aConnections[$oField->getId()] = $c;
    	    }
    
    	    //exit(0);
    
    	    $oJSON = new Services_JSON;
    	    switch ($type) {
	            case 'lookups':
    	            return $oJSON->encode($aLookups);
    	            break;
	            case 'connections' :
	                return $oJSON->encode($aConnections);
	                break;
	            default:
    	            return $oJSON->encode($aLookups);
    	    }
    	            
            return false;
		}
    }
    
}

$oDispatcher = new AjaxConditionalDispatcher();
$oDispatcher->dispatch();

?>
