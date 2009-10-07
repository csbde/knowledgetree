<?php
/**
* Database Step Controller. 
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
* @package Upgrader
* @version Version 0.1
*/

// include defaults
require '../../config/dmsDefaults.php';
require_once KT_LIB_DIR . '/config/config.inc.php';
include KT_LIB_DIR . '/upgrades/upgrade.inc.php';

class upgradeDatabase extends Step 
{
	/**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @var object
	*/	
    public $_dbhandler = null;
    	
	/**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @var object
	*/	
    public $_util = null;
    
	/**
	* Database type
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/	
    private $dtype = '';
    
	/**
	* Database types
	*
	* @author KnowledgeTree Team
	* @access private
	* @var array
	*/	
    private $dtypes = array();
    
	/**
	* Database host
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dhost = '';
    
	/**
	* Database port
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dport = '';
    
	/**
	* Database name
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dname = '';
    
	/**
	* Database root username
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $duname = '';
    
	/**
	* Database root password
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dpassword = '';
    
	/**
	* Database dms username
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dmsname = '';
    
	/**
	* Database dms password
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dmspassword = '';

	/**
	* Default dms user username
	*
	* @author KnowledgeTree Team
	* @access private
	* @var boolean
	*/
    private $dmsusername = '';
    
	/**
	* Default dms user password
	*
	* @author KnowledgeTree Team
	* @access private
	* @var boolean
	*/
	private $dmsuserpassword = '';
	
	/**
	* Location of database binaries.
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $mysqlDir; // TODO:multiple databases
    
	/**
	* Name of database binary.
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $dbbinary = ''; // TODO:multiple databases
    
	/**
	* Database table prefix
	*
	* @author KnowledgeTree Team
	* @access private
	* @var string
	*/
    private $tprefix = '';
    
	/**
	* Flag to drop database
	*
	* @author KnowledgeTree Team
	* @access private
	* @var boolean
	*/
    private $ddrop = false;
    
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $error = array();
    
	/**
	* List of errors used in template
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $templateErrors = array('dmspassword', 'dmsuserpassword', 'con', 'dname', 'dtype', 'duname', 'dpassword');
    
	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $storeInSession = true;
    
	/**
	* Flag if step needs to be upgraded
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runUpgrade = true;
    
	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $silent = true;
    
	/**
	* Constructs database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
        $this->temp_variables = array("step_name"=>"database", "silent"=>$this->silent);
    	$this->_dbhandler = new UpgradedbUtil();
        $this->_util = new UpgradeUtil();
    	if(WINDOWS_OS)
			$this->mysqlDir = MYSQL_BIN;
    }

	/**
	* Main control of database setup
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep() {
        parent::doStep();
    	$this->initErrors();
    	$this->setDetails(); // Set any posted variables
    	if(!$this->inStep("database")) {
    	    $this->doRun();
    		return 'landing';
    	}
		if($this->next()) {
		    if ($this->doRun()) {
    			return 'next';
            }
		} else if($this->previous()) {
			return 'previous';
		}
        else if ($this->backup()) {
            return 'backup';
        }
        else if ($this->restore()) {
            return 'restore';
        }
        else if ($this->upgrading()) {
            $this->doRun('runUpgrade');
            return 'next';
        }
        else if ($this->confirm()) {
            if ($this->doRun('confirm')) {
                return 'next';
            }
            return 'error';
        }
        
        $this->doRun();
        return 'landing';
    }
    
    function backup() {
        return isset($_POST['Backup']);
    }
     
    function restore() {
        return isset($_POST['Restore']);
    } 
     
    function upgrading() {
        return isset($_POST['RunUpgrade']);
    } 
    
    function doRun($action = null) {
        $this->readConfig(KTConfig::getConfigFilename());
        
        if($this->dbSettings['dbPort'] == '')  {
            $con = $this->_dbhandler->load($this->dbSettings['dbHost'], $this->dbSettings['dbUser'],  
                                           $this->dbSettings['dbPass'], $this->dbSettings['dbName']);
        } else {
            $con = $this->_dbhandler->load($this->dbSettings['dbHost'].":".$this->dbSettings['dbPort'], $this->dbSettings['dbUser'],  
                                           $this->dbSettings['dbPass'], $this->dbSettings['dbName']);
        }
        
        if (is_null($action) || ($action == 'preview')) {
            $this->temp_variables['action'] = 'preview';
            $this->temp_variables['title'] = 'Preview Upgrade';
            $this->temp_variables['upgradeTable'] = $this->generateUpgradeTable();
        }
        else if ($action == 'runUpgrade') {
            $this->temp_variables['action'] = 'runUpgrade';
            $this->temp_variables['title'] = 'Confirm Upgrade';
            $this->temp_variables['upgradeTable'] = $this->upgradeConfirm();
        }
        else if ($action == 'confirm') {
            $this->temp_variables['action'] = 'confirm';
            $this->temp_variables['title'] = 'Upgrade In Progress';
            if (!$this->upgrade()) {
                $this->temp_variables['upgradeTable'] = $this->upgradeErrors();
                return false;
            }
        }
        
        return true;
    }
    
    public function generateUpgradeTable() {
        global $default;

        $this->temp_variables['systemVersion'] = $default->systemVersion;
        $query = sprintf('SELECT value FROM %s WHERE name = "databaseVersion"', $default->system_settings_table);

        $result = $this->_dbhandler->query($query);
        if ($result) {
            $lastVersionObj = $this->_dbhandler->fetchNextObject($result);
            $lastVersion = $lastVersionObj->value;
        }
        $currentVersion = $default->systemVersion;
    
        $upgrades = describeUpgrade($lastVersion, $currentVersion);
    
        $ret = "<table border=1 cellpadding=1 cellspacing=1 width='100%'>\n";
        $ret .= "<tr bgcolor='darkgrey'><th width='10'>Code</th><th width='100%'>Description</th><th width='30'>Applied</th></tr>\n";
        $i=0;
        foreach ($upgrades as $upgrade) {
            $color=((($i++)%2)==0)?'white':'lightgrey';
            $ret .= sprintf("<tr bgcolor='$color'><td>%s</td><td>%s</td><td>%s</td></tr>\n",
            htmlspecialchars($upgrade->getDescriptor()),
            htmlspecialchars($upgrade->getDescription()),
            $upgrade->isAlreadyApplied() ? "Yes" : "No"
            );
        }
        $ret .= '</table>';
        return $ret;
    }

	/**
	* Store options
	*
	* @author KnowledgeTree Team
	* @params object SimpleXmlObject
	* @access private
	* @return void
	*/
   private function setDetails() {
        // create lock file to indicate Upgrade mode
        $this->createUpgradeFile();
    }
    
    /**
     * Creates miUpgradeock file so that system knows it is supposed to run an upgrade installation
     * 
     * @author KnowledgeTree Team
     * @access private
     * @return void
     */
    private function createUpgradeFile() {
        @touch($this->wizardLocation . DIRECTORY_SEPARATOR . "upgrade.lock");
    }
    
	/**
	* Safer way to return post data
	*
	* @author KnowledgeTree Team
	* @params SimpleXmlObject $simplexml
	* @access public
	* @return void
	*/
    public function getPostSafe($key) {
    	return isset($_POST[$key]) ? $_POST[$key] : "";
    }
    
	/**
	* Stores varibles used by template
	*
	* @author KnowledgeTree Team
	* @params none
	* @access public
	* @return array
	*/
    public function getStepVars() {
        return $this->temp_variables;
    }

	/**
	* Returns database errors
	*
	* @author KnowledgeTree Team
	* @access public
	* @params none
	* @return array
	*/
    public function getErrors() {

        return $this->error;
    }

	/**
	* Initialize errors to false
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function initErrors() {
    	foreach ($this->templateErrors as $e) {
    		$this->error[$e] = false;
    	}
    }
    
     private function readConfig($path) {
        $ini = new UpgradeIni($path);
        $dbSettings = $ini->getSection('db');
        $this->dbSettings = array('dbHost'=> $dbSettings['dbHost'],
                                    'dbName'=> $dbSettings['dbName'],
                                    'dbUser'=> $dbSettings['dbUser'],
                                    'dbPass'=> $dbSettings['dbPass'],
                                    'dbPort'=> $dbSettings['dbPort'],
                                    'dbAdminUser'=> $dbSettings['dbAdminUser'],
                                    'dbAdminPass'=> $dbSettings['dbAdminPass'],
        );
//        $ktSettings = $ini->getSection('KnowledgeTree');
//        $froot = $ktSettings['fileSystemRoot'];
//        if ($froot == 'default') {
//            $froot = $this->location;
//        }
//        $this->ktSettings = array('fileSystemRoot'=> $froot,
//        );
//        $urlPaths = $ini->getSection('urls');
//        $this->urlPaths = array(array('name'=> 'Var Directory', 'path'=> $froot.DS.'var'),
//                                    array('name'=> 'Log Directory', 'path'=> $froot.DS.'log'),
//                                    array('name'=> 'Document Root', 'path'=> $froot.DS.'Documents'),
//                                    array('name'=> 'UI Directory', 'path'=> $froot.DS.'presentation'.DS.'lookAndFeel'.DS.'knowledgeTree'),
//                                    array('name'=> 'Temporary Directory', 'path'=> $froot.DS.'tmp'),
//                                    array('name'=> 'Cache Directory', 'path'=> $froot.DS.'cache'),
//        );
//        $this->temp_variables['urlPaths'] = $this->urlPaths;
//        $this->temp_variables['ktSettings'] = $this->ktSettings;
        $this->temp_variables['dbSettings'] = $this->dbSettings;
    }
    
    function upgradeConfirm()
    {
        if (!isset($_SESSION['backupStatus']) || $_SESSION['backupStatus'] === false)
        {
            $this->temp_variables['backupStatus'] = false;
        }
    }


function upgrade()
{
    global $default;
?>
        <p>The table below describes the upgrades that have occurred to
        upgrade your <?php echo APP_NAME;?> installation to <strong><?php echo $default->systemVersion;?></strong>.

  <?php
    $pre_res = performPreUpgradeActions();
    if (PEAR::isError($pre_res))
    {
?>
<font color="red">Pre-Upgrade actions failed.</font><br/>
<?php
    }
    else
    {
?>
<p>
<font color="green">Pre-Upgrade actions succeeded.</font><br/>
<?php
    }
?>
<p>
  <?php
    $res = performAllUpgrades();
    if (PEAR::isError($res) || PEAR::isError($pres))
    {
?>
<font color="red">Upgrade failed.</font>
<?php
    }
    else
    {
?>
<p>
<font color="green">Upgrade succeeded.</font>
<?php
    }
?>
<p>
  <?php
    $post_pres = performPostUpgradeActions();
    if (PEAR::isError($post_res))
    {
?>
<font color="red">Post-Upgrade actions failed.</font><br/><br/>
<?php
    }
    else
    {
?>
<p>
<font color="green">Post-Upgrade actions succeeded.</font><br/><br/>
<script type="text/javascript">
    alert("To complete the upgrade please do the following before continuing:\n\n1. Restart the services as appropriate for your environment.\n\n\nOn first run of your upgraded installaton please do the following:\n\n1. Hard refresh your bowser (CTRL-F5) on first view of the Dashboard.\n2. Enable the new plugins you wish to use.\n\n\nSelect 'next' at the bottom of this page to continue.")
</script>
<?php
    }
?>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:document.location='..';">
<?php
}

}
?>