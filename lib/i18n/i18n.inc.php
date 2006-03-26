<?

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

class KTi18n {
    var $sLang = null;
    var $sFilename = null;

    function KTi18n($sDomain, $sPath, $aLangDirectories = null) {
        $this->sDomain = $sDomain;
        $this->sPath = $sPath;
        $this->aLangDirectories = $aLangDirectories;
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
