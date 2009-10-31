<?php

/**
 *
 * $Id$
 *
 * This uploads a file onto the file server. A web service method can later move the file to the correct location.
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

//debugger_start_debug();
$output = 'php';
if (array_key_exists('output',$_POST))
{
	$format = $_POST['output'];
	switch($format)
	{
		case 'xml':
		case 'json':
		case 'php':
			$output = $format;
			break;
		default:
			// don't do anything - defaulting to php
	}
	unset($format);
}

// TODO: allow for linking of related documents.

if (!array_key_exists('session_id',$_POST))
{
	respond(1, 'Session not specified.');
}

if (!array_key_exists('action',$_POST))
{
	respond(2, 'Action not specified.');
}

$action = $_POST['action'];
if (!in_array($action,array('C','A')))
{
	respond(3, 'Invalid action specified.');
}

//$session_id = $_POST['session_id'];
if (count($_FILES) == 0)
{
	respond(5, 'No files have been uploaded.');
}

if ($action == 'C')
{
	if (!array_key_exists('document_id',$_POST))
	{
		respond(6, 'Document ID not specified.');
	}
	$document_id = $_POST['document_id'];
}

require_once('../ktapi/ktapi.inc.php');
require_once('KTUploadManager.inc.php');

$apptype = (isset($_POST['apptype'])) ? $_POST['apptype'] : 'ws';
$session_id = $_POST['session_id'];

$ktapi = new KTAPI();
$session = $ktapi->get_active_session($session_id, null, $apptype);
if (PEAR::isError($session))
{
	respond(4, $session->getMessage());
}

$upload_manager = new KTUploadManager();
$upload_manager->cleanup();
$upload_manager->set_session($session);

$failed = 0;
$added=array();
$lastMessage='';
foreach($_FILES as $key =>$file)
{
	$filename=$file['name'];
	$tempfile=$file['tmp_name'];

	$error=$file['error'];
    $extra = $filename.'-'.$tempfile.'-'.$error;
	if ($error == UPLOAD_ERR_OK)
	{
		$result = $upload_manager->uploaded($filename, $tempfile, $action);
		if (PEAR::isError($result))
		{
			$lastMessage=$result->getMessage();
			$default->log->error("Cannot upload file '$filename'. Temp location: '$tempfile'. " . $lastMessage);
			$failed++;
			continue;
		}
		if ($result !== false)
		{
			$file['tmp_name'] = $result;
			$added[$key]=$file;
		}
		else
		{
			$failed++;
		}
	}
}

if ($failed)
{
	respond(7, 'Could not add files to the system. Please inspect the log file. ' . $lastMessage);
}
else
{
	respond(0, 'It worked'.$extra, $added);
}

function respond($code, $msg, $uploads=array())
{
	global $output;


	$response =array(
		'status_code'=>$code,
		'msg'=>$msg,
		'upload_status'=>$uploads
	);

	switch($output)
	{
		case 'xml':
			$xml = "<response>\n";
			$xml .= "\t<status_code>$code</status_code>\n";
			$xml .= "\t<msg>$msg</msg>\n";
			$xml .= "\t<upload_status>\n";
			$i=0;
			foreach($uploads as $key=>$value)
			{
				$servername = $value['tmp_name'];
				$filesize = $value['size'];
				$error = $value['error'];
				$name  = urlencode($value['name']);
				$xml .= "\t\t<file>\n";
				$xml .= "\t\t\t<offset>$i</offset>\n";
				$xml .= "\t\t\t<name>$name</name>\n";
				$xml .= "\t\t\t<filename>$servername</filename>\n";
				$xml .= "\t\t\t<filesize>$filesize</filesize>\n";
				$xml .= "\t\t\t<error>$error</error>\n";
				$xml .= "\t\t</file>\n";
				$i++;
			}
			$xml .= "\t</upload_status>\n";
			$xml .= "</response>\n";
			print $xml;
			exit;
		case 'json':
			print json_encode($response);
			exit;
		case 'php':
		default:
			$msg = urlencode($msg);
			$response['upload_status'] = serialize($response['upload_status']);
			$str = '';
			$i=0;
			foreach($response as $key=>$value)
			{
				if ($i++>0) $str .= '&';
				$str .= "$key=$value";
			}
			print $str;
			exit;
	}
}


?>
