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
