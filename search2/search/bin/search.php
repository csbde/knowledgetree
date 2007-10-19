<?php

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
			foreach($result as $item)
    		{
    			print 'Document ID: ' . $item->DocumentID . ' Title:' . $item->Title . ' Relevance:' . $item->Rank . "\n";
    		}
    		break;
		case 'csv':
   			print "DocumentId,Title,Relevance\n";
			foreach($result as $item)
    		{
    			print $item->DocumentID . ',' . $item->Title . ',' . $item->Rank . "\n";
    		}
    		break;
		case 'xml':
			print "<result>\n";
			foreach($result as $item)
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