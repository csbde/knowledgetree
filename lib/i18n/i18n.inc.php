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

class KTi18n {
    var $sLang = null;
    var $sFilename = null;

    function KTi18n($sDomain, $sPath, $aLangDirectories = null) {
        $this->sDomain = $sDomain;
        $this->sPath = $sPath;
        $this->aLangDirectories = $aLangDirectories;
    }

    function addLanguage($sLang, $sLocation) {
        $this->aLangDirectories[$sLang] = $sLocation;
        $this->sFilename = null;
    }

    function _generateLanguage() {
        if (!empty($this->sLang)) {
            return;
        }

        if ($this->sLang === false) {
            return;
        }

        global $default;
        $this->sLang = $default->defaultLanguage;
        return;
    }

    function _generateFilePath() {
        if (!empty($this->sFilename)) {
            return;
        }

        if ($this->sFilename === false) {
            return;
        }

        $sLocation = KTUtil::arrayGet($this->aLangDirectories, $this->sLang);
        if (empty($sLocation)) {
            $sLocation = $this->sPath;
        }

        if ($sLocation === "default") {
            $this->sFilename = false;
            return;
        }

        $aTry = array(
            sprintf("%s/%s/%s", $sLocation, $this->sLang, $this->sDomain),
            sprintf("%s/%s", $sLocation, $this->sDomain),
        );
        foreach ($aTry as $sTry) {
            $sPO = sprintf("%s.po", $sTry);
            if (file_exists($sPO)) {
                $this->sFilename = $sPO;
                $_format = "PO";
                break;
            }
            $sMO = sprintf("%s.mo", $sTry);
            if (file_exists($sMO)) {
                $this->sFilename = $sMO;
                $_format = "MO";
                break;
            }
        }
        if (empty($this->sFilename)) {
            $this->sFilename = false;
            return;
        }

        $this->_getStrings($_format, $this->sFilename);
    }

    function _getStrings($_format) {
        $oCache = KTCache::getSingleton();
        list($bCached, $stuff) = $oCache->get("i18nstrings", $this->sFilename);
        if (empty($bCached)) {
            require_once('File/Gettext.php');

            $this->oLang = File_Gettext::factory($_format, $this->sFilename);
            $bLoaded = $this->oLang->load();
            $stuff = $this->oLang->toArray();
            $oCache->set("i18nstrings", $this->sFilename, $stuff);
        }
        $this->aMeta = $stuff['meta'];
        $this->aStrings = $stuff['strings'];
    }

    function gettext($sContents) {
        $this->_generateLanguage();
        $this->_generateFilePath();

        if (empty($this->sFilename)) {
            return $sContents;
        }

        return KTUtil::arrayGet($this->aStrings, $sContents, $sContents);
        return dcgettext($this->sDomain, $sContents, LC_MESSAGES);
    }
}

class KTi18nGeneric {
    function KTi18n() {
    }

    function gettext($sContents) {
        return $sContents;
    }
}
