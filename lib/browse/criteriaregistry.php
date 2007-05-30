<?php

/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

class KTCriteriaRegistry {
    var $_aCriteria = array();
    var $_aCriteriaDetails = array();
    var $_bGenericRegistered = false;

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS['_KT_CRITERIA'], 'oKTCriteriaRegistry')) {
            $GLOBALS['_KT_CRITERIA']['oKTCriteriaRegistry'] = new KTCriteriaRegistry;
        // $GLOBALS['_KT_CRITERIA']['oKTCriteriaRegistry']->_buildGenericCriteria();
        }
        return $GLOBALS['_KT_CRITERIA']['oKTCriteriaRegistry'];
    }

    function _buildGenericCriteria() {
        $aFields =& DocumentField::getList();
        foreach($aFields as $oField) {
            $sNamespace = $oField->getNamespace();
            $oFieldset =& KTFieldset::get($oField->getParentFieldset());
            if(is_null($oFieldset->userinfo)){continue;}
            $aInitialize = array(sprintf("%s: %s", $oFieldset->getName(), $oField->getName()), 'id', 'id', $oField->getId(), $sNamespace);
            $this->registerCriterion('GenericMetadataCriterion', $sNamespace, null, $aInitialize);
        }
        $this->_bGenericRegistered = true;
    }    

    function registerCriterion($sClassName, $sNamespace = null, $sFilename = null, $aInitialize = null) {
        $this->_aCriteriaDetails[$sNamespace] = array($sClassName, $sNamespace, $sFilename, $aInitialize);
    }

    function &getCriterion($sNamespace) {
        if(!$this->_bGenericRegistered) {
            $this->_buildGenericCriteria();
        }

        if (array_key_exists($sNamespace, $this->_aCriteria)) {
            return $this->_aCriteria[$sNamespace];
        }

        $aDetails = KTUtil::arrayGet($this->_aCriteriaDetails, $sNamespace);
        if (empty($aDetails)) {
            return null;
        }
        $sFilename = $aDetails[2];
        if (!empty($sFilename)) {
            require_once($sFilename);
        }
        $sClassName = $aDetails[0];
        $oCriterion =& new $sClassName();

    
    if(is_array($aDetails[3])) {
        call_user_func_array(array(&$oCriterion, 'initialize'), $aDetails[3]);
    }


        $this->_aCriteria[$sNamespace] =& $oCriterion;
        return $oCriterion;
    }

    function &getCriteria() {
    if(!$this->_bGenericRegistered) {
        $this->_buildGenericCriteria();
    }
        $aRet = array();

        foreach (array_keys($this->_aCriteriaDetails) as $sCriteriaName) {
            $aRet[$sCriteriaName] =& $this->getCriterion($sCriteriaName);
        }
        return $aRet;
    }

}

