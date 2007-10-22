<?php

$myservicename = 'ktscheduler';

// Connect to service dispatcher and notify that startup was successful
if (!win32_start_service_ctrl_dispatcher($myservicename)) die('Could not connect to service :'.$myservicename);
win32_set_service_status(WIN32_SERVICE_RUNNING);

// Main Scheduler Service Loop
while (1) {

    switch (win32_get_last_control_message()) {

        case WIN32_SERVICE_CONTROL_CONTINUE: break; // Continue server routine
        case WIN32_SERVICE_CONTROL_INTERROGATE: win32_set_service_status(WIN32_NO_ERROR); break; // Respond with status
        case WIN32_SERVICE_CONTROL_STOP: win32_set_service_status(WIN32_SERVICE_STOPPED); exit; // Terminate script
        default: win32_set_service_status(WIN32_ERROR_CALL_NOT_IMPLEMENTED); // Add more cases to handle other service calls
    }

    // Change to knowledgeTree/bin folder
    $dir = realpath(dirname(__FILE__) . '/..');
    chdir($dir);

    // Setup php binary path
    $phpPath = realpath('../../php/php.exe');
    if (!is_file($phpPath))
    {
        die('Cannot find php.exe');
    }
	
    // Run the scheduler script
    system("$phpPath scheduler.php");

    sleep(10); // Run every 10 seconds

}
win32_set_service_status(WIN32_SERVICE_STOPPED);

?>
