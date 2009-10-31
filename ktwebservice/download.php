<?php

/**
 *
 * This does the download of a file based on the download_files table.
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

if (!array_key_exists('code',$_GET))
{
	$msg = urlencode('Code not specified.');
	print "status_code=1&msg=$msg";
	exit;
}

$hash = $_GET['code'];

if (!array_key_exists('d',$_GET))
{
	$msg = urlencode('Document not specified.');
	print "status_code=2&msg=$msg";
	exit;
}

$document_id = $_GET['d'];

if (!array_key_exists('u',$_GET))
{
	$msg = urlencode('Session not specified.');
	print "status_code=3&msg=$msg";
	exit;
}

$session = $_GET['u'];
$apptype = (isset($_GET['apptype'])) ? $_GET['apptype'] : 'ws';

require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');
require_once('KTDownloadManager.inc.php');

$download_manager = new KTDownloadManager();
$download_manager->set_session($session);

$response = $download_manager->download($document_id, $hash, null, $apptype);
if (PEAR::isError($response))
{
	$msg = urlencode($response->getMessage());
	print "status_code=4&msg=$msg:".$_GET["u"].":".$_GET["d"].":".$_GET["code"].":".$_GET["apptype"].":";
	exit;
}

?>
