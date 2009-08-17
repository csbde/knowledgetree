<?php
class kt extends client_service  {

/**
 * Get Supported (?) Languages
 *
 * returns array containing languages, count, & defaultlanguage
 *
 */
	function get_languages(){
		global $default;
	    $oReg =& KTi18nregistry::getSingleton();
		$aRegisteredLangs = $oReg->geti18nLanguages('knowledgeTree');
		$aLanguageNames = $oReg->getLanguages('knowledgeTree');
		$languages = array();

		if(!empty($aRegisteredLangs)){
			foreach (array_keys($aRegisteredLangs) as $sLang){
				$languages[] = array(
					'isoCode' => $sLang,
					'language' => $aLanguageNames[$sLang]
				);
			}
		}

		$this->setResponse(array('languages' => $languages, 'count' => count($languages), 'defaultLanguage' => $default->defaultLanguage));
	}


	function get_rootfolder_detail($params){
		$params['folderId'] = '1';
		$this->get_folder_detail($params);
	}


	function get_folder_detail($params)	{
		$kt = &$this->KT;

		$folder = &$kt->get_folder_by_id($params['folderId']);
		if (PEAR::isError($folder))
		{
			$this->setError("Could not get folder by Id:  {$params['folderId']}");
			$this->setDebug('FolderError',array('kt'=>$kt,'folder'=>$folder));
			return;
		}

		$detail = $folder->get_detail();
		if (PEAR::isError($detail))
		{
			$this->response= "detail error {$params['node']}";
		}

		if(strtolower($detail['folder_name']) == 'root folder') {
			$detail['folder_name'] = 'KnowledgeTree';
		}

		$qtip .= $this->xlate('Folder name').": {$detail['folder_name']}<br>";
		$class = 'folder';

		$permissions = $detail['permissions'];
		$perms = '';
		//default write permissions to false
		$canWrite = false;

		//iterate through the permissions and convert to human-readable
		for ($j = 0; $j < strlen($permissions); $j++)
		{
		    switch (strtoupper($permissions{$j}))
			{
				case 'W':
					$canWrite = true;
					$perms .= $this->xlate('write, ');
				break;
				case 'R':
					$perms .= $this->xlate('read, ');
				break;
				case 'A':
					$perms .= $this->xlate('add folder, ');
				break;
			}
		}

		//now chop off trailing ', ' if any
		if (strlen($perms) > 2)
		{
			$perms = substr($perms, 0, strlen($perms)-2);
		}

		//permissions
		$qtip .= $this->xlate('Permissions:') . " {$perms}<br>";

		//comment
		$qtip .= $canWrite ? $this->xlate('You may add content to this folder') : $this->xlate('You may not add content to this folder');

		$result[] = array
		(
			'text' => $detail['folder_name'],
			'id' => $params['control'] . $params['node'],
			'filename' => $detail['folder_name'],
			'cls' => 'folder',
			'leaf' => false,
			'document_type' => '',
			'item_type' => 'F',
			'permissions' => $permissions,
			'qtip'=> $qtip
		);

		$this->setResponse($result);
	}


	function get_folder_contents($params){
		$kt=&$this->KT;

		$params['control'] = 'F_';
		$params['node'] = substr($params['node'], strlen($params['control']));

		$folder = &$kt->get_folder_by_id($params['node']);
		if (PEAR::isError($folder)){
			$this->addError("[error 1] Folder Not Found: {$params['control']}{$params['node']}");
			return false;
		}

		$types = (isset($params['types']) ? $params['types'] : 'DF');

		$listing = $folder->get_listing(1, $types);

        $result = $this->_processListing($listing, 'folderContents', $params);

		$this->setResponse($result);
	}


    private function _processListing($listing, $type, $arr){
        $result = array();
        $methodToIncludeItem = '_processItemInclusion_'.$type;

		foreach ($listing as $item)
		{
			$filename = $item['filename'];
			$itemType = $item['item_type'];

			$includeMe = true;

			//build up tooltip
			$qtip = '';

			//default write permissions to false
			$canWrite = false;
			//default immutable to false
			$immutable = false;

			//first do permissions since they are applicable to both folders and docs
			$permissions = $item['permissions'];
			$perms = '';

			//iterate through the permissions and convert to human-readable
			for ($j = 0; $j < strlen($permissions); $j++)
			{
			    switch (strtoupper($permissions{$j}))
				{
					case 'W':
						$canWrite = true;
						$perms .= $this->xlate('write, ');
					break;
					case 'R':
						$perms .= $this->xlate('read, ');
					break;
					case 'A':
						$perms .= $this->xlate('add folder, ');
					break;
				//	default:
				//		$perms .= strtoupper($permissions{$j});
				//	break;
				}
			}

			//now chop off trailing ', ' if any
			if (strlen($perms) > 2)
			{
				$perms = substr($perms, 0, strlen($perms)-2);
			}

			//folders
			if ($itemType == 'F')
			{
				$qtip .= $this->xlate('Folder name').": {$filename}<br>";
				$class = 'folder';

				//permissions
				$qtip .= $this->xlate('Permissions:') . " {$perms}<br>";

				//comment
				$qtip .= $canWrite ? $this->xlate('You may add content to this folder') : $this->xlate('You may not add content to this folder');
			}

			//documents
			else
			{
				$qtip = '';

				//get file extension so can determine mimetype
				$extpos = strrpos($filename, '.') ;

				if ($extpos === false)
				{
					$class = 'file-unknown';
				}
				else
				{
					$ext = substr($filename, $extpos); // Get Extension including the dot
					$class = 'file-' . substr($filename, $extpos +1); // Get Extension without the dot
				}

				// Convert list to array
				$extensions = explode(',', $arr['extensions']);

				//don't include results which don't have the correct file extensions
				if(!in_array(strtolower($ext), $extensions))
				{
					$includeMe = false;
				}
				else
				{
					//filename
					$qtip .= $this->xlate('Filename') . ": {$filename}<br>";

					//size
					$qtip .= $this->xlate('File Size') . ": " . fsize_desc($item['filesize']) . "<br>";

					//last modified
					$qtip .= $this->xlate('Modified') . ": {$item['modified_date']}<br>";

					//owner
					$qtip .= $this->xlate('Owner') . ": {$item['created_by']}<br>";

					//version
					$qtip .= $this->xlate('Version') . ": {$item['version']}<br>";

					//immutability
					if (bool2str(strtolower($item['is_immutable'])) == 'true')
					{
						$canWrite = false;
						$immutable = true;
					}

					//status, i.e. checked out or not, or immutable
					if ($immutable)
					{
						$qtip .= $this->xlate('Status: Immutable') . '<br>';
					}
					else if (strtolower($item['checked_out_by']) != 'n/a' && ($item['checked_out_by'] != ''))
					{
						$qtip .= $this->xlate('Status: Checked out by') . " {$item['checked_out_by']}<br>";
					}
					else
					{
						$qtip .= $this->xlate('Status: Available') . '<br>';
					}

					//permissions
					$qtip .= $this->xlate('Permissions:') . " {$perms}<br>";

					//immutable
					if($immutable)
					{
						$qtip .= $this->xlate('This document is not editable');
					}
					else if ($canWrite)
					{
						$qtip .= $this->xlate('You may edit this document');
					}
					else
					{
						$qtip .= $this->xlate('This document is not editable');
					}
				}
			}//end of if for files
			if($includeMe)
			{
				$result[] = $this->$methodToIncludeItem($item, $class, $qtip);
			}
		}

		return $result;
    }




    private function _processItemInclusion_folderContents($item, $class, $qtip)
    {
        return array (
                'text' => htmlspecialchars($item['title']),
                'originaltext' => $item['title'],
                'id' => ($item['item_type'] == 'F' ? $item['item_type']."_" : "").$item['id'],
                'filename' => $item['filename'],
                'cls' => $class,
                'leaf' => ($item['item_type'] == 'D'),
                'document_type' => $item['document_type'],
                'item_type' => $item['item_type'],
                'permissions' => $item['permissions'],
				'content_id' => $item['content_id'],
                'qtip'=> $qtip
            );
    }


    private function _processItemInclusion_search($item, $class, $qtip)
    {
        return array (
                'text' => htmlspecialchars($item['title']),
                'originaltext' => $item['title'],
                'id' => $item['document_id'],
                'filename' => $item['filename'],
                'cls' => $class,
                'leaf' => true,
                'document_type' => $item['document_type'],
                'item_type' => 'D',
                'permissions' => $item['permissions'],
				'content_id' => $item['content_id'],
                'relevance' => $item['relevance'],
                'qtip'=> $qtip
            );
    }

    

	public function get_metadata($params) {

    	$kt = &$this->KT;

    	$document_id = (int)$params['document_id'];
    	if($document_id > 0) {
	    	$document = $kt->get_document_by_id($params['document_id']);
	    	$detail = $document->get_metadata();
	    	$document_detail = $document->get_detail();
	    	$title = $document_detail['title'];
	    	$document_type = $document_detail['document_type'];

    	} else {
    		if(isset($params['document_type'])) {
    			$document_type = $params['document_type'];
    		} else {
    			$document_type = 'Default';
    		}
    		$detail = $kt->get_document_type_metadata($document_type);
    		$title = "";
    	}

		$result = array();
		$items = array();
		$index = 0;
		$items[] = array("name" => "__title", "index" => 0, "value" => $title, "control_type" => "string");


		// Commented out for timebeing - will be used by 'Save in Format'

		if (isset($params['extensions'])) {

			$fileParts = pathinfo($document_detail['filename']);

			$items[] = array("name" => "__document_extension", "index" => 0, "value" => strtolower($fileParts['extension']), "control_type" => "lookup", "selection" => explode(',', str_replace('.', '', $params['extensions'])));
		}

		$document_types = $this->get_documenttypes($params);
		$json_document_types = array();
		foreach($document_types['items'] as $val) {
			$json_document_types[] = $val['name'];
		}
		$items[] = array("name" => "__document_type", "index" => 0, "value" => $document_type, "control_type" => "lookup", "selection" => $json_document_types);


		for($i=0;$i<count($detail);$i++) {

			for($j=0;$j<count($detail[$i]['fields']);$j++)
			{
				$items[] = array(
				'fieldset' => $detail[$i]['fieldset'],
				'name' => $detail[$i]['fields'][$j]['name'],

                // Change for value. If blank value is set to 1, change value to ''
                // Overcomes issue of n/a
				'value' => ($document_id > 0 ? ($detail[$i]['fields'][$j]['blankvalue'] == '1' ? '' : $detail[$i]['fields'][$j]['value']) : ''),

				'description' => $detail[$i]['fields'][$j]['description'],
				'control_type' => $detail[$i]['fields'][$j]['control_type'],
				'selection' => $detail[$i]['fields'][$j]['selection'],
				'required' => $detail[$i]['fields'][$j]['required'],
				'blankvalue' => $detail[$i]['fields'][$j]['blankvalue'],
				'index' => $index
				);
				$index++;
			}
		}


		$this->setResponse(array('id' => $title, 'items' => $items, 'count' => count($items)));


	}


	public function get_documenttypes($params) {

    	$kt = &$this->KT;

    	$detail = $kt->get_documenttypes();
		$result = array();
		$items = array();
		for($i=0;$i<count($detail);$i++) {
			if(strtolower(substr($detail[$i], -5)) != 'email')
			{
				$items[] = array(
					'name' => $detail[$i]
				);
			}
		}
		$this->setResponse(array('items' => $items, 'count' => count($items)));
	}

	function update_document_type($params) {
		$kt = &$this->KT;
		$document_id = (int)$params['document_id'];
    	if($document_id > 0) {
	    	$document = $kt->get_document_by_id($document_id);
	    	$document->change_document_type($params['document_type']);
	    	$this->setResponse(array('status_code' => 0));
	    	return true;

    	}else{
    		$this->addError("Invalid document Id : {$document_id}");
	    	$this->setResponse(array('status_code' => 1));
    		return false;
    	}

	}

	function download_document($params) {

    	$kt=&$this->KT;
    	$params['session_id']=$params['session_id']?$params['session_id']:$this->AuthInfo['session'];
    	$params['app_type']=$params['app_type']?$params['app_type']:$this->AuthInfo['appType'];
    	
    	$this->addDebug('parameters',$params);
    	
    	$session_id=$params['session_id'];


    	$document = &$kt->get_document_by_id($params['document_id']);
        $docname = $document->document->getFileName();
     //   $docname='test.txt';
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("download_document - cannot get $document_id - "  . $document->getMessage(), $session_id);

    		$this->setResponse(new SOAP_Value('$this->response=',"{urn:$this->namespace}kt_response", $response));
    		return;
    	}

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
			$this->setResponse(array('status_code' => 1, 'message' => $result->getMessage()));
			return;
    	}

    	$session = &$kt->get_session();
    	$download_manager = new KTDownloadManager();
    	$download_manager->set_session($session->session);
    	$download_manager->cleanup();
    	$url = $download_manager->allow_download($document);

    	$response['status_code'] = 0;
		$response['message'] = $url;
        $response['filename'] = $docname;
    	$this->setResponse($response);
    }

    
	/**
	 * Checkout a Document
	 * params contains:
	 * 		document_id			the id of the document
	 * 		reason				the checkout reason
	 *
	 * @param array $params
	 *
	 */
	function checkout_document($params){
    	$responseType = 'kt_response';
		$kt=&$this->KT;

    	$document = &$kt->get_document_by_id($params['document_id']);
		if (PEAR::isError($document))
    	{
			$this->addError("checkout_document - cannot get documentid {$params['document_id']} - "  . $document->getMessage());
			$this->setResponse(array('status_code' => 1, 'message' => $document->getMessage()));
			return;
    	}

    	$result = $document->checkout($params['reason']);
		if (PEAR::isError($result))
    	{
    		$this->addError($result->getMessage());
    		$this->setResponse(array('status_code' => 1, 'message' => $result->getMessage()));
    		return;
    	}

		$url = '';
    	if ($params['download'])
    	{
	    	$download_manager = new KTDownloadManager();
    		$download_manager->set_session($params['session_id']);
    		$download_manager->cleanup();
    		$url = $download_manager->allow_download($document);
    	}

    	$this->setResponse(array('status_code' => 0, 'message' => $url));
    }


	/**
	 * Checkin Document //TODO: Find out how upload works
	 * params contains:
	 * 		document_id
	 * 		filename
	 * 		reason
	 * 		tempfilename
	 *
	 * @param array $params
	 */
    function checkin_document($params){
    	$session_id = $this->AuthInfo['session'];
		$document_id = $params['document_id'];
		$filename = $params['filename'];
		$reason = $params['reason'];
		$tempfilename = $params['tempfilename'];
		$application = $this->AuthInfo['appType'];

    	$this->addDebug('Checkin',"checkin_document('$session_id',$document_id,'$filename','$reason','$tempfilename', '$application')");
    	$kt = &$this->KT;

    	// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
			$this->setResponse(array('status_code' => 12));
			return;
    	}

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
			$this->setResponse(array('status_code' => 13));
		}

		// checkin
		$result = $document->checkin($filename, $reason, $tempfilename, false);
		if (PEAR::isError($result))
		{
			$this->setResponse(array('status_code' => 14));
		}

    	// get status after checkin
		//$this->response= $this->get_document_detail($session_id, $document_id);
		$detail = $document->get_detail();
    	$detail['status_code'] = 0;
		$detail['message'] = '';

    	$this->setResponse($detail);
    }
    
    
    /**
     * Upload a document
     *
     * @param unknown_type $arr
     */
	function add_document_with_metadata($arr){
    	$session_id = $arr['session_id'];
    	//error_reporting(E_ALL);
		$metadata = array();
		$packed = @json_decode($arr['metadata']);

		$this->debug('Entered add_document_with_metadata');

		foreach($packed as $key => $val) {
			if(!is_array($metadata[$val->fieldset])) {
				$metadata[$val->fieldset]['fieldset'] = $val->fieldset;
				$metadata[$val->fieldset]['fields'] = array();
			}
			$metadata[$val->fieldset]['fields'][] = array(
				'name' => $val->name,
				'value' => $val->value
			);
		}

    	$add_result = $this->add_document($arr['session_id'], $arr['folder_id'], $arr['title'], $arr['filename'], $arr['documenttype'], $arr['tempfilename'], $arr['application']);
		$this->debug('$this->response= from add_document');

		$status_code = $add_result['status_code'];
		if ($status_code != 0)
		{
			$this->response= $add_result;
		}
		$document_id = $add_result['document_id'];
		$content_id = $add_result['content_id'];

		$update_result = $this->update_document_metadata($arr['session_id'], $document_id, $metadata, $arr['application'], array());
		$this->debug('$this->response= from update_document_metadata');
		$status_code = $update_result['status_code'];
		if ($status_code != 0)
		{
			$this->delete_document($arr['session_id'], $document_id, 'Rollback because metadata could not be added', $arr['application']);
			$this->response= $update_result;
		}

		$kt = &$this->KT;
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}

    	$document = $kt->get_document_by_id($document_id);
    	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}


		$result = $document->mergeWithLastMetadataVersion();
		if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		$this->response= array('status_code' => 0, 'document_id' => $document_id, 'content_id' => $content_id);
    }

}


/*












	function debug($str) {
		$this->response= true;
		if(!is_resource($this->dfp)) {
			$this->dfp = fopen("./debug.log", "a+");
		}
		fwrite($this->dfp, strftime("[DEBUG %Y-%m-%d %H:%M:%S] ").$str."\r\n");
	}




    function add_document_params($params)
    {
        $session_id = $params['session_id'];
        $folder_id = $params['folder_id'];
        $title = $params['title'];
        $filename = $params['filename'];
        $documenttype = $params['documenttype'];
        $tempfilename = $params['tempfilename'];
        $application = $params['application'];

    	$this->debug('Entered add_document');
    	$kt = &$this->get$this->xlateapi($session_id, $application);
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}
    	$this->debug("Got \$kt");

    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
			$this->response= array('status_code' => 1);
    	}
    	$this->debug('Exited is_valid_temporary file');

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
    		$this->response= array('status_code' => 1);
		}

		$this->debug('Exited get_folder_by_id');

    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$this->response= array('status_code' => 1);
		}

		$this->debug('Exited folder add_document');

    	$detail = $document->get_detail();
    	$detail['status_code'] = 0;
		$detail['message'] = '';

    	$this->response= $detail;

   }


   function add_document($session_id, $folder_id,  $title, $filename, $documenttype, $tempfilename, $application)
    {
    	$this->debug('Entered add_document');
    	$kt = &$this->get$this->xlateapi($session_id, $application);
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}
    	$this->debug("Got \$kt");

    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
			$this->response= array('status_code' => 1);
    	}
    	$this->debug('Exited is_valid_temporary file');

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
    		$this->response= array('status_code' => 1);
		}

		$this->debug('Exited get_folder_by_id');

    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$this->response= array('status_code' => 1);
		}

		$this->debug('Exited folder add_document');

    	$detail = $document->get_detail();
    	$detail['status_code'] = 0;
		$detail['message'] = '';

    	$this->response= $detail;
    }



    function delete_document($session_id, $document_id, $reason, $application)
    {
    	$kt = &$this->get$this->xlateapi($session_id, $application );
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}


    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$this->response= array('status_code' => 1);
    	}

    	$result = $document->delete($reason);
		if (PEAR::isError($result))
    	{
    		$this->response= array('status_code' => 1);
    	}
    	$response['status_code'] = 0;

    	$this->response= $response;

    }

	function update_document_metadata($session_id, $document_id, $metadata, $application, $sysdata=null)
	{
		$this->debug('entered update_document_metadata');
    	$kt = &$this->get$this->xlateapi($session_id, $application );
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1, 'kterror'=>$kt);
    	}

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$this->response= array('status_code' => 1, 'error'=>'Error getting document');
    	}

    	$result = $document->update_metadata($metadata);
    	if (PEAR::isError($result))
    	{
    		$this->response= array('status_code' => 1, 'error'=>'Error updating metadata');
    	}

    	if ($this->version >= 2)
    	{
    		$result = $document->update_sysdata($sysdata);
    		if (PEAR::isError($result))
    		{
   	 			$this->response= array('status_code' => 1, 'error'=>'Error update_sysdata');
    		}
       	}
    	$response['status_code'] = 0;

    	$this->response= array('status_code' => 0);
	}


	function get_client_policy($arr)
	{
		$policy_name = $arr['policy_name'];

		$config = KTConfig::getSingleton();

		$policy = array(
						'name' => $policy_name,
						'value' => bool2str($config->get($policy_name)),
						'type' => 'boolean'
					);

		$response['policy'] = $policy;
		$response['message'] = 'Knowledgetree client policies retrieval succeeded.';
		$response['status_code'] = 0;

		$this->response= $response;
	}

	function get_all_client_policies()
	{
		$config = KTConfig::getSingleton();

		$policies = array('allowRememberPassword', 'captureReasonsCheckin', 'captureReasonsCheckout');

		$$this->response=Policies = array();

		foreach ($policies as $policy_name)
		{
			$policyInfo = array(
						'name' => $policy_name,
						'value' => bool2str($config->get('addInPolicies/'.$policy_name)),
						'type' => 'boolean'
					);

			$this->response=Policies[$policy_name]  = $policyInfo;
		}

		$languages = $this->get_languages();

		$metadata = array('totalProperty'=>'resultsCounter', 'root'=>'languages', 'fields'=>array('isoCode', 'language'));

		$finalArray = array();
		$finalArray['metaData'] = $metadata;
		$finalArray['policies'] = $$this->response=Policies;
		$finalArray['languages'] = $languages['languages'];
		$finalArray['defaultLanguage'] = $languages['defaultLanguage'];
		$finalArray['resultsCounter'] = $languages['count'];


		$this->response= $finalArray;
	}

	function search($arr)
	{
		$kt = &$this->get$this->xlateapi($arr['session_id'], $arr['application']);

		if (is_array($kt))
		{
			$this->response= $kt;
		}

		$listing = processSearchExpression("(GeneralText contains \"".$arr['query']."\")");

		$result = ListController::_processListing($listing, 'search', $arr);

		if(!count($result)) {

			$result[] = array
			(
				'text' => $this->xlate("No results found"),
				'id' => ($listing[$i]['item_type'] == 'F' ? $listing[$i]['item_type']."_" : "").$listing[$i]['id'],
				'leaf' => true,
				'relevance' => 0,
				'qtip'=> $this->xlate("Please retry your search")
			);
		} else {
			$result = array_slice($result, 0, 200);
		}

		$this->response= $result;
	}

	public function update_metadata($arr)
    {
    	$session_id = $arr['session_id'];
		$metadata = array();
		$packed = @json_decode($arr['metadata']);

		$this->debug('Entered add_document_with_metadata');

		$special = array();

		foreach($packed as $key => $val) {
			if(substr($val->name,0,2) != '__') {

				if(!is_array($metadata[$val->fieldset])) {
					$metadata[$val->fieldset]['fieldset'] = $val->fieldset;
					$metadata[$val->fieldset]['fields'] = array();
				}
				$metadata[$val->fieldset]['fields'][] = array(
				'name' => $val->name,
				'value' => $val->value
				);
			} else {
				$special[$val->name] = $val->value;
			}
		}

		$document_id = $arr['document_id'];

		$update_result = $this->update_document_metadata($arr['session_id'], $document_id, $metadata, $arr['application'], array());
		$this->debug('$this->response= from update_document_metadata');
		$status_code = $update_result['status_code'];
		if ($status_code != 0)
		{
			$this->response= $update_result;
		}

		$kt = &$this->get$this->xlateapi($arr['session_id']);
    	if (is_array($kt))
    	{
    		$this->response= $kt;
    	}

    	if(!empty($special)) {

    		if($document_id > 0) {
	    		$document = $kt->get_document_by_id($document_id);

	    		if(isset($special['__title'])) {
	    			$this->debug("Renaming to {$special['__title']}");
	    			$res = $document->rename($special['__title']);
	    		}
    		}
    	}

		$this->response= array('status_code' => 0, 'document_id' => $document_id);
    }

 	function check_document_title($arr)
	{

    	$kt = &$this->get$this->xlateapi($arr['session_id'], $arr['application'] );


    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}

    	$folder = $kt->get_folder_by_id($arr['folder_id']);

    	if(PEAR::isError($folder)) {
    		$this->response= array('status_code' => 1, 'reason' => 'No such folder');
    	}

    	$doc = $folder->get_document_by_name($arr['title']);

    	if(PEAR::isError($doc)) {
    		$this->response= array('status_code' => 1, 'reason' => 'No document with that title '.$arr['title']);
    	}

    	$this->response= array('status_code' => 0);
	}


	//$session_id, $document_id, $reason
	function cancel_checkout($params)
    {
    	//$this->debug("undo_document_checkout({$params['session_id']}, {$params['document_id']}, {$params['reason']})");

    	$kt = &$this->get$this->xlateapi($params['session_id'], $params['application'] );
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}

    	$document = &$kt->get_document_by_id($params['document_id']);
		if (PEAR::isError($document))
    	{
    		$this->response= array('status_code' => 1, 'message' => $document->getMessage());
    	}

    	$result = $document->undo_checkout($params['reason']);
		if (PEAR::isError($result))
    	{
			$this->response= array('status_code' => 1, 'message' => $result->getMessage());
    	}

    	$response['status_code'] = 0;

    	$this->response= $response;
    }

	function get_users_groups($params)
	{
		$kt = &$this->get$this->xlateapi($params['session_id'],$params['application'] );
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}

		$query = $params['query'];
		//$start = $params['start'];
		//$page = $params['page'];

		$results = KTAPI_User::getList('name LIKE "%'.$query.'%" AND id>0');

		$$this->response=Array = array();

		if (count($results) > 0) {
			foreach ($results as $user)
			{
				$$this->response=Array[] = array('emailid'=>'u_'.$user->getId(), 'name'=> $user->getName(), 'to'=>preg_replace('/('.$query.')/i', '<b>${0}</b>', $user->getName()));
			}
		}

		$groups = KTAPI_Group::getList('name LIKE "%'.$query.'%"');

		if (count($groups) > 0) {
			foreach ($groups as $group)
			{
				$$this->response=Array[] = array('emailid'=>'g_'.$group->getId(), 'name'=> $group->getName(), 'to'=>preg_replace('/('.$query.')/i', '<b>${0}</b>', $group->getName()));
			}
		}


		$sendArray = array ('emails'=>$$this->response=Array, 'metaData'=>array('count'=>count($finalArray), 'root'=>'emails', fields=>array('name', 'to', 'emailid')));

		$this->response= $sendArray;



	}

	function send_email($params)
	{
		$kt = &$this->get$this->xlateapi($params['session_id'], $params['application'] );
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}


		$message = $params['message'];
		$list = $params['users'];


		$recipientsList = array();

		$list = explode(',', $list);

		foreach ($list as $recipient)
		{
			if (trim($recipient) != '') { // check that value is present

				// if @ sign is present, signifies email address
				if(strpos($recipient, '@') === false) {
					// Not email
					$recipient = trim($recipient);

					switch (substr($recipient, 0, 2))
					{
						case 'u_':
							$id = substr($recipient, 2);
							$user = KTAPI_User::getById($id);

							if ($user != null) {
								$recipientsList[] = $user;
							}

							break;
						case 'g_':
							$id = substr($recipient, 2);
							$group = KTAPI_Group::getById($id);

							if ($group != null) {
								$recipientsList[] = $group;
							}
							break;
					}

				} else { // Email - just add to list
					$recipientsList[] = trim($recipient);
				}
			}
		}

		$document = $kt->get_document_by_id($params['document']);


		if (count($recipientsList) == 0) {
			$this->response= array('status'=>'norecipients');
		} else {
			$document->email($recipientsList, $message, TRUE); // true to attach document
			$this->response= array('status'=>'documentemailed');
		}


	}


	function is_latest_version($params)
	{
		$kt=&$this->KT;


    	if (is_array($kt))
    	{
    		$this->response= $kt;
    	}

		$documentId = $params['document_id'];
		$contentId = $params['content_id'];

		$result = $kt->is_latest_version($documentId, $contentId);

		$this->response= $result;

	}

	function check_permission($params)
	{
		$kt=&$this->KT;


    	if (is_array($kt))
    	{
    		$this->response= $kt;
    	}

		$user = $kt->get_user();

		$document = $kt->get_document_by_id($params['document_id']);

		$folder = &$kt->get_folder_by_id($document->ktapi_folder->folderid);

		$folderDetail = $folder->get_detail();

		$permissions = $folderDetail['permissions'];

		if ($user->getId() == $document->document->getCheckedOutUserID()) {
			$permissions .= 'E';
		}

		$this->response= array('status_code'=>0, 'permissions'=>$permissions);
		//$this->response= $permissions;
	}


    function renamefolder($params)
    {
        $kt = &$this->get$this->xlateapi($params['session_id'], $params['application'] );
    	if (is_array($kt))
    	{
    		$this->response= array('status_code' => 1);
    	}

        $response = $kt->rename_folder($params['currentfolderid'], $params['newname']);

        if ($response['status_code'] == 0) {
            $this->response= array('status_code' => 0, 'status'=>'folderupdated', 'icon'=>'success', 'title'=>$this->xlate('Folder Renamed'), 'message'=>$this->xlate('Folder has been successfully renamed'));
        } else {
            $this->response= array('status_code' => 1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to rename folder'), 'message'=>$this->xlate('Unable to rename folder')); //$response['message']
        }

    }

    function addfolder($params)
    {
        $kt = &$this->get$this->xlateapi($params['session_id'], $params['application'] );
        if (is_array($kt))
        {
            $this->response= array('status_code' => 1);
        }


        $response = $kt->create_folder($params['currentfolderid'], $params['newname']);

        if ($response['status_code'] == 0) {
            $this->response= array('status_code' => 0, 'status'=>'folderupdated', 'icon'=>'success', 'title'=>$this->xlate('Folder Created'), 'message'=>$this->xlate('Folder has been successfully created'), 'id' =>$response['results']['id']); //$params['newname']);//
        } else {
            $this->response= array('status_code' => 1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to create folder'), 'message'=>$this->xlate('Unable to create folder')); //$response['message']
        }

    }

    function deletefolder($params)
    {
        $kt = &$this->get$this->xlateapi($params['session_id'], $params['application'] );
        if (is_array($kt))
        {
            $this->response= array('status_code' => 1);
        }

        $response = $kt->delete_folder($params['folderid'], 'Deleted from office addin');

        if ($response['status_code'] == 0) {
            $this->response= array('status_code' => 0, 'status'=>'folderdeleted', 'icon'=>'success', 'title'=>$this->xlate('Folder Deleted'), 'message'=>$this->xlate('Folder has been successfully deleted'));
        } else {
            $this->response= array('status_code' => 1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to delete folder'), 'message'=>$this->xlate('Unable to delete folder')); //$response['message']
        }

    }

    function candeletefolder($arr)
    {
        $kt = &$this->get$this->xlateapi($arr['session_id'], $arr['application']);

        if (is_array($kt))
        {
            $this->response= $kt;
        }


        $folder = &$kt->get_folder_by_id($arr['folderid']);
        if (PEAR::isError($folder))
        {
            $response = 'error';

            $this->response= 'error 1';
        }


        $listing = $folder->get_listing(1, 'DF');

        if (count($listing) == 0) {
            $this->response= array('status_code' => 0, 'candelete'=>TRUE);
        } else {
            $this->response= array('status_code' => 0, 'candelete'=>FALSE);
        }
    }

*/
?>