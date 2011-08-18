<?php
/**
 * $Id$
 *
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
// TODO : Abstract and refactor with backgrounding process
require_once(KT_LIB_DIR . '/backgroundactions/BackgroundProcess.php');

class BackgroundAction extends BackgroundProcess
{
	public $accountName = "";
	public $userId = 0;

	private $action = "";
	private $list = array();
	private $reason = "";
	private $targetFolderId = 0;
	private $currentFolderId = 0;
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

	public function __construct($action, $userId, $list, $reason = '', $targetFolderId, $currentFolderId)
	{
		$this->action = $action;
		$this->userId = $userId;
		$this->list = $list;
		$this->reason = $reason;
		$this->targetFolderId = $targetFolderId;
		$this->currentFolderId = $currentFolderId;
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
		return "A Batch $action is in progress. Please try again later.";
	}

	public function saveEvent()
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
		$folderIds[$this->action][$this->currentFolderId] = $this->currentFolderId;
		$folderIds[$this->action][$this->targetFolderId] = $this->targetFolderId;
		// Store other folders in list
		foreach ($this->list['folders'] as $folderId) {
			$folderIds[$this->action][$folderId] = $folderId;
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
    	$this->saveEvent();
    	$script =  KT_DIR . '/plugins/ktcore/backgroundTasks/BulkActionTask.php';
    	$arguments = "{$this->userId} {$this->action} {$this->targetFolderId} {$this->currentFolderId} " . ACCOUNT_NAME;
    	$command = "{$phpPath} {$script} {$arguments} > /dev/null &";

    	KTUtil::pexec($command);
    }

    public function execute()
    {
    	$message = '';
    	$success = false;
		$cached = $this->getFromMemcache();
		$this->list = unserialize($cached['list']);
		$this->reason = unserialize($cached['reason']);
		$ktapi = new KTAPI(3);
		$user = User::get($this->userId);
		$session = $ktapi->start_system_session($user->getUsername());
		$response = $ktapi->performBulkAction($this->action, $this->list, $this->reason, $this->targetFolderId);
		if($response) {
			if($response['status_code'] == 0) {
				$success = true;
			} else {
				$message = $response['message'];
			}
		}
		$this->clearMemcacheInfo();
		$this->sendNotification($success, $user, $message);
    }

    public function clearMemcacheInfo()
    {
	    $memcache = KTMemcache::getKTMemcache();
	    if(!$memcache->isEnabled()) return ;
	    $key = ACCOUNT_NAME . '_bulkaction';
	    $bulkActions = $memcache->get($key);
	    $bulkActions = unserialize($bulkActions);
	    if(isset($bulkActions[$this->action][$this->currentFolderId])) {
	    	unset($bulkActions[$this->action][$this->currentFolderId]);
	    	$memcache->set($key, serialize($bulkActions));
	    }
	    if(isset($bulkActions[$this->action][$this->targetFolderId])) {
	    	unset($bulkActions[$this->action][$this->targetFolderId]);
	    	$memcache->set($key, serialize($bulkActions));
	    }
    }

    private function storeInMemcache()
    {
    	$memcache = KTMemcache::getKTMemcache();
    	if(!$memcache->isEnabled()) return ;
    	$key = ACCOUNT_NAME . '_' . $this->userId . '_bulkaction_list_' . $this->targetFolderId;
		$memcache->set($key, serialize($this->list));
		$key = ACCOUNT_NAME . '_' . $this->userId . '_bulkaction_reason_' . $this->targetFolderId;
		$memcache->set($key, serialize($this->reason));
    }

    private function getFromMemcache()
    {
    	$memcache = KTMemcache::getKTMemcache();
    	if(!$memcache->isEnabled()) return ;
    	$key = ACCOUNT_NAME . '_' . $this->userId . '_bulkaction_list_' . $this->targetFolderId;
    	$cached['list'] = $memcache->get($key);
		$key = ACCOUNT_NAME . '_' . $this->userId . '_bulkaction_reason_' . $this->targetFolderId;
		$cached['reason'] = $memcache->get($key);

		return $cached;
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
		$memcache = KTMemcache::getKTMemcache();
		if(!$memcache->isEnabled()) return ;
		$folderIdsPath = explode(',', $folderIdsPath);
		$key = ACCOUNT_NAME . '_bulkaction';
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

	private function sendNotification($success = true, $user, $customMessage = '')
    {
        global $default;

        if (PEAR::isError($user)) {
            $default->log->error('Permissions: Error getting user - ' . $user->getMessage());
            return;
        }

        $name = $user->getName();
        $emailAddress = $user->getEmail();

        $folder = Folder::get($this->targetFolderId);
        $folderName = $folder->getName();

        $folderLink = KTUtil::kt_clean_folder_url($this->targetFolderId);

        $message = _kt("Dear {$name},") . '<br/><br/>';

        $link = "<a href='$folderLink'>{$folderName}</a>";

        if ($success) {
            $subject = _kt("Batch {$this->action} action successfully completed.");
            $message .= _kt("Your request has completed successfully in the folder {$link}. ");
        }
        else {
            $subject = _kt("Batch {$this->action} action failed.");
            $message .= _kt("Your request to batch {$this->action} in the folder {$link} has failed. ");
            $message .= '<br/><br/>';
            $message .= _kt("Reason : $customMessage");

            $default->log->error("Batch {$this->action}: failed");
        }

        $email = new Email();
        $email->send($emailAddress, $subject, $message);
    }
}

?>