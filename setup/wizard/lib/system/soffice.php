<?php
	$cmd = "nohup /usr/bin/soffice -nofirststartwizard -nologo -headless -accept=\"socket,host=localhost,port=8100;urp;StarOffice.ServiceManager\" &1> /dev/null &";
	$res = pexec($cmd);
	print_r($res);
/**
     * Portably execute a command on any of the supported platforms.
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param string $aCmd
     * @param array $aOptions
     * @return array
     */
    function pexec($aCmd, $aOptions = null) {
    	if (is_array($aCmd)) {
    		$sCmd = safeShellString($aCmd);
    	} else {
    		$sCmd = $aCmd;
    	}
    	$sAppend = arrayGet($aOptions, 'append');
    	if ($sAppend) {
    		$sCmd .= " >> " . escapeshellarg($sAppend);
    	}
    	$sPopen = arrayGet($aOptions, 'popen');
    	if ($sPopen) {
    	    if (WINDOWS_OS) {
                $sCmd = "start /b \"kt\" " . $sCmd;
    	    }
    		return popen($sCmd, $sPopen);
    	}
    	// for exec, check return code and output...
    	$aRet = array();
    	$aOutput = array();
    	$iRet = '';
    	//if(WINDOWS_OS) {
    	//    $sCmd = 'call '.$sCmd;
    //	}

    	exec($sCmd, $aOutput, $iRet);
    	$aRet['ret'] = $iRet;
    	$aRet['out'] = $aOutput;

    	return $aRet;
    }

	/**
	*
	*
	* @author KnowledgeTree Team
	* @access public
	* @return string
	*/
 	function arrayGet($aArray, $sKey, $mDefault = null, $bDefaultIfEmpty = true) {
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

	/**
	*
	*
	* @author KnowledgeTree Team
	* @access public
	* @return string
	*/
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
?>
