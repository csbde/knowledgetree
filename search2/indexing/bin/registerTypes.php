<?php

/**
 * $Id:$
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

/**
 * PURPOSE:
 *
 * The purpose of this script is to register types.
 *
 * Usage: registerTypes.php [clear]
 *
 * If 'clear' is specified, mime type mappings will be cleared.
 *
 */

session_start();
chdir(dirname(__FILE__));
require_once(realpath('../../../config/dmsDefaults.php'));

print _kt("Registering Extractor mapping to Mime types") . "...\n";

$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

require_once('indexing/indexerCore.inc.php');
$clear=false;
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		switch (strtolower($arg))
		{
			case 'clear':
				$clear=true;
				print "* " . _kt("Clearing mime type associations.") . "\n";
				break;
			case 'help':
				print "Usage: registerTypes.php [clear]\n";
				exit;
		}
		if (strtolower($arg) == 'clear')
		{
			$clear=true;
		}
	}
}

$indexer = Indexer::get();
$indexer->registerTypes($clear);

print _kt("Done.") . "\n";
?>