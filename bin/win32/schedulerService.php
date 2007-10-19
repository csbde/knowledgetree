<?php


$dir = realpath(dirname(__FILE__) . '/..');
chdir($dir);

$phpPath = realpath('../../../php/php.exe');
if (!is_file($phpPath))
{
	die('Cannot find php.exe');
}


  win32_start_service_ctrl_dispatcher('ktscheduler');

  while (WIN32_SERVICE_CONTROL_STOP != win32_get_last_control_message())
  {
	system("$phpPath scheduler.php");
    sleep(60);
  }

?>