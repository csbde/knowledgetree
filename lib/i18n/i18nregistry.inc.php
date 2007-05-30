<?php

/**
 * $Id$
 *
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

require_once(KT_LIB_DIR . '/i18n/i18n.inc.php');

class KTi18nRegistry {
    var $_ai18nDetails = array();
    var $_ai18nLangs = array();
    var $_ai18ns = array();
    var $_aLanguages = array();

    function &getSingleton() {
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
            $oi18n =& new KTi18nGeneric;
            return $oi18n;
        }
        $aDirectories = KTUtil::arrayGet($this->_ai18nLangs, $sDomain);
        $oi18n =& new KTi18n($sDomain, $sDirectory, $aDirectories);
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

