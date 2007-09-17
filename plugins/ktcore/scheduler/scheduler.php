<?php
/**
 * $Id: 
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once('schedulerUtil.php');

/**
* Class to add new tasks to the scheduler
*/
class scheduler
{
    var $sName = 'Task';
    var $sPath = '/var/tasks/';
    var $aParams = '';
    var $sFreq = 'daily';
    var $iStartTime = '';
    
    /**
    * Constructor function - set the name of the task
    */
    function scheduler($sName) {
        $this->sName = $sName;
        $this->sFreq = 'daily';
        $this->iStartTime = time();
    }
    
    /**
    * Set the name of the task
    */
    function setTaskName($sName) {
        $this->sName = $sName;
    }
    
    /**
    * Set the path to the script from the KT base path
    * For example: "/var/tasks/script.php" or "/bin/script.php"
    */
    function setScriptPath($sPath) {
        $this->sPath = $sPath;
    }
    
    /**
    * Add a parameter pair to pass to the script
    */
    function addParameter($param, $value){
        $this->aParams[$param] = $value;
    }
    
    /**
    * Set the frequency with which the task must be run
    * Frequencies are: daily, weekly, monthly, hourly, half_hourly, quarter_hourly, 10mins, 5mins and once
    */
    function setFrequency($sFrequency) {
        $this->sFreq = $sFrequency;
    }
    
    /**
    * Set the time at which the task should first be run or if it is a once off, the time to run it. 
    * Time should be in unix timestamp format.
    * By default the time is set to now. 
    */
    function setFirstRunTime($iTime) {
        $this->iStartTime = !empty($iTime) ? $iTime : time();
    }
    
    /**
    * Create a script - write it to the filesystem.
    * Scripts are saved in the KT_DIR."/var/tasks/" directory.
    * The file name is the task name followed by a random number.
    */
    function saveScript($sScript = '') {
        // Path to scripts
        $ktPath = '/var/tasks/';
        $path = KT_DIR.$ktPath;
        
        if(!is_dir($path)){
            mkdir($path, '0755');
        }
        
        // Create script file
        $sName = str_replace(' ', '_', $this->sName);
        $sName = str_replace('', "'", $sName);
        $sName = str_replace('', "&", $sName);
        $sFileName = $sName.'_'.mt_rand(1, 999).'.php';
        
        while(file_exists($path.$sFileName)){
            $sFileName = $sTask.'_'.mt_rand(1, 9999).'.php';
        }
        
        $fp = fopen($path.$sFileName, 'wb');
        fwrite($fp, $sScript);
        fclose($fp);
        
        $this->sPath = $ktPath.$sFileName;
    }
    
    /**
    * Register the task in the scheduler
    */
    function registerTask(){
        schedulerUtil::registerTask($this->sName, $this->sPath, $this->aParams, '', $this->sFreq, $this->iStartTime);
    }
}
?>