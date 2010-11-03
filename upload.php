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
	require_once('ktapi/ktapi.inc.php');
	require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');

	function uploadFile($fileTmp, $fileName, $folderID = 1, $documentTypeID = 1, $metadata = array()) {
		$GLOBALS['default']->log->debug("DRAGDROP Uploading file $fileTmp $fileName");

    	$oStorage = KTStorageManagerUtil::getSingleton();

    	$oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");

        $sS3TempFile = $oStorage->tempnam($sBasedir, 'kt_storecontents');

        $options['uploaded_file'] = 'true';

        $oStorage->uploadTmpFile($fileTmp, $sS3TempFile, $options);

        $oFolder = Folder::get($folderID);
        if (PEAR::isError($oFolder)) {
       		$GLOBALS['default']->log->error("DRAGDROP Folder $folderID: {$oFolder->getMessage()}");
       		return false;
        }

        $oUser = User::get($_SESSION['userID']);
        if (PEAR::isError($oUser)) {
       		$GLOBALS['default']->log->error("DRAGDROP User {$_SESSION['userID']}: {$oUser->getMessage()}");
       		return false;
        }

        $oDocumentType = DocumentType::get($documentTypeID);
        if (PEAR::isError($oDocumentType)) {
       		$GLOBALS['default']->log->error("DRAGDROP DocumentType: {$oDocumentType->getMessage()}");
       		return false;
        }

        //remove extension to generate title
        $aFilename = explode('.', $fileName);
        $cnt = count($aFilename);
        $sExtension = $aFilename[$cnt - 1];
        $title = preg_replace("/\.$sExtension/", '', $fileName);

        $aOptions = array(
            'temp_file' => $sS3TempFile,
            'documenttype' => $oDocumentType,
            'metadata' => $metadata,
            'description' => $title,
            'cleanup_initial_file' => true
        );

        $GLOBALS['default']->log->debug("DRAGDROP Folder $folderID User {$oUser->getID()}");



        $oDocument =& KTDocumentUtil::add($oFolder, $fileName, $oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $GLOBALS['default']->log->error("DRAGDROP Document add: {$oDocument->getMessage()}");
       		return false;
        }

        return $oDocument;
	}
	
	function getMetadata()
	{
		/*$t[0] = array('id'=>'author', 'value'=>'martin');
		$t[1] = array('id'=>'date', 'value'=>'25-10-2010');
		$GLOBALS['default']->log->debug("DRAGDROP Document t ".print_r($t, true));
		$test = array('metadata'=>$t);
		$GLOBALS['default']->log->debug("DRAGDROP Document test ".print_r($test, true));
		
		$jsonencoded = json_encode($test);
		
		$GLOBALS['default']->log->debug("DRAGDROP Document jsonencoded $jsonencoded");
		$jsondecode = json_decode($jsonencoded, true);
		$GLOBALS['default']->log->debug("DRAGDROP Document jsonencoded decoded ".print_r($jsondecode, true));*/
		
	    if(isset($_REQUEST['metadata'])){
	    	$metadata = $_REQUEST['metadata'];
	    	$GLOBALS['default']->log->debug("DRAGDROP Document metadata ".print_r($metadata, true));
	    	
	    	$json = json_decode($metadata, true);
	    	
	    	$GLOBALS['default']->log->debug("DRAGDROP Document json ".print_r($json, true));
	       	
	    	$MDPack = array();
	    	
	    	foreach($json as $element) {
	    		foreach($element as $MD) {
		       		$GLOBALS['default']->log->debug("DRAGDROP Document metadata ".print_r($MD, true));
		       		$GLOBALS['default']->log->debug("DRAGDROP Document metadata id ".(int)$MD['id']);
		       		$GLOBALS['default']->log->debug("DRAGDROP Document metadata value ".$MD['value']);
		       		$oField = DocumentField::get((int)$MD['id']);
			    		$MDPack[] = array(
			    			$oField,
			    			$MD['value']
		                );
		       	}
	    	}
	       	
	       	return $MDPack;
	    }
	    
	    return array();

	    /*$id = 1;
	    $uri = $_REQUEST['cleanFolderId'];

		// Check for slash
		if (substr($uri, 0, 1) == '/') {
		    $uri = substr($uri, 1);
		}

		// Remove Query String
		$uri = preg_replace('/(\?.*)/i', '', $uri);

		if (substr($uri, 0, 2) == '00') {
			$id = KTUtil::decodeId(substr($uri, 2));
		}

		return $id;*/
	}
	
	function getDocumentTypeId()
	{		
	    if(isset($_REQUEST['documentTypeID'])){
	    	$GLOBALS['default']->log->debug("DRAGDROP getDocumentTypeId ".$_REQUEST['documentTypeID']);
	       return (int)$_REQUEST['documentTypeID'];
	    }    
	    
	    
		return 1;
	}

	function getId()
	{
		$GLOBALS['default']->log->debug("DRAGDROP entire request ".print_r($_REQUEST, true));
		
	    if(isset($_REQUEST['folderID'])){
	       return (int)$_REQUEST['folderID'];
	    }

	    $id = 1;
	    $uri = $_REQUEST['cleanFolderID'];

		// Check for slash
		if (substr($uri, 0, 1) == '/') {
		    $uri = substr($uri, 1);
		}

		// Remove Query String
		$uri = preg_replace('/(\?.*)/i', '', $uri);

		if (substr($uri, 0, 2) == '00') {
			$id = KTUtil::decodeId(substr($uri, 2));
		}

		return $id;
	}

	// HTTP headers for no cache etc
	header('Content-type: text/plain; charset=UTF-8');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// Settings
	$targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "tmp";

	$cleanupTargetDir = false; // Remove old files
	$maxFileAge = 60 * 60; // Temp file age in seconds

	// 5 minutes execution time
	@set_time_limit(5 * 60);
	// usleep(5000);

	// Get parameters
	$chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
	$chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
	$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

	// Clean the fileName for security reasons
	//$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

	// Create target dir
	if (!file_exists($targetDir))
		@mkdir($targetDir);

	// Remove old temp files
	if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
		while (($file = readdir($dir)) !== false) {
			$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

			// Remove temp files if they are older than the max age
			if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge))
				unlink($filePath);
		}

		closedir($dir);
	} else
		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

	/*if (isset($_SERVER["HTTP_METADATA"])) {
		$tempmeta = $_SERVER["HTTP_METADATA"];
		$GLOBALS['default']->log->debug("DRAGDROP metadata ".print_r($tempmeta, true));
	}*/
	
	//$GLOBALS['default']->log->debug("DRAGDROP getallheaders " .print_r($_SERVER, true));
		
	// Look for the content type header
	if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

	if (isset($_SERVER["CONTENT_TYPE"]))
		$contentType = $_SERVER["CONTENT_TYPE"];

	if (strpos($contentType, "multipart") !== false) {
		if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen($_FILES['file']['tmp_name'], "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($out);
				unlink($_FILES['file']['tmp_name']);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
	} else {
		// Open temp file
		$fileTmp = tempnam($targetDir, 'kt_storecontents');

		$out = fopen($fileTmp, $chunk == 0 ? "wb" : "ab");
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");

			if ($in) {
				while ($buff = fread($in, 4096))
					fwrite($out, $buff);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

			fclose($out);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	}

	$folderID = getId();
	
	$GLOBALS['default']->log->debug("DRAGDROP folderID resolves to $folderID");
	
	$documentTypeID = getDocumentTypeId();
	
	$GLOBALS['default']->log->debug("DRAGDROP documentTypeID resolves to $documentTypeID");
	
	$metadata = getMetadata();
	
	$GLOBALS['default']->log->debug("DRAGDROP metadata resolves to ".print_r($metadata, true));
	
	if($folderID<=0){
		$GLOBALS['default']->log->error("DRAGDROP error getting folder ID");
		exit(1);
	}

	//$fileTmp = str_replace('\\','/',$targetDir.'/'.$fileName);
	
	//used to test failures
	//die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Document could not be uploaded", "filename":"'.$fileName.'"}, "id" : "id"}');

	$oDocument = uploadFile($fileTmp, $fileName, $folderID, $documentTypeID, $metadata);

	if ($oDocument === false)
	{
		die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Document could not be uploaded", "filename":"'.$fileName.'"}, "id" : "id"}');
	}

	//assemble the file's name
	$fileNameCutoff = 100;
	$fileName = $oDocument->getFileName();
	$fileName = (strlen($fileName)>$fileNameCutoff) ? substr($fileName, 0, $fileNameCutoff-3)."..." : $fileName;

	//get the icon path
	$mimetypeid = (method_exists($oDocument,'getMimeTypeId')) ? $oDocument->getMimeTypeId():'0';
	$iconFile = 'resources/mimetypes/newui/'.KTMime::getIconPath($mimetypeid).'.png';
	$iconExists = file_exists(KT_DIR.'/'.$iconFile);
	if($iconExists){
		$mimeIcon = str_replace('\\','/',$GLOBALS['default']->rootUrl.'/'.$iconFile);
		$mimeIcon = "background-image: url(".$mimeIcon.")";
	}else{
		$mimeIcon = '';
	}

	$oOwner = User::get($oDocument->getOwnerID());
	$oCreator = User::get($oDocument->getCreatorID());
	$oModifier = User::get($oDocument->getModifiedUserId());

	//assemble the item
	$item['id'] = $oDocument->getId();
	$item['owned_by'] = $oOwner->getName();
	$item['created_by'] = $oCreator->getName();
	$item['modified_by'] = $oModifier->getName();
	$item['filename'] = $fileName;
	$item['title'] = $oDocument->getName();
	$item['mimeicon'] = $mimeIcon;
	$item['created_date'] = $oDocument->getCreatedDateTime();
	$item['modified_date'] = $oDocument->getLastModifiedDate();

	$json['success'] = $item;

	echo(json_encode($json));

	//$documentID = $oDocument->getId();
	//$fileTitle = $oDocument->getName();

	//$output = '{"jsonrpc" : "2.0", "success" : {"id":"'.$documentID.'", "filename":"'.$fileName.'", "title":"'.$fileTitle.'", "owned_by":"'.$oOwner->getName().'", "created_by":"'.$oCreator->getName().'", "created_date":"'.$oDocument->getCreatedDateTime().'", "modified_by":"'.$oModifier->getName().'", "modified_date":"'.$oDocument->getLastModifiedDate().'", "mimeicon":"'.$mimeIcon.'"}, "id" : "id"}';

	exit(0);

?>