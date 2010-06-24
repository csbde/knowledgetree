<?php

	require_once('ktapi/ktapi.inc.php');
	require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');
	
	function uploadFile($fileTmp, $fileName, $folderID = 1) {
		
		global $default;
		
		$default->log->debug("DRAGDROP Uploading file $fileTmp $fileName");
		
    	$oStorage = KTStorageManagerUtil::getSingleton();
    	
    	$oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");

        $sS3TempFile = $oStorage->tempnam($sBasedir, 'kt_storecontents');
        
        $oStorage->uploadTmpFile($fileTmp, $sS3TempFile);
        
        //$aFile = $this->oValidator->validateFile($extra_d['file'], $aErrorOptions);
        //$sTitle = $extra_d['document_name'];
        
        $oFolder = Folder::get($folderID); 
        if (PEAR::isError($oFolder)) {
       		$default->log->error("DRAGDROP Folder $folderID: {$oFolder->getMessage()}");
       		return false;      
        }      
        
        $oUser = User::get($_SESSION['userID']);
        if (PEAR::isError($oUser)) {
       		$default->log->error("DRAGDROP User {$_SESSION['userID']}: {$oUser->getMessage()}");
       		return false;      
        } 
        
        $oDocumentType = DocumentType::get(1);
        if (PEAR::isError($oDocumentType)) {
       		$default->log->error("DRAGDROP DocumentType: {$oDocumentType->getMessage()}");
       		return false;      
        } 
		
        $aOptions = array(
            'temp_file' => $sS3TempFile,
            'documenttype' => $oDocumentType,
            'metadata' => array(),
            'description' => $fileName,
            'cleanup_initial_file' => true
        );
        
        $default->log->debug("DRAGDROP Folder $folderID User {$oUser->getID()}");
        
        $oDocument =& KTDocumentUtil::add($oFolder, $fileName, $oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $default->log->error("DRAGDROP Document add: {$oDocument->getMessage()}");
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
			global $default;
			$default->log->error("DRAGDROP Column data empty");
			return false;
		}
	}
	
	
	//	
	$fileTmp = $_FILES['user_file']['tmp_name'][0];
	$fileName = $_FILES['user_file']['name'][0];
	$folderID = $_REQUEST['fFolderId'];
	
	if (!is_numeric($folderID)) {
		global $default;		
		$default->log->error("DRAGDROP error getting folder ID");
		exit(1);
	}
		
	$folderID = (int)$folderID;
	
	$oDocument = uploadFile($fileTmp, $fileName, $folderID);
	
	if ($oDocument === false)
	{
		echo '<tr><td></td><td colspan=3>ERROR: document could not be uploaded</td></tr>';
		exit(1);		
	}
	
	$output = getColumnData($oDocument);
	
	echo($output);
	
	exit(0);

?>