<?php

/**
 * $Id: criteriaregistry.php 5492 2006-06-04 20:50:43Z bryndivey $
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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

