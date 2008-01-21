<?php

/**
 * $Id$
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
 *
 */
 
    $userAgentValue = $_SERVER['HTTP_USER_AGENT'];
    if (stristr($userAgentValue, "Microsoft Data Access Internet Publishing Provider DAV")) {
        // Fix for Novell Netdrive 
        chdir(realpath(dirname(__FILE__)));
    }

    $webdav_pear_path = 'thirdparty/pear';
    $kt_pear_path = '../thirdparty/pear';
    $include_path = ini_get('include_path');
    ini_set('include_path', $webdav_pear_path . PATH_SEPARATOR . $kt_pear_path . PATH_SEPARATOR . $include_path);

    require_once "lib/KTWebDAVServer.inc.php";
    $ktwebdav = new KTWebDAVServer();
    $ktwebdav->ServeRequest();
?>
