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
        $this->oPage->requireJSResource('resources/js/scheduler.js');

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/scheduler');

        // Link for clearing out old tasks
//        $lClear = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=clearTasks');
//        $sClear = "<a href='#' onclick='javascript: clearTasks(\"{$lClear}\");'>"._kt('Clean-up old tasks').'</a>';

        // Link for saving the updated frequencies
        $sUrl = KTUtil::ktLink('admin.php', 'misc/scheduler', 'action=saveFreq');

        // Get all tasks
        $aList = SchedulerEntity::getTaskList();
        $aHeadings = array('&nbsp;', _kt('Task'), _kt('Frequency'), _kt('Next run time'), _kt('Previous run time'), _kt('Time taken to complete'), '&nbsp;');

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
            '1min' => _kt('every minute'),
            '30secs' => _kt('every 30 seconds'),
        );

        $aTemplateData = array(
              'context' => $this,
              'aList' => $aList,
              'aHeadings' => $aHeadings,
              'aFrequencies' => $aFrequencies,
              'i' => 1,
              'sUrl' => $sUrl,
//              'sClear' => $sClear
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

    /**
     * Toggle the enable/disable on the task
     */
    function do_updateStatus() {
        $fId = schedulerUtil::arrayGet($_REQUEST, 'fId');
        schedulerUtil::toggleStatus($fId);
    }
}
?>
