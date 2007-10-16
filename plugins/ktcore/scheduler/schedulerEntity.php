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

require_once(KT_LIB_DIR . "/ktentity.inc");

/**
* Class to perform all database functions
*/
class schedulerEntity extends KTEntity
{
    var $_bUsePearError = true;
    
    var $sTask;
    var $sScript_url;
    var $sScript_params;
    var $bIs_complete;
    var $iFrequency;
    var $iRun_time;
    var $iPrevious_run_time;
    var $iRun_duration;
    
    var $_aFieldToSelect = array(
       'iId' => 'id',
       'sTask' => 'task',
       'sScript_url' => 'script_url',
       'sScript_params' => 'script_params',
       'bIs_complete' => 'is_complete',
       'iFrequency' => 'frequency',
       'iRun_time' => 'run_time',
       'iPrevious_run_time' => 'previous_run_time',
       'iRun_duration' => 'run_duration'
   );
   
   function _table () {
       return KTUtil::getTableName('scheduler_tasks');
   }
   
    function _cachedGroups() {
        return array('getList', 'getTaskList', 'getTasksToRun');
    }
    
    function getTask() { return $this->sTask; }
    function getUrl() { return $this->sScript_url; }
    function getParams() { return $this->sScript_params; }
    function getIsComplete() { return $this->bIs_complete; }
    function getFrequency() { return $this->iFrequency; }
    
    function getFrequencyByLang() { 
         $aFrequencies = array(
              'monthly' => _kt('monthly'), 
              'weekly' => _kt('weekly'), 
              'daily' => _kt('daily'), 
              'hourly' => _kt('hourly'),
              'half_hourly' => _kt('every half hour'),
              'quarter_hourly' => _kt('every quarter hour'),
              '10mins' => _kt('every 10 minutes'),
              '5mins' => _kt('every 5 minutes'),
         );
        return $aFrequencies[$this->iFrequency]; 
    }
    
    function getRunTime() { return $this->iRun_time; }
    
    function getPrevious($bFormat = FALSE) { 
        if($bFormat){
            return $this->iPrevious_run_time;
        }
        return strtotime($this->iPrevious_run_time);
    }
    
    function getRunDuration() { 
        $time = (!empty($this->iRun_duration)) ? $this->iRun_duration.'s' : '';
        return $time; 
    }
    
    function setTask($sValue) { return $this->sTask = $sValue; }
    function setUrl($sValue) { return $this->sScript_url = $sValue; }
    function setParams($sValue) { return $this->sScript_params = $sValue; }
    function setIsComplete($sValue) { return $this->bIs_complete = $sValue; }
    function setFrequency($sValue) { return $this->iFrequency = $sValue; }
    function setRunTime($sValue) { return $this->iRun_time = date('Y-m-d H:i', $sValue); }
    function setPrevious($sValue) { return $this->iPrevious_run_time = date('Y-m-d H:i', $sValue); }
    function setRunDuration($sValue) { return $this->iRun_duration = $sValue; }
    
    function get($iId) {
        return KTEntityUtil::get('schedulerEntity', $iId);
    }
    
    function getTasksToRun() {
        $aOptions = array('multi' => true);
        $aFields = array('is_complete', 'run_time');
        $aValues = array();
        $aValues[] = array('type' => 'equals', 'value' => '0');
        $aValues[] = array('type' => 'before', 'value' => time());
        
        return KTEntityUtil::getBy('schedulerEntity', $aFields, $aValues, $aOptions);
    }
    
    function getTaskList($completed = '0') {
        $aOptions = array('multi' => true);
        return KTEntityUtil::getBy('schedulerEntity', 'is_complete', $completed, $aOptions);
    }
    
    function getLastRunTime($date) {
        $aOptions = array('multi' => true, 'orderby' => 'previous_run_time DESC');
        $aFields = array('previous_run_time');
        $aValues = array();
        $aValues[] = array('type' => 'before', 'value' => $date);
        
        return KTEntityUtil::getBy('schedulerEntity', $aFields, $aValues, $aOptions);
    }
    
    function getNextRunTime($date) {
        $aOptions = array('multi' => true, 'orderby' => 'run_time ASC');
        $aFields = array('run_time');
        $aValues = array();
        $aValues[] = array('type' => 'after', 'value' => $date);
        
        return KTEntityUtil::getBy('schedulerEntity', $aFields, $aValues, $aOptions);
    }
    
    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('schedulerEntity', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('schedulerEntity', $sWhereClause, $aOptions);
    }

    // STATIC
    function &getByTaskName($sName) {
        return KTEntityUtil::getBy('schedulerEntity', 'task', $sName);
    }
    
    function clearAllCaches() {
        return KTEntityUtil::clearAllCaches('schedulerEntity');
    }
    
    /**
    * Get a link to alter the frequency of a task
    */
    function getAlterFreqLink() {
        $sId = $this->getId();
        $sLink = "<a href='#' onclick='javascript: showFrequencyDiv({$sId});'>"._kt('Alter frequency')."</a>";
        return $sLink;
    }
    
    /**
    * Run the task on the next iteration
    */
    function getRunNowLink() {
        $sId = $this->getId();
        $sUrl = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=updateRunTime');
        $sLink = "<a href='#' onclick='javascript: runOnNext(\"{$sId}\", \"{$sUrl}\");'>"._kt('Run on next iteration')."</a>";
        return $sLink;
    }
}
?>