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

require_once(KT_LIB_DIR . '/authentication/interceptorinstances.inc.php');

/**
 * This is where all login interceptors register themselves as available
 * to the system.
 *
 * Login interceptors allow for the login process to be more dynamic -
 * to call external programs to perform authentication, to redirect to
 * external authentication web sites, and so forth.
 */
class KTInterceptorRegistry {
    var $_aInterceptorsInfo = array();

    static function &getSingleton () {
		if  (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'],  'oKTInterceptorRegistry'))  {
        	$GLOBALS['_KT_PLUGIN']['oKTInterceptorRegistry']  =  new  KTInterceptorRegistry;
		}
		return  $GLOBALS['_KT_PLUGIN']['oKTInterceptorRegistry'];
    }

    function registerInterceptor($class, $nsname, $path = '', $plugin = null) 
    {
        $this->_aInterceptorsInfo[$nsname] = array($class, $nsname, $path, $plugin);
    }

    private function loadInterceptorHelpers()
    {
        if (!empty($this->_aInterceptorsInfo)) {
            return ;
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('interceptor');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[2])) {
                $params[2] = KTPluginUtil::getFullPath($params[2]);
            }
            call_user_func_array(array($this, 'registerInterceptor'), $params);
        }
    }
    
    function getInterceptorInfo($nsname) 
    {
        $this->loadInterceptorHelpers();
        
        return $this->_aInterceptorsInfo[$nsname];
    }

    function &getInterceptor($nsname, $config = null) 
    {
        $info = $this->getInterceptorInfo($nsname);
        $class = $info[0];
        $path = $info[2];
        
        if (!class_exists($class)) {
        
            if ($path) {
                if (file_exists($path)) {
                    require_once($path);
                }
            }
            
            if (!class_exists($class)) {
                return PEAR::raiseError(sprintf(_kt('Can\'t find interceptor: %s'), $nsname));
            }
        }
        
        $interceptor =new $class;
        if ($config) {
            $interceptor->configure($config);
        }
        return $interceptor;
    }

    function &getInterceptorFromInstance($instance) 
    {
        return $this->getInterceptor($instance->getInterceptorNamespace(), $instance->getConfig());
    }

    function &getConfiguredInstances() 
    {
        $interceptorInstances = $this->_getInterceptorInstances();
        $return = array();
        foreach ($interceptorInstances as $instance) {
            $interceptor = $this->getInterceptorFromInstance($instance);
            if (PEAR::isError($interceptor)) {
                continue;
            }
            $return[] = $interceptor;
        }
        return $return;
    }

    function checkInterceptorsForAuthenticated() 
    {
        $registry = KTInterceptorRegistry::getSingleton();
        $interceptors = $registry->getConfiguredInstances();
        
        $errors = array();
        foreach ($interceptors as $interceptor) {
            $user = $interceptor->authenticated();
            if (PEAR::isError($user)) {
                $errors[] = $user;
                continue;
            }
            
            if ($user) {
                return $user;
            }
        }
        
        if (count($errors)) {
            return $errors;
        }
        
        return false;
    }

    function _getInterceptorInstances() 
    {
        return KTInterceptorInstance::getInterceptorInstances();
    }

    function checkInterceptorsForTakeOver() 
    {
        $registry = KTInterceptorRegistry::getSingleton();
        $interceptors = $registry->getConfiguredInstances();
        
        foreach ($interceptors as $interceptor) {
            $interceptor->takeOver();
        }
        
        return false;
    }
}

?>