<?php

chdir(dirname(__FILE__));
require_once('../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/foldermanagement/compressionArchiveUtil.inc.php');

/**
 * The download task provides 2 pieces of functionality. The first is to process the download queue and the second is to "ping" the download queue.
 * The processing of the download queue will create the archives for the bulk download action and set a flag to indicate their completion.
 * The "ping" will check if the flag has been set and inform the user that the archive is ready for download.
 */

$queue = new DownloadQueue();

// Check for a ping then check if the download is finished
$ping = isset($_POST['ping']) ? $_POST['ping'] : false;
if($ping == 'ping'){

    $code = $_POST['code'];
    $status = $queue->isDownloadAvailable($code);

    if($status === false){
        echo 'wait';
    }else{
        $str = '';
        // display any error messages
        if(!empty($status)){
            $str = '<div><b>'._kt('The following errors occurred during the download').': </><br />';
            $str .= '<table style="padding-top: 5px;" cellspacing="0" cellpadding="5">';
            foreach ($status as $msg){
                $str .= '<tr><td style="border: 1px #888 solid;">'.$msg.'</td></tr>';
            }
            $str .= '</table></div>';
        }
        echo $str;
    }
    exit(0);
}

if($queue->isLocked()){
    exit(0);
}
// Not a ping, process the queue
$queue->processQueue();

exit(0);
?>
