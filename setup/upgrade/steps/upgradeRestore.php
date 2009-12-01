<?php
/**
* Restore Step Controller. 
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

class upgradeRestore extends Step {


    protected $silent = false;
    protected $temp_variables = array();    

    public function doStep() {        
    	$this->temp_variables = array("step_name"=>"restore", "silent"=>$this->silent, 
                                      "loadingText"=>"The database restore is under way.  Please wait until it completes");
        $this->temp_variables['restore'] = false;
        $this->temp_variables['display'] = '';
        $this->temp_variables['dir'] = '';
        
        if(!$this->inStep("restore")) {
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
        else if ($this->restoreNow()) {
            $this->temp_variables['restoreSuccessful'] = false;
            $this->doRun(true);
            return 'next';
        }
        
        $this->doRun();
        return 'landing';
    }
    
    private function restoreNow() {
        return isset($_POST['RunRestore']);
    } 
    
    private function doRun($restore = false) {
        $this->readConfig();
        
        if (!$restore) {
            $this->temp_variables['selected'] = false;
            if ($this->select()) {
                $this->restoreSelected();
                $this->temp_variables['selected'] = true;
                $this->temp_variables['availableBackups'] = true;
            }
            $this->restoreConfirm();
        } // end not running a restore, just setting up
        else {
            $this->restoreDatabase();
        }
            
        return true;
    }
    
    private function select() {
        return isset($_POST['RestoreSelect']);
    } 
    
    private function restoreDatabase()
    {
        $this->temp_variables['restore'] = true;
        //$status = $_SESSION['backupStatus'];
        $filename = $_SESSION['backupFile']; 
        $stmt = $this->util->create_restore_stmt($filename, $this->dbSettings);
        $dir = $stmt['dir'];
    
        if (is_file($dir . '/mysql') || is_file($dir . '/mysql.exe'))
        {
            $curdir=getcwd();
            chdir($dir);
    
            $ok=true;
            $stmts=explode("\n",$stmt['cmd']);
            foreach($stmts as $stmt)
            {
    
                $handle = popen($stmt, 'r');
                if ($handle=='false')
                {
                    $ok=false;
                    break;
                }
                $read = fread($handle, 10240);
                pclose($handle);
                $_SESSION['restoreOutput']=$read;
            }
    
            $_SESSION['restoreStatus'] = $ok;
            // should be some sort of error checking, really
            $this->restoreDone();
        }
    }

    private function restoreDone()
    {
        $status = $_SESSION['restoreStatus'];
        $filename = $_SESSION['backupFile'];
    
        if ($status)
        {
            $this->temp_variables['display'] = 'The restore of <nobr><i>"' . $filename . '"</i></nobr> has been completed.
            <P>
            It appears as though the <font color=green>restore has been successful</font>.
            <P>';
            
            $this->temp_variables['title'] = 'Restore Complete'; 
            $this->temp_variables['restoreSuccessful'] = true;
        }
        else
        {
            $this->temp_variables['display'] = 'It appears as though <font color=red>the restore process has failed</font>. <P>
            Unfortunately, it is difficult to diagnose these problems automatically
            and would recommend that you try to do the backup process manually.
            <P>
            We appologise for the inconvenience.
            <P>
            <table bgcolor="lightgrey">
            <tr>
            <td>' . $_SESSION['restoreOutput'] . '
            </table>';
            $this->temp_variables['title'] = 'Restore Failed';
            $this->temp_variables['restoreSuccessful'] = false;
        }
    }
    
    private function restoreSelect()
    {
        $this->temp_variables['availableBackups'] = false;
        $dir = $this->util->resolveTempDir();
    
        $files = array();
        $dh = opendir($dir);
        if ($dh)
        {
            while (($file = readdir($dh)) !== false)
            {
                if (!preg_match('/kt-backup.+\.sql/',$file)) {
                    continue;
                }
                $files[] = $file;
            }
            closedir($dh);
        }
        
        $this->temp_variables['title'] = 'Select Backup to Restore';
        $this->temp_variables['dir'] = $dir;
        if (count($files) != 0) {
            $this->temp_variables['availableBackups'] = true;
            $this->temp_variables['files'] = $files;
        }
    }
    
    private function restoreSelected()
    {
        $file=$_REQUEST['file'];
    
        $dir = $this->util->resolveTempDir();
        $_SESSION['backupFile'] = $dir . '/' . $file;
    }
    
    private function restoreConfirm()
    {
        if (!isset($_SESSION['backupFile']) || !is_file($_SESSION['backupFile']) || filesize($_SESSION['backupFile']) == 0)
        {
            $this->restoreSelect();
            return;
        }
    
//        $status = $_SESSION['backupStatus'];
        $filename = $_SESSION['backupFile'];
        $stmt = $this->util->create_restore_stmt($filename, $this->dbSettings);
        
        $this->temp_variables['title'] = 'Confirm Restore';
        $this->temp_variables['dir'] = $stmt['dir'];
        $this->temp_variables['display'] = $stmt['display'];
        $this->temp_variables['availableBackups'] = true;
        $this->temp_variables['selected'] = true;
    }
    
}
?>