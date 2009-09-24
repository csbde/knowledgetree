<?php
/**
 * $Id: $
 *
 * Base class for database-backed objects
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

require_once('../config/dmsDefaults.php');

print "KnowledgeTree MD5 Validation Tool\n";
print "=================================\n\n";
print "NOTE: This utility make take some time to run to completion!\n\n";

$sql = "SELECT
			dcv.id, dmv.document_id, MAX(dmv.id) AS metadata_version_id, MAX(dmv.metadata_version) AS metadata_version
		FROM
			document_content_version AS dcv
			INNER JOIN document_metadata_version AS dmv ON dcv.id = dmv.content_version_id
		GROUP BY
			dcv.id
		ORDER BY dcv.document_id";

$rows = DBUtil::getResultArray(array($sql));

$total = count($rows);
$problem = 0;
$ok = 0;
$no_hash = 0;
$current = 0;
$dots = 0;

foreach($rows as $row)
{
	if (++$current % 100 == 0)
	{
		print '.';
		if ($dots++ % 60 == 0)
		{
			print "\n";
		}
	}
	$content_id = $row['id'];
	$document_id = $row['document_id'];
	$metadata_version = $row['metadata_version'];
	$metadata_version_id = $row['metadata_version_id'];

	$document = Document::get($document_id, $metadata_version_id);
	$core = &$document->_oDocumentContentVersion;

	$filename = $document->getFileName();
	$md5 = $core->getStorageHash();

	if (empty($md5))
	{
		if ($dots > 0) print "\n";
		print("Document Id: $document_id - Content Id: $content_id - No MD5 hash available.\n");

		$no_hash++;
		$current = 0; $dots = 0;
		// don't exit here, we do so later
	}

	$storage = KTStorageManagerUtil::getSingleton();
	$storage_path = $storage->temporaryFile($document);
	if (PEAR::isError($storage_path))
	{
		if ($dots > 0) print "\n";
		print("Document Id: $document_id - Content Id: $content_id - Storage engine reported an error: " . $storage_path->getMessage() . "\n");

		$no_hash++;
		$current = 0; $dots = 0;
		continue;
	}
	if (!file_exists($storage_path))
	{
		if ($dots > 0) print "\n";
		print("Document Id: $document_id - Content Id: $content_id - File '$storage_path' cannot be found!\n");

		$no_hash++;
		$current = 0; $dots = 0;
		continue;
	}

	$actual_md5 = md5_file($storage_path);

	$storage->freeTemporaryFile($storage_path);

	if (empty($md5))
	{
		$core->setStorageHash($actual_md5);
		$core->update();
		print("\tHash set to: $actual_md5\n");
		continue;
	}


	if ($md5 != $actual_md5)
	{
		if ($dots > 0) print "\n";
		print("Document Id: $document_id - Content ID: $content_id - MD5 difference\n");
		print("\tStored MD5: $md5\n");
		print("\tCurrent MD5: $actual_md5\n");
		$problem++;
		$current = 0; $dots = 0;
		continue;
	}
	$ok++;
}

print("\nStatistics:\n");
print("\tNo Problem:\t$ok\n");
print("\tProblem:\t$problem\n");
print("\tNo Hash:\t$no_hash\n");
print("\tTotal:\t\t$total\n");


?>
