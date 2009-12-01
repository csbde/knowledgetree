<?php
/**
* Upgrade Step Controller.
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
* @package Upgrader
* @version Version 0.1
*/

define('KT_DIR', SYSTEM_DIR);
define('KT_LIB_DIR', SYSTEM_DIR.'lib');
require_once(WIZARD_LIB . 'upgrade.inc.php');

class upgradeDatabase extends Step
{
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
    private $dbBinary = ''; // TODO:multiple databases

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

    public $sysVersion = '';
    protected $silent = false;
    protected $temp_variables = array();
    public $paths = '';
	public $migrateCheck = false;
	
    /**
	* Main control of database setup
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep() {
        $this->temp_variables = array("step_name"=>"database", "silent"=>$this->silent,
                                      "loadingText"=>"The database upgrade is under way.  Please wait until it completes");
    	$this->initErrors();
    	$this->checkMigration();
    	if(!$this->inStep("database")) {
    	    $this->doRun();
    		return 'landing';
    	}
		if($this->next()) {
		    $this->doRun('preview');
			return 'next';
		} else if($this->previous()) {
			return 'previous';
		}
        else if ($this->confirmUpgrade()) {
            $this->doRun('confirm');
            return 'next';
        }
        else if ($this->upgrading()) {
            if ($this->doRun('runUpgrade')) {
                return 'next';
            }
            return 'error';
        }

        $this->doRun();
        return 'landing';
    }

    public function checkMigration() {
    	if($this->util->isMigration()) {
    		$this->migrateCheck = true;
    	}
    }
    
    private function confirmUpgrade() {
        return isset($_POST['ConfirmUpgrade']);
    }

    private function upgrading() {
        return isset($_POST['RunUpgrade']);
    }

    private function doRun($action = null) {
        $this->readConfig();

		$this->util->dbUtilities->load($this->dbSettings['dbHost'], $this->dbSettings['dbPort'], $this->dbSettings['dbUser'],$this->dbSettings['dbPass'], $this->dbSettings['dbName']);
        $this->temp_variables['action'] = $action;

        if (is_null($action) || ($action == 'preview')) {
            $this->temp_variables['title'] = 'Preview Upgrade';
            $this->temp_variables['upgradeTable'] = $this->generateUpgradeTable();
        }
        else if ($action == 'confirm') {
            $this->temp_variables['title'] = 'Confirm Upgrade';
            $this->temp_variables['upgradeTable'] = $this->upgradeConfirm();
        }
        else if ($action == 'runUpgrade') {
            $this->temp_variables['title'] = 'Upgrade In Progress';
            if (!$this->doDatabaseUpgrade()) {
                $this->temp_variables['backupSuccessful'] = false;
                return false;
            }
            $this->temp_variables['backupSuccessful'] = true;
        }

        return true;
    }

    private function generateUpgradeTable() {
		$this->sysVersion = $this->readVersion();
		$this->temp_variables['systemVersion'] = $this->sysVersion;
		$dconf = $this->util->iniUtilities->getSection('db');
		$this->util->dbUtilities->load($dconf['dbHost'], '', $dconf['dbUser'], $dconf['dbPass'], $dconf['dbName']);

		$query = sprintf('SELECT value FROM %s WHERE name = "databaseVersion"', 'system_settings');
        $result = $this->util->dbUtilities->query($query);
        $assArr = $this->util->dbUtilities->fetchAssoc($result);
        if ($result) {
            $lastVersion = $assArr[0]['value'];
        }
        $currentVersion = $this->sysVersion;

        $upgrades = describeUpgrade($lastVersion, $currentVersion);
        $ret = "<table border=1 cellpadding=1 cellspacing=1 width='100%'>\n";
        $ret .= "<tr bgcolor='darkgrey'><th width='10'>Code</th><th width='100%'>Description</th><th width='30'>Applied</th></tr>\n";
        $i=0;
        foreach ($upgrades as $upgrade) {
            $color = ((($i++)%2)==0) ? 'white' : 'lightgrey';
            $ret .= sprintf("<tr bgcolor='$color'><td>%s</td><td>%s</td><td>%s</td></tr>\n",
            htmlspecialchars($upgrade->getDescriptor()),
            htmlspecialchars($upgrade->getDescription()),
                $upgrade->isAlreadyApplied() ? "Yes" : "No"
            );
        }
        $ret .= '</table>';
        return $ret;
    }

    public function readVersion() {
    	$verFile = SYSTEM_DIR."docs".DS."VERSION.txt";
    	if(file_exists($verFile)) {
			$foundVersion = file_get_contents($verFile);
			return $foundVersion;
    	} else {
			$this->error[] = "KnowledgeTree installation version not found";
    	}

		return false;
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

    public function storeSilent() {
    	$this->temp_variables['paths'] = $this->paths;
    	$this->temp_variables['sysVersion'] = $this->sysVersion;
    	$this->temp_variables['sysVersion'] = $this->sysVersion;
    	$this->temp_variables['dbSettings'] = $this->dbSettings;
    	$this->temp_variables['migrateCheck'] = $this->migrateCheck;
    }

    private function upgradeConfirm()
    {
        if (!isset($_SESSION['backupStatus']) || $_SESSION['backupStatus'] === false) {
            $this->temp_variables['backupStatus'] = false;
        }
        else {
            $this->temp_variables['backupStatus'] = true;
        }
    }

    private function doDatabaseUpgrade()
    {
        $errors = false;

        $this->temp_variables['detail'] = '<p>The table below describes the upgrades that have occurred to
            upgrade your KnowledgeTree installation to <strong>' . $this->sysVersion . '</strong>';

        $this->performPreUpgradeActions();

        $res = $this->performAllUpgrades();
        if (!$res) {
            $errors = true;
            $this->error[] = 'An Error has occured';
            // TODO instantiate error details hideable section?
            $this->temp_variables['upgradeStatus'] = '<font color="red">Database upgrade failed</font>
                                                      <br/><br/>
                                                      Please restore from your backup and ensure that the database does not contain
                                                      any unsupported modifications and try the upgrade process again.
                                                      <br/><br/>
                                                      If the problem persists, contact KnowledgeTree Support.';
        }
        else {
            $this->temp_variables['upgradeStatus'] = '<font color="green">Upgrade succeeded.</font>';
        }

        $this->performPostUpgradeActions();


        return !$errors;
    }

    private function performPreUpgradeActions() {

        // This is just to test and needs to be updated to a more sane and error resistent architrcture if it works.
        // It should idealy work the same as the upgrades.
        // Lock the scheduler
        $lockFile = $this->cachePath . DIRECTORY_SEPARATOR . 'scheduler.lock';
        touch($lockFile);
        return true;

    }

    private function deleteDirectory($sPath) {
        if (empty($sPath) || !is_dir($sPath)) return;

        if (!WINDOWS_OS) {
            if (file_exists('/bin/rm')) {
                $this->util->pexec(array('/bin/rm', '-rf', $sPath));
                return;
            }
        }
        if (WINDOWS_OS) {
            // Potentially kills off all the files in the path, speeding
            // things up a bit
            exec("del /q /s " . escapeshellarg($sPath));
        }
        $hPath = opendir($sPath);
        while (($sFilename = readdir($hPath)) !== false) {
            if (in_array($sFilename, array('.', '..'))) {
                continue;
            }
            $sFullFilename = sprintf("%s/%s", $sPath, $sFilename);
            if (is_dir($sFullFilename)) {
                $this->deleteDirectory($sFullFilename);
                continue;
            }
            chmod($sFullFilename, 0666);
            unlink($sFullFilename);
        }
        closedir($hPath);
        rmdir($sPath);
    }

    private function performPostUpgradeActions() {

        // This is just to test and needs to be updated to a more sane and error resistent architrcture if it works.
        // It should idealy work the same as the upgrades.

        // Ensure all plugins are re-registered.
        $sql = "TRUNCATE plugin_helper";
    	$this->util->dbUtilities->query($sql);

        // Clear out all caches and proxies - they need to be regenerated with the new code
        $this->deleteDirectory($this->proxyPath);
    	$this->deleteDirectory($this->cachePath);

    	// Recreate the cache directory - it doesn't get regenerated
    	mkdir($this->cachePath, 0755);

        // Unlock the scheduler
        $lockFile = $this->cachePath . DIRECTORY_SEPARATOR . 'scheduler.lock';
        if(file_exists($lockFile)){
            unlink($lockFile);
        }

        return true;

    }

    private function performAllUpgrades () {
        $row = 1;
        $table = 'system_settings';
        $query = "SELECT value FROM $table WHERE name = 'databaseVersion'";
		$result = $this->util->dbUtilities->query($query);
        $assArr = $this->util->dbUtilities->fetchAssoc($result);
        $lastVersion = $assArr[0]['value'];
        $currentVersion = $this->sysVersion;
        $upgrades = describeUpgrade($lastVersion, $currentVersion);
        $this->temp_variables['upgradeTable'] = '';
        foreach ($upgrades as $upgrade) {
            if (($row % 2) == 1) {
                $class = "odd";
            } else {
                $class = "even";
            }
            $this->temp_variables['upgradeTable'] .= sprintf('<div class="row %s"><div class="foo">%s</div>' . "\n", $class,
                                                             htmlspecialchars($upgrade->getDescription()));
            ++$row;
            $res = $upgrade->performUpgrade();
            $errors = $upgrade->getErrors();
            $this->temp_variables['upgradeTable'] .= sprintf('<div class="bar">%s</div>', $this->showResult($res, $errors));
            $this->temp_variables['upgradeTable'] .= '<br>' . "\n";
            $this->temp_variables['upgradeTable'] .= "</div>\n";
            if ($res === false) {
                $this->error = $this->util->dbUtilities->getErrors();
                break;
            }
        }

        return $res;
    }

    private function showResult($res, $errors = null) {
        if ($res && is_a($res, 'Upgrade_Already_Applied')) {
            return '<span style="color: orange">Already applied</span>';
        }
        if ($res === true) {
            return '<span style="color: green">Success</span>';
        }
        if ($res === false) {
            $str = '<span style="color: red">Failure</span>';
            if(is_array($errors)){
                $str .= '<ul style="padding-left: 20px; color: red;">';
                foreach ($errors as $error){
                    $str .= '<li style="padding: 5px 2px 0;">'.$error."</li>\n";
                }
                $str .= '</ul>';
            }
            return $str;
        }
        return $res;
    }
}
?>