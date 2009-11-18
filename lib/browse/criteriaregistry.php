<?php

/*
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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

class KTCriteriaRegistry {
    var $_aCriteria = array();
    var $_aCriteriaDetails = array();
    var $_bGenericRegistered = false;

    static function &getSingleton () {
		if (!KTUtil::arrayGet($GLOBALS['_KT_CRITERIA'], 'oKTCriteriaRegistry'))  {
			$GLOBALS['_KT_CRITERIA']['oKTCriteriaRegistry'] = new KTCriteriaRegistry;
		}
		return $GLOBALS['_KT_CRITERIA']['oKTCriteriaRegistry'];
    }


    function _buildGenericCriteria() {
        $aFields =& DocumentField::getList();
        foreach($aFields as $oField) {
            $sNamespace = $oField->getNamespace();
            $oFieldset =& KTFieldset::get($oField->getParentFieldset());
            if ($oFieldset->getName() == 'Tag Cloud')
            {
                continue;
            }

            //if(is_null($oFieldset->userinfo)){continue;}
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
        $oCriterion =new $sClassName();


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

