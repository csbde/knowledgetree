<?php

	require_once('ktapi/ktapi.inc.php');
	require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');

	function uploadFile($fileTmp, $fileName, $folderID = 1) {
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

        $oDocumentType = DocumentType::get(1);
        if (PEAR::isError($oDocumentType)) {
       		$GLOBALS['default']->log->error("DRAGDROP DocumentType: {$oDocumentType->getMessage()}");
       		return false;
        }

        $aOptions = array(
            'temp_file' => $sS3TempFile,
            'documenttype' => $oDocumentType,
            'metadata' => array(),
            'description' => $fileName,
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



	function getColumnData($oDocument){
		$oColumnRegistry =& KTColumnRegistry::getSingleton();
		$columns = $oColumnRegistry->getColumnsForView('ktcore.views.browse');

		if(is_array($columns) && !empty($columns)){

			$aDataRow = array();
			$aDataRow['type'] = 'document';
			$aDataRow['document'] = $oDocument;

			$output = '<tr class="dragdrop">';

			foreach($columns as $column) {
				$class = 'browse_column';
				if($column->name == 'title') {
					$class .= ' title sort_on dragdrop';
				}
				$data = $column->renderData($aDataRow);
				$output .= "<td class='$class'>$data</td>";
			}

			$output .= '</tr>';

			return $output;

		}else{
			$GLOBALS['default']->log->error("DRAGDROP Column data empty");
			return false;
		}
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

	$folderID = (int)$_REQUEST['fFolderId'];
	if($folderID<=0){
		$GLOBALS['default']->log->error("DRAGDROP error getting folder ID");
		exit(1);
	}

	//$fileTmp = str_replace('\\','/',$targetDir.'/'.$fileName);

	$oDocument = uploadFile($fileTmp, $fileName, $folderID);

	if ($oDocument === false)
	{
		die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Document could not be uploaded", "filename":"'.$fileName.'"}, "id" : "id"}');
	}

	$output = getColumnData($oDocument);

	echo($output);

	exit(0);

?>