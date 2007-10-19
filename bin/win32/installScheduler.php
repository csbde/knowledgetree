<?php

$dir = realpath(dirname(__FILE__) . '/schedulerService.php');

win32_create_service(array(
        'service' => 'ktscheduler',
        'display' => 'KnowledgeTree Scheduler Service',
        'params' => $dir
    ));

?>