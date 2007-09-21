<?php
/**
 * $Id$
 *
 * Small non-domain-specific utility functions
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
 */

require_once(KT_LIB_DIR . '/util/KTStopwords.php');

class KTUtil {
    function extractGPC () {
        foreach (func_get_args() as $var) {
            if (array_key_exists($var, $_REQUEST)) {
                $GLOBALS["$var"] = $_REQUEST["$var"];
            }
        }
    }

    function strToBool ($sString, $null = false, $empty = false) {
        $sString = strtoupper($sString);
        if ($sString == "Y") {
            return true;
        } elseif (($sString == "N")) {
            return false;
        } elseif (($sString == "ON")) {
            return true;
        } elseif (($sString == "OFF")) {
            return false;
        } elseif (($sString == "")) {
            return $empty;
        } else {
            return $null;
        }
    }

    function intToBool ($sString) {
        $iInt = (int)$sString;
        return $iInt !== 0;
    }


    function anyToBool ($sString, $null = false) {
        if (is_bool($sString)) {
            return $sString;
        }

        if (is_numeric($sString)) {
            return KTUtil::intToBool($sString);
        }

        if (is_string($sString)) {
            if (KTUtil::strToBool($sString) === true) {
                return true;
            }
        }

        if (is_null($sString)) {
            return $null;
        }

        return false;
    }

    function randomString($length=16, $sRandom="ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"){
        $sString = "";
        $iCurLen = 0;
        $iRandomLen = strlen($sRandom);
        while ($length > $iCurLen) {
            $sString .= substr($sRandom, mt_rand(0, $iRandomLen -1), 1);
            $iCurLen++;
        }
        return $sString;
    }
    
    //this function fudges the strlen. It returns a ? when the character is a multi-byte character.
	//str len is therefore measured correctly.
	//http://www.phpwact.org/php/i18n/charsets
	function utf8_strlen($string){
    	return strlen(utf8_decode($str));
	}  

    static function &arrayGet($aArray, $sKey, $mDefault = null, $bDefaultIfEmpty = true) {
        if (!is_array($aArray)) {
            $aArray = (array) $aArray;
        }
        if (empty($aArray)) {
            return $mDefault;
        }
        if (array_key_exists($sKey, $aArray)) {
            $mVal =& $aArray[$sKey];
            if (empty($mVal) && $bDefaultIfEmpty) {
                return $mDefault;
            }
            return $mVal;
        }
        return $mDefault;
    }

    function requestValue($sKey, $mDefault = null) {
        return KTUtil::arrayGet($_REQUEST, $sKey, $mDefault);
    }

    // {{{ whereToString
    /**
     * Convert an array of parameterised strings to a single
     * parameterised string.
     *
     * Return null in case of an empty array.
     */
    static function whereToString($aWhere) {
        $aStrings = array();
        $aParams = array();
        foreach ($aWhere as $oSomething) {
            if (is_string($oSomething)) {
                $aStrings[] = $oSomething;
            } else if (is_array($oSomething)) {
                $aStrings[] = $oSomething[0];
                $aNewParams = array();
                foreach ($oSomething[1] as $oParam) {
                    if (is_array($oParam)) {
                        $aNewParams = array_merge($aNewParams, $oParam);
                    } else {
                        $aNewParams[] = $oParam;
                    }
                }
                $aParams = array_merge($aParams, $aNewParams);
            } else {
                return PEAR::raiseError(_kt("Weird WhereClause passed"));
            }
        }
        if (count($aStrings) === 0) {
            return null;
        }
        return array(join(" AND ", $aStrings), $aParams);
    }
    // }}}

    // {{{ safeShellString
    function safeShellString () {
        $aArgs = func_get_args();
        $aSafeArgs = array();
        if (is_array($aArgs[0])) {
            $aArgs = $aArgs[0];
        }
        $aSafeArgs[] = escapeshellarg(array_shift($aArgs));
        if (is_array($aArgs[0])) {
            $aArgs = $aArgs;
        }
        foreach ($aArgs as $sArg) {
            if (empty($sArg)) {
                $aSafeArgs[] = "''";
            } else {
                $aSafeArgs[] = escapeshellarg($sArg);
            }
        }
        return join(" ", $aSafeArgs);
    }
    // }}}

    // {{{ pexec
    /**
     * Portably execute a command on any of the supported platforms.
     */
    function pexec($aCmd, $aOptions = null) {
    	if (is_array($aCmd)) {
    		$sCmd = KTUtil::safeShellString($aCmd);
    	} else {
    		$sCmd = $aCmd;
    	}
    	$sAppend = KTUtil::arrayGet($aOptions, 'append');
    	if ($sAppend) {
    		$sCmd .= " >> " . escapeshellarg($sAppend);
    	}
    	if (OS_WINDOWS) {
    		$sCmd = "start /b \"kt\" " . $sCmd;
    	}
    	$sPopen = KTUtil::arrayGet($aOptions, 'popen');
    	if ($sPopen) {
    		return popen($sCmd, $sPopen);
    	}

    	// for exec, check return code and output...
    	$aRet = array();
    	$aOutput = array();
    	$iRet = '';

    	exec($sCmd, $aOutput, $iRet);
    	$aRet['ret'] = $iRet;
    	$aRet['out'] = $aOutput;
    	return $aRet;
    }
    // }}}

    // {{{ copyDirectory
    function copyDirectory($sSrc, $sDst, $bMove = false) {
        if (file_exists($sDst)) {
            return PEAR::raiseError(_kt("Destination directory already exists."));
        }
        if (OS_UNIX) {
            if ($bMove && file_exists('/bin/mv')) {
                KTUtil::pexec(array('/bin/mv', $sSrc, $sDst));
                return;
            }
            if (!$bMove && file_exists('/bin/cp')) {
                KTUtil::pexec(array('/bin/cp', '-R', $sSrc, $sDst));
                return;
            }
        }

        if (substr($sDst, 0, strlen($sSrc)) === $sSrc) {
            return PEAR::raiseError(_kt("Destination of move is within source"));
        }

        $hSrc = @opendir($sSrc);
        if ($hSrc === false) {
            return PEAR::raiseError(sprintf(_kt("Could not open source directory: %s"), $sSrc));
        }

        if (@mkdir($sDst, 0777) === false) {
            return PEAR::raiseError(sprintf(_kt("Could not create destination directory: %s"), $sDst));
        }

        while (($sFilename = readdir($hSrc)) !== false) {
            if (in_array($sFilename, array('.', '..'))) {
                continue;
            }
            $sOldFile = sprintf("%s/%s", $sSrc, $sFilename);
            $sNewFile = sprintf("%s/%s", $sDst, $sFilename);

            if (is_dir($sOldFile)) {
                KTUtil::copyDirectory($sOldFile, $sNewFile, $bMove);
                continue;
            }

            if ($bMove) {
                KTUtil::moveFile($sOldFile, $sNewFile);
            } else {
                copy($sOldFile, $sNewFile);
            }
        }
        if ($bMove) {
            @rmdir($sSrc);
        }
    }
    // }}}

    // {{{ moveDirectory
    function moveDirectory($sSrc, $sDst) {
        return KTUtil::copyDirectory($sSrc, $sDst, true);
    }
    // }}}

    // {{{ deleteDirectory
    function deleteDirectory($sPath) {
        if (OS_UNIX) {
            if (file_exists('/bin/rm')) {
                KTUtil::pexec(array('/bin/rm', '-rf', $sPath));
                return;
            }
        }
        if (OS_WINDOWS) {
            // Potentially kills off all the files in the path, speeding
            // things up a bit
            exec("del /q /s " . escapeshellarg($sPath));
        }
        $hPath = @opendir($sPath);
        while (($sFilename = readdir($hPath)) !== false) {
            if (in_array($sFilename, array('.', '..'))) {
                continue;
            }
            $sFullFilename = sprintf("%s/%s", $sPath, $sFilename);
            if (is_dir($sFullFilename)) {
                KTUtil::deleteDirectory($sFullFilename);
                continue;
            }
            @chmod($sFullFilename, 0666);
            @unlink($sFullFilename);
        }
        closedir($hPath);
        @rmdir($sPath);
    }
    // }}}

    // {{{ moveFile
    function moveFile ($sSrc, $sDst) {
        // Only 4.3.3 and above allow us to use rename across partitions
        // on Unix-like systems.
        if (OS_UNIX) {
            // If /bin/mv exists, just use it.
            if (file_exists('/bin/mv')) {
                KTUtil::pexec(array('/bin/mv', $sSrc, $sDst));
                return;
            }

            $aSrcStat = stat($sSrc);
            if ($aSrcStat === false) {
                return PEAR::raiseError(sprintf(_kt("Couldn't stat source file: %s"), $sSrc));
            }
            $aDstStat = stat(dirname($sDst));
            if ($aDstStat === false) {
                return PEAR::raiseError(sprintf(_kt("Couldn't stat destination location: %s"), $sDst));
            }
            if ($aSrcStat["dev"] === $aDstStat["dev"]) {
                $res = @rename($sSrc, $sDst);
                if ($res === false) {
                    return PEAR::raiseError(sprintf(_kt("Couldn't move file to destination: %s"), $sDst));
                }
                return;
            }

            $res = @copy($sSrc, $sDst);
            if ($res === false) {
                return PEAR::raiseError(sprintf(_kt("Could not copy to destination: %s"), $sDst));
            }
            $res = @unlink($sSrc);
            if ($res === false) {
                return PEAR::raiseError(sprintf(_kt("Could not remove source: %s"), $sSrc));
            }
        } else {
            $res = @rename($sSrc, $sDst);
            if ($res === false) {
                return PEAR::raiseError(sprintf(_kt("Could not move to destination: %s"), $sDst));
            }
        }
    }
    // }}}

    // {{{ copyFile
    function copyFile ($sSrc, $sDst) {
        // Only 4.3.3 and above allow us to use rename across partitions
        // on Unix-like systems.
        if (OS_UNIX) {
            $aSrcStat = stat($sSrc);
            if ($aSrcStat === false) {
                return PEAR::raiseError(sprintf(_kt("Couldn't stat source file: %s"), $sSrc));
            }
            $aDstStat = stat(dirname($sDst));
            if ($aDstStat === false) {
                return PEAR::raiseError(sprintf(_kt("Couldn't stat destination location: %s"), $sDst));
            }

            $res = @copy($sSrc, $sDst);
            if ($res === false) {
                return PEAR::raiseError(sprintf(_kt("Could not copy to destination: %s"), $sDst));
            }
        } else {
            $res = @copy($sSrc, $sDst);
            if ($res === false) {
                return PEAR::raiseError(sprintf(_kt("Could not copy to destination: %s"), $sDst));
            }
        }
    }
    // }}}

    // {{{ getTableName
    /**
     * The one true way to get the correct name for a table whilst
     * respecting the administrator's choice of table naming.
     */
    static function getTableName($sTable) {
        $sDefaultsTable = $sTable . "_table";
        if (isset($GLOBALS['default']->$sDefaultsTable)) {
            return $GLOBALS['default']->$sDefaultsTable;
        }
        return $sTable;
    }
    // }}}

    // {{{ getId
    function getId($oEntity) {
        if (is_object($oEntity)) {
            if (method_exists($oEntity, 'getId')) {
                return $oEntity->getId();
            }
            return PEAR::raiseError(_kt('Non-entity object'));
        }

        if (is_numeric($oEntity)) {
            return $oEntity;
        }

        return PEAR::raiseError(_kt('Non-entity object'));
    }
    // }}}

    // {{{ getObject
    function getObject($sClassName, &$iId) {
        if (is_object($iId)) {
            return $iId;
        }

        if (is_numeric($iId)) {
            return call_user_func(array($sClassName, 'get'), $iId);
        }
        return PEAR::raiseError(_kt('Non-entity object'));
    }
    // }}}

    // {{{ meldOptions
    static function meldOptions($aStartOptions, $aAddOptions) {
        if (!is_array($aStartOptions)) {
            $aStartOptions = array();
        }
        if (!is_array($aAddOptions)) {
            $aAddOptions = array();
        }
        return array_merge($aStartOptions, $aAddOptions);
    }
    // }}}

    // {{{ getRequestScriptName
    function getRequestScriptName($server) {
        $request_uri = $server['REQUEST_URI'];
        $script_name = $server['SCRIPT_NAME'];

        /*
         * Until script_name is fully inside request_uri, strip off bits
         * of script_name.
         */

        //print "Checking if $script_name is in $request_uri\n";
        while ($script_name && strpos($request_uri, $script_name) === false) {
            //print "No it isn't.\n";
            $lastslash = strrpos($script_name, '/');
            $lastdot = strrpos($script_name, '.');
            //print "Last slash is at: $lastslash\n";
            //print "Last dot is at: $lastdot\n";
            if ($lastslash > $lastdot) {
                $script_name = substr($script_name, 0, $lastslash);
            } else {
                $script_name = substr($script_name, 0, $lastdot);
            }
            //print "Checking is $script_name is in $request_uri\n";
        }
        return $script_name;
    }
    // }}}

    // {{{ nameToLocalNamespace
    function nameToLocalNamespace ($sSection, $sName) {
        $sBase = generateLink("");
        $sName = trim($sName);
        $sName = strtolower($sName);
        $sName = str_replace(array("'", "/",'"', " "), array(), $sName);
        $sSection = trim($sSection);
        $sSection = strtolower($sSection);
        $sSection = str_replace(array("'", "/",'"', " "), array(),
                $sSection);
        return $sBase . 'local' . '/' . $sSection . '/' . $sName;
    }
    // }}}

    // {{{ findCommand
    function findCommand($sConfigVar, $sDefault = null) {
    	$oKTConfig =& KTConfig::getSingleton();
    	$sCommand = $oKTConfig->get($sConfigVar, $sDefault);
    	if (empty($sCommand)) {
    		return false;
    	}
    	if (file_exists($sCommand)) {
    		return $sCommand;
    	}
    	if (file_exists($sCommand . ".exe")) {
    		return $sCommand . ".exe";
    	}

    	$sExecSearchPath = $oKTConfig->get("KnowledgeTree/execSearchPath");
    	$sExecSearchPath .= PATH_SEPARATOR . KT_DIR . "/../common/";
    	$sExecSearchPath .= PATH_SEPARATOR . KT_DIR . "/../bin/xpdf/";
    	$sExecSearchPath .= PATH_SEPARATOR . KT_DIR . "/../bin/antiword/";
    	$sExecSearchPath .= PATH_SEPARATOR . KT_DIR . "/../bin/zip/";
    	$sExecSearchPath .= PATH_SEPARATOR . KT_DIR . "/../bin/unzip/";

    	$paths = split(PATH_SEPARATOR, $sExecSearchPath);
    	foreach ($paths as $path) {

    		if (file_exists($path . '/' . $sCommand)) {
    			return $path . '/' . $sCommand;
    		}
    		if (file_exists($path . '/' . $sCommand . ".exe")) {
    			return $path . '/' . $sCommand . ".exe";
    		}
    	}
    	return false;
    }
    // }}}

    // now accepts strings OR arrays!
    // {{{ addQueryString
    static function addQueryString($url, $qs) {
        require_once(KT_DIR . '/thirdparty/pear/Net/URL.php');
        $oUrl = new Net_URL($url);

        $oKTConfig =& KTConfig::getSingleton();
        if ($oKTConfig->get("KnowledgeTree/sslEnabled")) {
            $oUrl->protocol = 'https';
            if ($oUrl->port == 80) {
                $oUrl->port = 443;
            }
        }
        $host = $oKTConfig->get("KnowledgeTree/serverName");
        $host = explode(':', $host);
        $oUrl->host = $host[0];

	if(!is_array($qs)) {
	    $aQs = $oUrl->_parseRawQuerystring($qs);
	} else {
	    $aQs =& $qs;
	}

        foreach ($aQs as $k => $v) {
            $oUrl->addQueryString($k, $v, true);
        }
        return $oUrl->getUrl();
    }
    // }}}

    // {{{ ktLink
    function ktLink($base, $subpath='', $qs='') {
        $KTConfig =& KTConfig::getSingleton();
        $root = $KTConfig->get("KnowledgeTree/rootUrl");
        $url = generateLink($base);

        if (!empty($subpath)) {
            $hasPathInfo = $KTConfig->get("KnowledgeTree/pathInfoSupport");
            if ($hasPathInfo) {
                $url .= $subpath;
            } else {
                $url = KTUtil::addQueryString($url, "kt_path_info=" . $subpath);
            }
        }

        return KTUtil::addQueryString($url, $qs);
    }
    // }}}

    // {{{ addQueryStringSelf
    static function addQueryStringSelf($qs) {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], $qs);
    }
    // }}}

    // {{{ isAbsolutePath
    static function isAbsolutePath($sPath) {
        if (substr($sPath, 0, 1) == '/') {
            return true;
        }
        if (OS_WINDOWS && (substr($sPath, 1, 2) == ':/')) {
            return true;
        }
        if (OS_WINDOWS && (substr($sPath, 1, 2) == ':\\')) {
            return true;
        }
        return false;
    }
    // }}}


    // {{{ formatPlainText
    /* formats input text for discussion body. replaces newlines
    with <br/> tags to allow for paragraphing. should also strip
    html elements.
    */
    function formatPlainText($sText) {
        return str_replace("\n", '<br/>', str_replace("\r\n", '<br/>', trim($sText)));
    }
    // }}}

    // getBenchmarkTime
    function getBenchmarkTime () {
        $microtime_simple = explode(' ', microtime());

        return ((float) $microtime_simple[1] + (float) $microtime_simple[0]);
    }

    function phraseSplit($sSearchString) {
	// this should probably be moved to a DBUtil method

	$sMinWord = DBUtil::getOneResultKey("SHOW VARIABLES LIKE 'ft_min_word_len'", "Value");
	if(is_numeric($sMinWord)) {
	    $iMinWord = (int)$sMinWord;
	} else {
	    $iMinWord = 4;
	}

        $a = preg_split('#"#', $sSearchString);
        $i = 0;
        $phrases = array();
        $word_parts = array();
        foreach ($a as $part) {
            if ($i%2 == 0) {
                $word_parts[] = $part;
            } else {
                $phrases[] = $part;
            }
            $i += 1;
        }

	$oStopwords =& KTStopwords::getSingleton();

        $words = array();
        foreach ($word_parts as $part) {
            $w = (array) explode(' ', $part);
            foreach ($w as $potential) {
		if (strlen($potential) >= $iMinWord && !$oStopwords->isStopword($potential)) {
		    $words[] = $potential;
		}
	    }
        }

        return array(
            'words' => $words,
            'phrases' => $phrases,
        );
    }

    function phraseQuote($sQuery) {
	foreach(KTUtil::phraseSplit($sQuery) as $k => $v) {
	    $t = array();
	    foreach ($v as $part) {
		$t[] = sprintf('+"%s"', $part);
	    }
	    $q_set[$k] = join(' ', $t);
	}
	return  implode(' ',$q_set);
    }

    static function running_user() {
        if (substr(PHP_OS, 0, 3) == "WIN") {
            return null;
        }
        if (extension_loaded("posix")) {
            $uid = posix_getuid();
            $userdetails = posix_getpwuid($uid);
            return $userdetails['name'];
        }
        if (file_exists('/usr/bin/whoami')) {
            return exec('/usr/bin/whoami');
        }
        if (file_exists('/usr/bin/id')) {
            return exec('/usr/bin/id -nu');
        }
        return null;
    }

    function getSystemSetting($name, $default = null) {
        // XXX make this use a cache layer?
        $sTable = KTUtil::getTableName('system_settings');
        $aQuery = array(
            sprintf('SELECT value FROM %s WHERE name = ?', $sTable),
            array($name),
        );
        $res = DBUtil::getOneResultKey($aQuery, 'value');
        if (PEAR::isError($res)) {
            return PEAR::raiseError(sprintf(_kt('Unable to retrieve system setting %s: %s'), $name, $res->getMessage()));
        }

        if (is_null($res)) { return $default; }

        return $res;
    }

    function setSystemSetting($name, $value) {
        // we either need to insert or update:
        $sTable = KTUtil::getTableName('system_settings');
        $current_value = KTUtil::getSystemSetting($name);
        if (is_null($current_value)) {
            // insert
            $res = DBUtil::autoInsert(
                $sTable,
                array(
                    'name' => $name,
                    'value' => $value,
                ),
                null // opts
            );
            if (PEAR::isError($res)) { return $res; }
            else { return true; }
        } else {
            // update
            $aQuery = array(
                sprintf('UPDATE %s SET value = ? WHERE name = ?', $sTable),
                array($value, $name),
            );
            $res = DBUtil::runQuery($aQuery);
            if (PEAR::isError($res)) { return $res; }
            return true;
        }
    }

    function getSystemIdentifier() {
        $sIdentifier = KTUtil::getSystemSetting('kt_system_identifier');
        if (empty($sIdentifier)) {
            $sIdentifier = md5(uniqid(mt_rand(), true));
            KTUtil::setSystemSetting('kt_system_identifier', $sIdentifier);
        }
        return $sIdentifier;
    }

    function getKTVersions() {
        $aVersions = array();
        $sProfessionalFile = KT_DIR . '/docs/VERSION-PRO.txt';
        $sOssFile = KT_DIR . '/docs/VERSION-OSS.txt';
        $sDevProfessionalFile = KT_DIR . '/docs/VERSION-PRO-DEV.txt';
        $sDevOssFile = KT_DIR . '/docs/VERSION-OSS-DEV.txt';
        if (file_exists($sDevProfessionalFile)) {
            $sVersion = trim(file_get_contents($sDevProfessionalFile));
            $aVersions['Development Commercial'] = $sVersion;
        } elseif (file_exists($sDevOssFile)) {
            $sVersion = trim(file_get_contents($sDevOssFile));
            $aVersions['Development OSS'] = $sVersion;
        } elseif (file_exists($sProfessionalFile)) {
            $sVersion = trim(file_get_contents($sProfessionalFile));
            $aVersions['Commercial Edition'] = $sVersion;
        } elseif (file_exists($sOssFile)) {
            $sVersion = trim(file_get_contents($sOssFile));
            $aVersions['OSS'] = $sVersion;
        } else {
            $aVersions['ERR'] = "Unknown version";
        }
        return $aVersions;
    }


    // this will have to move somewhere else
    function buildSelectOptions($aVocab, $cur = null) {
	$sRet = '';
	foreach($aVocab as $k=>$v) {
	    $sRet .= '<option value="' . $k . '"';
	    if($k == $cur) $sRet .= ' selected="selected"';
	    $sRet .= '>' . $v . '</option>';
	}
	return $sRet;
    }

    function keyArray($aEntities, $sIdFunc = 'getId') {
        $aRet = array();
        foreach ($aEntities as $oEnt) {
            $meth = array(&$oEnt, $sIdFunc);
            $id = call_user_func($meth);
            $aRet[$id] = $oEnt;
        }
        return $aRet;
    }

}

/**
 *
 * Merges two arrays using array_merge
 *
 * array_merge in PHP5 got more strict about its parameter handling,
 * forcing arrays.
 *
 */
if (version_compare(phpversion(), '5.0') === -1) {
    function kt_array_merge() {
        $args = func_get_args();
        return call_user_func_array("array_merge",$args);
    }
} else {
    eval('
    function kt_array_merge() {
        $args = func_get_args();
        foreach ($args as &$arg)  {
            $arg = (array)$arg;
        }
        return call_user_func_array("array_merge",$args);
    }
    ');
}


?>
