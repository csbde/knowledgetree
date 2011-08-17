<?php
/**
 * $Id$
 *
 * Utilities helpful to traversing the document repository
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

require_once(KT_LIB_DIR . '/memcache/ktmemcache.php');

class BackgroundAction
{
	public $accountName;
	private $action;
	private $list;
	private $reason;
	private $targetFolderId;
	private $currentFolderId;
	private $numDocuments = 0;
	private $numFolders = 0;
	/**
	 * If number of documents to process exceeds threshold,
	 * background operation
	 * @var array
	 */
	private $threshold = array(	'move' =>	array(	'documents' => 500,
													'folders' => 50
												),
								'delete' =>	array(	'documents' => 250,
													'folders' => 25
												),
								'copy' =>	array(	'documents' => 0,
													'folders' => 10
												)
								);

	public function __construct($action, $list, $reason = '', $targetFolderId, $currentFolderId)
	{
		$this->accountName = ACCOUNT_NAME;
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
		// Check number of folders and documents
		$folders = $this->list['folders'];
		$this->numFolders = count($this->list['folders']);
		$this->numDocuments = count($this->list['documents']);
		while (count($folders) > 0) {
			$folderId = array_pop($folders);
			$this->getFolderDocuments($folderId);
			$folders = $this->getFolderSubFolders($folderId, $folders);
			if($this->hitThreshold()) {
				return true;
			}
		}

		return $this->hitThreshold();

	}

	public static function getMessage($action)
	{
		return "Bulk $action in progress. Please try again later.";
	}

	public static function saveEvent($action, $folders, $currentFolderId, $targetFolderId)
	{
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
		$folderIds[$action][$currentFolderId] = $currentFolderId;
		$folderIds[$action][$targetFolderId] = $targetFolderId;
		// Store all subfolders
		foreach ($folders as $folderId) {
			$folderIds[$action][$folderId] = $folderId;
		}
		$memcache->set($key, serialize($folderIds));
	}

	public static function isDocumentInBulkAction($document = null)
	{
		$folderIdsPath = '';
		if($document instanceof Document) {
			$folderIdsPath = $document->getParentFolderIds();
		}
		else {
			if(!is_null($document)) {
				$document = Document::get($document);
				$folderIdsPath = $document->getParentFolderIds();
			}
		}

		return self::isBulkActionInProgress($folderIdsPath);
	}

	public static function isFolderInBulkAction($folder = null)
	{
		$folderIdsPath = '';
		if($folder instanceof Folder || $folder instanceof FolderProxy) {
			$folderIdsPath = Folder::generateFolderIDs($folder->getId());
		}
		else {
			if(!is_null($folder)) {
				$folderIdsPath = Folder::generateFolderIDs($folder);
			}
		}

		return self::isBulkActionInProgress($folderIdsPath);
	}

    public function background()
    {
        global $default;
    	$phpPath = $default->php;
    	$this->storeInMemcache();
    	$script =  KT_DIR . '/plugins/ktcore/backgroundTasks/BulkActionTask.php';
    	$arguments = "{$this->accountName} {$this->action} {$this->targetFolderId} {$this->currentFolderId}";
    	$command = "{$phpPath} {$script} {$arguments} > /dev/null &";

    	KTUtil::pexec($command);
    }

    public function execute()
    {
    	// TODO : Should I redo, or just call ktapi.
		$this->getFromMemcache();

    }

    private function storeInMemcache()
    {
    	$key = ACCOUNT_NAME . '_bulkaction';

    }

    private function getFromMemcache()
    {
    	$key = ACCOUNT_NAME . '_bulkaction';

    }

	private function hitThreshold()
	{
		if(($this->numDocuments > $this->threshold[$this->action]['documents']) ||
			($this->numFolders > $this->threshold[$this->action]['folders'])) {
				return true;
		}

		return false;
	}

	private function getFolderDocuments($folderId)
	{
		// Get all parent folder documents
		$query = "SELECT COUNT(id) FROM documents WHERE folder_id = '$folderId';";
		$results = DBUtil::getOneResultKey($query, 'id');
		if($results)
		{
			$this->numDocuments + $results['id'];
		}
	}

	private function getFolderSubFolders($folderId, $folders)
	{
		// Get all parent folder sub folders
		$whereClause = "parent_folder_ids = '{$folderId}' OR parent_folder_ids LIKE '{$folderId},%' OR parent_folder_ids LIKE '%,{$folderId},%' OR parent_folder_ids LIKE '%,{$folderId}' ";
		$query = "SELECT id, parent_folder_ids, linked_folder_id FROM folders WHERE $whereClause";
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

	private static function isBulkActionInProgress($folderIdsPath)
	{
		$folderIdsPath = explode(',', $folderIdsPath);
		$key = ACCOUNT_NAME . '_bulkaction';
		$memcache = KTMemcache::getKTMemcache();
		if(!$memcache->isEnabled()) return ;
	    $bulkActions = $memcache->get($key);
	    $bulkActions = unserialize($bulkActions);
	    if($bulkActions) {
		    foreach ($bulkActions as $action => $folderIds) {
		    	foreach ($folderIds as $folderId) {
			    	if(in_array($folderId, $folderIdsPath)) {
			    		return $action;
			    	}
		    	}
		    }
	    }

	    return false;
	}


}

?>