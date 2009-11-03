<?php

/**
 * $Id$
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

$myservicename = 'ktscheduler';

// Connect to service dispatcher and notify that startup was successful
if (!win32_start_service_ctrl_dispatcher($myservicename)) die('Could not connect to service :'.$myservicename);
win32_set_service_status(WIN32_SERVICE_RUNNING);

// Scheduler is dependent on the mysql server being up,
// so we sleep for a minute to ensure the server is running before we start the scheduler
sleep(120);

chdir(dirname(__FILE__)); // need to be here to include dmsDefaults
require_once('../../config/dmsDefaults.php');

global $default;

$config = KTConfig::getSingleton();
$schedulerInterval = $config->get('KnowledgeTree/schedulerInterval',30); // interval in seconds

// Change to knowledgeTree/bin folder
$dir = realpath(dirname(__FILE__) . '/..');
chdir($dir);

// Setup php binary path
$phpPath = $config->get('externalBinary/php','php');
if (!is_file($phpPath))
{
	$default->log->error("Scheduler: php not found: $phpPath");
	exit;
}


$loop = true;
$bTableExists = false;

while(!$bTableExists){
	switch (win32_get_last_control_message())
    {

        case WIN32_SERVICE_CONTROL_CONTINUE:
        	break; // Continue server routine
        case WIN32_SERVICE_CONTROL_INTERROGATE:
        	win32_set_service_status(WIN32_SERVICE_RUNNING);
        	break; // Respond with status
        case WIN32_SERVICE_CONTROL_STOP:
            win32_set_service_status(WIN32_SERVICE_STOPPED);
        	$loop = false; // Terminate script
        	$bTableExists = true;
        	continue;
        default:
    }

	$default->log->info("Scheduler Service: Checking if the scheduler_tasks table exists.");

	$checkQuery = 'show tables';
	$tableList = DBUtil::getResultArray($checkQuery);

	if(!empty($tableList)){
		foreach($tableList as $table){
			if(in_array('scheduler_tasks', $table)){
				$bTableExists = true;
			}
		}
	}


	if(!$bTableExists){
		$default->log->error('Scheduler Service: Scheduler_tasks table does not exist, sleeping for 30 seconds');
		sleep(30);
	}
}

$default->log->info("Scheduler Service: starting main loop");

// Main Scheduler Service Loop
while ($loop)
{
    switch (win32_get_last_control_message())
    {

        case WIN32_SERVICE_CONTROL_CONTINUE:
        	break; // Continue server routine
        case WIN32_SERVICE_CONTROL_INTERROGATE:
        	win32_set_service_status(WIN32_SERVICE_RUNNING);
        	break; // Respond with status
        case WIN32_SERVICE_CONTROL_STOP:
            win32_set_service_status(WIN32_SERVICE_STOPPED);
        	$loop = false; // Terminate script
        	continue;
        default:
    }
    // Run the scheduler script
    $cmd = "\"$phpPath\" \"$dir/scheduler.php\"";
$default->log->info('Scheduler Service: cmd - ' .$cmd );

	$WshShell = new COM("WScript.Shell");
	$res = $WshShell->Run($cmd, 0, true);
	//$cmd =  str_replace( '/','\\',$cmd);
	//$res = `$cmd 2>&1`;
	if (!empty($res))
	{
		$default->log->error('Scheduler Service: unexpected output - ' .$res);
	}

    sleep($schedulerInterval);

}
win32_set_service_status(WIN32_SERVICE_STOPPED);

$default->log->error("Scheduler Service: exiting main loop");

?>
