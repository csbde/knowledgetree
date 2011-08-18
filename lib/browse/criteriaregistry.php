<?php

/*
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


    function _buildGenericCriteria() 
    {
        $this->loadCriteriaHelpers();
        
        $fields = DocumentField::getList();
        foreach($fields as $field) {
            $namespace = $field->getNamespace();
            $fieldset =& KTFieldset::get($field->getParentFieldset());
            if ($fieldset->getName() == 'Tag Cloud') {
                continue;
            }

            $initialize = array(sprintf("%s: %s", $fieldset->getName(), $field->getName()), 'id', 'id', $field->getId(), $namespace);
            $this->registerCriterion('GenericMetadataCriterion', $namespace, null, $initialize);
        }

        $this->_bGenericRegistered = true;
    }

    function registerCriterion($className, $namespace = null, $filename = null, $initialize = null) 
    {
        $this->_aCriteriaDetails[$namespace] = array($className, $namespace, $filename, $initialize);
    }

    private function loadCriteriaHelpers()
    {
        if (!empty($this->_aCriteriaDetails)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('criterion');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            $init = unserialize($params[3]);
            if ($init != false) {
               $params[3] = $init;
            }
            
            if (isset($params[2])) {
                $params[2] = KTPluginUtil::getFullPath($params[2]);
            }
            call_user_func_array(array($this, 'registerCriterion'), $params);
        }
    }
    
    function &getCriterion($namespace) 
    {
        if (!$this->_bGenericRegistered) {
            $this->_buildGenericCriteria();
        }
        
        if (array_key_exists($namespace, $this->_aCriteria)) {
            return $this->_aCriteria[$namespace];
        }
        
        $details = KTUtil::arrayGet($this->_aCriteriaDetails, $namespace);
        if (empty($details)) {
            return null;
        }
        
        $filename = $details[2];
        if (!empty($filename)) {
            require_once($filename);
        }
        $className = $details[0];
        $criterion =new $className();
        
        if (is_array($details[3])) {
            call_user_func_array(array(&$criterion, 'initialize'), $details[3]);
        }
        
        $this->_aCriteria[$namespace] =& $criterion;
        return $criterion;
    }

    function &getCriteria() 
    {
        if (!$this->_bGenericRegistered) {
            $this->_buildGenericCriteria();
        }
        $ret = array();
        
        foreach (array_keys($this->_aCriteriaDetails) as $criteriaName) {
            $ret[$criteriaName] =& $this->getCriterion($criteriaName);
        }
        return $ret;
    }

}

