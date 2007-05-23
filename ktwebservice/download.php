<?

/**
 *
 * This does the download of a file based on the download_files table.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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

require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');
require_once('KTDownloadManager.inc.php');

$download_manager = new KTDownloadManager();
$download_manager->set_session($session);

$response = $download_manager->download($document_id, $hash);
if (PEAR::isError($response))
{
	$msg = urlencode($response->getMessage());
	print "status_code=4&msg=$msg";
	exit;	
}

?>