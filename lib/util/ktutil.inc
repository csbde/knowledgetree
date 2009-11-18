<?php
/**
 * $Id$
 *
 * Small non-domain-specific utility functions
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
 */

require_once(KT_LIB_DIR . '/util/KTStopwords.php');

class KTUtil {

	const MIN_IN_SECS = 60;
	const HOUR_IN_SECS = 3600;
	const DAY_IN_SECS = 86400;
	const KB = 1024;
    const MB = 1048576;
    const GB = 1000000000;
    const TB = 1099511627776;
    const PB = 1125899906842624;

    /**
     * Used to resolve the server name
     *
     */
    static function save_base_kt_url()
    {
    	global $default;

    	$serverName = $default->server_name;
    	$serverPort = $default->server_port;

    	$pos = strpos($serverName, '://');
        if($pos !== false){
            $serverName = substr($serverName, $pos + 3);
        }

    	$server = $_SERVER['SERVER_NAME'];
    	$port = $_SERVER['SERVER_PORT']+0;

    	// If server_name exists, exit
    	if(!empty($serverName)){
    	    // check that it hasn't changed and update if it has - disabled because it should be set by the system administrator and may have unforeseen consequences
    	    //if($port == $serverPort && $server == $serverName){
    	       return true;
    	    //}
    	}

    	// We don't want to set the servername to localhost, it should be set to the url that will be used normally
		if($server == 'localhost' || $server == '127.0.0.1')
		{
		    return false;
		}

		// If the servername is empty and not localhost - update it

		// Save the server name and port
		DBUtil::whereUpdate('config_settings',
            array('value' => $server),
            array('item' => 'server_name', 'group_name' => 'server'));

        if($port > 0){
            DBUtil::whereUpdate('config_settings',
                array('value' => $port),
                array('item' => 'server_port', 'group_name' => 'server'));
        }

        // Save the rootUrl
    	$rootUrl = $default->rootUrl;
        if(!empty($rootUrl)){
    		DBUtil::whereUpdate('config_settings',
                array('value' => $rootUrl),
                array('item' => 'rootUrl', 'group_name' => 'KnowledgeTree'));
        }
        return true;
    }

    static function getServerName($disallow_localhost = FALSE)
    {
        static $host = null;

        if(!is_null($host)){
            return $host;
        }

        $host = $_SERVER['HTTP_HOST'];

        if(empty($host) || ($disallow_localhost && $host == 'localhost')){
            global $default;
            $host = (!empty($default->server_name)) ? $default->server_name : '127.0.0.1';
            $host .= !empty($default->server_port) ? ':'.$default->server_port : '';

            $pos = strpos($host, '://');
            if($pos !== false){
                $host = substr($host, $pos + 3);
            }
        }
        return $host;
    }

    static function kt_url()
	{
		global $default;
		static $base_url = null;

		if (!is_null($base_url))
		{
		    return $base_url;
		}

    	$serverName = $_SERVER['HTTP_HOST'];

		// $serverName gets set in dmsDefaults using KTUtil::getServerName();
		if(empty($serverName))
		{
		    // The host has not been set - check if server_name is set in the config_settings
		    $serverName = KTUtil::getServerName();
		}

		// build up the url
		$base_url = ($default->sslEnabled ? 'https' : 'http') .'://'.$serverName . $default->rootUrl;
		if(is_object($default->log))
		{
			$default->log->debug("kt_url: base url - $base_url");
		}
		return $base_url;
	}

	static function kt_internal_url()
	{
	    global $default;
	    static $internal_url = null;

	    if(!is_null($internal_url)){
	        return $internal_url;
	    }

	    // build url
	    $internal_url = !empty($default->internal_server_name) ? $default->internal_server_name : '127.0.0.1';
	    $internal_url .= !empty($default->internal_server_port) ? ':' . $default->internal_server_port : '';
	    $internal_url .= !empty($default->rootUrl) ? $default->rootUrl : '';

	    $pos = strpos($internal_url, 'http');
        if($pos === false){
            $port = $default->internal_server_port;
            $internal_url = (($port == 443 || $port == 8443) ? 'https://' : 'http://') . $internal_url;
        }

	    return $internal_url;
	}

	static function call_page($path)
	{
	    global $default;

	    // Using curl so we want to run using the internal url
		$base_url = KTUtil::kt_internal_url();

		if (false === $base_url || empty($base_url))
		{
			$default->log->info("call_page: $path - cannot call script, there is a problem with the internal url. Please check the configuration settings for General Settings => Server Settings.");
			return;
		}

		$full_url = $base_url . '/' . $path;

		$default->log->debug("call_page: calling curl with - $full_url");

		$ch = curl_init($full_url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		curl_close($ch);
	}

	static function computePeriod($diff, $suffix = null, $returnArray=false)
	{
		if (is_null($suffix))
		{
			$suffix = _kt('ago');
		}

		$days = floor($diff / KTUtil::DAY_IN_SECS);
		$hours = floor(($diff - $days * KTUtil::DAY_IN_SECS) / KTUtil::HOUR_IN_SECS);
		$mins = floor(($diff - $days * KTUtil::DAY_IN_SECS - $hours * KTUtil::HOUR_IN_SECS) / KTUtil::MIN_IN_SECS);
		$secs = $diff % KTUtil::MIN_IN_SECS;

		$str = '';
		if ($days > 0) $str .= sprintf(_kt(' %d day(s)'), $days);
		if ($hours > 0) $str .= sprintf(_kt(' %d hour(s)'), $hours);
		if ($mins > 0) $str .= sprintf(_kt(' %d minute(s)'), $mins);

		// lets loose some granularity. the string does get quite long...
		if ($days == 0 && $hours == 0 && $mins == 0 && $secs > 0)
		{
		    $str .= sprintf(_kt(' %d second(s)'), $secs);
		}
		if (empty($str))
		{
		    return _kt('never');
		}
		$str .= " $suffix";

		if ($returnArray)
		{
			return array(
					'str'=>$str,
					'days'=>$days,
					'mins'=>$mins,
					'secs'=>$secs
				);
		}

		return $str;
	}

	static function computePeriodToDate($start, $suffix = null, $returnArray=false)
	{
		return KTUtil::computePeriod(time() - $start, $suffix, $returnArray);
	}

	static function filesizeToString($filesize)
	{
		$filesize = (double) $filesize;

		if ($filesize >= KTUtil::PB)
		{
			return number_format($filesize /  KTUtil::PB, 2, '.',',') . _kt('PB');
		}
		elseif ($filesize >= KTUtil::TB)
		{
			return number_format($filesize /  KTUtil::TB, 2, '.',',') . _kt('TB');
		}
		elseif ($filesize >= KTUtil::GB)
		{
			return number_format($filesize /  KTUtil::GB, 2, '.',',') . _kt('GB');
		}
		elseif ($filesize >= KTUtil::MB)
		{
			return number_format($filesize /  KTUtil::MB, 2, '.',',') . _kt('MB');
		}
		elseif ($filesize >= KTUtil::KB)
		{
			return number_format($filesize /  KTUtil::KB, 2, '.',',') . _kt('KB');
		}
		else
		{
			return $filesize . _kt('B');
		}
	}



	static $invalidFilenameCharacters = array('\\','/',':','*','?','"','<','>','|','%','+','\'','`');

	/**
	 * Checks if a filename is valid
	 *
	 * @param string $filename
	 * @return boolean
	 */
	static function isValidFilename($filename)
	{
		foreach(KTUtil::$invalidFilenameCharacters as $char)
		{
			if (strpos($filename, $char) !== false)
			{
				return false;
			}
		}

		return true;
	}

	static function replaceInvalidCharacters($filename)
	{
		foreach(KTUtil::$invalidFilenameCharacters as $char)
		{
			$filename = str_replace($char, '-', $filename);
		}

		return $filename;
	}


    function extractGPC () {
        foreach (func_get_args() as $var) {
            if (array_key_exists($var, $_REQUEST)) {
                $GLOBALS["$var"] = $_REQUEST["$var"];
            }
        }
    }

    function strToBool ($sString, $null = false, $empty = false) {
        $sString = strtoupper($sString);
        if (in_array($sString, array('Y','YES','ON','TRUE')))
        {
        	return true;
        }
        elseif (in_array($sString, array('N', 'NO','OFF','FALSE')))
        {
        	return false;
        }
		elseif ($sString == '')
		{
            return $empty;
        }
        else
        {
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
	//str len is therefore measured correctly by counting the ?'s.
	//http://www.phpwact.org/php/i18n/charsets
	function utf8_strlen($string){
    	return strlen(utf8_decode($string));
	}

    static function &arrayGet($aArray, $sKey, $mDefault = null, $bDefaultIfEmpty = true) {
        if (!is_array($aArray)) {
            $aArray = (array) $aArray;
        }

        if ($aArray !== 0 && $aArray !== '0' && empty($aArray)) {
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

    	$sPopen = KTUtil::arrayGet($aOptions, 'popen');
    	if ($sPopen) {
    	    if (OS_WINDOWS) {
                $sCmd = "start /b \"kt\" " . $sCmd;
    	    }
    		return popen($sCmd, $sPopen);
    	}

    	// for exec, check return code and output...
    	$aRet = array();
    	$aOutput = array();
    	$iRet = '';

    	if(OS_WINDOWS){
    	    $sCmd = 'call '.$sCmd;
    	}
    	exec($sCmd, $aOutput, $iRet);
    	$aRet['ret'] = $iRet;
    	$aRet['out'] = $aOutput;
    	return $aRet;
    }
    // }}}


    // {{{ winexec
    /**
     * Execute a command on a windows platform.
     */
    function winexec($aCmd, $aOptions = null) {
    	if (is_array($aCmd)) {
    		$sCmd = KTUtil::safeShellString($aCmd);
    	} else {
    		$sCmd = $aCmd;
    	}
    	$sAppend = KTUtil::arrayGet($aOptions, 'append');
    	if ($sAppend) {
    		$sCmd .= " >> " . escapeshellarg($sAppend);
    	}

    	$sCmd = str_replace( '/','\\',$sCmd);

    	// Set wait to true if the execute must wait for the script to complete before continuing
    	$wait = true;
        if(isset($aOptions['exec_wait']) && ($aOptions['exec_wait'] == 'false')){
            $wait = false;
        }

    	// Iterate through the various execute functions till one works.
  		$WshShell = new COM("WScript.Shell");
        $res = $WshShell->Run($sCmd, 0, $wait);

        if($res){
            return $res;
        }

    	$sCmd = "start /b \"kt\" " . $sCmd;
    	$fp = popen($sCmd, 'r');
    	fclose($fp);

    	if($wait){
    	    sleep(1);
    	}
    	return 1;
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
        // Check for the stack command before using the user defined command
        $result = KTUtil::checkForStackCommand($sConfigVar);
    	if (!empty($result))
    	{
    		return $result;
    	}

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

    function checkForStackCommand($configCommand)
    {
    	$config = KTConfig::getSingleton();
    	$stackPath = realpath(KT_DIR . '/..');

    	switch ($configCommand)
    	{
    		case 'externalBinary/xls2csv':
    			if (OS_WINDOWS)
				{
					$script = $stackPath . '/bin/catdoc/xls2csv.exe';
				}
				else
				{
					$script = $stackPath . '/common/bin/xls2csv';
				}
				break;
    		case 'externalBinary/pdftotext':;
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/bin/xpdf/pdftotext.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/common/bin/pdftotext';
    			}
    			break;
    		case 'externalBinary/catppt':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/bin/catdoc/catppt.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/common/bin/catppt';
    			}
    			break;
    		case 'externalBinary/pstotext':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/bin/pstotext/pstotext.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/common/bin/pstotext';
    			}
    			break;
    		case 'externalBinary/catdoc':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/bin/catdoc/catdoc.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/common/bin/catdoc';
    			}
    			break;
    		case 'externalBinary/antiword':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/bin/antiword/antiword.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/common/bin/antiword';
    			}
    			break;
    		case 'externalBinary/python':
    			if (OS_WINDOWS)
				{
					$script = $stackPath . '/openoffice/program/python.bat';
				}
				else
				{
					$script = $stackPath . '/openoffice/program/python';
				}
				break;
    		case 'externalBinary/java':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/java/jre/bin/java.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/java/jre/bin/java';
    			}
    			break;
    		case 'externalBinary/df':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/bin/gnuwin32/df.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/common/bin/df';
    			}
    			break;
    		case 'externalBinary/php':
    			if (OS_WINDOWS)
    			{
    				$script = $stackPath . '/php/php.exe';
    			}
    			else
    			{
    				$script = $stackPath . '/php/bin/php';
    			}
    			break;
    		default:
    			return null;
    	}
    	if (is_file($script))
		{
			return $script;
		}
    	return false;
    }


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

        $sPath = str_replace('\\', '/', $sPath);
        $sReal = str_replace('\\', '/', realpath($sPath));

        if(substr($sPath, -1, 1) == '/' && substr($sReal, -1, 1) != '/'){
            $sReal .= '/';
        }

        return (strtolower($sReal) == strtolower($sPath));

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

    static function getSystemSetting($name, $default = null) {
        // XXX make this use a cache layer?
        $sTable = KTUtil::getTableName('system_settings');
        $aQuery = array(
            sprintf('SELECT value FROM %s WHERE name = ?', $sTable),
            array($name),
        );
        $res = DBUtil::getOneResultKey($aQuery, 'value');
        if (PEAR::isError($res)) {
            if(!is_null($default)){
                return $default;
            }
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

    /**
     * The system identifier is a unique ID defined in every installation of KnowledgeTree
     *
     * @return string The system identifier
     */
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


    /**
     * Generates breadcrumbs for a browsable collection
     *
     * @param object $oFolder The folder being browsed to
     * @param integer $iFolderId The id of the folder
     * @param array $aURLParams The url parameters for each folder/breadcrumb link
     * @return unknown
     */
    function generate_breadcrumbs($oFolder, $iFolderId, $aURLParams) {
        static $aFolders = array();
        static $aBreadcrumbs = array();

        // Check if selected folder is a parent of the current folder
        if(in_array($iFolderId, $aFolders)){
            $temp = array_flip($aFolders);
            $key = $temp[$iFolderId];
            array_splice($aFolders, $key);
            array_splice($aBreadcrumbs, $key);
            return $aBreadcrumbs;
        }

        // Check for the parent of the selected folder unless its the root folder
        $iParentId = $oFolder->getParentID();

        if(is_null($iParentId)){
            // folder is root
            return '';
        }

        if($iFolderId != 1 && in_array($iParentId, $aFolders)){
            $temp = array_flip($aFolders);
            $key = $temp[$iParentId];
            array_splice($aFolders, $key);
            array_splice($aBreadcrumbs, $key);
            array_push($aFolders, $iFolderId);

            $aParams = $aURLParams;
            $aParams['fFolderId'] = $iFolderId;
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], $aParams);
            $aBreadcrumbs[] = array('url' => $url, 'name' => $oFolder->getName());
            return $aBreadcrumbs;
        }

        // Get the root folder name
        $oRootFolder = Folder::get(1);
        $sRootFolder =$oRootFolder->getName();

        // Create the breadcrumbs
        $folder_path_names = $oFolder->getPathArray();
        array_unshift($folder_path_names, $sRootFolder);
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        $folder_path_ids[] = $oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        $iCount = count($folder_path_ids);
        $range = range(0, $iCount - 1);
        foreach ($range as $index) {
            $id = $folder_path_ids[$index];
            $name = $folder_path_names[$index];

            $aParams = $aURLParams;
            $aParams['fFolderId'] = $id;
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], $aParams);
            $aBreadcrumbs[] = array('url' => $url, 'name' => $name);
        }
        $aFolders = $folder_path_ids;
        return $aBreadcrumbs;
    }

    /**
     * Generates the correct headers for downloading a document
     */
    function download($path, $mimeType, $fileSize, $fileName, $displayType = 'attachment')
    {
        if (file_exists($path)) {
            // IE7 adds underscores to filenames: fix
            // IE6 displays characters incorrectly
            $browser = $_SERVER['HTTP_USER_AGENT'];
            if ( strpos( strtoupper( $browser), 'MSIE') !== false) {
                $fileName = rawurlencode($fileName);
            }

            // Set the correct headers
            header("Content-Type: {$mimeType}");
            header("Content-Length: {$fileSize}");
            header("Content-Disposition: {$displayType}; filename=\"{$fileName}\"");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

            // Document must be cached in order to open it
            header("Cache-Control: must-revalidate");

            // Allows the file to be downloaded, otherwise it attempts the download as "action.php?..."
            header("Pragma: public");

            readfile($path);
        }else {
            return false;
        }
    }

    static
    function camelize($string) {
    	$result = "";
    	$parts = explode("_", $string);
    	for($i=0;$i<count($parts);$i++) {
    		$result .= ucfirst($parts[$i]);
    	}
    	return $result;
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
