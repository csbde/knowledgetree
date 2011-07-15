<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 */

class BulkDocumentActions
{
	/**
	 * Bulk action name
	 * @var string
	 */
	private $action;
	/**
	 * Array list of document and folder ids
	 * @var string
	 */
	private $list;
	/**
	 * Bulk action reason
	 * @var string
	 */	
	private $reason;
	/**
	 * Bulk action target folder id
	 * @var string
	 */
	private $targetFolderId;
	/**
	 * Logged in user's id
	 * @var string
	 */
	private $userId;
	/**
	 * If number of documents to process exceeds threshold, 
	 * send operation to queue
	 * @var array
	 */
	private $threshold = array(	'documents' => 10,
								'folders' => 0
								);
	
	public function __construct($action, $list, $reason = '', $targetFolderId) {
		$this->action = $action;
		$this->list = $list;
		$this->reason = $reason;
		$this->targetFolderId = $targetFolderId;
	}
	
	public function canSendToQueue() {
		if(count($this->list['documents']) > $this->threshold['documents'])
			return true;
		if(count($this->list['folders']) > $this->threshold['folders'])
			return true;
		return false;
	}
	
	public function setUser($userId) {
		$this->userId = $userId;
	}
	
	public function queueBulkAction() {
    	require_once(KT_LIVE_DIR . '/sqsqueue/dispatchers/bulkactionDispatcher.php');
    	$bulkActionDispatcher = new BulkactionDispatcher();
    	$params['action'] = $this->action;
    	$params['files_and_folders'] = $this->list;
    	$params['reason'] = $this->reason;
    	$params['targetFolderId'] = $this->targetFolderId;
    	$bulkActionDispatcher->addProcess("bulkactions", $params);
    	$queueResponse = $bulkActionDispatcher->sendToQueue();
    	if($queueResponse) {
			$this->saveEvent();
    	}
    	
    	return $queueResponse;
	}
	
	private function saveEvent() {
		require_once(KT_LIB_DIR . '/memcache/ktmemcache.php');
		$memcache = KTMemcache::getKTMemcache();
		if(!$memcache->isEnabled()) return ;
		$userKey = "bulkaction_" . ACCOUNT_NAME . "{$this->userId}";
		$usersBulkActions = $memcache->get($userKey);
		if(empty($usersBulkActions))
			$folderIds = array();
		else {
			$folderIds = unserialize($usersBulkActions);
		}
			
		$folderIds[$this->action][$this->targetFolderId] = $this->targetFolderId;
		$memcache->set($userKey, serialize($folderIds));
	}
}
?>