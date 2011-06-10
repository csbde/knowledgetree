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
	
    public function is_reasons_enabled() {
    	global $default;
        
    	if($default->enableESignatures) { 
    		$this->addResponse('success', 'esign');
			return true;
    	}
    	$oKTConfig = KTConfig::getSingleton();
    	if($oKTConfig->get('actionreasons/globalReasons')) { 
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
		$reason = (isset($params['reason']) && !empty($params['reason'])) ? $params['reason'] : _kt('Document Copied.');
        $targetFolderId = str_replace('folder_', '', $params['targetFolderId']);
        $documentId = $params['documentId'];
        
        $ktapi = $this->KT;
        $document = KTAPI_Document::get($ktapi, $documentId);
        
        if(PEAR::isError($document)) {
        	$error = $document->getMessage();
        	$result = array('type' => 'fatal', 'error' => $error);
        	$this->addResponse('result', json_encode($result));
        	return;
        }
        
        $folder = KTAPI_Folder::get($ktapi, $targetFolderId);
        
        if(PEAR::isError($folder)) {
        	$error = $folder->getMessage();
        	$result = array('type' => 'fatal', 'error' => $error);
        	$this->addResponse('result', json_encode($result));
        	return;
        }
        
        if($action == 'move') {
        	$result = $document->move($folder, $reason);
        	$newDocument = $document;
        } else {
        	$result = $document->copy($folder, $reason);
        	$newDocument = $result;
        }
        
        if(PEAR::isError($result)) {
        	$error = $result->getMessage();
        	$result = array('type' => 'error', 'error' => $error);
        	$this->addResponse('result', json_encode($result));
        	return;
        }
        
        $newDocId = $newDocument->documentid;
        
        $url = KTUtil::ktLink('view.php', '', 'fDocumentId='.$newDocId);
        
        $result = array('type' => 'success', 'newDocId' => $newDocId, 'url' => $url);
    	$this->addResponse('result', json_encode($result));
	}
    
    public function getFolderStructure($params)
    {
        global $default;
        $default->log->error('DEBUG. HERE!');

        $ktapi = $this->KT;
        $folder_id = str_replace('folder_', '', $params['id']);
        $options = array('permission' => KTAPI_PERMISSION_WRITE);
        $totalItems = 0;
        $contents = $ktapi->get_folder_contents($folder_id, '1', 'F', $totalItems, $options);
        $nodes = $this->formatTreeStructure($contents['results']);
        
        if($folder_id != 1) {
        	$nodes = $nodes[0]['children'];
        }
        
        $this->addResponse('nodes', json_encode($nodes));
    }
    
    private function formatTreeStructure($structure)
    {
    	$children = $this->formatChildren($structure['items']);
    	$attributes = array('id' => 'folder_'.$structure['folder_id']);
    	
    	$nodes = array();
    	$nodes[] = array('data' => $structure['folder_name'], 'state' => 'open', 'children' => $children, 'attr' => $attributes);
    	return $nodes;
    }
    
    private function formatChildren($node)
    {
    	$tree = array();
    	foreach ($node as $nodeItem) {
    		$children = $this->formatChildren($nodeItem['items']);
    		$attributes = array('id' => 'folder_'.$nodeItem['id']);
    		$metadata = "node_{$nodeItem['id']}";
    		$tree[] = array('data' => $nodeItem['title'], 'state' => 'closed', 'children' => $children, 
    			'attr' => $attributes, 'metadata' => $metadata);
    	}
    	return $tree;
    }

}
?>