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
    var $sStatus;

    var $_aFieldToSelect = array(
       'iId' => 'id',
       'sTask' => 'task',
       'sScript_url' => 'script_url',
       'sScript_params' => 'script_params',
       'bIs_complete' => 'is_complete',
       'iFrequency' => 'frequency',
       'iRun_time' => 'run_time',
       'iPrevious_run_time' => 'previous_run_time',
       'iRun_duration' => 'run_duration',
       'sStatus' => 'status'
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
    function getStatus() { return $this->sStatus; }

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
              '1min' => _kt('every minute'),
              '30secs' => _kt('every 30 seconds'),
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
    function setRunTime($sValue) { return $this->iRun_time = date('Y-m-d H:i:s', $sValue); }
    function setPrevious($sValue) { return $this->iPrevious_run_time = date('Y-m-d H:i:s', $sValue); }
    function setRunDuration($sValue) { return $this->iRun_duration = $sValue; }
    function setStatus($sValue) { return $this->sStatus = $sValue; }

    function get($iId) {
        return KTEntityUtil::get('schedulerEntity', $iId);
    }

    function getTasksToRun() {
        $aOptions = array('multi' => true);
        $aFields = array('is_complete', 'run_time', 'status');
        $aValues = array();
        $aValues[] = array('type' => 'equals', 'value' => '0');
        $aValues[] = array('type' => 'before', 'value' => time());
        $aValues[] = array('type' => 'nequals', 'value' => 'disabled');

        return KTEntityUtil::getBy('schedulerEntity', $aFields, $aValues, $aOptions);
    }

    function getTaskList($completed = '0') {
        $aOptions = array('multi' => true);
        return KTEntityUtil::getBy('schedulerEntity', 'is_complete', $completed, $aOptions);
    }

    function getLastRunTime($date) {
        $aOptions = array('multi' => true, 'orderby' => 'previous_run_time DESC');
        $aFields = array('previous_run_time', 'status');
        $aValues = array();
        $aValues[] = array('type' => 'before', 'value' => $date);
        $aValues[] = array('type' => 'nequals', 'value' => 'disabled');

        return KTEntityUtil::getBy('schedulerEntity', $aFields, $aValues, $aOptions);
    }

    function getNextRunTime($date) {
        $aOptions = array('multi' => true, 'orderby' => 'run_time ASC');
        $aFields = array('run_time', 'status');
        $aValues = array();
        $aValues[] = array('type' => 'after', 'value' => $date);
        $aValues[] = array('type' => 'nequals', 'value' => 'disabled');

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
     * Display the task name. If the task is disabled then grey it out.
     *
     */
    function getTaskDiv() {
        $sId = $this->getId();
        $sStatus = $this->getStatus();

        $sDiv = "<span id='font{$sId}' ";
        $sDiv .= ($sStatus != 'disabled') ? 'class="">' : 'class="descriptiveText">';
        $sDiv .= $this->getTask().'</span>';
        return $sDiv;
    }

    function getFreqDiv() {
        $sId = $this->getId();
        $sStatus = $this->getStatus();
        $sFreqs = $this->getFrequencyByLang();

        $sLink = "<div id='div{$sId}'";
        $sLink .= ($sStatus == 'disabled') ? 'style="visibility: hidden;" >' : '>';
        $sLink .= $sFreqs.'</div>';
        return $sLink;
    }

    /**
    * Get a link to alter the frequency of a task
    */
    function getAlterFreqLink() {
        $sId = $this->getId();
        $sStatus = $this->getStatus();

        $sLink = "<input type='button' id='freqLink{$this->getId()}' onclick='javascript: showFrequencyDiv(\"{$sId}\");'";
        $sLink .= " value='"._kt('Change Frequency')."' ";
        $sLink .= ($sStatus == 'disabled') ? 'style="visibility: hidden;" />' : ' />';

        return $sLink;
    }

    /**
    * Run the task on the next iteration
    */
    function getRunNowLink() {
        $sId = $this->getId();
        $sStatus = $this->getStatus();
        $sUrl = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=updateRunTime');

        $sTitle = _kt('This task will run the next time the Scheduler runs'); //, and then revert to the frequency you set on this page');
        $sLink = "<input type='button' id='runnowLink{$this->getId()}' onclick='javascript: runOnNext(\"{$sId}\", \"{$sUrl}\");'";
        $sLink .= " title='$sTitle' value='"._kt('Run on Next Iteration')."' ";
        $sLink .= ($sStatus == 'disabled') ? 'style="visibility: hidden;" />' : ' />';

        return $sLink;
    }

    /**
    * Run the task on the next iteration
    */
    function getStatusLink() {
        $sId = $this->getId();
        $sStatus = $this->getStatus();
        if($sStatus == 'system'){
            return '';
        }

        $sDisableText = _kt('Disable Task');
        $sEnableText = _kt('Enable Task');

        $sLinkText = ($sStatus == 'enabled') ? $sDisableText : $sEnableText;
        $sUrl = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=updateStatus');
        $sLink = "<input type='button' id='statusLink{$this->getId()}'
            onclick='javascript: toggleStatus(\"{$sId}\", \"{$sUrl}\", \"{$sDisableText}\", \"{$sEnableText}\");' value='{$sLinkText}' />";
        return $sLink;
    }
}
?>
