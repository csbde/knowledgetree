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

require_once(KT_LIB_DIR . '/i18n/i18n.inc.php');

class KTi18nRegistry {
    var $_ai18nDetails = array();
    var $_ai18nLangs = array();
    var $_ai18ns = array();
    var $_aLanguages = array();

    static function &getSingleton() {
		if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTi18nRegistry')) {
			$GLOBALS['_KT_PLUGIN']['oKTi18nRegistry'] =& new KTi18nRegistry;
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

    function &geti18n($sDomain) {
        $oi18n =& KTUtil::arrayGet($this->_ai18ns, $sDomain);
        if (!empty($oi18n)) {
            return $oi18n;
        }
        $aDetails = KTUtil::arrayGet($this->_ai18nDetails, $sDomain);
        if (empty($aDetails)) {
            $oi18n =new KTi18nGeneric;
            return $oi18n;
        }
        $aDirectories = KTUtil::arrayGet($this->_ai18nLangs, $sDomain);
        $oi18n =new KTi18n($sDomain, $sDirectory='', $aDirectories);
        $this->_ai18ns[$sDomain] =& $oi18n;
        return $oi18n;
    }

    function &geti18nLanguages($sDomain) {
        return $this->_ai18nLangs[$sDomain];
    }

    function &getLanguages() {
        return $this->_aLanguages;
    }
}

