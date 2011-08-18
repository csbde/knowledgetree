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
 */

/*
 * The valiodator factory is a singleton, which can be used to create
 * and register validators.
 *
 */

class KTValidatorFactory {
    var $validators = array();

    static function &getSingleton () {
		static $singleton=null;
    	if (is_null($singleton))
    	{
    		$singleton = new KTValidatorFactory();
    	}
    	return $singleton;
    }

    function registerValidator($classname, $namespace,  $filename = null)
    {
        $this->validators[$namespace] = array(
            'ns' => $namespace,
            'class' => $classname,
            'file' => $filename,
        );
    }

    private function loadValidatorHelpers()
    {
        if (!empty($this->validators)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('validator');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[2])) {
                $params[2] = KTPluginUtil::getFullPath($params[2]);
            }
            call_user_func_array(array($this, 'registerValidator'), $params);
        }
    }
    
    function &getValidatorByNamespace($namespace) 
    {
        $this->loadValidatorHelpers();
        
        $info = KTUtil::arrayGet($this->validators, $namespace);
        if (empty($info)) {
            return PEAR::raiseError(sprintf(_kt('No such validator: %s'), $namespace));
        }
        
        if (!empty($info['file'])) {
            require_once($info['file']);
        }

        return new $info['class'];
    }

    // this is overridden to either take a namespace or an instantiated
    // class.  Doing it this way allows for a consistent approach to building
    // forms including custom widgets.
    function &get($namespaceOrObject, $config = null) 
    {
        if (is_string($namespaceOrObject)) {
            $validator =& $this->getValidatorByNamespace($namespaceOrObject);
        } else {
            $validator = $namespaceOrObject;
        }

        if (PEAR::isError($validator)) {
            return $validator;
        }

        $config = (array) $config; // always an array
        $res = $validator->configure($config);
        if (PEAR::isError($res)) {
            return $res;
        }

        return $validator;
    }
}

?>
