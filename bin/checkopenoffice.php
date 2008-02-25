<?php

/**
 *
 * $Id:
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 */

chdir(realpath(dirname(__FILE__)));
require_once('../config/dmsDefaults.php');

// Check if open office is running
$sCheckOO = SearchHelper::checkOpenOfficeAvailablity();


// If it is running - exit, we don't need to do anything otherwise start it
if(!empty($sCheckOO)){
	
	$default->log->debug('Check Open Office Task: Open office service is not running... trying to start it.');
	
    if(OS_WINDOWS){
    	
        // Check the path first
        $sPath = realpath('../../winserv.exe');

        if(file_exists($sPath)){
            $sCmd = "\"$sPath\" start kt_openoffice";
            KTUtil::pexec($sCmd);
            exit;
        }
        // If that doesn't work, check for the all start
        $sPath = realpath('../../bin/allctl.bat');
        if(file_exists($sPath)){
            $sCmd = "\"$sPath\" start";
            KTUtil::pexec($sCmd);
            exit;
        }
        // Might be a source install ... ???
        $default->log->debug('Check Open Office Task: Can\'t start Open office, this may be a source install.');
        exit;
    }else{
        $sPath = realpath('../../dmsctl.sh');
        if(file_exists($sPath)){
            $sCmd = "\"$sPath\" start";
            KTUtil::pexec($sCmd);
            exit;
        }
        // might be a source install
        $default->log->debug('Check Open Office Task: Can\'t start Open office, this may be a source install.');
        exit;
    }
}

exit;
?>