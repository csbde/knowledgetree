<?php
class siteapi extends client_service{
	
	function uploadFile($params) {
		//$fileTmp, $fileName, $folderID = 1, $documentTypeID = 1, $metadata = NULL) {
		//$GLOBALS['default']->log->debug("DRAGDROP Uploading file $fileTmp $fileName");

		$document = $params['documents'];
		
		file_put_contents('uploadFile.txt', "\n\r".print_r($document, true), FILE_APPEND);
		
    	$oStorage = KTStorageManagerUtil::getSingleton();
    	
    	$folderID = $document['folderID'];
    	
    	$documentTypeID = $document['docTypeID'];
    	
    	$fileName = $document['fileName'];
    	
    	$sS3TempFile  = $document['s3TempFile'];
    	
    	$metadata = $document['metadata'];
    	
    	$aString = "\n\rfolderID: $folderID documentTypeID: $documentTypeID fileName: $fileName S3TempFile: $S3TempFile";
    	
    	file_put_contents('uploadFile.txt', $aString, FILE_APPEND);

    	//$oKTConfig =& KTConfig::getSingleton();
       	//$sBasedir = $oKTConfig->get("urls/tmpDirectory");

        //$sS3TempFile = $oStorage->tempnam($sBasedir, 'kt_storecontents');

        $options['uploaded_file'] = 'true';

        //$oStorage->uploadTmpFile($fileTmp, $sS3TempFile, $options);

        $oFolder = Folder::get($folderID);
        if (PEAR::isError($oFolder)) {
        	file_put_contents('uploadFile.txt', "\n\rFolder $folderID: {$oFolder->getMessage()}", FILE_APPEND);
       		//return false;
        }

        //TODO!!
        $oUser = User::get($_SESSION['userID']);
        if (PEAR::isError($oUser)) {
        	file_put_contents('uploadFile.txt', "\n\rUser {$_SESSION['userID']}: {$oUser->getMessage()}", FILE_APPEND);
       		//return false;
        }

        $oDocumentType = DocumentType::get($documentTypeID);
        if (PEAR::isError($oDocumentType)) {
        	file_put_contents('uploadFile.txt', "\n\rDocumentType: {$oDocumentType->getMessage()}", FILE_APPEND);
       		//return false;
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

        //$GLOBALS['default']->log->debug("DRAGDROP Folder $folderID User {$oUser->getID()}");

        $oDocument =& KTDocumentUtil::add($oFolder, $fileName, $oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
        	file_put_contents('uploadFile.txt', "\n\rDocument add: {$oDocument->getMessage()}", FILE_APPEND);
       		//return false;
        }

        //return $oDocument;
	}
	
	/**
	 * Check whether the specified document type has required fields
	 * @param $params
	 * @return unknown_type
	 */
	public function docTypeHasRequiredFields($params){
		$docType=$params['docType'];
		
		$aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($docType, array('ids' => false));
        $fieldSets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);	
        
		$hasRequiredFields = false;
	    
	    foreach($fieldSets as $fieldSet){
			$fields=$fieldSet->getFields();
			fwrite($fh, "\r\nfields ".print_r($fields, true));
			foreach($fields as $field){
				if ($field->getIsMandatory()) {
					$hasRequiredFields = true;
					break;
				}
			}
	    }
		
		$this->addResponse('hasRequiredFields',$hasRequiredFields);
	}
	
	/**
	 * Get all fields for the specified DocType
	 * @param $params
	 * @return unknown_type
	 */
	public function docTypeFields($params){
		$type=$params['type'];
		$filter=is_array($params['filter'])?$params['filter']:NULL;
		$oDT=DocumentType::get($type);
		
		$aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($oDT->getID(), array('ids' => false));
        $fieldSets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);		
		
		$ret=array();
		foreach($fieldSets as $fieldSet){
			$ret[$fieldSet->getID()]['properties']=$fieldSet->getProperties();
			$fields=$fieldSet->getFields();
			foreach($fields as $field){
				$properties=$field->getProperties();
				
				if(isset($properties['has_lookup'])) {
					if($properties['has_lookup']==1){
						if($properties['has_lookuptree']==1){
							//need to recursively populate tree lookup fields!
							$properties['tree_lookup_values'] = $this->get_metadata_tree($field->getId());
						} else {
							$properties['lookup_values'] = $this->get_metadata_lookup($field->getId());
					
						}
					}
				}
				
				if(isset($properties['has_inetlookup'])) { 
					if($properties['has_inetlookup']==1) {
						if($properties['inetlookup_type']=="multiwithlist") {
							$properties['multi_lookup_values'] = $this->get_metadata_lookup($field->getId());
						} else if($properties['inetlookup_type']=="multiwithcheckboxes") {
							$properties['checkbox_lookup_values'] = $this->get_metadata_lookup($field->getId());
						}
					}
				}
				
				if(is_array($filter)){
					$requirements=true;
					foreach($filter as $elem=>$value){
						if($properties[$elem]!=$value)$requirements=false;
					}
					if($requirements)$ret[$fieldSet->getID()]['fields'][$field->getID()]=$properties;
				}else{
					$ret[$fieldSet->getID()]['fields'][$field->getID()]=$properties;
				}
			}
		}
		$this->addResponse('fieldsets',$ret);
	}
	
	/**
	 * Get the required fields for the specified docType
	 * @param $params
	 * @return unknown_type
	 */
	public function docTypeRequiredFields($params){
		$nparams=$params;
		$nparams['filter']=array(
			'is_mandatory'=>1
		);
		$this->docTypeFields($nparams);
	}
	
	
	public function getDocTypes($params){
		$types=DocumentType::getList();
		$ret=array();
		foreach($types as $type){
			$ret[$type->aFieldArr['id']]=$type->aFieldArr;
		}
		$this->addResponse('documentTypes',$ret);
	}
	
	/**
	* This returns an array for a metadata tree lookup or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @param integer $fieldid The field id to get metadata for
	* @return array|object $results SUCCESS - the array of metedata for the field | FAILURE - an error object
	*/
	public function get_metadata_lookup($fieldid)
	{
		$sql = "SELECT id, name FROM metadata_lookup WHERE disabled=0 AND document_field_id=$fieldid ORDER BY id";
		$rows = DBUtil::getResultArray($sql);
		/*if (is_null($rows) || PEAR::isError($rows))
		{
			$results = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{*/
		$results = array();
		foreach($rows as $row)
		{
			//need to prepend "id" otherwise it sees it as the i-th element of the array!
			$results[] = array('id'.$row['id']=> $row['name']);
		}
		//}
		return json_encode($results);
	}
	
	/**
	* This returns a metadata tree or an error object.
	*
    * @author KnowledgeTree Team
	* @access public
	* @param integer $fieldid The id of the tree field to get the metadata for
	* @return array|object $results SUCCESS - the array of metadata for the field | FAILURE - an error object
	*/
	public function get_metadata_tree($fieldid, $parentid=0)
	{
		//$myFile = "siteapi.txt";
		//$fh = fopen($myFile, 'a');
		
		$sql = "(SELECT mlt.metadata_lookup_tree_parent AS parentid, ml.treeorg_parent AS treeid, mlt.name AS treename, ml.id AS id, ml.name AS fieldname 
				FROM metadata_lookup ml 
				INNER JOIN (metadata_lookup_tree mlt) ON (ml.treeorg_parent = mlt.id) 
				WHERE ml.disabled=0 AND ml.document_field_id=$fieldid)
				UNION
				(SELECT -1 AS parentid, 0 AS treeid, \"Root\" AS treename, ml.id AS id, ml.name AS fieldname
				FROM metadata_lookup ml 
				LEFT JOIN (metadata_lookup_tree mlt) ON (ml.treeorg_parent = mlt.id) 
				WHERE ml.disabled=0 AND ml.document_field_id=$fieldid AND (ml.treeorg_parent IS NULL OR ml.treeorg_parent = 0))
				ORDER BY parentid, id";
		$rows = DBUtil::getResultArray($sql);
		
		$results = array();
		
		if (sizeof($rows) > 0) {			
			$results = $this->convertToTree($rows);			
		}
		
		//fclose($fh);
				
		return json_encode($results);
	}
	
	private function convertToTree(array $flat) {
		$idTree = 'treeid';
		$idField = 'id';
		$parentIdField = 'parentid';
                        
        //$myFile = "convertToTree.txt";
		//$fh = fopen($myFile, 'a');
		
		//fwrite($fh, "\r\nflat ".print_r($flat, true));
		
	    $indexed = array();
	    // first pass - get the array indexed by the primary id
	   	foreach ($flat as $row) {
        	$treeID = $row[$idTree];
        	if (!isset($indexed[$treeID])) {
        		$indexed[$treeID] = array('treeid' => $treeID,
        									'parentid' => $row[$parentIdField],
        									'treename' => $row['treename'],
        									'type' => 'tree');//$row;
	        	$indexed[$treeID]['fields'] = array();
        	}
	        
	        $indexed[$treeID]['fields'][$row[$idField]] = array('fieldid' => $row[$idField],
	        													'parentid' => $treeID,
	        													'name' =>  $row['fieldname'],
	        													'type' => 'field');
	    }
	    
	    //second pass
	    $root = -1;
	    foreach ($indexed as $id => $row) {	    	
	        $indexed[$row[$parentIdField]]['fields'][$id] =& $indexed[$id];
	    }
	    
	    $results = array($root => $indexed[$root]);
	    
	    //fclose($fh);
	    
	    return $results;
	} 
	
	
	/**
	 * Get the subfolders of the specified folder
	 * @param $params
	 * @return unknown_type
	 */
	public function getSubFolders($params){
		$folderId=isset($params['folderId']) ? $params['folderId'] : 1;
		$filter=isset($params['fields']) ? $params['fields'] : '';
		$options = array( 'orderby'=>'name' );
		$folders = Folder::getList ( array ('parent_id = ?', $folderId ), $options );
		$subfolders=array();
		foreach($folders as $folder){
			$subfolders[$folder->aFieldArr['id']]=$this->filter_array($folder->aFieldArr,$filter,false);
		}	
		$this->addResponse('children',$subfolders);
	}
	
	/**
	 * Get the ancestors and direct descendants of the specified folder;
	 * @param $params
	 * @return unknown_type
	 */
	public function getFolderHierarchy($params){
		$folderId=$params['folderId'];
		$filter=isset($params['fields']) ? $params['fields'] : '';

		$oFolder = Folder::get($folderId);
		$ancestors = array();
		
		if ($oFolder) {
			
			if ($oFolder->getParentFolderIDs() != '') {
				$ancestors=($this->ext_explode(",",$oFolder->getParentFolderIDs()));
				$ancestors=Folder::getList(array('id IN ('.join(',',$ancestors).')'),array());
				$parents=array();
				
				foreach($ancestors as $obj){
					$parents[$obj->getID()]=$this->filter_array($obj->aFieldArr,$filter,false);
				}
			}
		}
		
		$this->addResponse('currentFolder',$this->filter_array($oFolder->_fieldValues(),$filter,false));
		$this->addResponse('parents', $parents);
		$this->addResponse('amazoncreds', $this->getAmazonCredentials());
		
		$this->getSubFolders($params);
	}
	
	public function getAmazonCredentials()
	{
		require_once(KT_LIVE_DIR . '/thirdparty/AWS_S3_PostPolicy/AWS_S3_PostPolicy.php');
		
		/* Amazon Prep Work */
		ConfigManager::load('/etc/ktlive.cnf', KT_LIVE_DIR . '/config/config-path');
        if (ConfigManager::error()) {
        	global $default;
        	$default->log->error("Configuration file not found.");
        }
		// load amazon authentication information
        $aws = ConfigManager::getSection('aws');
		
		
        $buckets = ConfigManager::getSection('buckets');
		$bucket = $buckets['accounts'];
		
		$oUser = User::get($_SESSION['userID']);
		$username = $oUser->getUserName();
		$randomfile = rand();// . '_';
		$aws_tmp_path = ACCOUNT_NAME . '/' . 'tmp/' . $username . '/';
		
		
		
		/* OVERRIDE FOR TESTING */
		//$bucket = 'testa';
		//$aws_tmp_path = '';
		
		
		
		
		
		// TODO : Is there a callback handler? Create one.
		$success_action_redirect = KTLiveUtil::getServerUrl() . '/plugins/ktlive/webservice/callback.php';
		$aws_form_action = 'https://' . $bucket . '.s3.amazonaws.com/';
		
		// Create a new POST policy document
		$s3policy = new Aws_S3_PostPolicy($aws['key'], $aws['secret'], $bucket, 86400);
		$s3policy->addCondition('', 'acl', 'private')
				 ->addCondition('', 'bucket', $bucket)
				 ->addCondition('starts-with', '$key', $aws_tmp_path)
				 ->addCondition('starts-with', '$Content-Type', '')
				 ->addCondition('', 'success_action_redirect', $success_action_redirect);
		
		
		return array(
			'formAction' => $aws_form_action,
			'awstmppath'				=> $aws_tmp_path,
			'randomfile'				=> $randomfile,
			
			'AWSAccessKeyId' 			=> $s3policy->getAwsAccessKeyId(),
			'acl'            			=> $s3policy->getCondition('acl'),
			'policy'         			=> $s3policy->getPolicy(true),
			'signature'      			=> $s3policy->getSignedPolicy(),
			'success_action_redirect'   => $s3policy->getCondition('success_action_redirect'),
		);
	}
	
}