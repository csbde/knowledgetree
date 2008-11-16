<?php

/**
 *
 * $Id$
 *
 * This uploads a file onto the file server. A web service method can later move the file to the correct location.
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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

// TODO: allow for linking of related documents.

if (!array_key_exists('session_id',$_POST))
{
	$msg = urlencode('Session not specified.');
	print "status_code=1&msg=$msg";
	exit;
}


if (!array_key_exists('action',$_POST))
{
	$msg = urlencode('Action not specified.');
	print "status_code=2&msg=$msg";
	exit;
}

$action = $_POST['action'];

if (!in_array($action,array('C','A')))
{
	$msg = urlencode('Invalid action specified.');
	print "status_code=3&msg=$msg";
	exit;
}


$session_id = $_POST['session_id'];



if (count($_FILES) == 0)
{
	$msg = urlencode('No files have been uploaded.');
	print "status_code=5&msg=$msg";
	exit;
}

if ($action == 'C')
{
	if (!array_key_exists('document_id',$_POST))
	{
		$msg = urlencode('document not specified.');
		print "status_code=6&msg=$msg";
		exit;
	}
	$document_id = $_POST['document_id'];
}

//require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');
require_once('KTUploadManager.inc.php');


$ktapi = new KTAPI();

$session = $ktapi->get_active_session($session_id);

if (PEAR::isError($session))
{
	$msg = urlencode($session->getMessage());
	print "status_code=4&msg=$msg";
	exit;
}

$upload_manager = new KTUploadManager();

$upload_manager->cleanup();

$upload_manager->set_session($session);

$added=array();
foreach($_FILES as $key =>$file)
{
	$filename=$file['name'];
	$tempfile=$file['tmp_name'];

	$error=$file['error'];
	if ($error == UPLOAD_ERR_OK)
	{
		$result = $upload_manager->uploaded($filename, $tempfile, $action);
		if (PEAR::isError($result))
		{
			continue;
		}
		if ($result !== false)
		{
			$file['tmp_name'] = $result;
			$added[$key]=$file;
		}
	}
}

$added=urlencode(serialize($added));
print "status_code=0&upload_status=$added";


?>