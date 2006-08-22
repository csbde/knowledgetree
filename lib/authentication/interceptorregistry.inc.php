<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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

    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTInterceptorRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTInterceptorRegistry'] = new KTInterceptorRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTInterceptorRegistry'];
    }
    // }}}

    function registerInterceptor($class, $nsname, $path = "", $sPlugin = null) {
        $this->_aInterceptorsInfo[$nsname] = array($class, $nsname, $path, $sPlugin);
    }

    function getInterceptorInfo($nsname) {
        return $this->_aInterceptorsInfo[$nsname];
    }

    function &getInterceptor($nsname, $config = null) {
        $aInfo = $this->_aInterceptorsInfo[$nsname];
        $sClass = $aInfo[0];
        $sPath = $aInfo[2];
        if ($sPath) {
            if (file_exists($sPath)) {
                require_once($sPath);
            }
        }
        if (!class_exists($sClass)) {
            return PEAR::raiseError(sprintf(_kt("Can't find interceptor: %s"), $nsname));
        }
        $oInterceptor =& new $sClass;
        if ($config) {
            $oInterceptor->configure($config);
        }
        return $oInterceptor;
    }

    function &getInterceptorFromInstance($oInstance) {
        return $this->getInterceptor($oInstance->getInterceptorNamespace(), $oInstance->getConfig());
    }

    function &getConfiguredInstances() {
        $aInterceptorInstances = $this->_getInterceptorInstances();
        $aReturn = array();
        foreach ($aInterceptorInstances as $oInstance) {
            $oInterceptor = $this->getInterceptorFromInstance($oInstance);
            if (PEAR::isError($oInterceptor)) {
                continue;
            }
            $aReturn[] = $oInterceptor;
        }
        return $aReturn;
    }

    function checkInterceptorsForAuthenticated() {
        $oRegistry =& KTInterceptorRegistry::getSingleton();
        $aInterceptors = $oRegistry->getConfiguredInstances();
        $aErrors = array();
        foreach ($aInterceptors as $oInterceptor) {
            $oUser = $oInterceptor->authenticated();
            if (PEAR::isError($oUser)) {
                $aErrors[] = $oUser;
                continue;
            }
            if ($oUser) {
                return $oUser;
            }
        }
        if (count($aErrors)) {
            return $aErrors;
        }
        return false;
    }

    function _getInterceptorInstances() {
        return KTInterceptorInstance::getInterceptorInstances();
    }

    function checkInterceptorsForTakeOver() {
        $oRegistry =& KTInterceptorRegistry::getSingleton();
        $aInterceptors = $oRegistry->getConfiguredInstances();
        foreach ($aInterceptors as $oInterceptor) {
            $oInterceptor->takeover();
        }
        return false;
    }
}

?>
