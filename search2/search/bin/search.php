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

chdir(dirname(__FILE__));
require_once(realpath('../../../config/dmsDefaults.php'));
require_once(KT_DIR . '/ktapi/ktapi.inc.php');

$username = '';
$password = '';
$expr = '';


$output = 'simple';
$verbose = false;
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		if (strpos($arg, '=') === false)
		{
			$expr = $arg;
			continue;
		}
		list($param, $value) = explode('=', $arg);

		switch (strtolower($param))
		{
			case 'verbose':
				$verbose=true;
				break;
			case 'output':
				switch(strtolower($value))
				{
					case 'xml': $output = 'xml'; break;
					case 'csv': $output = 'csv'; break;
					default:
						$output = 'simple';
				}
				break;
			case 'user':
				$username = $value;
				if ($verbose) print "* User = $value\n";
				break;
			case 'pass':
				$password = $value;
				if ($verbose) print "* User = $value\n";
				break;
			case 'help':
				print "Usage: search.php [verbose] user=username pass=password [output=simple|xml|csv] 'search criteria'\n";
				exit;
		}
	}
}


if ($verbose) print _kt('Command line search') . "...\n";

if (empty($username))
{
	die(_kt("Please specify a username!"));
}

if (empty($expr))
{
	die(_kt("Please specify search criteria!"));
}

$ktapi = new KTAPI();
$result = $ktapi->start_session($username, $password);
if (PEAR::isError($result))
{
	print _kt("Cannot locate user:") . " $username\n";
	exit;
}

try
{
    $expr = parseExpression($expr);

    $result = $expr->evaluate();

	switch ($output)
	{
		case 'simple':
			foreach($result['docs'] as $item)
    		{
    			print 'Document ID: ' . $item->DocumentID . ' Title:' . $item->Title . ' Relevance:' . $item->Rank . "\n";
    		}
    		break;
		case 'csv':
   			print "DocumentId,Title,Relevance\n";
			foreach($result['docs'] as $item)
    		{
    			print $item->DocumentID . ',' . $item->Title . ',' . $item->Rank . "\n";
    		}
    		break;
		case 'xml':
			print "<result>\n";
			foreach($result['docs'] as $item)
    		{
    			print "\t<item id=\"$item->DocumentID\" title=\"$item->Title\" relevance=\"$item->Rank\"/>\n";
    		}
			print "</result>\n";
	}

	if ($verbose)
	{
		print _kt("Done.") . "\n";
	}

}
catch(Exception $e)
{
    print $e->getMessage();
}

?>
