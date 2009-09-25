<?php

require_once('../config/dmsDefaults.php');

$action = 'check';
$sqlaction = 'check table';
if ($argc > 0)
{
	foreach($argv as $arg)
	{
		$action=strtolower($arg);
		switch ($action)
		{
			case 'repair':
				$sqlaction='repair table';
				break;
			case 'optimize':
				$sqlaction='optimize table';
				break;
			case 'help':
				print "Usage: dbmaint.php repair|check|optimize\n";
				exit;
			case 'check':
			default:
			    $action = 'check';
				$sqlaction='check table';
				break;
		}
	}
}

$default->log->info("DB Maintenance... \nAction selected: {$action}");

$sql = "show tables";
$tables = DBUtil::getResultArray($sql);

if(!empty($tables)){
    foreach($tables as $table)
    {
    	$key = array_keys($table);

    	$tablename=$table[$key[0]];
    	$sql = "$sqlaction $tablename;";
    	$result = DBUtil::getOneResult($sql);

    	if (PEAR::isError($result))
    	{
    		$default->log->error('Attempted: '.$sql);
    		$default->log->error(' *: '.$result->getMessage());
    		continue;
    	}
    	$default->log->info('Running: '.$sql .' - '. $result['Msg_text']);
    }
}

$default->log->info('Done.');
exit;
?>
