<?php

/**
 *
 * $Id$
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
$session = $ktapi->start_session($user, $password);
if (PEAR::isError($session))
{
	print $session->getMessage() . "\n";
	return;
}

print "Expunging documents.\n(Attempting $maximum documents)\n\n";

$sql = sprintf("SELECT id FROM documents WHERE status_id=%d LIMIT %d", DELETED, $maximum);

$rows = DBUtil::getResultArray($sql);
$count = count($rows);

if ($count == 0)
{
	print "Nothing to do.\n";
	$session->logout();
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
$session->logout();
?>