<?php
/**
* Dependency Step Controller.
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

class dependencies extends Step
{
    private $maxPHPVersion = '5.3';
    private $minPHPVersion = '5.0.0';
    private $done;
	private $versionSection = false;
	private $extensionSection = false;
	private $configurationSection = false;

	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $storeInSession = true;

	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = true;

	/**
	 * Control function for position within the step
	 *
	 * @author KnowledgeTree Team
     * @access public
	 * @return string The position in the step
	 */
    public function doStep()
    {
        $this->temp_variables = array("step_name"=>"dependencies", "silent"=>$this->silent);
        $this->error = array();
        $this->done = true;
    	if(!$this->inStep("dependencies")) {
    		$this->doRun();
    		$this->storeSilent();
    		return 'landing';
    	}
        // Check dependencies
        $passed = $this->doRun();
        $this->storeSilent();
        if($this->next()) {
            if($passed)
                return 'next';
            else
                return 'error';
        } else if($this->previous()) {
            return 'previous';
        }
        return 'landing';
    }

    /**
     * Execute the step
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return boolean True to continue | False if errors occurred
     */
    public function doRun()
    {
        $check = $this->checkPhpVersion();
        $this->temp_variables['version'] = $check;
        $this->temp_variables['php'] = $check['class'];
        $configs = $this->checkPhpConfiguration();
        $this->temp_variables['configurations'] = $configs;
        // get the list of extensions
        $list = $this->getRequiredExtensions();
        $extensions = array();
        $this->temp_variables['php_ext'] = 'tick';
        $extSec = false;
        foreach($list as $ext) {
            $ext['available'] = 'no';
            if($this->checkExtension($ext['extension'])){
                $ext['available'] = 'yes';
            } else {
            	$extSec = true; // Mark failed extension
                if($ext['required'] == 'no') {
                	if($this->temp_variables['php_ext'] != 'cross')
                		$this->temp_variables['php_ext'] = 'cross_orange';
                    $ext['available'] = 'optional';
                    $this->warnings[] = 'Missing optional extension: '.$ext['name'];
                } else {
                    $this->done = false;
                    $this->temp_variables['php_ext'] = 'cross';
                    $this->error[] = 'Missing required extension: '.$ext['name'];
                    $this->error[$ext['extension']] = 'Missing required extension: '.$ext['name'];
                }
            }

            $extensions[] = $ext;
        }
		if($extSec) {
			$this->extensionSection = true;
		}
        $this->temp_variables['extensions'] = $extensions;

        return $this->done;
    }

    /**
     * Get any errors that occurred
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return array The error list
     */
    public function getErrors() {
        return $this->error;
    }

  	/**
     * Get any warnings that occurred
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return array The error list
     */
    public function getWarnings() {
        return $this->warnings;
    }

    /**
     * Get the variables to be passed to the template
     *
	 * @author KnowledgeTree Team
     * @access public
     * @return array
     */
    public function getStepVars()
    {
        return $this->temp_variables;
    }

    /**
     * Check the php configuration
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array The configurations list
     */
   private function checkPhpConfiguration()
    {
        $configs = $this->getConfigurations();
		$this->temp_variables['php_con'] = 'tick';
        foreach($configs as $key => $config) {
            $setting = ini_get($config['configuration']);
            switch($config['type']){
                case 'bool':
                    $value = ($setting == 1) ? 'ON' : 'OFF';
                    break;

                case 'empty':
                    $value = ($setting === false || $setting === '') ? 'unset' : $setting;
                    break;

                default:
                    $value = $setting;
            }

            $class = ($value == $config['recommended']) ? 'green' : 'orange';
			if($class == 'orange') {
				$this->configurationSection = true;
				$this->temp_variables['php_con'] = 'cross_orange';
				$this->warnings[] = "$value";
			}
            $configs[$key]['setting'] = $value;
            $configs[$key]['class'] = $class;
        }

        $limits = $this->getLimits();

        foreach($limits as $key => $limit) {
            $setting = ini_get($limit['configuration']);

            $setting = $this->prettySizeToActualSize($setting);
            $recommended = $this->prettySizeToActualSize($limit['recommended']);
            $class = ($recommended < $setting || $setting = -1) ? 'green' : 'orange';
			if($class == 'orange') {
				$this->temp_variables['php_con'] = 'cross_orange';
			}
			if($setting < 0)
				$setting = "unset";
            $limits[$key]['setting'] = $this->prettySize($setting);
            $limits[$key]['class'] = $class;
        }
        $configs = array_merge($configs, $limits);

        return $configs;
    }

    /**
     * Check that the version of php is correct
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array Version check result
     */
    private function checkPhpVersion()
    {
        $phpversion = phpversion();
        $phpversion5 = version_compare($phpversion, $this->minPHPVersion, '>=');
        $phpversion6 = version_compare($phpversion, $this->maxPHPVersion, '<');
        $check['class'] = 'cross';
        if($phpversion5 != 1){
        	$this->versionSection = true; // Mark failed version
            $this->done = false;
            $check['version'] = "Your PHP version needs to be PHP 5.0 or higher. You are running version <b>{$phpversion}</b>.";
            return $check;
        }
        if($phpversion6 != 1){
        	$this->versionSection = true; // Mark failed version
            $this->done = false;
            $check['version'] = "KnowledgeTree is not supported on PHP 6.0 and higher. You are running version <b>{$phpversion}</b>.";
            return $check;
        }
        $check['class'] = 'tick';
        $check['version'] =  "You are running version <b>{$phpversion}</b>.";

        return $check;
    }

    /**
     * Check whether the given extension is loaded
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $extension
     * @return boolean
     */
    private function checkExtension($extension)
    {
        if(extension_loaded($extension)){
            return true;
        }
        $this->continue = false;
        return false;
    }

    /**
     * Convert a formatted string to a size integer
     *
	 * @author KnowledgeTree Team
     * @access string
     * @param integer $pretty
     * @return integer
     */
    private function prettySizeToActualSize($pretty) {
        if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'G') {
            return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024 * 1024;
        }
        if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'M') {
            return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024;
        }
        if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'K') {
            return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024;
        }
        return (int)$pretty;
    }

    /**
     * Convert a size integer to a formatted string
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param integer $v
     * @return string
     */
    private function prettySize($v) {
        $v = (float)$v;
        foreach (array('B', 'K', 'M', 'G') as $unit) {
            if ($v < 1024) {
                return $v . $unit;
            }
            $v = $v / 1024;
        }
    }

    /**
     * Get the list of extensions used by the system
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array
     */
    private function getRequiredExtensions() {
    	$ext = array(
	            array('extension' => 'iconv', 'required' => 'no', 'name' => 'IconV', 'details' => 'Used for conversion between character sets.'),
	            array('extension' => 'mysql', 'required' => 'yes', 'name' => 'MySQL', 'details' => 'Used for accessing a MySQL database.'),
	            array('extension' => 'curl', 'required' => 'yes', 'name' => 'cURL', 'details' => 'Allows the connection and communication between different servers types using various protocols.'),
	            array('extension' => 'xmlrpc', 'required' => 'yes', 'name' => 'XMLRPC', 'details' => 'Used with XML-RPC servers and clients.'),
	            array('extension' => 'win32service', 'required' => 'no', 'name' => 'Win32 Services', 'details' => 'Allows control of Microsoft Windows services.'),
	            array('extension' => 'mbstring', 'required' => 'no', 'name' => 'Multi Byte Strings', 'details' => 'Used in the manipulation of multi-byte strings.'),
	            array('extension' => 'ldap', 'required' => 'no', 'name' => 'LDAP', 'details' => 'Used to access LDAP directory servers.'),
	            array('extension' => 'json', 'required' => 'yes', 'name' => 'JSON', 'details' => 'Implements the javascript object notation (json) data-interchange format.'),
	            array('extension' => 'openssl', 'required' => 'no', 'name' => 'Open SSL', 'details' => 'Used for the generation and verification of signatures and the encrypting and decrypting of data.'),
	        );
    	if(WINDOWS_OS) {
	        return $ext;
    	} else {
    		unset($ext[4]); // Relies on current structure of $ext.
	        return $ext;
    	}
    }

    /**
     * Get the recommended configuration settings
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array
     */
    private function getConfigurations()
    {
    	$conf = array(
            array('name' => 'Safe Mode', 'configuration' => 'safe_mode', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Display Errors', 'configuration' => 'display_errors', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Display Startup Errors', 'configuration' => 'display_startup_errors', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'File Uploads', 'configuration' => 'file_uploads', 'recommended' => 'ON', 'type' => 'bool'),
            array('name' => 'Magic Quotes GPC', 'configuration' => 'magic_quotes_gpc', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Magic Quotes Runtime', 'configuration' => 'magic_quotes_runtime', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Register Globals', 'configuration' => 'register_globals', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Output Buffering', 'configuration' => 'output_buffering', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Session auto start', 'configuration' => 'session.auto_start', 'recommended' => 'OFF', 'type' => 'bool'),
            array('name' => 'Automatic prepend file', 'configuration' => 'auto_prepend_file', 'recommended' => 'unset', 'type' => 'empty'),
            array('name' => 'Automatic append file', 'configuration' => 'auto_append_file', 'recommended' => 'unset', 'type' => 'empty'),
            array('name' => 'Open base directory', 'configuration' => 'open_basedir', 'recommended' => 'unset', 'type' => 'empty'),
            array('name' => 'Default MIME type', 'configuration' => 'default_mimetype', 'recommended' => 'text/html', 'type' => 'string'),
        );
        if(!WINDOWS_OS) { // Remove linux settings
        	unset($conf[1]);
        	unset($conf[2]);
        }
        return $conf;
    }

    /**
     * Get the recommended limits settings
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array
     */
    private function getLimits()
    {
        return array(
            array('name' => 'Maximum POST size', 'configuration' => 'post_max_size', 'recommended' => '32M', 'type' => 'int'),
            array('name' => 'Maximum upload size', 'configuration' => 'upload_max_filesize', 'recommended' => '32M', 'type' => 'int'),
            array('name' => 'Memory limit', 'configuration' => 'memory_limit', 'recommended' => '32M', 'type' => 'int'),
        );
    }

    public function storeSilent() {
	  	$this->temp_variables['versionSection'] = $this->versionSection;
		$this->temp_variables['extensionSection'] = $this->extensionSection;
		$this->temp_variables['configurationSection'] = $this->configurationSection;
    }
}
?>
