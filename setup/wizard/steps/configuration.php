<?php
/**
* Configuration Step Controller.
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

class configuration extends Step
{
	/**
	* Database object
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/	
    private $_dbhandler = null;
    private $host;
    private $port;
    private $root_url;
    private $file_system_root;
    private $ssl_enabled;
    private $done;
	public $temp_variables = array("step_name"=>"configuration");
	public $displayFirst = true;
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $storeInSession = true;
	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = true;

	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = true;
    
    protected $util = null;
    /**
     * Class constructor
     *
	 * @author KnowledgeTree Team
     * @access public
     */
    public function __construct()
    {
    	$this->temp_variables = array("step_name"=>"configuration", "silent"=>$this->silent);
    	$this->_dbhandler = new dbUtil();
    	$this->util = new InstallUtil();
        $this->done = true;
    }

    /**
     * Set the variables from those stored in the session.
     * Used for stepping back to the step from a future step.
     *
	 * @author KnowledgeTree Team
     * @access private
     */
	private function setDetails() {
		$conf = $this->getDataFromSession("configuration");
		if($conf) {
			$this->temp_variables['server'] = $conf['server'];
			$this->temp_variables['paths'] = $conf['paths'];
		}
	}

	/**
	 * Control function for position within the step
	 *
	 * @author KnowledgeTree Team
     * @access public
	 * @return string The position in the step
	 */
    public function doStep() {
    	if(!$this->inStep("configuration")) {
    		$this->doRun();
    		return 'landing';
    	}
    	$this->loadTemplateDefaults();
        if($this->next()) {
            if($this->doRun()) {
                return 'confirm';
            }
            return 'error';
        } else if($this->previous()) {
        	$this->setDetails();
            return 'previous';
        } else if($this->confirm()) {
        	if($this->doRun()) {
            	return 'next';
        	}
        	return 'error';
        } else if($this->edit()) {
        	$this->setDetails();
        	if($this->doRun()) {
        		
        	}
        	return 'landing';
        }

        $this->doRun();
        return 'landing';
    }

    public function loadTemplateDefaults() {
    	$this->temp_variables['paths_perms'] = 'tick';
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
        $server = $this->getServerInfo();
        $this->temp_variables['server'] = $server;

        $paths = $this->getPathInfo($server['file_system_root']['value']);
        $this->temp_variables['paths'] = $paths;

        // Running user
        // Logging

        return $this->done;
    }

    /**
     * Get the database configuration settings
     *
	 * @author KnowledgeTree Team
     * @access public
     * @param array $server
     * @param array $dbconf
     * @return array
     */
    public function registerDBConfig($server, $dbconf) {
        // Adjust server variables
        $server['dbName'] = array('where'=>'file', 'name'=>ucwords($dbconf['dname']), 'section'=>'db', 'value'=>$dbconf['dname'], 'setting'=>'dbName');
        $server['dbUser'] = array('where'=>'file', 'name'=>ucwords($dbconf['duname']), 'section'=>'db', 'value'=>$dbconf['duname'], 'setting'=>'dbUser');
        $server['dbPass'] = array('where'=>'file', 'name'=>ucwords($dbconf['dpassword']), 'section'=>'db', 'value'=>$dbconf['dpassword'], 'setting'=>'dbPass');
        $server['dbPort'] = array('where'=>'file', 'name'=>ucwords($dbconf['dport']), 'section'=>'db', 'value'=>$dbconf['dport'], 'setting'=>'dbPort');
        $server['dbAdminUser'] = array('where'=>'file', 'name'=>ucwords($dbconf['dmsname']), 'section'=>'db', 'value'=>$dbconf['dmsname'], 'setting'=>'dbAdminUser');
        $server['dbAdminPass'] = array('where'=>'file', 'name'=>ucwords($dbconf['dmspassword']), 'section'=>'db', 'value'=>$dbconf['dmspassword'], 'setting'=>'dbAdminPass');

        return $server;
    }

    /**
     * Perform the installation associated with the step.
     * Variables required by the installation are stored within the session.
     *
	 * @author KnowledgeTree Team
     * @access public
     */
    public function installStep()
    {
        // get data from the server
        $conf = $this->getDataFromSession("configuration");
        $server = $conf['server'];
        $paths = $conf['paths'];

        // initialise writing to config.ini
        $configPath = realpath('../../config/config.ini');

        $ini = false;
        if(file_exists($configPath)) {
            $ini = new Ini($configPath);
        }

        // initialise the db connection

        // retrieve database information from session
        $dbconf = $this->getDataFromSession("database");

        // make db connection
        $this->_dbhandler->load($dbconf['dhost'], $dbconf['duname'], $dbconf['dpassword'], $dbconf['dname']);

        // add db config to server variables
		$server = $this->registerDBConfig($server, $dbconf);

        $table = 'config_settings';
        // write server settings to config_settings table and config.ini
        foreach($server as $item){

            switch($item['where']){
                case 'file':
                    $value = $item['value'];
                    if($value == 'yes'){
                        $value = 'true';
                    }
                    if($value == 'no'){
                        $value = 'false';
                    }
                    if(!$ini === false){
                        $ini->updateItem($item['section'], $item['setting'], $value);
                    }
                    break;

                case 'db':
                    $value = mysql_real_escape_string($item['value']);
                    $setting = mysql_real_escape_string($item['setting']);

                    $sql = "UPDATE {$table} SET value = '{$value}' WHERE item = '{$setting}'";
                    $this->_dbhandler->query($sql);
                    break;
            }
        }

        // write the paths to the config_settings table
        if(is_array($paths)) {
	        foreach ($paths as $item){
	            if(empty($item['setting'])){
	                continue;
	            }
	
	            $value = mysql_real_escape_string($item['path']);
	            $setting = mysql_real_escape_string($item['setting']);
	
	            $sql = "UPDATE {$table} SET value = '{$value}' WHERE item = '{$setting}'";
	            $this->_dbhandler->query($sql);
	        }
        }

        // write out the config.ini file
        if(!$ini === false){
            $ini->write();
        }

        // close the database connection
        $this->_dbhandler->close();
    }

    /**
     * Get the server settings information
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array Server settings
     */
    private function getServerInfo()
    {
        $script = $_SERVER['SCRIPT_NAME'];
        $file_system_root = $_SERVER['DOCUMENT_ROOT'];
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $ssl_enabled = isset($_SERVER['HTTPS']) ? (strtolower($_SERVER['HTTPS']) === 'on' ? 'yes' : 'no') : 'no';

        $pos = strpos($script, '/setup/wizard/');
        $root_url = substr($script, 0, $pos);

        $root_url = (isset($_POST['root_url'])) ? $_POST['root_url'] : $root_url;
        $file_system_root = (isset($_POST['file_system_root'])) ? $_POST['file_system_root'] : $file_system_root.$root_url;
        $host = (isset($_POST['host'])) ? $_POST['host'] : $host;
        $port = (isset($_POST['port'])) ? $_POST['port'] : $port;
        $ssl_enabled = (isset($_POST['ssl_enabled'])) ? $_POST['ssl_enabled'] : $ssl_enabled;

        $server = array();
        $server['root_url'] = array('name' => 'Root Url', 'setting' => 'rootUrl', 'where' => 'db', 'value' => $root_url);
        $server['file_system_root'] = array('name' => 'File System Root', 'section' => 'KnowledgeTree', 'setting' => 'fileSystemRoot', 'where' => 'file', 'value' => $file_system_root);
        $server['host'] = array('name' => 'Host', 'setting' => 'server_name', 'where' => 'db', 'value' => $host);
        $server['port'] = array('name' => 'Port', 'setting' => 'server_port', 'where' => 'db', 'value' => $port);
        $server['ssl_enabled'] = array('name' => 'SSL Enabled', 'section' => 'KnowledgeTree', 'setting' => 'sslEnabled', 'where' => 'file', 'value' => $ssl_enabled);

        if(empty($server['host']['value']))
            $this->error[] = 'Please enter the server\'s host name';

        if(empty($server['port']['value']))
            $this->error[] = 'Please enter the server\'s port';

        if(empty($server['file_system_root']['value']))
            $this->error[] = 'Please enter the file system root';

        return $server;
    }

    /**
     * Get the path information for directories. Check permissions and existence of each.
     * Expands any ${fileSystemRoot} and ${varDirectory} variables contained in the path.
     *
	 * @author KnowledgeTree Team
     * @access private
     * @param string $fileSystemRoot The file system root of the installation
     * @return array The path information
     */
    private function getPathInfo($fileSystemRoot)
    {
        $dirs = $this->getDirectories();
        $varDirectory = $fileSystemRoot . DIRECTORY_SEPARATOR . 'var';
        foreach ($dirs as $key => $dir){
            $path = (isset($_POST[$dir['setting']])) ? $_POST[$dir['setting']] : $dir['path'];

            while(preg_match('/\$\{([^}]+)\}/', $path, $matches)){
                $path = str_replace($matches[0], $$matches[1], $path);
            }

            $dirs[$key]['path'] = $path;
            $class = $this->util->checkPermission($path, $dir['create']);
            
			if($class['class'] != 'tick') {
				$this->temp_variables['paths_perms'] = $class['class'];
				$this->done = false;
			}
            $dirs[$key] = array_merge($dirs[$key], $class);
        }

        return $dirs;
    }



     /**
     * Get the list of directories that need to be checked
     *
	 * @author KnowledgeTree Team
     * @access private
     * @return array The directory list
     */
    private function getDirectories()
    {
        return array(
                array('name' => 'Var Directory', 'setting' => 'varDirectory', 'path' => '${fileSystemRoot}/var', 'create' => false),
                array('name' => 'Document Directory', 'setting' => 'documentRoot', 'path' => '${varDirectory}/Documents', 'create' => true),
                array('name' => 'Log Directory', 'setting' => 'logDirectory', 'path' => '${varDirectory}/log', 'create' => true),
                array('name' => 'Temporary Directory', 'setting' => 'tmpDirectory', 'path' => '${varDirectory}/tmp', 'create' => true),
                array('name' => 'Uploads Directory', 'setting' => 'uploadDirectory', 'path' => '${varDirectory}/uploads', 'create' => true),
                array('name' => 'Configuration File', 'setting' => '', 'path' => '${fileSystemRoot}/config/config.ini', 'create' => false),
            );
    }
}
?>