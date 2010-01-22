<?php
/*
 * Electronic Signatures ajax functionality
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package Electronic Signatures
 * @version Version 0.9
 */

//$full_dir = dirname(__FILE__);

//$pos = strpos($full_dir, 'plugins');
//$dir = substr($full_dir, 0, $pos);

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/foldermanagement/compressionArchiveUtil.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/downloadNotification.inc.php');

$action = $_POST['action'];
$code = $_POST['code'];
if ($action == 'delete') {
	DownloadQueue::deleteDownload($code);
}
else {
	$head = $_POST['head'];

	// display the download notification
	$notification = new KTDownloadNotification($code);
	echo $notification->getNotificationForm($head);
}

exit;
?>