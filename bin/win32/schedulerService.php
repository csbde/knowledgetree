<?php

$myservicename = 'ktscheduler';

// Connect to service dispatcher and notify that startup was successful
if (!win32_start_service_ctrl_dispatcher($myservicename)) die('Could not connect to service :'.$myservicename);
win32_set_service_status(WIN32_SERVICE_RUNNING);

chdir(dirname(__FILE__)); // need to be here to include dmsDefaults
require_once('../../config/dmsDefaults.php');

global $default;

$config = KTConfig::getSingleton();
$schedulerInterval = $config->get('KnowledgeTree/schedulerInterval',10); // interval in seconds
//$phpPath = $config->get('externalBinary/php','php'); // TODO - fix me

// Change to knowledgeTree/bin folder
$dir = realpath(dirname(__FILE__) . '/..');
chdir($dir);

// Setup php binary path
$phpPath = realpath('../../php/php.exe');
if (!is_file($phpPath))
{
	$default->log->error("Scheduler: php not found: $phpPath");
	exit;
}

$default->log->info("Scheduler Service: starting main loop");

$loop = true;
// Main Scheduler Service Loop
while ($loop)
{
    switch (win32_get_last_control_message())
    {

        case WIN32_SERVICE_CONTROL_CONTINUE:
        	break; // Continue server routine
        case WIN32_SERVICE_CONTROL_INTERROGATE:
        	win32_set_service_status(WIN32_NO_ERROR);
        	break; // Respond with status
        case WIN32_SERVICE_CONTROL_STOP: win32_set_service_status(WIN32_SERVICE_STOPPED);
        	$loop = false; // Terminate script
        	continue;
        default:
        	win32_set_service_status(WIN32_ERROR_CALL_NOT_IMPLEMENTED); // Add more cases to handle other service calls
    }
    // Run the scheduler script
    $cmd = "\"$phpPath\" scheduler.php";

	$cmd = str_replace( '/','\\',$cmd);
	$res = `"$cmd" 2>&1`;
	if (!empty($res))
	{
		$default->log->error('Scheduler: unexpected output - ' .$res);
	}

    sleep($schedulerInterval);

}
win32_set_service_status(WIN32_SERVICE_STOPPED);

$default->log->error("Scheduler Service: exiting main loop");

?>
