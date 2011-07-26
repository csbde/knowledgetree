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
	 * Bulk action current folder id
	 * @var string
	 */
	private $currentFolderId;
	/**
	 * Logged in user's id
	 * @var string
	 */
	private $userId;
	/**
	 * Number of documents
	 * @var int
	 */
	private $numDocuments = 0;
	/**
	 * Number of folders
	 * @var int
	 */
	private $numFolders = 0;
	/**
	 * If number of documents to process exceeds threshold,
	 * send operation to queue
	 * @var array
	 */
	private $threshold = array(	'documents' => 1000,
								'folders' => 50
								);

	public function __construct($action, $list, $reason = '', $targetFolderId, $currentFolderId)
	{
		$this->action = $action;
		$this->list = $list;
		$this->reason = $reason;
		$this->targetFolderId = $targetFolderId;
		$this->currentFolderId = $currentFolderId;
		$this->numFolders = 0;
		$this->numDocuments = 0;
	}

	public function checkIfNeedsBackgrounding()
	{
		$this->numFolders = count($this->list['folders']);
		$this->numDocuments = count($this->list['documents']);
		while (count($folders) > 0) {
			$folderId = array_pop($folders);
			$this->getFolderDocuments($folderId);
			$folders = $this->getFolderSubFolders($folderId, $folders);
		}
		if(($this->numDocuments > $this->threshold['documents']) || ($this->numFolders > $this->threshold['folders']))
			return true;

		return false;
	}

	private function getFolderDocuments($folderId)
	{
		// Get all parent folder documents
		$query = "SELECT id,folder_id from documents WHERE folder_id = '$folderId';";
		$results = DBUtil::getResultArray($query);
		if($results)
		{
			foreach ($results as $aResult) {
				$this->numDocuments++;
			}
		}
	}

	private function getFolderSubFolders($folderId, $folders)
	{
		// Get all parent folder sub folders
		$whereClause = "parent_folder_ids = '{$folderId}' OR parent_folder_ids LIKE '{$folderId},%' OR parent_folder_ids LIKE '%,{$folderId},%' OR parent_folder_ids LIKE '%,{$folderId}' ";
		$query = "SELECT id, parent_folder_ids, linked_folder_id from folders WHERE $whereClause";
		$results = DBUtil::getResultArray($query);
		if($results)
		{
			foreach ($results as $aResult) {
				if(is_null($aResult['linked_folder_id']))
				{
					array_push($folders, $aResult['id']);
				}
				else {
					array_push($folders, $aResult['linked_folder_id']);
				}
				$this->numFolders++;
			}
		}

		return $folders;
	}

	public function setUser($userId)
	{
		$this->userId = $userId;
	}

	public function queueBulkAction()
	{
    	require_once(KT_LIVE_DIR . '/sqsqueue/dispatchers/BulkactionDispatcher.php');
    	$bulkActionDispatcher = new BulkactionDispatcher();
    	$params['action'] = $this->action;
    	$params['files_and_folders'] = $this->list;
    	$params['reason'] = $this->reason;
    	$params['targetFolderId'] = $this->targetFolderId;
    	$params['currentFolderId'] = $this->currentFolderId;
    	$bulkActionDispatcher->addProcess("bulkactions", $params);
    	$queueResponse = $bulkActionDispatcher->sendToQueue();
    	if($queueResponse) {
			$this->saveEvent();
    	}

    	return $queueResponse;
	}

	private function saveEvent()
	{
		require_once(KT_LIB_DIR . '/memcache/ktmemcache.php');
		$memcache = KTMemcache::getKTMemcache();
		if(!$memcache->isEnabled()) return ;
		$key = ACCOUNT_NAME . '_bulkaction';
		$bulkActions = $memcache->get($key);
		if(empty($bulkActions))
			$folderIds = array();
		else {
			$folderIds = unserialize($bulkActions);
		}
		// Store current and target folder.
		$folderIds[$this->action][$this->currentFolderId] = $this->currentFolderId;
		$folderIds[$this->action][$this->targetFolderId] = $this->targetFolderId;
		// Store all subfolders
		foreach ($this->list['folders'] as $folderId) {
			$folderIds[$this->action][$folderId] = $folderId;
		}
		$memcache->set($key, serialize($folderIds));
	}
}
?>