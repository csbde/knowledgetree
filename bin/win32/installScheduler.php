<?php

$scriptPath = realpath(dirname(__FILE__) . '/schedulerService.php');
    
// Setup php binary path
$phpPath = realpath('../../php/php.exe');
if (!is_file($phpPath))
{
    die('Cannot find php.exe');
}

win32_create_service(array( 
            'service' => 'ktscheduler',
            'display' => 'ktdmsScheduler',
            'params' => $scriptPath,
            'path' => $phpPath
            ));

?>
