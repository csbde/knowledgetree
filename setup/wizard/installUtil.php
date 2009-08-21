<?php
/**
* Installer Utilities Library
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/
class InstallUtil {	
	/**
	* Constructs installation object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct() {
	}
	
	/**
	* Check if system needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function isSystemInstalled() {
		if (file_exists(dirname(__FILE__)."/install")) {

			return true;
		}
		
		return false;
	}

	public function error($error) {
		$template_vars['error'] = $error;
		$file = "templates/error.tpl";
		if (!file_exists($file)) {
			return false;
		}
		extract($template_vars); // Extract the vars to local namespace
		ob_start();
		include($file);
        $contents = ob_get_contents();
        ob_end_clean();
        echo $contents;
	}
	/**
	* Check if system needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return mixed
 	*/
    public function checkStructurePermissions() {
    	// Check if Wizard Directory is writable
    	if(!$this->_checkPermission(WIZARD_DIR)) {
    		return 'wizard';
    	}

    	return true;
    }

    /**
     * Redirect
     * 
     * This function redirects the client. This is done by issuing
     * a "Location" header and exiting if wanted.  If you set $rfc2616 to true
     * HTTP will output a hypertext note with the location of the redirect.
     * 
     * @static 
     * @access  public 
     *                  have already been sent.
     * @param   string  $url URL where the redirect should go to.
     * @param   bool    $exit Whether to exit immediately after redirection.
     * @param   bool    $rfc2616 Wheter to output a hypertext note where we're
     *                  redirecting to (Redirecting to <a href="...">...</a>.)
     * @return  mixed   Returns true on succes (or exits) or false if headers
     */
    public function redirect($url, $exit = true, $rfc2616 = false)
    {
        if (headers_sent()) {
            return false;
        }
        
        $url = $this->absoluteURI($url);
        header('Location: '. $url);
        
        if (    $rfc2616 && isset($_SERVER['REQUEST_METHOD']) &&
                $_SERVER['REQUEST_METHOD'] != 'HEAD') {
            printf('Redirecting to: <a href="%s">%s</a>.', $url, $url);
        }
        if ($exit) {
            exit;
        }
        return true;
    }

   /**
     * Absolute URI
     * 
     * This function returns the absolute URI for the partial URL passed.
     * The current scheme (HTTP/HTTPS), host server, port, current script
     * location are used if necessary to resolve any relative URLs.
     * 
     * Offsets potentially created by PATH_INFO are taken care of to resolve
     * relative URLs to the current script.
     * 
     * You can choose a new protocol while resolving the URI.  This is 
     * particularly useful when redirecting a web browser using relative URIs 
     * and to switch from HTTP to HTTPS, or vice-versa, at the same time.
     * 
     * @author  Philippe Jausions <Philippe.Jausions@11abacus.com> 
     * @static 
     * @access  public 
     * @param   string  $url Absolute or relative URI the redirect should go to.
     * @param   string  $protocol Protocol to use when redirecting URIs.
     * @param   integer $port A new port number.
     * @return  string  The absolute URI.
     */
    public function absoluteURI($url = null, $protocol = null, $port = null)
    {
        // filter CR/LF
        $url = str_replace(array("\r", "\n"), ' ', $url);
        
        // Mess around with already absolute URIs
        if (preg_match('!^([a-z0-9]+)://!i', $url)) {
            if (empty($protocol) && empty($port)) {
                return $url;
            }
            if (!empty($protocol)) {
                $url = $protocol .':'. end($array = explode(':', $url, 2));
            }
            if (!empty($port)) {
                $url = preg_replace('!^(([a-z0-9]+)://[^/:]+)(:[\d]+)?!i', 
                    '\1:'. $port, $url);
            }
            return $url;
        }
            
        $host = 'localhost';
        if (!empty($_SERVER['HTTP_HOST'])) {
            list($host) = explode(':', $_SERVER['HTTP_HOST']);
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            list($host) = explode(':', $_SERVER['SERVER_NAME']);
        }

        if (empty($protocol)) {
            if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
                $protocol = 'https';
            } else {
                $protocol = 'http';
            }
            if (!isset($port) || $port != intval($port)) {
                $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
            }
        }
        
        if ($protocol == 'http' && $port == 80) {
            unset($port);
        }
        if ($protocol == 'https' && $port == 443) {
            unset($port);
        }

        $server = $protocol .'://'. $host . (isset($port) ? ':'. $port : '');
        
        if (!strlen($url)) {
            $url = isset($_SERVER['REQUEST_URI']) ? 
                $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
        }
        
        if ($url{0} == '/') {
            return $server . $url;
        }
        
        // Check for PATH_INFO
        if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO']) && 
                $_SERVER['PHP_SELF'] != $_SERVER['PATH_INFO']) {
            $path = dirname(substr($_SERVER['PHP_SELF'], 0, -strlen($_SERVER['PATH_INFO'])));
        } else {
            $path = dirname($_SERVER['PHP_SELF']);
        }
        
        if (substr($path = strtr($path, '\\', '/'), -1) != '/') {
            $path .= '/';
        }
        
        return $server . $path . $url;
    }
    
    /**
     * Check whether a given directory / file path exists and is writable
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $dir The directory / file to check
     * @param boolean $create Whether to create the directory if it doesn't exist
     * @return array The message and css class to use
     */
    private function _checkPermission($dir)
    {
        if(is_readable($dir)){
			return true;
        } else {
        	return false;
        }

    }
    
    /**
     * Check whether a given directory / file path exists and is writable
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $dir The directory / file to check
     * @param boolean $create Whether to create the directory if it doesn't exist
     * @return array The message and css class to use
     */
    public function checkPermission($dir, $create=false)
    {
        $exist = 'Directory does not exist';
        $write = 'Directory is not writable';
        $ret = array('class' => 'cross');

        if(!file_exists($dir)){
            if($create === false){
                $this->done = false;
                $ret['msg'] = $exist;
                return $ret;
            }
            $par_dir = dirname($dir);
            if(!file_exists($par_dir)){
                $this->done = false;
                $ret['msg'] = $exist;
                return $ret;
            }
            if(!is_writable($par_dir)){
                $this->done = false;
                $ret['msg'] = $exist;
                return $ret;
            }
            mkdir($dir, '0755');
        }

        if(is_writable($dir)){
            $ret['class'] = 'tick';
            return $ret;
        }

        $this->done = false;
        $ret['msg'] = $write;
        return $ret;
    }
    
	 /**
     * Change permissions on a directory helper
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param string $folderPath The directory / file to check
     * @return boolean
     */
    public function canChangePermissions($folderPath) {
		return $this->_chmodRecursive($folderPath, 0755);
    }
    
	/**
     * Change permissions on a directory (recursive)
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $folderPath The directory / file to check
     * @param boolean $create Whether to create the directory if it doesn't exist
     * @return boolean
     */
	private function _chmodRecursive($path, $filemode) {
		if (!is_dir($path))
			return chmod($path, $filemode);
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
		if($file != '.' && $file != '..') {
		    $fullpath = $path.'/'.$file;
		    if(is_link($fullpath))
		        return false;
		    elseif(!is_dir($fullpath)) {
		    	$perms = substr(sprintf('%o', fileperms($fullpath)), -4);
		    	if($perms != $filemode)
		        	if (!chmod($fullpath, $filemode))
		            	return false;
		    } elseif(!$this->chmodRecursive($fullpath, $filemode))
		        return false;
			}
		}
		closedir($dh);
		$perms = substr(sprintf('%o', fileperms($path)), -4);
		if($perms != $filemode) {
			if(chmod($path, $filemode))
				return true;
			else
				return false;
		} else {
			return true;
		}
	}
	
   /**
     * Check if a file can be written to a folder
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param string $filename the path to the file to create
     * @return boolean
     */
    public function canWriteFile($filename) {
    	$fh = fopen($filename, "w+");
    	if($fr = fwrite($fh, 'test') === false) {
    		return false;
    	}
    	
    	fclose($fh);
    	return true;
    }
    
    public function javaBridge() {
		try {
    		$javaSystem = new Java('java.lang.System');
		} catch (JavaException $e) {
			return false;
		}
		return true;
    }
		
    function tryJava1() {
    	$response = $this->pexec("java -version"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return false;
    	}

    	return 'java';
//    	return array('response'=>$response, 'java'=>'java');
    }
    
    function tryJava2() {
    	$response = $this->pexec("java"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return false;
    	}

    	return 'java';
//    	return array('response'=>$response, 'java'=>'java');
    }
    
    function tryJava3() {
    	$response = $this->pexec("whereis java"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return false;
    	}
    	$broke = explode(' ', $response['out'][0]);
		foreach ($broke as $r) {
			$match = preg_match('/bin/', $r);
			if($match) {
				return preg_replace('/java:/', '', $r);
//		    	return array('response'=>$response, 'java'=>preg_replace('/java:/', '', $r));
			}
		}
    }
    
    /**
	* Check if user entered location of JRE
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return mixed
	*/
    public function javaSpecified() {
    	if(isset($_POST['java'])) {
    		if($_POST['java'] != '') {
    			return $_POST['java'];
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    }
    
    /**
	* Determine the location of JAVA_HOME
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return mixed
	*/
    function getJava() {
    	$response = $this->tryJava1();
    	if(!is_array($response)) {
    		$response = $this->tryJava2();
    		if(!is_array($response)) {
    			$response = $this->tryJava3();
    		}
    	}
		
    	return $response;
    }
    
   /**
     * Portably execute a command on any of the supported platforms.
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param string $aCmd
     * @param array $aOptions
     * @return array
     */
    public function pexec($aCmd, $aOptions = null) {
    	if (is_array($aCmd)) {
    		$sCmd = $this->safeShellString($aCmd);
    	} else {
    		$sCmd = $aCmd;
    	}
    	$sAppend = $this->arrayGet($aOptions, 'append');
    	if ($sAppend) {
    		$sCmd .= " >> " . escapeshellarg($sAppend);
    	}
    	$sPopen = $this->arrayGet($aOptions, 'popen');
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
    	if(WINDOWS_OS) {
    	    $sCmd = 'call '.$sCmd;
    	}

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
 	public function arrayGet($aArray, $sKey, $mDefault = null, $bDefaultIfEmpty = true) {
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
	public function safeShellString () {
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

}
?>