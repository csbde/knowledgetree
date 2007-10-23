<?php
/**
 * $Id:$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

// Set the time limit to 0 to prevent the script timing out
set_time_limit(0);


/* ** Set up functions ** */

// Calculate the next run time based on the frequency of iteration and the given time
function calculateRunTime($sFreq, $iTime) {

    switch($sFreq){
        case 'monthly':
            $iDays = date('t');
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

// Update the task information in the database
function updateTask($sTable, $aFieldValues, $iId) {
    DBUtil::autoUpdate($sTable, $aFieldValues, $iId);
}

// Get the list of tasks due to be run from the database
function getTaskList($sTable) {
    $now = date('Y-m-d H:i:s'); //time();
    $query = "SELECT * FROM {$sTable}
        WHERE is_complete = 0 AND run_time < '{$now}'";

    $result = DBUtil::getResultArray($query);

    if (PEAR::isError($result)){
        exit();
    }
    return $result;
}


/* ** Scheduler script ** */

$sTable = 'scheduler_tasks';

// Get task list
$aList = getTaskList($sTable);

global $default;

// Loop through tasks and run
if(!empty($aList)){
    foreach($aList as $item){
        $aUpdate = array();
        $iEnd = 0; $iStart = 0; $iDuration = 0;
        $sFreq = ''; $sParameters = '';
        $retval = TRUE;

        // Set up start variables
        $sTask = $item['task'];
        $sTaskUrl = $item['script_url'];
        $iDuration = $item['run_duration'];
        $sFreq = $item['frequency'];
        $sParameters = $item['script_params'];

        // Check if script is windows or *nix compatible
        $extArr = explode('.', $sTaskUrl);
        $ext = array_pop($extArr);
        $script = implode('.', $extArr);
        if(OS_WINDOWS){
            switch($ext){
                case 'sh':
                    $sTaskUrl = $script.'.bat';
                    break;
                case 'bin':
                    $sTaskUrl = $script.'.exe';
                    break;
            }
        }else{
            switch($ext){
                case 'bat':
                    if(file_exists(KT_DIR . $script.'.sh')){
                        $sTaskUrl = $script.'.sh';
                        break;
                    }
                    // File doesn't exist - log error
                    $default->log->error("Scheduler: Task script can't be found at ".KT_DIR."{$script}.sh");
                    continue;
                    break;
                case 'exe':
                    if(file_exists(KT_DIR . $script)){
                        $sTaskUrl = $script;
                        break;
                    }
                    if(file_exists(KT_DIR . $script.'.bin')){
                        $sTaskUrl = $script.'.bin';
                        break;
                    }
                    // File doesn't exist - log error
                    $default->log->error("Scheduler: Task script can't be found at ".KT_DIR."{$script} or ".KT_DIR."{$script}.bin");
                    continue;
                    break;
            }
        }

        $iTime = time();
        $iStart = explode(' ', microtime());

        // Run the script
        $file = realpath(KT_DIR . '/' . $sTaskUrl);

        $cmd = "\"$file\" {$sParameters}";

        $start = KTUtil::getBenchmarkTime();
        if (OS_WINDOWS)
		{
			$cmd = str_replace( '/','\\',$cmd);
			$res = `"$cmd" 2>&1`;
		}
		else
		{
			 $res = shell_exec($cmd." 2>&1");
		}

		if (!empty($res))
		{
			$default->log->info("Scheduler - Task: $sTask");
			$default->log->info("Scheduler - Command: $cmd");
			$default->log->info("Scheduler - Output: $res");
			$default->log->info("Scheduler - Background tasks should not produce output. Please review why this is producing output.");
		}
		else
		{
			$time = number_format(KTUtil::getBenchmarkTime() - $start,2,'.',',');
			$default->log->debug("Scheduler - Task: {$sTask} completed in {$diff}s.");
		}


        // On completion - reset run time
        $iEnd = explode(' ', microtime());
        $iDuration = ($iEnd[1] + $iEnd[0]) - ($iStart[1] + $iStart[0]);
        $iDuration = round($iDuration, 3);

        if(($sFreq == 'once' || empty($sFreq)) && $retval !== FALSE){
            // Set is_complete to true
            $aUpdate['is_complete'] = '1';
        }else{
            $iNextTime = calculateRunTime($sFreq, $iTime);
            $aUpdate['run_time'] = date('Y-m-d H:i:s', $iNextTime);
        }
        $aUpdate['previous_run_time'] = date('Y-m-d H:i:s', $iTime);
        $aUpdate['run_duration'] = $iDuration;

        updateTask($sTable, $aUpdate, $item['id']);

        // clear parameters
        if(!empty($aParams)){
            foreach($aParams as $param){
                $aParam = explode('=', $param);
                $$aParam[0] = '';
            }
            $aParam = array();
            $aParams = array();
        }
    }
}
?>