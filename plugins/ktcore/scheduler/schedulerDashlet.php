<?php
/**
 * $Id:
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

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once('schedulerUtil.php');

class schedulerDashlet extends KTBaseDashlet {
	var $oUser;
    var $sClass = "ktError";
    var $aTimes = array();

    function schedulerDashlet() {
        $this->sTitle = _kt('Scheduler');
    }

	function is_active($oUser) {
	    // Check if the user has admin rights
		if(Permission::userIsSystemAdministrator($_SESSION['userID'])) {
    		// Check if the scheduler is overdue
    		return schedulerDashlet::checkOverDue();
		}
		return false;
	}

	/**
	* Get the last and next run times for the scheduler.
	* @return bool true if scheduler is overdue
	*/
	function checkOverDue() {
	    $this->aTimes = schedulerUtil::checkLastRunTime();
	    $sNextRunTime = $this->aTimes['nextruntime'];

        $iNow = time();
        $iNext = strtotime($sNextRunTime);

        if($iNow > $iNext){
            $iDif = $iNow - $iNext;

            // if it hasn't run for a whole day and its not a fresh install then display dashlet alert.
            if($iDif > 60*60*24) {
                // check if its a new install
                $check = schedulerUtil::checkNewInstall();
                return !$check;
            }
        }

        return false;
	}

	/**
	* Calculate the time difference in days/hours/minutes
	*/
	function renderTime($iDif, $iUnit, $iRemainder, $sUnit, $sRemainder) {
        // days
        $iTime = round($iDif / $iUnit, 2);
        $aRemainder = explode('.', $iTime);
        if(isset($aRemainder[1]) && !empty($aRemainder[1])){
            $rem = (int)$aRemainder[1];
            $rem = $rem * $iRemainder/100;
            $rem = round($rem, 0);
            $remainder =  ($rem > 0) ? ' '.$rem.' '.$sRemainder : '';
        }
        $time = floor($iTime).' '.$sUnit.$remainder;
        return $time;
	}

	/**
	* Get the last and next run times for the scheduler
	*/
	function getRunTimes() {
        $bDue = FALSE;

        // Check when the scheduler last ran and when the next task run time should be
        $aTimes = $this->aTimes;
        $sLastRunTime = $aTimes['lastruntime'];
        $sNextRunTime = $aTimes['nextruntime'];

        // Check if the previous time is empty - mysql DB defaults to 0000-00-00 for an empty date
        if($sLastRunTime == '0000-00-00 00:00:00'){
            $sLastRunTime = false;
        }

        // Check if scheduler has missed the last run
        $iNow = time();
        $iNext = strtotime($sNextRunTime);

        if($iNow > $iNext){
            $bDue = TRUE;
            $iDif = $iNow - $iNext;
        }else{
            $iDif = $iNext - $iNow;
        }

        $time = $iDif.' '._kt('seconds'); $remainder = '';
        // Get the difference in easy units of time
        if($iDif >= 60*60*24*7){
            // weeks
            $time = '  '.schedulerDashlet::renderTime($iDif, 60*60*24*7, 7, _kt('week(s)'), _kt('day(s)'));
        }else if($iDif >= 60*60*24){
            // days
            $time = '  '.schedulerDashlet::renderTime($iDif, 60*60*24, 24, _kt('day(s)'), _kt('hour(s)'));
        }else if($iDif >= 60*60){
            // hours
            $time = '  '.schedulerDashlet::renderTime($iDif, 60*60, 60, _kt('hour(s)'), _kt('minute(s)'));
        }else if($iDif >= 60){
            // minutes
            $time = '  '.schedulerDashlet::renderTime($iDif, 60, 60, _kt('minute(s)'), _kt('second(s)'));
        }

        return array('lasttime' => $sLastRunTime, 'timedif' => $time, 'due' => $bDue);
	}

    function render() {
        $bWin = false;
        if(OS_WINDOWS){
            $bWin = true;
        }
        $aTimes = schedulerDashlet::getRunTimes();
        $sLastTime = $aTimes['lasttime'];
        $sTimeDif = $aTimes['timedif'];
        $bDue = $aTimes['due'];

        $oKTConfig =& KTConfig::getSingleton();
        $rootUrl = $oKTConfig->get("rootUrl");

        if($oKTConfig->get("ui/morphEnabled") == '1') {
            $sImg = $rootUrl.'/skins/kts_'.$oKTConfig->get("ui/morphTo");
        }else{
            $sImg = $rootUrl.'/resources/graphics';
        }
        $sImgPlus = $sImg.'/bullet_toggle_plus.png';
        $sImgMinus = $sImg.'/bullet_toggle_minus.png';

        $sPath = KT_DIR.'/bin/scheduler.php';
        $sOnClick = " var cron = document.getElementById('cronguide');
            var icon = document.getElementById('scheduler_icon');
            if(cron.style.visibility == 'hidden'){
                cron.style.visibility = 'visible'; cron.style.display = 'block';
                icon.src = '{$sImgMinus}';
            }else{
                cron.style.visibility = 'hidden'; cron.style.display = 'none';
                icon.src = '{$sImgPlus}';
            }";

        $sAdmin = KTUtil::ktLink('admin.php', 'misc/scheduler');
        $sAdminLink = "<a href='{$sAdmin}'>"._kt('Administration page').'</a>';

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/scheduler');

        $aTemplateData = array(
            'lasttime' => $sLastTime,
            'timedif' => $sTimeDif,
            'isDue' => $bDue,
            'bWin' => $bWin,
            'sPath' => $sPath,
            'sAdminLink' => $sAdminLink,
            'sImg' => $sImgPlus,
            'onClick' => $sOnClick,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>
