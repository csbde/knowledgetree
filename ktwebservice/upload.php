<?

/**
 *
 * This uploads a file onto the file server. A web service method can later move the file to the correct location.
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