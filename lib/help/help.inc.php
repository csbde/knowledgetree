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

/* help has changed significantly.  see /help.php */

require_once(KT_LIB_DIR . "/database/dbutil.inc");

class KTHelp {

    // world's simplest (and possibly worst) regex-split.
    function _parseHTML($sHTML) {
        $title_array = preg_split('#</?title>#',$sHTML,-1,PREG_SPLIT_NO_EMPTY);
        $body_array = preg_split('#</?body>#',$sHTML,-1,PREG_SPLIT_NO_EMPTY);

        $res = array();
        if (count($title_array) > 2) {
            $res['title'] = $title_array[1];
        }

        if (count($body_array) > 2) {
            $res['body'] = $body_array[1];
        }

        //var_dump($body_array);
        return $res;
    }

    // $sFSPath : filesystem path for the resource.
    // return true or false
    function isImageFile($sFSPath) {
        $pi = pathinfo($sFSPath);
        $mime_type = "";
        $sExtension = KTUtil::arrayGet($pi, 'extension');
        if (!empty($sExtension)) {
            $mime_type = DBUtil::getOneResultKey(array("SELECT mimetypes FROM " . KTUtil::getTableName('mimetypes') . " WHERE LOWER(filetypes) = ?", $sExtension), "mimetypes");
        }

        if (($mime_type == 'image/png') || ($mime_type == 'image/gif') || ($mime_type == 'image/jpeg')) {
            return true;
        }

        return false;
    }

    // output the help image referred to by the subpath
    function outputHelpImage($sSubPath) {
        // FIXME there are error cases here ...
        $aPathInfo = KTHelp::_getLocationInfo($sSubPath);
        if (PEAR::isError($aPathInfo)) { return $aPathInfo; } // gets caught further up the stack

        $pi = pathinfo($aPathInfo['external']);
        $mime_type = "";
        $sExtension = KTUtil::arrayGet($pi, 'extension');
        if (!empty($sExtension)) {
            $mime_type = DBUtil::getOneResultKey(array("SELECT mimetypes FROM " . KTUtil::getTableName('mimetypes') . " WHERE LOWER(filetypes) = ?", $sExtension), "mimetypes");
        }

        header("Content-Type: $mime_type");
        header("Content-Length: " . filesize($fspath));
        readfile($fspath);   // does this output it?!
        exit(0);
    }

    /*
    input:
        sSubPath : the path in the form "plugin/lower-level-file"
        sLangCode : the language code in the form "en_US"

    returns a dictionary
    {
        'is_image': string
        'title': string
        'body': string
    }
    */
    function getHelpInfo($sSubPath, $sLangCode = null) {
        $aInfo = array(
            'is_image' => false,
            'title' => null,
            'body' => null,
            'help_id' => null,
            'name' => null,
        );
        $aPathInfo = KTHelp::_getLocationInfo($sSubPath, $sLangCode);
        if (PEAR::isError($aPathInfo)) {
            return $aPathInfo;
        }

        // first, check the extension to see if its an image.
        // failing that, check the DB for an entry (+ return if found)
        // failing that, check the FS for the entry (+ return if found)
        // failing that, through an exception.
        if (!empty($aPathInfo['external'])) {
            if (KTHelp::isImageFile($aPathInfo['external'])) {
                $aInfo['is_image'] = true;
                return $aInfo;
            }
        }

        // check DB
        $oReplacement =& KTHelpReplacement::getByName($aPathInfo['internal']);
        if (!PEAR::isError($oReplacement)) {
            $aInfo['title'] = $oReplacement->getTitle();
            $aInfo['body'] = $oReplacement->getDescription();
            $aInfo['help_id'] = $oReplacement->getID();
            $aInfo['name'] = $oReplacement->getName();
            return $aInfo;
        }

        // if we don't have an external address at this point, return an error.
        if (empty($aPathInfo['external'])) {
            return PEAR::raiseError(_kt("Unable to locate the requested help file for this language."));
        }

        // check FS
        if (!file_exists($aPathInfo['external'])) {
            return PEAR::raiseError(_kt("Unable to locate the requested help file for this language."));
        }

        // so it might exist in some form.
        $contents = file_get_contents($aPathInfo['external']);

        $aData = KTHelp::_parseHTML($contents);

        $aInfo['body'] = KTUtil::arrayGet($aData,'body');
        if (empty($aInfo['body'])) {
            return PEAR::raiseError(_kt("The requested help language has no contents."));
        }
        $aInfo['title'] = KTUtil::arrayGet($aData, 'title', _kt("Untitled Help File"));
        $aInfo['name'] = $aPathInfo['internal'];
        return $aInfo;
    }

    function _getLocationInfo($sSubPath, $sLangCode = null, $bFailOK = true) {
        // FIXME use a cheap cache here?  is it even worth it?
        $aInfo = array(
            'subpath' => null,
            'internal' => null,
            'external' => null,
        );

        $oHelpReg =& KTHelpRegistry::getSingleton();

        if (is_null($sLangCode)) {
            global $default;
            $sLangCode = $default->defaultLanguage;
        }

        $aParts = explode('/', $sSubPath);

        if (count($aParts) < 2) {
            return PEAR::raiseError(_kt("Too few parts to the requested help location."));
        }

        $sPluginName = $aParts[0];
        $sSubLocation = implode('/', array_slice($aParts, 1));

        // always use the "correct" internal name
        $sInternalName = sprintf("%s/%s/%s", $sPluginName, $sLangCode, $sSubLocation);

        // this is a pseudo-name.  essentially, this maps to the canonical
        // name of the help file in the database, NOT to the filesystem

        //$sBaseDir = sprintf("%s/kthelp/%s/%s", KT_DIR, $sPluginName, $sLangCode);
        $sBaseDir = $oHelpReg->getBaseDir($sPluginName, $sLangCode);
        if (PEAR::isError($sBaseDir)) {
            if (!$bFailOK) { return $sBaseDir; }
            else {
                // try in english
                $sAltBase = $oHelpReg->getBaseDir($sPluginName, 'en');
                if (PEAR::isError($sAltBase)) {
                    // nothing, even in anglais.
                    $sExternalName = '';
                } else {
                    $sExternalName = sprintf("%s/%s", $sAltBase, $sSubLocation);
                }
            }
        } else {
            $sExternalName = sprintf("%s/%s", $sBaseDir, $sSubLocation);
        }


        $aInfo['subpath'] = $sSubPath;
        $aInfo['internal'] = $sInternalName;
        $aInfo['external'] = $sExternalName;

        return $aInfo;
    }
}

class KTHelpRegistry {
    var $plugin_lang_map;

    function KTHelpRegistry() {
        $this->plugin_lang_map = array();
    }

	static function &getSingleton () {
		static $singleton=null;
		if (is_null($singleton))
		{
			$singleton = new KTHelpRegistry();
		}
		return $singleton;
	}


    function registerHelp($sPluginName, $sLang, $sBaseDir) {
        $lang_map = KTUtil::arrayGet($this->plugin_lang_map, $sPluginName, array());
        $lang_map[$sLang] = $sBaseDir;
        $this->plugin_lang_map[$sPluginName] = $lang_map;
    }

    function getBaseDir($sPluginName, $sLangCode) {
        $lang_map = KTUtil::arrayGet($this->plugin_lang_map, $sPluginName);

        if (is_null($lang_map)) {
            return PEAR::raiseError(_kt("There is no help available in your language for this plugin"));
        }
        $sBaseDir = KTUtil::arrayGet($lang_map, $sLangCode);
        if (is_null($sBaseDir)) {
            return PEAR::raiseError(_kt("There is no help available in your language for this plugin"));
        }
        return $sBaseDir;
    }
}

?>
