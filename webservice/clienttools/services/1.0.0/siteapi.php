<?php

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class siteapi extends client_service {

    function uploadFile($params)
    {
		global $default;

		$documents = $params['documents'];
		$default->log->debug('Uploading files '.print_r($documents, true));
		$index = 0;
		$returnResponse = array();

		foreach ($documents as $document) {
			$default->log->debug('Uploading file ' . $document['fileName']);
			//file_put_contents('uploadFile.txt', "\n\rUploading file ".$document['fileName'], FILE_APPEND);
			try {
				$baseFolderID = $document['baseFolderID'];
		    	$oStorage = KTStorageManagerUtil::getSingleton();
		    	$folderID = $document['folderID'];
		    	$documentTypeID = $document['docTypeID'];
		    	$fileName = $document['fileName'];
		    	$sS3TempFile  = $document['s3TempFile'];
		    	$metadata = $document['metadata'];
		    	$default->log->debug('Uploading file :: metadata '.print_r($metadata, true));
		    	$MDPack = array();
		    	//assemble the metadata and convert to fileds and fieldsets
		    	foreach ($metadata as $MD) {
		    		$oField = DocumentField::get($MD['id']);
		    		$MDPack[] = array(
		    			$oField,
		    			$MD['value']
	                );
		    	}
		    	$default->log->debug('Uploading file :: metadatapack '.print_r($MDPack, true));
		    	//file_put_contents('uploadFile.txt', "\n\rMDPack ".print_r($MDPack, true), FILE_APPEND);
		       	$aString = "\n\rfolderID: $folderID documentTypeID: $documentTypeID fileName: $fileName S3TempFile: $sS3TempFile";
		    	$default->log->debug("uploading with options $aString");
		        $options['uploaded_file'] = 'true';
		        $oFolder = Folder::get($folderID);
		        if (PEAR::isError($oFolder)) {
		        	//$default->log->error("\n\rFolder $folderID: {$oFolder->getMessage()}");
		       		throw new Exception($oFolder->getMessage());
		        }

		        $oUser = User::get($_SESSION['userID']);
		        if (PEAR::isError($oUser)) {
		        	//$default->log->error("\n\rUser {$_SESSION['userID']}: {$oUser->getMessage()}");
		       		throw new Exception($oUser->getMessage());
		        }

		        $oDocumentType = DocumentType::get($documentTypeID);
		        if (PEAR::isError($oDocumentType)) {
		        	//$default->log->error("\n\rDocumentType: {$oDocumentType->getMessage()}");
		       		throw new Exception($oDocumentType->getMessage());
		        }

		        //remove extension to generate title
		        $aFilename = explode('.', $fileName);
		        $cnt = count($aFilename);
		        $sExtension = $aFilename[$cnt - 1];
		        $title = preg_replace("/\.$sExtension/", '', $fileName);

		        /*file_put_contents('uploadFile.txt', "\n\r".print_r(array(
		            'temp_file' => $sS3TempFile,
		            'documenttype' => $oDocumentType,
		            'metadata' => $metadata,
		            'description' => $title,
		            'cleanup_initial_file' => true
		        ), true), FILE_APPEND);*/

		        $aOptions = array(
		            'temp_file' => $sS3TempFile,
		            'documenttype' => $oDocumentType,
		            'metadata' => $MDPack,
		            'description' => $title,
		            'cleanup_initial_file' => true
		        );

		        if ($document['doBulk'] == 'true') {
		        	$dir = realpath(dirname(__FILE__) . '/../../../../');
		        	require_once($dir . '/plugins/ktlive/lib/import/amazons3zipimportstorage.inc.php');
					require_once($dir . '/plugins/ktlive/lib/import/amazons3bulkimport.inc.php');

		         	// Check if archive is a deb package
			        if ($sExtension == 'deb')
			        {
						$this->sExtension = 'ar';
			        }

					$fileData = array();
		        	$fileData['name'] = $fileName;
		        	$fileData['tmp_name'] = $sS3TempFile;

		        	$fs = new KTAmazonS3ZipImportStorage('', $fileData);
	        	    $response = $oStorage->headS3Object($sS3TempFile);
	        	    $size = 0;
	        	    if (($response instanceof ResponseCore) && $response->isOK()) {
	        	        $size = $response->header['content-length'];
	        	    }
	        	    $aOptions = array('documenttype' => $oDocumentType,
	        	    				'metadata' => $MDPack);

					$bm = new KTAmazonS3BulkImportManager($oFolder, $fs, $oUser, $aOptions);
			        $res = $bm->import($sS3TempFile, $size);
			        $archives[] = $res;
			        //give dummy response
			        //$this->addResponse('addedDocuments', '');
			        $item = array();
					$json = array();
					$item['filename'] = $fileName;
			        $item['isBulk'] = true;
			        $json['success'] = $item;

					$returnResponse[] = json_encode($json);
		        } else {
					//add to KT
		        	$oDocument =& KTDocumentUtil::add($oFolder, $fileName, $oUser, $aOptions);

		        	if (PEAR::isError($oDocument)) {
	        			file_put_contents('uploadFile.txt', "\n\rabout to throw exception {$oDocument->getMessage()}", FILE_APPEND);
	        			throw new Exception($oDocument->getMessage());
		        	}

					//get the icon path
					$mimetypeid = (method_exists($oDocument,'getMimeTypeId')) ? $oDocument->getMimeTypeId():'0';
					$iconFile = 'resources/mimetypes/newui/'.KTMime::getIconPath($mimetypeid).'.png';
					$iconExists = file_exists(KT_DIR.'/'.$iconFile);
					if ($iconExists) {
						$mimeIcon = str_replace('\\','/',$GLOBALS['default']->rootUrl.'/'.$iconFile);
						$mimeIcon = "background-image: url(".$mimeIcon.")";
					} else {
						$mimeIcon = '';
					}

					$oOwner = User::get($oDocument->getOwnerID());
					$oCreator = User::get($oDocument->getCreatorID());
					$oModifier = User::get($oDocument->getModifiedUserId());

					$item = array();
					$json = array();
					$ns = ' not_supported';
					//assemble the item
					$item['baseFolderID'] = $baseFolderID;
					$item['isBulk'] = false;
					$item['id'] = $oDocument->getId();
					$item['document_url'] = KTBrowseUtil::getUrlForDocument($oDocument);
					$item['owned_by'] = $oOwner->getName();
					$item['created_by'] = $oCreator->getName();
					$item['modified_by'] = $oModifier->getName();
					$item['filename'] = $fileName;
					$item['filesize'] = KTUtil::filesizeToString($oDocument->getFileSize());
					$item['title'] = $oDocument->getName();
					$item['mimeicon'] = $mimeIcon;
					$item['created_date'] = $oDocument->getDisplayCreatedDateTime();
					$item['modified_date'] = $oDocument->getDisplayLastModifiedDate();
					$item['item_type'] = 'D';
					$item['user_id'] = $_SESSION['userID'];
					$item['isfinalize_document'] = 1;
					$item['allowdoczohoedit'] = $ns;
					if (KTPluginUtil::pluginIsActive('zoho.plugin')) {
						require_once(KT_PLUGIN_DIR . '/ktlive/zoho/zoho.inc.php');
						if (Zoho::resolve_type($oDocument))
						{
							$item['allowdoczohoedit'] = '<li class="action_zoho_document"><a href="javascript:;" onclick="zohoEdit(\'' . $item['id'] . '\')">Edit Document Online</a></li>';
						}

					}

					$json['success'] = $item;
					$returnResponse[] = json_encode($json);
					$default->log->debug('Document add added response '.print_r($returnResponse, true));
	        	}
			}
	        catch(Exception $e) {
	        	$default->log->error("Document add failed {$e->getMessage()}");
	        	file_put_contents('uploadFile.txt', "\n\rDocument add failed {$e->getMessage()}", FILE_APPEND);
	        	$item = array();
				$json = array();
	        	//construct error message
        		$item['message'] = $e->getMessage();
        		$item['filename'] = $fileName;
        		$json['error'] = $item;

        		$returnResponse[] = json_encode($json);
	        }
		}

		$this->addResponse('addedDocuments', $returnResponse);

		$default->log->debug('Document add Response '.print_r($this->getResponse(), true));
	}

	/**
	 * Check whether the specified document type has required fields
	 * @param $params
	 * @return unknown_type
	 */
	public function docTypeHasRequiredFields($params)
	{
		$docType = $params['docType'];

		$aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($docType, array('ids' => false));
        $fieldSets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
		$hasRequiredFields = false;

	    foreach ($fieldSets as $fieldSet) {
			$fields = $fieldSet->getFields();
			//fwrite($fh, "\r\nfields ".print_r($fields, true));
			foreach ($fields as $field) {
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
	public function docTypeFields($params)
	{
		$type = $params['type'];
		$filter = is_array($params['filter']) ? $params['filter'] : null;
		$oDT = DocumentType::get($type);

		$aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($oDT->getID(), array('ids' => false));
        $fieldSets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);

		$ret = array();
		foreach ($fieldSets as $fieldSet) {
			$ret[$fieldSet->getID()]['properties'] = $fieldSet->getProperties();
			$fields = $fieldSet->getFields();
			foreach ($fields as $field) {
				$properties = $field->getProperties();

				/*if (isset($properties['has_lookup'])) {
					if ($properties['data_type'] == 'LARGE TEXT') {
						file_put_contents('docTypeFields.txt', "\n\rI have large text ".$properties['name'], FILE_APPEND);
					}
				}*/

				if (isset($properties['has_lookup'])) {
					if ($properties['has_lookup'] == 1) {
						if ($properties['has_lookuptree'] == 1) {
							//need to recursively populate tree lookup fields!
							$properties['tree_lookup_values'] = $this->get_metadata_tree($field->getId());
						} else {
							$properties['lookup_values'] = $this->get_metadata_lookup($field->getId());

						}
					}
				}

				if (isset($properties['has_inetlookup'])) {
					if ($properties['has_inetlookup'] == 1) {
						if ($properties['inetlookup_type'] == "multiwithlist") {
							$properties['multi_lookup_values'] = $this->get_metadata_lookup($field->getId());
						} else if ($properties['inetlookup_type'] == "multiwithcheckboxes") {
							$properties['checkbox_lookup_values'] = $this->get_metadata_lookup($field->getId());
						}
					}
				}

				if (is_array($filter)) {
					$requirements = true;
					foreach ($filter as $elem => $value) {
						if ($properties[$elem] != $value) { $requirements = false; }
					}
					if ($requirements) { $ret[$fieldSet->getID()]['fields'][$field->getID()] = $properties; }
				} else {
					$ret[$fieldSet->getID()]['fields'][$field->getID()] = $properties;
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
	public function docTypeRequiredFields($params)
	{
		$nparams  =  $params;
		$nparams['filter'] = array(
			'is_mandatory' => 1
		);
		$this->docTypeFields($nparams);
	}

	public function getDocTypes($params)
	{
		$types = DocumentType::getList();
		$ret = array();
		foreach ($types as $type) {
			$ret[$type->aFieldArr['id']] = $type->aFieldArr;
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
		foreach ($rows as $row) {
			//need to prepend "id" otherwise it sees it as the i-th element of the array!
			$results[] = array('id' . $row['id'] => $row['name']);
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
	public function get_metadata_tree($fieldid, $parentid = 0)
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

	private function convertToTree(array $flat)
	{
		$idTree = 'treeid';
		$idField = 'id';
		$parentIdField = 'parentid';

		$root = 0;

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

	        if ($row[$parentIdField] < $root) {
	        	$root = $row[$parentIdField];
	        }
	    }

	    //file_put_contents('convertToTree.txt', "\n\rroot $root ".print_r($indexed, true), FILE_APPEND);

	    //second pass
	    //$root = 0;
	    foreach ($indexed as $id => $row) {
	        $indexed[$row[$parentIdField]]['fields'][$id] =& $indexed[$id];
	    }

	    $results = array($root => $indexed[$root]);

	    return $results;
	}

	/**
	 * Get the subfolders of the specified folder
	 * @param $params
	 * @return unknown_type
	 */
	public function getSubFolders($params)
	{
		$folderId = isset($params['folderId']) ? $params['folderId'] : 1;
		$filter = isset($params['fields']) ? $params['fields'] : '';
		$options = array('orderby' => 'name');
		$folders = Folder::getList(array('parent_id = ?', $folderId), $options);
		$subfolders = array();
		foreach ($folders as $folder) {
			if($this->userHasPermissionOnItem(User::get($_SESSION['userID']), 'ktcore.permissions.write', $folder, 'folder')) {
				$subfolders[$folder->aFieldArr['id']] = $this->filter_array($folder->aFieldArr, $filter, false);
			}
		}

		$this->addResponse('children', $subfolders);
	}

	/**
	 * Get the ancestors and direct descendants of the specified folder;
	 * @param $params
	 * @return unknown_type
	 */
	public function getFolderHierarchy($params)
	{
		$folderId = $params['folderId'];
		$filter = isset($params['fields']) ? $params['fields'] : '';

		$oFolder = Folder::get($folderId);
		$ancestors = array();

		if ($oFolder) {
			$parent_ids = $oFolder->getParentFolderIDs();
			if ($parent_ids != '') {
				$ancestors = ($this->ext_explode(',', $parent_ids));
				$ancestors = Folder::getList(array('id IN (' . join(',', $ancestors) . ')'), array());
				$parents = array();
				foreach ($ancestors as $obj) {
					$parents[$obj->getID()] = $this->filter_array($obj->aFieldArr, $filter, false);
				}
			}
		}

		$this->addResponse('currentFolder', $this->filter_array($oFolder->_fieldValues(), $filter, false));
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
		$randomfile = mt_rand();// . '_';
		$aws_tmp_path = ACCOUNT_NAME . '/' . 'tmp/' . $username . '/';

		/* OVERRIDE FOR TESTING */
		//$bucket = 'testa';
		//$aws_tmp_path = 'martin/';

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
			'formAction'                 => $aws_form_action,
			'awstmppath'                 => $aws_tmp_path,
			'randomfile'                 => $randomfile,
			'AWSAccessKeyId'             => $s3policy->getAwsAccessKeyId(),
			'acl'                        => $s3policy->getCondition('acl'),
			'policy'                     => $s3policy->getPolicy(true),
			'signature'                  => $s3policy->getSignedPolicy(),
			'success_action_redirect'    => $s3policy->getCondition('success_action_redirect'),
		);
	}

	public function inviteUsers($params)
    {
        include_once(KT_LIB_DIR . '/users/userutil.inc.php');

        global $default;
        $default->log->debug("Inviting users (group id: {$params['group']}): {$params['addresses']}, type: {$params['type']}");

        /**
    	 * Break string into separate email addresses
    	 *
    	 * NOTE this is a modification of a strict RFC 2822 based implementation.
    	 *      Refer to http://www.regular-expressions.info/email.html for more information
    	 *      The version chosen here is the one which omits the syntax using double quotes and square brackets
    	 * NOTE it is possible that this will match invalid domains, however any email matching regex will be one of:
    	 *      1. too strict (skipping certain valid addresses
    	 *      2. too lax (allowing addresses which match the valid email address format but not a valid domain)
    	 *      3. too long and/or difficult to maintain (explicitly list allowed domains; list will be very long &
    	 *         require an update if new domains come into existence)
    	 *
    	 * The case-insensitive modifier has been added to the final query to allow capital letters in email addresses
    	 * (this may not exactly match RFC 2822)
    	 *
    	 * NOTE there may be address formats missed by this expression which were added in later RFC documents.
    	 *      If we find addresses not matching then we can modify the query to accept them.
    	 *      This was the most comprehensive expression (for RFC 2822) that I could find and should match
    	 *      the vast majority of common valid addresses.
    	 */
        $regex = "[a-z0-9!#\$%&'\*\+\/=\?\^_`{\|}~\-]+(?:\.[a-z0-9!#\$%&'\*\+\/=\?\^_`{\|}~\-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?";
        $matches = array();
        preg_match_all("/$regex/i", $params['addresses'], $matches);
        // Check against DB to ensure uniqueness
        $emailList = array_unique($matches[0]);
		// Send invite email
        $response = KTUserUtil::inviteUsersByEmail($emailList, $params['group'], $params['userType'], $params['sharedData']);

        $default->log->debug("Invited response: " . print_r($response, true));

        $this->addResponse('invitedUsers', json_encode($response));
    }

    public function getUserType()
    {
    	$oUser = User::get($_SESSION['userID']);
        //$response = array('usertype'=> );
        $response = $oUser->getDisabled();
        $this->addResponse('usertype', $response);
    }

    private function userHasPermissionOnItem($oUser, $sPermissions, $documentOrFolder, $type)
    {
    	// Shared user
    	if($oUser->getDisabled() == 4 && !is_null($type))
    	{
    		require_once(KT_LIB_DIR . '/render_helpers/sharedContent.inc');
    		return (SharedContent::getPermissions($oUser->getId(), $documentOrFolder->getId(), null, $type) == 1);
    	}
    	// System User
    	else
    	{
    		return KTPermissionUtil::userHasPermissionOnItem($oUser, $sPermissions, $documentOrFolder);
    	}
    }

    public function getDownloadUrl($params)
    {
        if(isset($params['clean']) && !empty($params['clean'])) {
            $clean = substr($params['clean'], 2);
            $documentId = KTUtil::decodeId($clean);
        } else {
            $documentId = $params['docId'];
        }

        // Create uniqueish temporary session id
        $session = 'ktext_' . $documentId . time() . $counter++;

        // Create download link
        $downloadManager = new KTDownloadManager();
        $downloadManager->set_session($session);
        $link = $downloadManager->allow_download($documentId);

        global $default;
        $response['url'] = $link;
        $default->log->error("URL response: " . print_r($params, true));
        $this->addResponse('downloadUrl', json_encode($response));
    }
}

?>