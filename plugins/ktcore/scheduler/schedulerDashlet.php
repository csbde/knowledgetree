<?php
/**
 * $Id: BrowseableDashlet.php 6609 2007-05-30 14:40:10Z kevin_fourie $
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
             
            // if it hasn't run for a whole day then display dashlet alert.
            if($iDif > 60*60*24) {
                return true;
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
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/scheduler');

        $aTemplateData = array(
            'lasttime' => $sLastTime,
            'timedif' => $sTimeDif,
            'isDue' => $bDue,
            'bWin' => $bWin,
            'sPath' => $sPath,
            'sAdmin' => $sAdmin,
            'sImg' => $sImgPlus,
            'onClick' => $sOnClick,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>
