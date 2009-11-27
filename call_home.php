<?php
/*
* Data incoming format: <installation guid>|<user count>|<document count>|<KT version>|<KT edition>|<OS info>
*/

$data = isset($_REQUEST['system_info']) ? strip_tags($_REQUEST['system_info']) : '';

if(empty($data)){
    exit(0);
}

$file = 'var/system_info.txt';
$fp = fopen($file, 'a');
fwrite($fp, $data."\n");
fclose($fp);

exit(0);
?>