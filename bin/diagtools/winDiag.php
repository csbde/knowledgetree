<?php
/*
	$template=file_get_contents('ReportTemplate.html');

	$rep=array();
	$rep['running processes']=shell_exec('tasklist');
	$rep['services']=shell_exec('sc query');
	$rep['system path']=shell_exec('path');
	$rep['environment variables']=shell_exec('set');
	
	$report=array();
		
	foreach ($rep as $test=>$results){
		$test=ucwords($test);
		$report[]=str_replace(array('[heading]','[content]'),array($test,$results),$template);
	}
	
	$report=join('',$report);
	
	echo $report;
*/
?>