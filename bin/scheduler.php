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

// Loop through tasks and run
if(!empty($aList)){
    foreach($aList as $item){
        $aUpdate = array();
        $iEnd = 0; $iStart = 0; $iDuration = 0;
        $sFreq = ''; $sParameters = '';
        
        // Set up start variables
        $sTask = $item['task'];
        $sTaskUrl = $item['script_url'];
        $iDuration = $item['run_duration'];
        $sFreq = $item['frequency'];
        $sParameters = $item['script_params'];
        
        $iTime = time();
        $iStart = explode(' ', microtime());
        
        // Set up parameters for use by the script
        $aParams = explode('|', $sParameters);
        
        foreach($aParams as $param){
            $aParam = explode('=', $param);
            if(!empty($aParam)){
                $$aParam[0] = $aParam[1];
            }
        }
        
        // Run the script
        include(KT_DIR . $sTaskUrl);
        
        // On completion - reset run time
        $iEnd = explode(' ', microtime());
        $iDuration = ($iEnd[1] + $iEnd[0]) - ($iStart[1] + $iStart[0]);
        $iDuration = round($iDuration, 3);
        
        if($sFreq == 'once' || empty($sFreq)){
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