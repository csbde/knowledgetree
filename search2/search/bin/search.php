<?php

require_once(realpath('../../../config/dmsDefaults.php'));
require_once(KT_DIR . '/ktapi/ktapi.inc.php');

$username = '';
$password = '';
$expr = '';

print _kt('Command line search') . "...\n";

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
					case 'xml': $output = 'simple'; break;
					case 'csv': $output = 'csv'; break;
					default:
						$output = 'simple';
				}
			case 'user':
				$username = $value;
				print "* User = $value\n";
				break;
			case 'pass':
				$password = $value;
				print "* User = $value\n";
				break;
			case 'help':
				print "Usage: search.php [verbose] user=username pass=password [output=simple|xml|csv] 'expression'\n";
				exit;
		}
	}
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


    foreach($result as $item)
    {
    	print $item->DocumentID . ' ' . $item->Title . ' ' . $item->Rank . "\n";
    }

    print _kt("Done.") . "\n";

}
catch(Exception $e)
{
    print $e->getMessage();
}

?>