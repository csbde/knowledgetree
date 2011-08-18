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

require_once(KT_LIB_DIR . '/i18n/i18n.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

class KTi18nRegistry {
    var $_ai18nDetails = array();
    var $_ai18nLangs = array();
    var $_ai18ns = array();
    var $_aLanguages = array();

    static function &getSingleton() {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTi18nRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTi18nRegistry'] = new KTi18nRegistry;
		}
		return $GLOBALS['_KT_PLUGIN']['oKTi18nRegistry'];
    }

    function registeri18n($sDomain, $sDirectory) {
        $this->_ai18nDetails[$sDomain] = array($sDomain, $sDirectory);
    }

    function registeri18nLang($sDomain, $sLang, $sDirectory) {
        if (empty($this->_ai18nLangs[$sDomain])) {
            $this->_ai18nLangs[$sDomain] = array();
        }
        if (is_string($sLang)) {
            $aLang = array($sLang);
        } else {
            $aLang = $sLang;
        }
        $oi18n =& KTUtil::arrayGet($this->_ai18ns, $sDomain);

        foreach ($aLang as $sLang) {
            $this->_ai18nLangs[$sDomain][$sLang] = $sDirectory;
            if (!empty($oi18n)) {
                $oi18n->addLanguage($sLang, $sDirectory);
            }
        }
        if (!empty($oi18n)) {
            $this->_ai18ns[$sDomain] =& $oi18n;
        }
    }

    function registerLanguage($sLanguage, $sLanguageName) {
        $this->_aLanguages[$sLanguage] = $sLanguageName;
    }

    private function loadLanguageHelpers()
    {
        if (!empty($this->_aLanguages) || !empty($this->_ai18ns)) {
            return ;
        }
        
        $helpersLang = KTPluginUtil::loadPluginHelpers('i18nlang');
        
        foreach ($helpersLang as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[2]) && $params[2] != 'default') {
                $params[2] = KTPluginUtil::getFullPath($params[2]);
            }
            call_user_func_array(array($this, 'registeri18nLang'), $params);
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('i18n');
        $helpers = array_merge($helpersLang, $helpers);
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            if (isset($params[2])) {
                $params[1] = $params[2];
                unset($params[2]);
            } 
            $params[1] = KTPluginUtil::getFullPath($params[1]);
            call_user_func_array(array($this, 'registeri18n'), $params);
        }
        
        $helpers = KTPluginUtil::loadPluginHelpers('language');
        
        foreach ($helpers as $helper) {
            extract($helper);
            $params = explode('|', $object);
            
            call_user_func_array(array($this, 'registerLanguage'), $params);
        }
    }
    
    function &geti18n($domain) 
    {
        $this->loadLanguageHelpers();
        
        $i18n =& KTUtil::arrayGet($this->_ai18ns, $domain);
        if (!empty($i18n)) {
            return $i18n;
        }
        
        $details = KTUtil::arrayGet($this->_ai18nDetails, $domain);
        if (empty($details)) {
            $i18n = new KTi18nGeneric;
            return $i18n;
        }
        
        $directories = KTUtil::arrayGet($this->_ai18nLangs, $domain);
        $i18n =new KTi18n($domain, $directory='', $directories);
        $this->_ai18ns[$domain] =& $i18n;
        return $i18n;
    }

    function &geti18nLanguages($domain) 
    {
        $this->loadLanguageHelpers();
        return $this->_ai18nLangs[$domain];
    }

    function &getLanguages() 
    {
        $this->loadLanguageHelpers();
        return $this->_aLanguages;
    }
}

