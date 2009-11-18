<?php
/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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
 * Contributor( s): ______________________________________
 *
 */

require_once('schedulerUtil.php');

/**
* Class to add new tasks to the scheduler
*/
class Scheduler
{
    var $sName = 'Task';
    var $sPath = '/var/tasks/';
    var $aParams = '';
    var $sFreq = 'daily';
    var $iStartTime = '';
    var $sStatus = 'disabled';

    /**
    * Constructor function - set the name of the task
    */
    function Scheduler($sName) {
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
    * Time should be in datetime format.
    * By default the time is set to now.
    */
    function setFirstRunTime($iTime) {
        $this->iStartTime = !empty($iTime) ? $iTime : date('Y-m-d H:i:s');
    }

    /**
    * Set the task as enabled or disabled. If the task is already set as a system task, then ignore.
    */
    function setEnabled($bStatus = FALSE) {
        if($bStatus && $this->sStatus != 'system'){
            $this->sStatus = 'enabled';
        }
    }

    /**
    * Set the task as a system task, this cannot be enabled or disabled.
    */
    function setAsSystemTask($bSystem = FALSE) {
        if($bSystem){
            $this->sStatus = 'system';
        }
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
        schedulerUtil::registerTask($this->sName, $this->sPath, $this->aParams, $this->sFreq, $this->iStartTime, $this->sStatus);
    }
}
?>
