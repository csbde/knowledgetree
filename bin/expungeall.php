<?php

/**
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
 */

require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');

/**
 * This script expects the following in the config.ini:
 * [autoexpunge]
 * admin=admin
 * password=admin
 * maximum=50
 * 
 * admin and password is required to expunge documents from the system.
 * 
 * maximum is the maximum number of documents that should be expunged from the system in one run.
 * 
 */

$start_time = time();

$config = KTConfig::getSingleton();
$user = $config->get('autoexpunge/admin','admin');
$password = $config->get('autoexpunge/password','admin');
$maximum = $config->get('autoexpunge/maximum',50);

$ktapi = new KTAPI();
$result = $ktapi->start_session($user, $password);
if (PEAR::isError($result))
{
	print $result->getMessage() . "\n";
	return;
}

print "Expunging documents.\n(Attempting $maximum documents)\n\n";

$sql = sprintf("SELECT id FROM documents WHERE status_id=%d LIMIT %d", DELETED, $maximum);

$rows = DBUtil::getResultArray($sql);
$count = count($rows);

if ($count == 0)
{
	print "Nothing to do.\n";
	return;
}

print "Rows found: $count\n\n";

foreach($rows as $row)
{
	$id = $row['id'];

	$document = $ktapi->get_document_by_id($id);
	$title = $document->get_title();

	print "Document ID: $id Name: '$title'\n";
	$result = $document->expunge();
	if (PEAR::isError($result))
	{
		print $result->getMessage() . "\n";
	}
}

$end_time = time();

$diff = $end_time - $start_time;

print "\ndone. $diff seconds.\n";
?>