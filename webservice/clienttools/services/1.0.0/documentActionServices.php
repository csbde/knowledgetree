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

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/views/viewactionsutil.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');

class documentActionServices extends client_service {
	
	public function run_action($params) {
		$classaction = $params['action'];
		$classname = $params['name'];
		$classpath = $params['class'];
		$classpath = str_replace('/./', '/', $classpath);
		if(file_exists($classpath)) {
			require_once($classpath);
			$class = new $classname();
			$class->$classaction($params);
		}
		
	}
	
	public function checkout_download($params) {
		$response = array();
		if($this->checkout($params)) {
			$this->addResponse('success', 'Document checked out.');
		} else {
			$this->addError('Failed to checkout document.');
		}
		
		return true;
	}
	
	public function checkout($params) {
		$response = array();
		$iDocumentID = $params['documentId'];
		$oDocument = Document::get($iDocumentID);
		$oUser = User::get($_SESSION['userID']);
        $defaultCheckoutMessage = _kt('Document Checked Out.');
        $reason = $defaultCheckoutMessage . (isset($params['reason']) ? "\n\n{$params['reason']}" : '');

        DBUtil::startTransaction();
        $res = KTDocumentUtil::checkout($oDocument, $reason, $oUser);
        if (PEAR::isError($res)) {
        	DBUtil::rollback();
        	$this->addError(json_encode($response));
        	
        	return false;
        }
    	DBUtil::commit();
    	$this->addResponse('success', json_encode($response));
    	
        return true;
	}
	
	public function checkout_cancel($params) {
		$response = array();
		$iDocumentID = $params['documentId'];
		$oDocument = Document::get($iDocumentID);
		$oUser = User::get($_SESSION['userID']);
        DBUtil::startTransaction();
        // actually do the checkin.
        $oDocument->setIsCheckedOut(0);
        $oDocument->setCheckedOutUserID(-1);
        $res = $oDocument->update();
        if (PEAR::isError($res) || ($res === false)) {
            DBUtil::rollback();
        	$this->addError(json_encode($response));
        	
        	return false;
        }
        // checkout cancelled transaction
        $defaultCancelMessage = _kt('Document Checkout Cancelled.');
        $reason = $defaultCancelMessage . (isset($params['reason']) ? "\n\n{$params['reason']}" : '');
        $oDocumentTransaction = new DocumentTransaction($oDocument, $reason, 'ktcore.transactions.force_checkin');
        $res = $oDocumentTransaction->create();
        if (PEAR::isError($res) || ($res === false)) {
            DBUtil::rollback();
        	$this->addError(json_encode($response));
        	
        	return false;
        }
    	DBUtil::commit();
    	$this->addResponse('success', json_encode($response));
    	
		return true;
    }
	
    public function checkESignaturesEnabled($params) 
    {
    	$config = KTConfig::getSingleton();
        
    	if ($config->get('e_signatures/enableESignatures')) { 
    		$this->addResponse('success', 'esign');
			return true;
    	}
    	
    	if ($config->get('actionreasons/globalReasons')) { 
    		$this->addResponse('success', 'reason');
			return true;
    	}
    	$this->addResponse('success', false);

		return true;
    }
	
	public function is_document_checkedout($params) {
    	global $default;
        
        if (isset($params['documentId'])) {
            $oDocument = Document::get($params['documentId']);
            
            // Check for document error
            $this->addResponse('checkedout', $oDocument->getIsCheckedOut() ? '1': '0');
        } else {
            $this->addResponse('checkedout', '0');
        }
		
		return true;
    }
    
	public function checkin($params) {
		$response = array();
		$iDocumentID = $params['documentId'];
		$oDocument = Document::get($iDocumentID);
		$oUser = User::get($_SESSION['userID']);
        // If the filename is different to the original check if "Force Original Filename" is set and return an error if it is.
        $docFileName = $oDocument->getFilename();
        $data = $res['results'];
        if ($data['file']['name'] != $docFileName) {
            global $default;

            if ($default->disableForceFilenameOption) {
                $extra_errors['file'] = sprintf(_kt('The file you uploaded was not called "%s". The file must have the same name as the original file.'), htmlentities($docFileName,ENT_QUOTES,'UTF-8'));
            } else if ($data['forcefilename']) {
                $extra_errors['file'] = sprintf(_kt('The file you uploaded was not called "%s". If you wish to change the filename, please set "Force Original Filename" below to false. '), htmlentities($docFileName,ENT_QUOTES,'UTF-8'));
            }
        }

        if (!empty($res['errors']) || !empty($extra_errors)) {
        	$response['extra_errors'] = $extra_errors;
	        $this->addError(json_encode($response));
	        
	        return true;
        }

        $defaultCheckinMessage = _kt('Document Checked In.');
        $sReason = $defaultCheckinMessage . (isset($params['reason']) ? "\n\n{$params['reason']}" : '');

        $sCurrentFilename = $docFileName;
        $sNewFilename = $data['file']['name'];
        $aOptions = array();

        if ($data['major_update']) {
            $aOptions['major_update'] = true;
        }

        if ($sCurrentFilename != $sNewFilename) {
            $aOptions['newfilename'] = $sNewFilename;
        }

        // document checkin for the new storage drivers requires the document to be first uploaded
        // to the temp directory from the php upload directory or the checkin will fail
        $oStorage = KTStorageManagerUtil::getSingleton();
        $oKTConfig = KTConfig::getSingleton();
        $sTempFilename = $oStorage->tempnam($oKTConfig->get("urls/tmpDirectory"), 'kt_storecontents');
        $oStorage->uploadTmpFile($data['file']['tmp_name'], $sTempFilename);
        $data['file']['tmp_name'] = $sTempFilename;
        $res = KTDocumentUtil::checkin($oDocument, $data['file']['tmp_name'], $sReason, $oUser, $aOptions);
        if (PEAR::isError($res)) {
	        $this->addError(json_encode('Pear Error on Checkin: '.$res->getMessage()));
	        
	        return false;
        }
        $this->addResponse('success', json_encode($response));
        
        return true;
    }
    
    function refresh_actions($params) {
		$iDocumentID = $params['documentId'];
		$sLocation = $params['location'];
		$oUser = User::get($_SESSION['userID']);
		$oDocument = Document::get($iDocumentID);
		$oViewUtil = new ViewActionsUtil();
		$oViewUtil->initActions($oDocument, $oUser);
        $oViewUtil->createButtons();
		$response = $oViewUtil->renderActions($sLocation);
    	$this->addResponse('success', $response);
    	
    	return true;
    }
    
	public function doCopy($params)
	{
		$action = $params['action'];
		$reason = (isset($params['reason']) && !empty($params['reason'])) ? $params['reason'] : false;
        $targetFolderId = str_replace('folder_', '', $params['targetFolderId']);
        $documentId = $params['documentId'];
        
        $ktapi = $this->KT;
        $document = KTAPI_Document::get($ktapi, $documentId);
        
        if (PEAR::isError($document)) {
        	$error = $document->getMessage();
        	$result = array('type' => 'fatal', 'error' => $error);
        	$this->addResponse('result', json_encode($result));
        	return;
        }
        
        if (is_numeric($targetFolderId)) {
	        $folder = KTAPI_Folder::get($ktapi, $targetFolderId);
	        
	        if (PEAR::isError($folder)) {
	        	$error = $folder->getMessage();
	        	$result = array('type' => 'fatal', 'error' => $error);
	        	$this->addResponse('result', json_encode($result));
	        	return;
	        }
        }
        
        $redirectTarget = 'document';
        switch ($action) {
        	case 'delete':
        		$reason = ($reason === false) ? _kt('Document deleted') : $reason;
        		$docDetails = $document->get_detail();
        		$folderId = $docDetails['folder_id'];
        		$result = $document->delete($reason);
        		$redirectTarget = 'folder';
    			break;
    			
        	case 'archive':
        		$reason = ($reason === false) ? _kt('Document archived') : $reason;
        		$docDetails = $document->get_detail();
        		$folderId = $docDetails['folder_id'];
        		$result = $document->archive($reason);
        		$redirectTarget = 'folder';
        		break;
        		
        	case 'immutable':
        		$result = $document->immute();
        		$newDocument = $document;
        		$msg = _kt('Success. The document will be updated shortly.');
        		break;
    			
        	case 'copy':
        		$reason = ($reason === false) ? _kt('Document copied') : $reason;
        		$result = $document->copy($folder, $reason);
        		$newDocument = $result;
        		$msg = _kt('Success. You will be redirected to the new document.');
        		break;
        		
        	case 'move':
        		$newName = '';
        		if ($params['newname'] != 'undefined') {
        			$newName = urldecode($params['newname']);
        		}
        		
        		$newFilename = '';
        		if ($params['newfilename'] != 'undefined') {
        			$newFilename = urldecode($params['newfilename']);
        		}
        		
        		$reason = ($reason === false) ? _kt('Document moved') : $reason;
        		$result = $document->move($folder, $reason, $newName, $newFilename);
        		$newDocument = $document;
        		$msg = _kt('Success. You will be redirected to the updated document shortly.');
        		break;
        		
        	default:
        		$error = _kt('Please refresh the page and try again.');
	        	$result = array('type' => 'error', 'error' => $error);
	        	$this->addResponse('result', json_encode($result));
	        	return;
        }
        
        if (PEAR::isError($result)) {
        	$error = $result->getMessage();
        	
        	// special case - if the file exists then a new title and/or filename need to be specified.
        	if ($action == 'move' && strpos($error, 'already exists in your chosen folder') !== false) {
        		$error .= $this->getMoveRenameForm($document, $error);
        	}
        	
        	$result = array('type' => 'error', 'error' => $error);
        	$this->addResponse('result', json_encode($result));
        	return;
        }

        $newDocId = '';
        if ($redirectTarget == 'folder'){
        	$url = KTUtil::kt_clean_folder_url($folderId);
        	$msg = _kt('Success. You will be redirected shortly.');
        } 
        else {
	        $newDocId = $newDocument->documentid;
	        $url = KTUtil::kt_clean_document_url($newDocId);
        }
        
        $result = array('type' => 'success', 'newDocId' => $newDocId, 'url' => $url, 'msg' => $msg);
    	$this->addResponse('result', json_encode($result));
	}
	
	public function getMoveRenameForm($document, $error)
	{
		$properties = $document->get_detail();
		
		$fields = '<div class="" style="padding: 8px;">';
		if (strpos($error, 'title') !== false) {
			$fields .= '<label for="newname"><span class="required"></span><b>' . _kt('Title') . '</b></label><br />';
			$fields .= "<input name='newname' id='newname' value='{$properties['title']}'><br />";
		}
		
		if (strpos($error, 'filename') !== false) {
			$fields .= '<label for="newfilename"><span class="required"></span><b>' . _kt('Filename') . '</b></label><br />';
			$fields .= "<input name='newfilename' id='newfilename' value='{$properties['filename']}'><br />";
		}
		
		$fields .= '</div>';
		
		return $fields;
	}
    
	public function doBulkCopy($params)
	{
		$action = $params['action'];
		$targetFolderId = str_replace('folder_', '', $params['targetFolderId']);
		
		$itemList = $params['itemList'];
        $organisedItemList = $this->formatItemList($itemList);

        switch ($action) {
        	case 'copy':
        		$reason = (isset($params['reason']) && !empty($params['reason'])) ? $params['reason'] : _kt('Bulk copy performed');
        		break;
        	case 'move':
        		$reason = (isset($params['reason']) && !empty($params['reason'])) ? $params['reason'] : _kt('Bulk move performed');
        		break;
        	case 'delete':
        		$reason = (isset($params['reason']) && !empty($params['reason'])) ? $params['reason'] : _kt('Bulk delete performed');
        		break;
        	case 'archive':
        		$reason = (isset($params['reason']) && !empty($params['reason'])) ? $params['reason'] : _kt('Bulk archive performed');
        		break;
    		default:
    			$reason = '';
        }
        
        
        $ktapi = $this->KT;
        $actionResult = $ktapi->performBulkAction($action, $organisedItemList, $reason, $targetFolderId);
        $url = KTUtil::kt_clean_folder_url($targetFolderId);
                                      
        if ($actionResult['status_code'] == 1) {
        	$error = $actionResult['message'];
	        
        	$result = array('type' => 'fatal', 'error' => $error, 'url' => $url);
	    	$this->addResponse('result', json_encode($result));
	    	
	    	return true;
        }
        
        if (!empty($actionResult['results'])) {
        	$error = _kt("The following items failed:");
        	$failed = $this->formatActionResults($actionResult['results'], $url);
        	
        	$result = array('type' => 'partial', 'error' => $error, 'failed' => $failed, 'url' => $url);
        	$this->addResponse('result', json_encode($result));
        	
        	return true;
        }
        
        $msg = _kt('Success. You will be redirected shortly.');
        
        $result = array('type' => 'success', 'url' => $url, 'msg' => $msg);
        $this->addResponse('result', json_encode($result));
        
        return true;
	}
	
	public function formatItemList($itemList = array())
	{
		$itemList = is_array($itemList) ? $itemList : array();
		$organisedItemList = array('documents' => array(), 'folders' => array());
		
        foreach ($itemList as $item) {
        	$parts = array();
        	$parts = str_split($item, 12);
        	
        	$type = ($parts[0] == 'selection_d_') ? 'documents' : 'folders';
        	
    		$organisedItemList[$type][] = $parts[1];
        }
        
        return $organisedItemList;
	}
	
	public function formatActionResults($results, $url = '')
	{
		$html = '';
		
		if (isset($results['folders'])) {
			foreach ($results['folders'] as $item) {
				$html .= "<tr><td><span class='contenttype folder'><a href='{$item['object']['clean_uri']}'>
				{$item['object']['folder_name']}</a></span><br />{$item['reason']}</td></tr>";
			}
		}
		
		if (isset($results['docs'])) {
			foreach ($results['docs'] as $item) {
				$html .= "<tr><td><span class='contenttype {$item['object']['mime_icon_path']}'><a href='{$item['object']['clean_uri']}'>
				{$item['object']['title']}</a></span><br />{$item['reason']}</td></tr>";
			}
		}
		
		$html .= '<tr><td class="ul_actions" align="right" valign="bottom">
			<span id="copy-spinner" class="copy-spinner none">&nbsp;</span>
        	<input id="select-btn" class="ul_actions_btns" type="button" value="Continue" onClick="kt.app.copy.showSpinner(); kt.app.copy.redirect(\''.$url.'\');" />
        	<input id="select-btn-return" class="ul_actions_btns" type="button" value="Return to Folder" onClick="kt.app.copy.reload();" />
    		</td></tr>';
		
		return $html;
	}
	
    public function getFolderStructure($params)
    {
        $folderId = str_replace('folder_', '', $params['id']);
        
        // On first load of the tree the folderId is set to 'initial-load' which loads the root folder and immediate sub-folders.
        // The tree is set to "load_open" which means it will try to load the node based on the node attribute / folderId, this will
        // create a loading loop where the Root Folder will be added as a sub-folder to the preceding Root Folder, infinitely.
        // For this reason a folderId of 1 must return an empty array.
        if ($folderId == 1) {
        	$this->addResponse('nodes', json_encode(array()));
        	return ;
        }
        
        if ($folderId == 'initial-load') {
        	$folderId = 1;
        }
        
        if (!is_numeric($folderId)) {
        	$this->addResponse('nodes', json_encode(array()));
        	return ;
        }
        
        $ignoreIds = $this->formatItemList($params['ignoreIds']);
        $ignoreIds = $ignoreIds['folders'];
        
        $options = array('permission' => KTAPI_PERMISSION_WRITE);
        $totalItems = 0;
        
        $ktapi = $this->KT;
        $contents = $ktapi->get_folder_contents($folderId, '1', 'F', $totalItems, $options);
        $nodes = $this->formatTreeStructure($contents['results'], $ignoreIds);
    	
    	if ($folderId == 1) {
    		$nodes[] = $this->getOrphanedFolders($ignoreIds);
    	} 
    	else {
    		$nodes = $nodes[0]['children'];
    	}
    	
        $this->addResponse('nodes', json_encode($nodes));
    }
    
    private function formatTreeStructure($structure, $ignoreIds = null)
    {
    	$children = $this->formatChildren($structure['items'], $ignoreIds);
    	
    	$attributes = array('id' => 'folder_'.$structure['folder_id']);
    	
    	$nodes = array();
    	$nodes[] = array('data' => $structure['folder_name'], 'state' => 'open', 'children' => $children, 'attr' => $attributes);
    	
    	return $nodes;
    }
    
    private function formatChildren($node, $ignoreIds = null)
    {
    	$tree = array();
    	foreach ($node as $nodeItem) {
    		if (in_array($nodeItem['id'], $ignoreIds)) {
    			continue;
    		}
    		$children = $this->formatChildren($nodeItem['items'], $ignoreIds);
    		$attributes = array('id' => 'folder_'.$nodeItem['id']);
    		$metadata = "node_{$nodeItem['id']}";
    		$name = (isset($nodeItem['title'])) ? $nodeItem['title'] : $nodeItem['folder_name'];
    		$tree[] = array('data' => $name, 'state' => 'closed', 'children' => $children, 
    			'attr' => $attributes, 'metadata' => $metadata);
    	}
    	
    	return $tree;
    }

    private function getOrphanedFolders($ignoreIds = null)
    {
    	$user = User::get($_SESSION['userID']);
    	$ktapi = $this->KT;
    	$orphans = $ktapi->get_orphaned_folders($user);
    	
    	if ($orphans['status_code'] == 0 || empty($orphans['results'])) {
    		return array();
    	}
    	
    	$treeOrphans = array();
    	$treeOrphans['data'] = 'Orphaned Folders';
    	$treeOrphans['state'] = 'open';
    	$treeOrphans['attributes'] = array('id' => 'folder_orphans');
    	$treeOrphans['children'] = $this->formatChildren($orphans['results'], $ignoreIds);
    	
    	return $treeOrphans;
    }
}
?>
