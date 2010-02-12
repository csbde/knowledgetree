<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */
			
require_once(KT_LIB_DIR . DIRECTORY_SEPARATOR . 'foldermanagement' . DIRECTORY_SEPARATOR . 'compressionArchiveUtil.inc.php');

class BulkDownloadTrigger {
	
	var $sNamespace = 'ktcore.triggers.download';
    var $sFriendlyName;
    var $sDescription;

    function __construct()
    {
        $this->sFriendlyName = _kt('Bulk Download Notification');
        $this->sDescription = _kt('Notifies the newly logged in user of any waiting bulk downloads.');
    }
    
    public function invoke()
    {
    	// TODO get this working with triggers instead of this?
		// check for timed out downloads - now we may be looking at enough code to justify a trigger instead of all being here...?
		DownloadQueue::timeout();
		
		// determine whether there is a waiting bulk download
		global $main;
		// first check whether there is in fact a download waiting for this user
		$config = KTConfig::getSingleton();
		$file = DownloadQueue::getNotificationFileName();
        if (!PEAR::isError($file)) {
			$notificationFile = $config->get('cache/cacheDirectory') . '/' . $file;
        }
        if ((isset($_SESSION['checkBulkDownload']) && ($_SESSION['checkBulkDownload'])) || (file_exists($notificationFile))) {
			unset($_SESSION['checkBulkDownload']);
			DownloadQueue::removeNotificationFile();
			
			$userID = $_SESSION['userID'];
			$code = DownloadQueue::userDownloadAvailable($userID);
			if ($code) {
				$sBaseUrl = KTUtil::kt_url();
				$sUrl = $sBaseUrl . '/';
				$heading = _kt('You have a download waiting');
				$main->requireJSResource('resources/js/download_notification.js');
				$notification = "showDownloadNotification('{$sUrl}', '{$heading}', 'dms.users.bulk_download_notification', "
							  . "'{$code}', 'close');";
			}
			
			return $notification;
		}
		
		return null;
    }
    
}

?>
