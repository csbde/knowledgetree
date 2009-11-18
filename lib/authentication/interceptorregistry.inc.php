<?php

/**
 * $Id$
 *
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

    function registerInterceptor($class, $nsname, $path = '', $sPlugin = null) {
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
            return PEAR::raiseError(sprintf(_kt('Can\'t find interceptor: %s'), $nsname));
        }
        $oInterceptor =new $sClass;
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
