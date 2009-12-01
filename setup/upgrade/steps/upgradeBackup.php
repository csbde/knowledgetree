<?php
/**
* Backup Step Controller. 
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

class upgradeBackup extends Step {
    
    protected $silent = false;
    protected $temp_variables = array();
    
    public function doStep() {
    	$this->temp_variables = array("step_name"=>"backup", "silent"=>$this->silent, 
                                      "loadingText"=>"Your backup is under way.  Please wait until it completes");
        if(!$this->inStep("backup")) {
            $this->doRun();
            return 'landing';
        }
        if($this->next()) {
            if ($this->doRun()) {
                return 'next';
            }
        }
        else if ($this->confirm()) {
            if ($this->doRun('confirm')) {
                return 'confirm';
            }
        }
        else if ($this->backupNow()) {
            if ($this->doRun('backupNow')) {
                return 'next';
            }
            else {
                return 'error';
            }
        }
        else if($this->previous()) {
            return 'previous';
        }
        else if ($this->upgrade()) {
            header('Location: index.php?step_name=database');
            exit;
        }
        
        $this->doRun();
        return 'landing';
    }
    
    private function backupNow() {
        return isset($_POST['BackupNow']);
    }
    
    private function doRun($action = null) {
        $this->readConfig();
        $this->temp_variables['action'] = $action;
        $this->temp_variables['backupStatus'] = false;
        
        if (is_null($action) || ($action == 'confirm')) {
            $this->temp_variables['title'] = 'Confirm Backup';
            $this->backupConfirm();
        }
        else {
            $this->temp_variables['title'] = 'Backup Created';
            $this->backup();
            // TODO error checking (done in backupDone at the moment)
            $this->backupDone();
        }
        
        return true;
    }
    
    private function backup() {
        $targetfile = $_SESSION['backupFile'];
        $stmt = $this->create_backup_stmt($targetfile);
        $dir = $stmt['dir'];
    
        if (is_file($dir . '/mysqladmin') || is_file($dir . '/mysqladmin.exe'))
        {
            $curdir=getcwd();
            chdir($dir);
            
            $handle = popen($stmt['cmd'], 'r');
            $read = fread($handle, 10240);
            pclose($handle);
            $_SESSION['backupOutput'] = $read;
            $dir = $this->util->resolveTempDir();
            $_SESSION['backupFile'] = $stmt['target'];
    
            if (!WINDOWS_OS) {
                chmod($stmt['target'],0600);
            }
    
            if (is_file($stmt['target']) && filesize($stmt['target']) > 0) {
                $_SESSION['backupStatus'] = true;
            }
            else {
                $_SESSION['backupStatus'] = false;
            }
        }
    }
    
    private function backupDone() {
        $status = $_SESSION['backupStatus'];
        $filename = $_SESSION['backupFile'];
        
        $this->temp_variables['backupStatus'] = $status;
    
        if ($status)
        {
            $stmt = $this->util->create_restore_stmt($filename, $this->dbSettings);
            $this->temp_variables['display'] = 'The backup file <nobr><i>"' . $filename . '"</i></nobr> has been created.
            <P> It appears as though the <font color=green>backup has been successful</font>.
            <P>';
                if ($stmt['dir'] != '')
                {
                    $this->temp_variables['dir'] = $stmt['dir'];
                    $this->temp_variables['display'] .= 'Manually, you would do the following to restore the backup:
                    <P>
                    <table bgcolor="lightgrey">
                    <tr>
                        <td>
                            <nobr>cd ' . $stmt['dir'] . '</nobr>
                            <br/>';
                }
                else
                {
                    $this->temp_variables['display'] .= 'The mysql backup utility could not be found automatically. Please edit the config.ini and update the backup/mysql Directory entry.
                    <P>
                    If you need to restore from this backup, you should be able to use the following statements:
                    <P>
                    <table bgcolor="lightgrey">
                    <tr>
                        <td>';
                }
            $this->temp_variables['display'] .= '<nobr>' . $stmt['display'] . '</nobr>
                    </table>';
        }
        else
        {
            $this->temp_variables['display'] .= 'It appears as though <font color=red>the backup process has failed</font>.<P></P> Unfortunately, it is difficult to diagnose these problems automatically
            and would recommend that you try to do the backup process manually.
            <P>
            We appologise for the inconvenience.
            <P>
            <table bgcolor="lightgrey">
            <tr>
            <td>' . $_SESSION['backupOutput'] . '</table>';
        }
    }

    private function create_backup_stmt($targetfile=null)
    {        
        $adminUser = $this->dbSettings['dbAdminUser'];
        $adminPwd = $this->dbSettings['dbAdminPass'];
//        $dbHost = $this->dbSettings['dbHost'];
        $dbName = $this->dbSettings['dbName'];
        
        $dbPort = trim($this->dbSettings['dbPort']);
        if (empty($dbPort) || $dbPort=='default') $dbPort = get_cfg_var('mysql.default_port');
        if (empty($dbPort)) $dbPort='3306';
        // dbSocket doesn't exist as far as I can find, where was it coming from?
        //$dbSocket = trim($this->dbSettings['dbSocket']);
        $dbSocket = '';
        if (empty($dbSocket) || $dbSocket=='default') $dbSocket = get_cfg_var('mysql.default_socket');
        if (empty($dbSocket)) $dbSocket='../tmp/mysql.sock';
    
        $date=date('Y-m-d-H-i-s');
    
        $dir = $this->util->resolveMysqlDir();
    
        $info['dir'] = $dir;
        $prefix = '';
        if (!WINDOWS_OS)
        {
            $prefix .= "";//$prefix .= "./";
        }
    
        if (@stat($dbSocket) !== false)
        {
            $mechanism = "--socket=\"$dbSocket\"";
        }
        else
        {
            $mechanism = "--port=\"$dbPort\"";
        }
    
        $tmpdir = $this->util->resolveTempDir();
    
        if (is_null($targetfile))
        {
            $targetfile = "$tmpdir/kt-backup-$date.sql";
        }
    
        $stmt = $prefix . "mysqldump --user=\"$adminUser\" -p $mechanism \"$dbName\" > \"$targetfile\"";
        $info['display'] = $stmt;
        $info['target'] = $targetfile;
    
        $stmt  = $prefix. "mysqldump --user=\"$adminUser\" --password=\"$adminPwd\" $mechanism \"$dbName\" > \"$targetfile\"";
        $info['cmd'] = $stmt;
        return $info;
    }

    private function backupConfirm()
    {
        $stmt = $this->create_backup_stmt();
        $_SESSION['backupFile'] = $stmt['target'];
    
        $dir = $stmt['dir'];
        $this->temp_variables['dir'] = $dir;
        $this->temp_variables['display'] = $stmt['display'];
    }

}
?>