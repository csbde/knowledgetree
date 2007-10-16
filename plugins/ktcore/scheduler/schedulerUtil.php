<?php
/**
 * $Id:$ 
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

require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once('background.php');
require_once('schedulerEntity.php');

class schedulerUtil extends KTUtil
{
    
    /**
    * Create a task
    * Parameters must be passed as an associative array => array('param1' => 'value1')
    */
    function createTask($sTask, $sScript, $aParams, $sFreq, $iStartTime = NULL){
        // Path to scripts
        $ktPath = '/var/tasks/';
        $path = KT_DIR.$ktPath;
        
        if(!is_dir($path)){
            mkdir($path, '0755');
        }
        
        // Create script file
        $sName = str_replace(' ', '_', $sTask);
        $sName = str_replace('', "'", $sName);
        $sName = str_replace('', "&", $sName);
        $sFileName = $sName.'_'.mt_rand(1, 999).'.php';
        
        while(file_exists($path.$sFileName)){
            $sFileName = $sTask.'_'.mt_rand(1, 9999).'.php';
        }
        
        $fp = fopen($path.$sFileName, 'w');
        fwrite($fp, $sScript);
        fclose($fp);
        
        // Register task in the schedule
        schedulerUtil::registerTask($sTask, $ktPath.$sFileName, $sParams, $sFreq, $iStartTime);
    }

    
    /**
    * Method to register a task in the schedule
    */
    function registerTask($sTask, $sUrl, $aParams, $sFreq, $iStartTime = NULL) {
        // Run task on next iteration if no start time given
        $iStartTime = (!empty($iStartTime)) ? strtotime($iStartTime) : time();
        
        // Calculate the next run time - get frequency
        $iNextTime = schedulerUtil::calculateRunTime($sFreq, $iStartTime);
        
        // Convert parameter array to a string => param=value|param2=value2|param3=value3
        $sParams = schedulerUtil::convertParams($aParams);
        
        // Convert run times to date time format for DB storage
        $dNextTime = date('Y-m-d H:i:s', $iNextTime);
        $dStartTime = date('Y-m-d H:i:s', $iStartTime);
        
        // Insert task into DB / task list
        $aTask = array();
        $aTask['task'] = $sTask;
        $aTask['script_url'] = $sUrl;
        $aTask['script_params'] = $sParams;
        $aTask['is_background'] = '0';
        $aTask['is_complete'] = '0';
        $aTask['frequency'] = $sFreq;
        $aTask['run_time'] = $dNextTime;
        $aTask['previous_run_time'] = $dStartTime;
        $aTask['run_duration'] = '0';
        
        $oEntity = schedulerEntity::createFromArray($aTask);
        if (PEAR::isError($oEntity)){
            return _kt('Scheduler object can\'t be created');
        }
        
        return $iNextTime;
    }
    
    /**
    * Method to register a background task to be run immediately
    */
    function registerBackgroundTask($sTask, $sUrl, $aParams) {
        
        // Convert parameter array to a string => param=value|param2=value2|param3=value3
        $sParams = schedulerUtil::convertParams($aParams);
        
        // Insert task into DB / task list
        $aTask = array();
        $aTask['task'] = $sTask;
        $aTask['script_url'] = $sUrl;
        $aTask['script_params'] = $sParams;
        $aTask['frequency'] = 'once';
        $aTask['is_complete'] = '0';
        $aTask['run_time'] = date('Y-m-d H:i:s');
        $aTask['run_duration'] = '0';
        
        $oEntity = schedulerEntity::createFromArray($aTask);
        if (PEAR::isError($oEntity)){
            return _kt('Scheduler object can\'t be created');
        }        
        return 'TRUE';
    }
    
    /**
    * Convert parameter array to a string
    */
    function convertParams($aParams) {
        if(is_array($aParams)){
            $sParams = '';
            foreach($aParams as $key => $value){
                //$sParams .= !empty($sParams) ? '|' : '';
                //$sParams .= $key.'='.$value;
                $sParams .= "{$key} {$value} ";
            }
        }else{
            $sParams = $aParams;
        }
        
        return $sParams;
    }
    
    /**
    * Calculate the next run time based on the frequency of iteration and the given time
    */
    function calculateRunTime($sFreq, $iTime) {
        
        switch($sFreq){
            case 'monthly':
                $iDays = date('t', $iTime);
                $iDiff = (60*60)*24*$iDays;
                break;
            case 'weekly':
                $iDiff = (60*60)*24*7;
                break;
            case 'daily':
                $iDiff = (60*60)*24;
                break;
            case 'hourly':
                $iDiff = (60*60);
                break;
            case 'half_hourly':
                $iDiff = (60*30);
                break;
            case 'quarter_hourly':
                $iDiff = (60*15);
                break;
            case '10mins':
                $iDiff = (60*10);
                break;
            case '5mins':
                $iDiff = (60*5);
                break;
            case 'once':
                $iDiff = 0;
                break;
        }
        $iNextTime = $iTime + $iDiff;
        return $iNextTime;
    }   
    
    /**
    * Update the frequency of a task
    */
    function updateTask($id, $sFreq) {
        $oScheduler = schedulerEntity::get($id);
        
        if (PEAR::isError($oScheduler)){
            return _kt('Object can\'t be created');
        }
        
        // Recalculate the next run time, use the previous run time as the start time. 
        $iPrevious = $oScheduler->getPrevious();
        $iNextTime = schedulerUtil::calculateRunTime($sFreq, $iPrevious);
        
        $oScheduler->setFrequency($sFreq);
        $oScheduler->setRunTime($iNextTime);
        $oScheduler->update();
    }
    
    /**
    * Update the run time of a task
    */
    function updateRunTime($id, $iNextTime) {
        $oScheduler = schedulerEntity::get($id);
        
        if (PEAR::isError($oScheduler)){
            return _kt('Object can\'t be created');
        }
        
        $oScheduler->setRunTime($iNextTime);
        $oScheduler->update();
    }
    
    /**
    * Check the last run time of the scheduler
    */
    function checkLastRunTime() {
        $now = date('Y-m-d H:i:s');
        $sLastRunTime = ''; $sNextRunTime = '';
        
        // Get the last time the scheduler was run
        $aList = schedulerEntity::getLastRunTime($now);
        
        if(PEAR::isError($aList)){
            return _kt('Tasks can\'t be retrieved');
        }
        
        if(!empty($aList)){
            $sLastRunTime = $aList[0]->getPrevious(TRUE);
        }
        
        // Get the next date when it should be / have been executed
        $aList2 = schedulerEntity::getNextRunTime('');
        
        if(PEAR::isError($aList2)){
            return _kt('Tasks can\'t be retrieved');
        }
        
        if(!empty($aList2)){
            $sNextRunTime = $aList2[0]->getRunTime();
        }
        
        return array('lastruntime' => $sLastRunTime, 'nextruntime' => $sNextRunTime);
    }
    
    /**
    * Delete task by name
    */
    function deleteByName($sName) {
        // Get task by name
        $oTask = schedulerEntity::getByTaskName($sName);
        
        if(PEAR::isError($oTask)){
            return $oTask;
        }
        
        // Delete
        return $oTask->delete();
    }
    
    /**
    * Get all completed tasks and delete
    */
    function cleanUpTasks() {
        // Get list of completed from database
        $aList = schedulerEntity::getTaskList('1');
        
        if (PEAR::isError($aList)){
            return _kt('List of tasks can\'t be retrieved.');
        }
        
        if(!empty($aList)){
            // start the background process
            $bg = new background();
            $bg->checkConnection();
            $bg->keepConnectionAlive();
            
            foreach($aList as $oScheduler){
                $oScheduler->delete();
            }
        }
        schedulerEntity::clearAllCaches();
    }
}
?>
