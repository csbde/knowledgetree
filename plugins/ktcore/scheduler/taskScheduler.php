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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once('schedulerEntity.php');
require_once('schedulerUtil.php');

class manageSchedulerDispatcher extends KTAdminDispatcher
{
    /**
    * Dispatch function
    */
    function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Task Scheduler Management'));
        $this->oPage->setTitle(_kt('Task Scheduler Management'));
        $this->oPage->requireJSResource('thirdpartyjs/yui/event/event.js');
        $this->oPage->requireJSResource('thirdpartyjs/yui/connection/connection.js');
        $this->oPage->requireJSResource('thirdpartyjs/yui/dom/dom.js');
        $this->oPage->requireJSResource('resources/js/scheduler.js');
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/scheduler');          
                
        // Link for clearing out old tasks
        $lClear = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=clearTasks');
        $sClear = "<a href='#' onclick='javascript: clearTasks(\"{$lClear}\");'>"._kt('Clean-up old tasks').'</a>';
        
        // Link for saving the updated frequencies
        $sUrl = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=saveFreq');
        
        // Get all tasks
        $aList = SchedulerEntity::getTaskList();
        $aHeadings = array('', _kt('Task'), _kt('Frequency'), _kt('Next run time'), _kt('Previous run time'), _kt('Time taken to complete'), '');
        
        //$aFrequencies = array('monthly', 'weekly', 'daily', 'hourly', 'half_hourly', 'quarter_hourly', '10mins', '5mins');
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
        
        $aTemplateData = array( 
              'context' => $this, 
              'aList' => $aList,
              'aHeadings' => $aHeadings,
              'aFrequencies' => $aFrequencies,
              'i' => 1,
              'sUrl' => $sUrl,
              'sClear' => $sClear
        );
        return $oTemplate->render($aTemplateData);
    }
    
    /**
    * Remove all completed tasks
    */
    function do_clearTasks() {
        schedulerUtil::cleanUpTasks();
        return 'DONE';
    }
    
    /**
    * Save the changed frequency
    */
    function do_saveFreq() {
        $id = schedulerUtil::arrayGet($_REQUEST, 'fId');
        $sFreq = schedulerUtil::arrayGet($_REQUEST, 'frequency');
        schedulerUtil::updateTask($id, $sFreq);
        return $sFreq;
    }
    
    /**
    * Update the runtime to run on the next iteration
    */
    function do_updateRunTime() {
        $id = schedulerUtil::arrayGet($_REQUEST, 'fId');
        $iNextTime = time();
        schedulerUtil::updateRunTime($id, $iNextTime);
        return $iNextTime;
    }
}
?>