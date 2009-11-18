<?php

/**
 * $Id:$
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

/**
 * PURPOSE:
 *
 * This script has the purpose of shutting down the java lucene server.
 *
 */

session_start();
chdir(dirname(__FILE__));
require_once(realpath('../../../config/dmsDefaults.php'));

print _kt("Shutdown the Document Indexer") . "...\n";

$config = KTConfig::getSingleton();
$indexer = $config->get('indexer/coreClass');

$sure=false;
$indexall = false;
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		switch (strtolower($arg))
		{
			case 'positive':
				$sure=true;
				break;
			case 'help':
				print "Usage: shutdown.php [positive]\n";
				exit;
		}
	}
}
if (!$sure)
{
	print "* " . _kt("Are you sure you want to do this? Add 'positive' as a parameter to continue.") . "\n";
	exit;
}

require_once('indexing/indexerCore.inc.php');

$indexer = Indexer::get();
$indexer->shutdown();

print "\n* " . _kt("The request to shutdown has been sent to the server. It may take a few seconds.") . "\n";


print _kt("Done.") . "\n";

exit;
?>