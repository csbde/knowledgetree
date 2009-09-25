<?php

/**
 *
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
 */

chdir(realpath(dirname(__FILE__)));
require_once('../config/dmsDefaults.php');

/*
Script checks if open office is running, if it isn't then it attempts to start it.

Windows Vista always returns false if we try and check the host and port
so for windows we use the win32 service status checks.

*/

// Check if the calling function requires a return value
$sGiveOutput = (isset($argv[1]) && $argv[1] == 'output') ? true : false;

// Check indexed document count
// If the number of indexed documents is greater than the set amount, restart open office
// this clears open office's memory usage
$resetPoint = 50; // todo: put in config
$count = Indexer::getIndexedDocumentCount();

$restartOO = false;
if($count > $resetPoint){
    $restartOO = true;

    // reset the count
    Indexer::updateIndexedDocumentCount(0);
    $default->log->debug('Check Open Office Task: Restarting open office.');
}

// First we check the host:port to see if open office is running
$sCheckOO = SearchHelper::checkOpenOfficeAvailablity();

if(empty($sCheckOO) && !$restartOO){
    // If the check returns empty then it is available on that port so we exit
    if($sGiveOutput){
        echo 1;
    }
    exit;
}

// Open office appears not to be running or requires a restart
if(OS_WINDOWS){
    $OOService = 'ktopenoffice';
    $default->log->debug('Check Open Office Task: ' . get_current_user());

    if($restartOO){
        // If Open office needs to be restarted - stop it here
        $result_stop = win32_stop_service($OOService);


        // Wait for the service to stop fully before trying to restart it
        $continue = false;
        $cnt = 0;
        while($continue === false && $cnt < 15){
            $result = win32_query_service_status($OOService);

            if(isset($result['ProcessId']) && $result['ProcessId'] != 0){
                // If there is still a process id then the service has not stopped yet.
                sleep(2);
                $continue = false;
                $cnt++;
            }else{
                $continue = true;
            }
        }
    }else{
        // If this is vista, checking the port may not work so we query the service
        $result = win32_query_service_status($OOService);

        if(is_array($result)){
            $iProcessId = $result['ProcessId'];
            if(!empty($iProcessId) && $iProcessId != 0){
                // If there is a process id (PID) then open office is running so we exit
                if($sGiveOutput){
                    echo 1;
                }
            	exit;
            }
        }
    }

    // Service is not running - log it and attempt to start
	$default->log->error('Check Open Office Task: Open office service is not running... trying to start it.');

	// Use the win32 service start
	$result2 = win32_start_service($OOService);

	if($result2 == 0){
	    // Service started successfully
	    $default->log->debug('Check Open Office Task: Open office service started.');
	    if($sGiveOutput){
            echo 1;
        }
        exit;
	}

	$default->log->error('Check Open Office Task: Open office service could not be started. Error code '.$result2);

	// Attempt using the dmsctl batch script
	$sPath = realpath('../../bin/dmsctl.bat');

	if(file_exists($sPath)){
	    $sCmd = "\"$sPath\" start";
	    $default->log->debug('Check Open Office Task: ' . get_current_user());
        $default->log->debug('Check Open Office Task: ' . $sCmd);

	    $res = KTUtil::pexec($sCmd);

	    $default->log->debug('Check Open Office Task: Attempted start using dmsctl.bat.');
	    if($sGiveOutput){
            echo 2;
        }
	    exit;
	}else{
	    $default->log->debug('Check Open Office Task: Can\'t find dmsctl.bat, this may be a source install.');
	    if($sGiveOutput){
            echo 0;
        }
        exit;
	}
}else{
    // If the OS is Unix or Linux
    $sPath = realpath('../../dmsctl.sh');
    if(file_exists($sPath)){
        // If Open office needs to be restarted - stop it here
        if($restartOO){
            $sCmd = "\"$sPath\" restart soffice >/dev/null &";
            $default->log->debug('Check Open Office Task: ' . get_current_user());
            $default->log->debug('Check Open Office Task: ' . $sCmd);

            KTUtil::pexec($sCmd);

            $default->log->debug('Check Open Office Task: Attempted restart using dmsctl.sh.');
        }else{
            $sCmd = "\"$sPath\" start soffice >/dev/null &";
            $default->log->debug('Check Open Office Task: ' . get_current_user());
            $default->log->debug('Check Open Office Task: ' . $sCmd);

            KTUtil::pexec($sCmd);

            $default->log->debug('Check Open Office Task: Attempted start using dmsctl.sh.');
        }
        if($sGiveOutput){
            echo 2;
        }
        exit;
    }else{
        $default->log->debug('Check Open Office Task: Can\'t find dmsctl.sh, this may be a source install.');
        if($sGiveOutput){
            echo 0;
        }
        exit;
    }
}
$default->log->debug('Check Open Office Task: Can\'t start Open office, this may be a source install.');
if($sGiveOutput){
    echo 0;
}
exit(0);
?>
