<?php
/**
* Installer Utilities Library
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
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
require_once("path.php");
require_once("iniUtilities.php");
require_once("dbUtilities.php");

class InstallUtil {
	private $salt = 'installers';
	public $dbUtilities = null;
	public $iniUtilities = null;

	/**
	* Constructs installation object
	*
	* @author KnowledgeTree Team
	* @access public
 	*/
	public function __construct() {
		$this->dbUtilities = new dbUtilities();
		$this->iniUtilities = new iniUtilities();
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
		if (file_exists(SYSTEM_DIR.'var'.DS.'bin'.DS."install.lock")) {
			return true;
		}
		return false;
	}

	public function error($error) {
		$template_vars['install_type'] = strtoupper(substr(INSTALL_TYPE,0,1)).substr(INSTALL_TYPE,1);
		$template_vars['install_version'] = $this->readVersion();
		$template_vars['error'] = $error;
		$file = "templates/error.tpl";
		if (file_exists($file)) {
			extract($template_vars); // Extract the vars to local namespace
			ob_start();
			include($file);
	        $contents = ob_get_contents();
	        ob_end_clean();
	        echo $contents;
		}
		return false;

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
                $url = $protocol .':'. end(explode(':', $url, 2));
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
    public function _checkPermission($dir, $writable = false)
    {
        if(is_readable($dir)) {
        	if($writable) {
        		if(!is_writable($dir)) {
        			return false;
        		}
        	}
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
    public function checkPermission($dir, $create=false, $file = false)
    {
    	if(!$file) {
        	$exist = 'Directory doesn\'t exist';
    	} else {
        	$exist = 'File doesn\'t exist';
    	}
        $write = 'Directory not writable';
        $fwrite = 'File not writable';
        $ret = array('class' => 'cross');
        if(!file_exists($dir)) {
            if($create === false){
                $ret['msg'] = $exist;
                return $ret;
            }
            $par_dir = dirname($dir);
            if(!file_exists($par_dir)){
                $ret['msg'] = $exist;
                return $ret;
            }
            if(!is_writable($par_dir)){
                $ret['msg'] = $exist;
                return $ret;
            }
            mkdir($dir, 0755);
        }
        if(is_writable($dir)) {
            $ret['class'] = 'tick';

            return $ret;
        }
        if(!$file) {
        	$ret['msg'] = $write;
        } else {
        	$ret['msg'] = $fwrite;
    	}
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
		    	if($perms != $filemode) {
		        	if (!chmod($fullpath, $filemode)) {
		            	return false;
		        	}
		    	}
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
    	$fr = fwrite($fh, 'test');
    	if($fr === false) {
    		return false;
    	}

    	fclose($fh);
    	return true;
    }

    /**
     * Attempt using the php-java bridge
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return boolean
     */
    public function javaBridge() {
		try {
    		new Java('java.lang.System');
		} catch (JavaException $e) {
			return $e;
		}
		return true;
    }

    /**
	* Check if Zend Bridge is enabled
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function zendBridge() {
		$mods = get_loaded_extensions();
		if(in_array('Zend Java Bridge', $mods))
			return true;
		else
			return false;
    }

    /**
     * Attempt java detection
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return boolean
     */
    public function tryJava1() {
    	$response = $this->pexec("java -version"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return '';
    	}

    	return 'java';
    }

    /**
     * Attempt java detection
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return boolean
     */
    public function tryJava2() {
    	$response = $this->pexec("java"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return '';
    	}

    	return 'java';
    }

    /**
     * Attempt java detection
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return boolean
     */
    public function tryJava3() {
    	$response = $this->pexec("whereis java"); // Java Runtime Check
    	if(empty($response['out'])) {
    		return '';
    	}
    	$broke = explode(' ', $response['out'][0]);
		foreach ($broke as $r) {
			$match = preg_match('/bin/', $r);
			if($match) {
				return preg_replace('/java:/', '', $r);
			}
		}

		return '';
    }

    /**
	* Check if user entered location of JRE
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
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
	* Check if user entered location of PHP
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return mixed
	*/
    public function phpSpecified() {
    	if(isset($_POST['php'])) {
    		if($_POST['php'] != '') {
    			return $_POST['php'];
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    }

    public function openOfficeSpecified() {
    	if(isset($_POST['soffice'])) {
    		if($_POST['soffice'] != '') {
    			return $_POST['soffice'];
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    }


	/**
	* Check if system needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function migrationSpecified() {
    	if(isset($_POST['installtype'])) {
        	if($_POST['installtype'] == "Upgrade Installation") {
            	return true;
        	}
    	}

        return false;
	}

	public function upgradeInstall() {
		if(isset($_GET['Upgrade'])) {
			return true;
		}
    	if(isset($_GET['Next'])) {
        	if($_POST['Next'] == "Upgrade") {
            	return true;
        	}
    	}

        return false;
	}
	
	/**
	* Check if system needs to be accessed
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function finishInstall() {
    	if(isset($_GET['Next'])) {
        	if($_GET['Next'] == "Finish") {
            	return true;
        	}
    	}

        return false;
	}
	
	/**
	* Check if system needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function upgradeSpecified() {
    	if(isset($_POST['installtype'])) {
        	if($_POST['installtype'] == "Upgrade Only") {
            	return true;
        	}
    	}

        return false;
	}

	/**
	* Check if system needs to be migrated
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
	* @return boolean
 	*/
	public function installationSpecified() {
    	if(isset($_GET['Return'])) {
        	if($_GET['Return'] == "Return To Installation") {
            	return true;
        	}
    	}

        return false;
	}

	public function loginSpecified() {
    	if(isset($_GET['Return'])) {
        	if($_GET['Return'] == "Return To Installation") {
            	return true;
        	}
    	}

        return false;
	}
	
	/**
	* Get session data from package
	*
	* @author KnowledgeTree Team
	* @params none
	* @access public
	* @return boolean
	*/
    public function getDataFromPackage($package, $class) {
    	if(empty($_SESSION[$package][$class])) {
    		return false;
    	}

    	return $_SESSION[$package][$class];
    }

	/**
	* Get session data from post
	*
	* @author KnowledgeTree Team
	* @params none
	* @access public
	* @return boolean
	*/
    public function getDataFromSession($class) {
    	if(empty($_SESSION[$this->salt][$class])) {
    		return false;
    	}

    	return $_SESSION[$this->salt][$class];
    }

    /**
	* Determine the location of JAVA_HOME
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
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
	* Determine the location of PHP
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return mixed
	*/
    function getPhp() {
		$cmd = "whereis php";
		$res = $this->getPhpHelper($cmd);
		if($res != '') {
			return $res;
		}
		$cmd = "which php";
		$res = $this->getPhpHelper($cmd);
		if($res != '') {
			return $res;
		}
		$phpDir = $this->useZendPhp();
		if(!$phpDir) return 'php';
		if(file_exists($phpDir."php")) {
			return $phpDir."php";
		}

		return 'php';
    }

    function getPhpHelper($cmd) {
    	$response = $this->pexec($cmd);
		if(is_array($response['out'])) {
			if (isset($response['out'][0])) {
				$broke = explode(' ', $response['out'][0]);
				foreach ($broke as $r) {
					$match = preg_match('/bin/', $r);
					if($match) {
						return preg_replace('/php:/', '', $r);
					}
				}
			}
		}

		return '';
    }

    /**
     * Deletes migration lock file if a clean install is chosen
     * This is in case someone changes their mind after choosing upgrade/migrate and clicks back up to this step
     *
     * @author KnowledgeTree Team
     * @access public
     * @return void
     */
    function deleteMigrateFile() {
    	if(file_exists(SYSTEM_DIR.'var'.DS.'bin'.DS."migrate.lock"))
    		unlink(SYSTEM_DIR.'var'.DS.'bin'.DS."migrate.lock");
    }

    /**
     * Check if we are migrating an existing installation
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return boolean
     */
    public function isMigration() {
    	if(file_exists(SYSTEM_DIR.'var'.DS.'bin'.DS."migrate.lock"))
    		return true;
    	return false;
    }
	
    public function isCommunity() {
    	if(INSTALL_TYPE == "community")
    		return true;
    	return false;
    }
    
    /**
     * Determine type of installation
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function installEnvironment() {
    	$matches = false;
	    preg_match('/Zend/', SYSTEM_DIR, $matches); // Install Type
	    if($matches) {
	    	return  'Zend';
	    } else {
	    	$modules = get_loaded_extensions();
	    	if(in_array('Zend Download Server', $modules) || in_array('Zend Monitor', $modules) || in_array('Zend Utils', $modules) || in_array('Zend Page Cache', $modules)) {
	    		return  'Zend';
	    	} else {
	    		return 'Source';
	    	}
	    }
    }

    /**
     * Determine if zend php exists
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function useZendPhp() {
	    if($this->installEnvironment() == 'Zend') {
	    	if(WINDOWS_OS) { // For Zend Installation only
				$sysdir = explode(DS, SYSTEM_DIR);
				array_pop($sysdir);
				array_pop($sysdir);
				array_pop($sysdir);
				$zendsys = '';
				foreach ($sysdir as $v) {
					$zendsys .= $v.DS;
				}
				$bin = $zendsys."ZendServer".DS."bin".DS;
				if(file_exists($bin))
					return $bin;
	    	} else {
	    		return DS."usr".DS."local".DS."zend".DS."bin".DS;
	    	}
	    }

	    return false;
    }

    public function useZendJava() {
	    if($this->installEnvironment() == 'Zend') {
	    	if(WINDOWS_OS) { // For Zend Installation only
				$sysdir = explode(DS, SYSTEM_DIR);
				array_pop($sysdir);
				array_pop($sysdir);
				$zendsys = '';
				foreach ($sysdir as $v) {
					$zendsys .= $v.DS;
				}
				$jvm = $zendsys."java".DS."jre".DS."bin".DS."java.exe";
				if(file_exists($jvm))
					return $jvm;
	    	} else {
	    		$java = "/usr/bin/java";
	    		if(file_exists($java)) {
	    			return $java;
	    		}
	    	}
	    }

	    return false;
    }

    /**
     * Determine if mysql exists
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function detectMysql() {
	    if(WINDOWS_OS) { // Mysql bin [Windows]
		    $serverPaths = explode(';',$_SERVER['PATH']);
		    foreach ($serverPaths as $apath) {
		    	$matches = false;
		    	preg_match('/mysql/i', $apath, $matches);
		    	if($matches) {
		    		return $apath.DS;
		    		break;
		    	}
		    }
	    }

	    return "mysql"; // Assume its linux and can be executed from command line
    }

    public function sqlInstallDir() {
    	return SYSTEM_DIR."sql".DS."mysql".DS."install".DS;
    }

    public function getFileByLine($file) {
    	$fileLines = array();
		$file_handle = fopen($file, "rb");
		while (!feof($file_handle) ) {
			$line_of_text = fgets($file_handle);
			$parts = explode('=', $line_of_text);
			$fileLines[] = trim($line_of_text);
		}
		fclose($file_handle);
		return $fileLines;
    }

    public function readVersion() {
    	$verFile = SYSTEM_DIR."docs".DS."VERSION.txt";
    	if(file_exists($verFile)) {
			$foundVersion = file_get_contents($verFile);
			return $foundVersion;
    	}

		return false;
    }

    public function getVersionType() {
    	$verFile = SYSTEM_DIR."docs".DS."VERSION-TYPE.txt";
    	if(file_exists($verFile)) {
			$type = file_get_contents($verFile);
			return $type;
    	}

		return "community";
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

	/**
     * The system identifier is a unique ID defined in every installation of KnowledgeTree
     *
     * @return string The system identifier
     */
    function getSystemIdentifier($db = true)
    {
    	$sIdentifier = null;

    	if ($db) {
        	$sIdentifier = $this->getSystemSetting('kt_system_identifier');
    	}

        if (empty($sIdentifier)) {
	        // if we have one from the session, simply return that one
			if (isset($_SESSION['installers']['registration']['installation_guid'])
			     && !empty($_SESSION['installers']['registration']['installation_guid'])) {
				$sIdentifier = $_SESSION['installers']['registration']['installation_guid'];
			}
			else { // generate
	            $sIdentifier = md5(uniqid(mt_rand(), true));
			}
            if ($db) {
            	$this->setSystemSetting('kt_system_identifier', $sIdentifier);
            }
        }
        return $sIdentifier;
    }

    function setSystemSetting($name, $value)
    {
        // we either need to insert or update:
        $sTable = $this->getTableName('system_settings');
        $current_value = $this->getSystemSetting($name);
        $query = '';
        if (is_null($current_value)) {
            // insert
            $query = 'INSERT INTO ' . $sTable . '(name, value) VALUES ("' . $name . '", "' . $value . '")';
        } else {
            // update
            $query = 'UPDATE ' . $sTable . ' SET value = "' . $value . '" WHERE name = "' . $name . '"';
        }

        $res = $this->dbUtilities->query($query);
	    $errors = $this->dbUtilities->getErrors();
		if (count($errors)) { return false; }

        return true;
    }

	function getSystemSetting($name, $default = null)
	{
        // XXX make this use a cache layer?
        $sTable = $this->getTableName('system_settings');
        $query = 'SELECT value FROM %s WHERE name = "' . $name . '"';
		$res = $this->dbUtilities->query($query);
        $errors = $this->dbUtilities->getErrors();
		if (count($errors)) {
            if(!is_null($default)){
                return $default;
            }
            return null;
        }

        $result = $this->dbUtilities->fetchAssoc($res);
        if (is_null($result)) { return $default; }

        return $result[$name];
    }

	// {{{ getTableName
    /**
     * The one true way to get the correct name for a table whilst
     * respecting the administrator's choice of table naming.
     */
    function getTableName($sTable)
    {
        $sDefaultsTable = $sTable . "_table";
        if (isset($GLOBALS['default']->$sDefaultsTable)) {
            return $GLOBALS['default']->$sDefaultsTable;
        }
        return $sTable;
    }
    // }}}

    // {{{ copyDirectory
    function copyDirectory($sSrc, $sDst, $bMove = false) {
        if (!WINDOWS_OS) {
            if ($bMove && file_exists('/bin/mv')) {
                $this->pexec(array('/bin/mv', $sSrc, $sDst));
                return;
            }
            if (!$bMove && file_exists('/bin/cp')) {
                $this->pexec(array('/bin/cp', '-R', $sSrc, $sDst));
                return;
            }
        }
        if (substr($sDst, 0, strlen($sSrc)) === $sSrc) {
            return false; //PEAR::raiseError(_kt("Destination of move is within source"));
        }
        $hSrc = @opendir($sSrc);
        if ($hSrc === false) {
            return false; //PEAR::raiseError(sprintf(_kt("Could not open source directory: %s"), $sSrc));
        }
        @mkdir($sDst, 0777);
        while (($sFilename = readdir($hSrc)) !== false) {
            if (in_array($sFilename, array('.', '..'))) {
                continue;
            }
            $sOldFile = sprintf("%s"  . DS . "%s", $sSrc, $sFilename);
            $sNewFile = sprintf("%s" . DS . "%s", $sDst, $sFilename);
            if (is_dir($sOldFile)) {
                $this->copyDirectory($sOldFile, $sNewFile, $bMove);
                continue;
            }
            if ($bMove) {
                $this->moveFile($sOldFile, $sNewFile);
            } else {
                copy($sOldFile, $sNewFile);
            }
        }
        if ($bMove) {
            @rmdir($sSrc);
        }
    }
    // }}}
    
    // {{{ moveFile
    function moveFile ($sSrc, $sDst) {
        // Only 4.3.3 and above allow us to use rename across partitions
        // on Unix-like systems.
        if (!WINDOWS_OS) {
            // If /bin/mv exists, just use it.
            if (file_exists('/bin/mv')) {
                $this->pexec(array('/bin/mv', $sSrc, $sDst));
                return;
            }
            $aSrcStat = stat($sSrc);
            if ($aSrcStat === false) {
                return false; //PEAR::raiseError(sprintf(_kt("Couldn't stat source file: %s"), $sSrc));
            }
            $aDstStat = stat(dirname($sDst));
            if ($aDstStat === false) {
                return false; //PEAR::raiseError(sprintf(_kt("Couldn't stat destination location: %s"), $sDst));
            }
            if ($aSrcStat["dev"] === $aDstStat["dev"]) {
                $res = @rename($sSrc, $sDst);
                if ($res === false) {
                    return false; //PEAR::raiseError(sprintf(_kt("Couldn't move file to destination: %s"), $sDst));
                }
                return;
            }
            $res = @copy($sSrc, $sDst);
            if ($res === false) {
                return false; //PEAR::raiseError(sprintf(_kt("Could not copy to destination: %s"), $sDst));
            }
            $res = @unlink($sSrc);
            if ($res === false) {
                return false; //PEAR::raiseError(sprintf(_kt("Could not remove source: %s"), $sSrc));
            }
        } else {
            $res = @rename($sSrc, $sDst);
            if ($res === false) {
                return false; //PEAR::raiseError(sprintf(_kt("Could not move to destination: %s"), $sDst));
            }
        }
    }
    // }}}
}
?>
