<?php

$scriptPath = realpath(dirname(__FILE__) . '/taskrunner.bat');
    
win32_create_service(array( 
            'service' => 'ktscheduler',
            'display' => 'ktdmsScheduler',
            'path' => $scriptPath
            ));

?>
