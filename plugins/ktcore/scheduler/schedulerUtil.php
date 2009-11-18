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
    function registerTask($sTask, $sUrl, $aParams, $sFreq, $iStartTime = NULL, $sStatus = 'disabled') {
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
        $aTask['is_complete'] = '0';
        $aTask['frequency'] = $sFreq;
        $aTask['run_time'] = $dNextTime;
        $aTask['previous_run_time'] = $dStartTime;
        $aTask['run_duration'] = '0';
        $aTask['status'] = $sStatus;

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
        $aTask['status'] = 'enabled';

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
            case '1min':
                $iDiff = 60;
                break;
            case '30secs':
                $iDiff = 30;
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
        $iNextTime = ($iNextTime < time()) ? time() : $iNextTime;

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
    * Toggle whether a task is enabled or disabled. If its a system task, then ignore.
    */
    function toggleStatus($id) {
        $oScheduler = schedulerEntity::get($id);

        if (PEAR::isError($oScheduler)){
            return _kt('Object can\'t be created');
        }

        $sStatus = $oScheduler->getStatus();

        if($sStatus == 'system'){
            // ignore
            return $sStatus;
        }
        if($sStatus == 'disabled'){
            // If the task is being enabled, set the next run time to the current date plus the frequency period
            $freq = $oScheduler->getFrequency();
            $runTime = schedulerUtil::calculateRunTime($freq, time());
            $oScheduler->setRunTime($runTime);
        }

        $sNewStatus = ($sStatus == 'enabled') ? 'disabled' : 'enabled';
        $oScheduler->setStatus($sNewStatus);
        $oScheduler->update();
        return $sNewStatus;
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
     * Check if this is a new installation
     *
     */
    function checkNewInstall() {
        // The date and time of installation is not stored anywhere so we work around it
        // On installation the run_time of all tasks is set to '2007-10-01', so we check if all the tasks have the same run_time date with time set to 00:00:00
        // We then set run_time to the current date, ensuring the time is not 00.

        $query = 'SELECT count(*) as cnt, run_time FROM scheduler_tasks s GROUP BY run_time';
        $res = DBUtil::getResultArray($query);

        if(PEAR::isError($res)){
            return false;
        }

        // if they aren't all the same return false - not a fresh install
        $iCnt = count($res);
        if($iCnt > 1){
            return false;
        }

        // Check if the time is 00
        $sRunTime = $res[0]['run_time'];

        $aRunTime = explode(' ', $sRunTime);

        if(!isset($aRunTime[1]) || empty($aRunTime[1])){
            // set install date
            schedulerUtil::setInstallDate();
            return true;
        }
        if($aRunTime[1] == '00:00:00'){
            // set install date
            schedulerUtil::setInstallDate();
            return true;
        }
        return false;
    }

    /**
     * Set the date first checked as the install date for all scheduler tasks
     *
     */
    function setInstallDate() {
        // get current date
        $date = date('Y-m-d H:i:s');

        $query = "UPDATE scheduler_tasks SET run_time = '$date'";

        DBUtil::runQuery($query);
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
