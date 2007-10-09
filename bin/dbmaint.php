<?php

require_once('../config/dmsDefaults.php');

print _kt('DB Maintenance') . "...\n\n";

$action = 'check';
$sqlaction = 'check table';
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		$arg=strtolower($arg);
		switch ($arg)
		{
			case 'check':
			case 'repair':
			case 'optimize':
				$sqlaction="$arg table";
				$action = $arg;
				break;
			case 'help':
				print "Usage: dbmaint.php repair|check|optimize\n";
				exit;
		}
	}
}

print '* ' . sprintf(_kt("Action selected: %s"), $action) . "\n\n";

$sql = "show tables";
$tables = DBUtil::getResultArray($sql);
foreach($tables as $table)
{
	$key = array_keys($table);

	$tablename=$table[$key[0]];
	$sql = "$sqlaction $tablename";
	$result = DBUtil::getOneResult($sql);

	if (PEAR::isError($result))
	{
		print sprintf(_kt("Attempted: %s"), $sql) . "\n";
		print sprintf(_kt(' *: %s'), $result->getMessage()) . "\n";
		continue;
	}
	print sprintf(_kt("Running: %s - %s"), $sql, $result['Msg_text']) . "\n";
}

print _kt('Done.') . "\n";

?>