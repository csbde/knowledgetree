<?php
class kt extends client_service  {
		

	
/**
 * Get Supported (?) Languages
 *
 * returns array containing languages, count, & defaultlanguage
 *
 */
	function get_languages($passthru=false){
		global $default;
	    $oReg =& KTi18nregistry::getSingleton();
		$aRegisteredLangs=$oReg->geti18nLanguages('knowledgeTree');
		$aLanguageNames=$oReg->getLanguages('knowledgeTree');
		$languages=array();

		if(!empty($aRegisteredLangs)){
			foreach (array_keys($aRegisteredLangs) as $sLang){
				$languages[]=array(
					'isoCode'=>$sLang,
					'language'=>$aLanguageNames[$sLang]
				);
			}
		}
		$response=array('languages'=>$languages, 'count'=>count($languages), 'defaultLanguage'=>$default->defaultLanguage);
		if(is_bool($passthru))if($passthru)return $response;
		$this->setResponse($response);
	}


	function get_rootfolder_detail($params){
		$params['folderId']='1';
		$this->get_folder_detail($params);
	}


	function get_folder_detail($params)	{
		if(isset($params['node'])&&!isset($params['folderId'])){
			$params['node']=split('_',$params['node']);
			$params['folderId']=$params['node'][1];
		}
		$kt=&$this->KT;

		$folder=&$kt->get_folder_by_id($params['folderId']);
		if (PEAR::isError($folder))
		{
			$this->setError("Could not get folder by Id:  {$params['folderId']}");
			$this->setDebug('FolderError',array('kt'=>$kt,'folder'=>$folder));
			return false;
		}

		$detail=$folder->get_detail();
		if (PEAR::isError($detail)){
			$this->setResponse("detail error {$params['node']}");
			return false;
		}

		if(strtolower($detail['folder_name'])=='root folder'){
			$detail['folder_name']='KnowledgeTree';
		}

		$qtip .= $this->xlate('Folder name').": {$detail['folder_name']}<br>";
		$class='folder';

		$permissions=$detail['permissions'];
		$perms='';
		$canWrite=false;

		for ($j=0; $j < strlen($permissions); $j++){
		    switch (strtoupper($permissions{$j})){
				case 'W':
					$canWrite=true;
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

		if (strlen($perms) > 2){
			$perms=substr($perms, 0, strlen($perms)-2);
		}

		$qtip .= $this->xlate('Permissions:') . " {$perms}<br>";
		$qtip .= $canWrite ? $this->xlate('You may add content to this folder') : $this->xlate('You may not add content to this folder');

		$result[]=array(
			'text'=>$detail['folder_name'],
			'id'=>'F_'. $params['folderId'],
			'filename'=>$detail['folder_name'],
			'cls'=>'folder',
			'leaf'=>false,
			'document_type'=>'',
			'item_type'=>'F',
			'permissions'=>$permissions,
			'qtip'=> $qtip
		);

		$this->setResponse($result);
		return true;
	}


	function get_folder_contents($params){
		$kt=&$this->KT;

		$params['control']='F_';
		$params['node']=substr($params['node'], strlen($params['control']));

		$folder=&$kt->get_folder_by_id($params['node']);
    	if(!$this->checkPearError($folder,"[error 1] Folder Not Found: {$params['control']}{$params['node']}",'',array()))return false;

    	$types=(isset($params['types']) ? $params['types'] : 'DF');
		$listing=$folder->get_listing(1, $types);
        $result=$this->_processListing($listing, 'folderContents', $params);

        $this->setResponse($result);
        return true;
	}


    /**
     * Returns the contents of a folder formatted for a grid view.
     *
     * @param array $arr
     * @return array
     */
	function get_folder_contents_for_grid($arr)
	{		
		$kt=&$this->KT;
		
		$arr['control']='F_';
		$arr['node']=substr($arr['node'], strlen($arr['control']));
		
		$folder=&$kt->get_folder_by_id($arr['node']);
		if (PEAR::isError($folder)){
			echo '<pre>'.print_r($arr,true).'</pre>';
			$this->addError('Folder Not found');
			return false;
		}
        
		$types=(isset($arr['types']) ? $arr['types'] : 'DF');
		
		$listing=$folder->get_listing(1, $types);
        
        $result=$this->_processListing($listing, 'grid', $arr);
		
		$this->setResponse(array('totalCount'=>count($listing), 'items'=>$result));
		
		return true;
	}

	
	
    private function _processListing($listing, $type, $arr){
        $result=array();
        $methodToIncludeItem='_processItemInclusion_'.$type;

		foreach($listing as $item){
			/* Trying to fix folder sizes */
			if($item['filesize']<=0){
				$item['filesize']='';
			}else{
				$item['filesize']=serviceHelper::size_readable($item['filesize']);
			}
			
			$filename=$item['filename'];
			$itemType=$item['item_type'];

			$includeMe=true;
			$qtip='';
			$canWrite=false;
			$immutable=false;
			$permissions=$item['permissions'];
			$perms='';

			for ($j=0; $j < strlen($permissions); $j++){
			    switch (strtoupper($permissions{$j})){
					case 'W':
						$canWrite=true;
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

			if(strlen($perms) > 2){
				$perms=substr($perms, 0, strlen($perms)-2);
			}

			if($itemType=='F'){
				$qtip .= $this->xlate('Folder name').": {$filename}<br>";
				$class='folder';
				$qtip .= $this->xlate('Permissions:') . " {$perms}<br>";
				$qtip .= $canWrite ? $this->xlate('You may add content to this folder') : $this->xlate('You may not add content to this folder');
			}

			//documents
			else{
				$qtip='';
				$extpos=strrpos($filename, '.') ;

				if($extpos === false){
					$class='file-unknown';
				}else{
					$ext=substr($filename, $extpos); // Get Extension including the dot
					$class='file-' . substr($filename, $extpos +1); // Get Extension without the dot
				}
				
				$extensions=explode(',', $arr['extensions']);
				if(!in_array(strtolower($ext), $extensions) && !in_array('*',$extensions)){
					$includeMe=false;
				}else{
					$qtip .= $this->xlate('Filename') . ": {$filename}<br>";
					$qtip .= $this->xlate('File Size') . ": " . serviceHelper::fsize_desc($item['filesize']) . "<br>";
					$qtip .= $this->xlate('Modified') . ": {$item['modified_date']}<br>";
					$qtip .= $this->xlate('Owner') . ": {$item['created_by']}<br>";
					$qtip .= $this->xlate('Version') . ": {$item['version']}<br>";
					if (serviceHelper::bool2str(strtolower($item['is_immutable']))=='true'){
						$canWrite=false;
						$immutable=true;
					}
					
					if($immutable){
						$qtip .= $this->xlate('Status: Immutable') . '<br>';
					}else if (strtolower($item['checked_out_by']) != 'n/a' && ($item['checked_out_by'] != '')){
						$qtip .= $this->xlate('Status: Checked out by') . " {$item['checked_out_by']}<br>";
					}else{
						$qtip .= $this->xlate('Status: Available') . '<br>';
					}
					$qtip .= $this->xlate('Permissions:') . " {$perms}<br>";

					if($immutable){
						$qtip .= $this->xlate('This document is not editable');
					}else if ($canWrite){
						$qtip .= $this->xlate('You may edit this document');
					}else{
						$qtip .= $this->xlate('This document is not editable');
					}
				}
			}//end of if for files
			if($includeMe){
				$result[]=$this->$methodToIncludeItem($item, $class, $qtip);
			}
		}
		return $result;
    }




    private function _processItemInclusion_folderContents($item, $class, $qtip){
        return array (
                'text'=>htmlspecialchars($item['title']),
                'originaltext'=>$item['title'],
                'id'=>($item['item_type']=='F' ? $item['item_type']."_" : "").$item['id'],
                'filename'=>$item['filename'],
                'cls'=>$class,
                'leaf'=>($item['item_type']=='D'),
                'document_type'=>$item['document_type'],
                'item_type'=>$item['item_type'],
                'permissions'=>$item['permissions'],
				'content_id'=>$item['content_id'],
				'checked_out_by'=>$item['checked_out_by'],
                'qtip'=> $qtip
            );
    }


    private function _processItemInclusion_search($item, $class, $qtip)
    {
		if ($item['filesize']=='n/a') {
			$item['filesize']=-1;
		}
        return array (
                'text'=>htmlspecialchars($item['title']),
                'originaltext'=>$item['title'],
                'id'=>$item['document_id'],
                'filename'=>$item['filename'],
                'cls'=>$class,
                'leaf'=>true,
                'document_type'=>$item['document_type'],
                'item_type'=>'D',
                'permissions'=>$item['permissions'],
				'content_id'=>$item['content_id'],
				'filesize'=>$item['filesize'],
				'modified'=>$item['modified_date'],
				'created_date'=>$item['created_date'],
				'checked_out_by'=>$item['checked_out_by'],
                'relevance'=>$item['relevance'],
                'qtip'=> $qtip,
				'version'=>$item['version']
            );
    }

    private function _processItemInclusion_grid($item, $class, $qtip)
    {
        //var_dump($item);
		
		if ($item['filesize']=='n/a') {
			$item['filesize']=-1;
		}
		
		return array (
                'text'=>htmlspecialchars($item['title']),
                'originaltext'=>$item['title'],
                'id'=>$item['id'],
                'filename'=>$item['filename'],
                'cls'=>$class,
				'owner'=>$item['created_by'],
                'document_type'=>$item['document_type'],
                'item_type'=>$item['item_type'],
                'permissions'=>$item['permissions'],
				'created_date'=>$item['created_date'],
				'content_id'=>$item['content_id'],
				'filesize'=>$item['filesize'],
				'modified'=>$item['modified_date'],
				'checked_out_by'=>$item['checked_out_by'],
				'version'=>$item['version']
            );
    }
    
    
	public function get_metadata($params) {
    	$kt=&$this->KT;

    	if (substr($params['document_id'], 0, 2)=='D_') {
			$params['document_id']=substr($params['document_id'], 2);
		}

    	$document_id=(int)$params['document_id'];
    	if($document_id > 0) {
	    	$document=$kt->get_document_by_id($params['document_id']);
	    	$detail=$document->get_metadata();
	    	$document_detail=$document->get_detail();
	    	$title=$document_detail['title'];
	    	$document_type=$document_detail['document_type'];

    	}else{
    		if(isset($params['document_type'])) {
    			$document_type=$params['document_type'];
    		}else{
    			$document_type='Default';
    		}
    		$detail=$kt->get_document_type_metadata($document_type);
    		$title="";
    	}

		$result=array();
		$items=array();
		$index=0;
		$items[]=array("name"=>"__title", "index"=>0, "value"=>$title, "control_type"=>"string");


		// Commented out for timebeing - will be used by 'Save in Format'

		if (isset($params['extensions'])) {

			$fileParts=pathinfo($document_detail['filename']);

			$items[]=array("name"=>"__document_extension", "index"=>0, "value"=>strtolower($fileParts['extension']), "control_type"=>"lookup", "selection"=>explode(',', str_replace('.', '', $params['extensions'])));
		}

		$document_types=$kt->get_documenttypes($params);
		$items[]=array("name"=>"__document_type", "index"=>0, "value"=>$document_type, "control_type"=>"lookup", "selection"=>$document_types);

		foreach ($detail as $fieldset) {
			foreach ($fieldset['fields'] as $field)	{

				$prepArray = array(
                    'fieldset' => $fieldset['fieldset'],
                    'name' => $field['name'],
                    
                    // Change for value. If blank value is set to 1, change value to ''
                    // Overcomes issue of n/a
                    'value' => ($document_id > 0 ? ($field['blankvalue'] == '1' ? '' : $field['value']) : ''),
                    
                    'description' => $field['description'],
                    'control_type' => $field['control_type'],
                    'selection' => $field['selection'],
                    'required' => $field['required'],
                    'blankvalue' => $field['blankvalue'],
                    'index' => $index
                );
                
                // Small Adjustment for multiselect to real type
                if ($field['control_type'] == 'multiselect') {
                    $prepArray['control_type'] = $field['options']['type'];
                }
		
                
				if (isset($field['options']['ishtml'])) {
					$prepArray['ishtml'] = $field['options']['ishtml'];
				} else {
					$prepArray['ishtml'] = '0';
				}
				
				if (isset($field['options']['maxlength'])) {
					$prepArray['maxlength'] = $field['options']['maxlength'];
				} else {
					$prepArray['maxlength'] = '-1';
				}
                
                $items[] = $prepArray;
                $index++;
            }
        }



		$this->setResponse(array('id'=>$title, 'items'=>$items, 'count'=>count($items)));

		return true;
	}


	public function get_documenttypes($params) {

    	$kt=&$this->KT;

    	$detail=$kt->get_documenttypes();
		$result=array();
		$items=array();
		for($i=0;$i<count($detail);$i++) {
			if(strtolower(substr($detail[$i], -5)) != 'email')
			{
				$items[]=array(
					'name'=>$detail[$i]
				);
			}
		}
		$this->setResponse(array('items'=>$items, 'count'=>count($items)));
		return true;
	}

	function update_document_type($params) {
		$kt=&$this->KT;
		$document_id=(int)$params['document_id'];
    	if($document_id > 0) {
	    	$document=$kt->get_document_by_id($document_id);
	    	$document->change_document_type($params['document_type']);
	    	$this->setResponse(array('status_code'=>0));
	    	return true;

    	}else{
    		$this->addError("Invalid document Id : {$document_id}");
	    	$this->setResponse(array('status_code'=>1));
    		return false;
    	}

	}

	/**
	 * Get a url for downloading the specified document
	 * Parameters:
	 * 		session_id
	 * 		app_type
	 *		document_id
	 *
	 * @param unknown_type $params
	 */
	function download_document($params,$returnResult=false) {

    	$kt=&$this->KT;
    	$params['session_id']=$params['session_id']?$params['session_id']:$this->AuthInfo['session'];
    	$params['app_type']=$params['app_type']?$params['app_type']:$this->AuthInfo['appType'];
    	$params['app_type']='air';
    	$multipart=isset($params['multipart'])?(bool)$params['multipart']:false;
    	
    	$this->addDebug('parameters',$params);
    	
    	
    	$session_id=$params['session_id'];


    	$document=&$kt->get_document_by_id($params['document_id']);
     //   $docname='test.txt';
		if (PEAR::isError($document))
    	{
    		$response['message']=$document->getMessage();
    		//$this->addDebug("download_document - cannot get $document_id - "  . $document->getMessage(), $session_id);

//    		$this->setResponse(new SOAP_Value('$this->response=',"{urn:$this->namespace}kt_response", $response));
    		$this->setResponse($response);
    		return;
    	}
        $docname=$document->document->getFileName();
    	$result=$document->download();
		if (PEAR::isError($result))
    	{
    		$response['message']=$result->getMessage();
			$this->setResponse(array('status_code'=>1, 'message'=>$result->getMessage()));
			return;
    	}

    	$session=&$kt->get_session();
    	$download_manager=new KTDownloadManager();
    	$download_manager->set_session($session->session);
    	$download_manager->cleanup();
    	$url=$download_manager->allow_download($document,NULL,$multipart);
    	//http://ktair.dev?code=750f7a09d40a3d855f2897f417baf0bbb9a1f615&d=16&u=evm2pdkkhfagon47eh2b9slqj6
    	/*
    	$this->addDebug('url before split',$url);
    	$url=split('\?',$url);
    	$this->addDebug('url after split',$url);
    	$url=$url[0].'/ktwebservice/download.php?'.$url[1];
    	$this->addDebug('url after recombo',$url);
    	*/

    	$response['status_code']=0;
		$response['message']=$url.'&apptype='.$params['app_type'];
        $response['filename']=$docname;
        
        $this->addDebug('effective params',$params);
        
        if($returnResult){
        	return $response;	
        }else{
    		$this->setResponse($response);
        }
    }
    
    /**
     * Get download URLS for multiple documents
     * params contains:
     * 		app_type
     * 		documents = array of doc_id
     *
     * @param unknown_type $params
     */
    public function download_multiple_documents($params){
    	$response=array();
    	foreach($params['documents'] as $docId){
    		$ret=$this->download_document(array('document_id'=>$docId,'app_type'=>$params['app_type'],'multipart'=>$params['multipart']),true);
    		$rec=array(
    			'filename'	=>$ret['filename'],
    			'url'		=>$ret['message'],
    			'succeeded'	=>$ret['status_code']==0?true:false
    		);
    		if(is_array($ret))$response[$docId]=$rec;
    	}
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
    	$responseType='kt_response';
		$kt=&$this->KT;

    	$document=&$kt->get_document_by_id($params['document_id']);
		if (PEAR::isError($document))
    	{
			$this->addError("checkout_document - cannot get documentid {$params['document_id']} - "  . $document->getMessage());
			$this->setResponse(array('status_code'=>1, 'message'=>$document->getMessage()));
			return;
    	}

    	$result=$document->checkout($params['reason']);
		if (PEAR::isError($result))
    	{
    		$this->addError($result->getMessage());
    		$this->setResponse(array('status_code'=>1, 'message'=>$result->getMessage()));
    		return;
    	}

		$url='';
    	if ($params['download'])
    	{
	    	$download_manager=new KTDownloadManager();
    		$download_manager->set_session($params['session_id']);
    		$download_manager->cleanup();
    		$url=$download_manager->allow_download($document);
    	}

    	$this->setResponse(array('status_code'=>0, 'message'=>$url));
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
    	$session_id=$this->AuthInfo['session'];
		$document_id=$params['document_id'];
		$filename=$params['filename'];
		$reason=$params['reason'];
		$tempfilename=$params['tempfilename'];
		$major_update=$params['major_update'];
		$application=$this->AuthInfo['appType'];

    	$this->addDebug('Checkin',"checkin_document('$session_id',$document_id,'$filename','$reason','$tempfilename', '$application', $major_update)");
    	$kt=&$this->KT;

    	// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager=new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
			$this->setResponse(array('status_code'=>12));
			return;
    	}

    	$document=&$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
			$this->setResponse(array('status_code'=>13));
		}

		// checkin
		$result=$document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
			$this->setResponse(array('status_code'=>14));
		}

    	// get status after checkin
		//$this->response= $this->get_document_detail($session_id, $document_id);
		$detail=$document->get_detail();
    	$detail['status_code']=0;
		$detail['message']='';

    	$this->setResponse($detail);
    }
    
    
    /**
     * Upload a document
     *
     * @param unknown_type $arr
     */
	function add_document_with_metadata($arr){
    	$session_id=$arr['session_id'];
    	//error_reporting(E_ALL);
		$metadata=array();
		$packed=$arr['metadata'];

		foreach($meta as $item){
			$fieldSet=$item['fieldset'];
			unset($item['fieldset']);
			$metadata[$fieldSet]['fieldset']=$fieldSet;
			$metadata[$fieldSet]['fields'][]=$item;
		}		

    	$kt=&$this->KT;

    	$upload_manager=new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($arr['tempfilename'])) 	{
    		$this->addError('Temporary File Not Valid');
			$this->setResponse(array('status_code'=>1, 'message'=>'Temporary File Not Valid'));
			return false;
    	}
    	$this->addDebug('','Exited is_valid_temporary file');

    	$folder=&$kt->get_folder_by_id($arr['folder_id']);
		if (PEAR::isError($folder)){
			$this->addError('Could not find Folder '.$arr['folder_id']);
    		$this->setResponse(array('status_code'=>1, 'message'=>'Could not find Folder '.$arr['folder_id']));
    		return false;
		}
		
    	$document=&$folder->add_document($arr['title'], $arr['filename'], $arr['documenttype'], $arr['tempfilename']);
		if (PEAR::isError($document)){
			$this->addError("Could not add Document [title:{$title},filename:{$filename},documenttype:{$documenttype},tempfilename:{$tempfilename}]");
    		$this->setResponse(array('status_code'=>1, 'message'=>'Could not add Document'));
    		return false;
		}

  
		$document_id=$document->get_documentid();

		$update_result=$this->update_document_metadata($arr['session_id'], $document_id, $metadata, $arr['application'], array());

		$status_code=$update_result['status_code'];
		if ($status_code != 0)
		{
			$this->delete_document(array('session_id' => $arr['session_id'], 'document_id' => $document_id, 'reason' => 'Rollback because metadata could not be added', 'application' => $arr['application']));
			$this->response= $update_result;
		}


		$result=$document->mergeWithLastMetadataVersion();
		if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		$this->response= array('status_code'=>0, 'document_id'=>$document_id);
    }
	
	function create_empty_upload_file($params){
		$config=KTConfig::getSingleton();
		$this->addDebug('KTConfig Singleton',$config);
		$uploadFolder=$config->get('webservice/uploadDirectory');
		
		$result = array();
		
		if ($file = fopen($uploadFolder."/".$params['filename'], 'w')) {
			fclose($file);
			$result['status_code'] = '0';
			$result['filename'] = $uploadFolder."/".$params['filename'];
		} else {
			$result['status_code'] = '1';
			$result['filename'] = $uploadFolder."/".$params['filename'];
		}
		$this->setResponse($result);
		return true;
	}

    function get_all_client_policies(){
		$config=KTConfig::getSingleton();
		$this->addDebug('KTConfig Singleton',$config);
		
		$policies=array('allowRememberPassword', 'captureReasonsCheckin', 'captureReasonsCheckout');
		
		$returnPolicies=array();
		
		foreach ($policies as $policy_name)
		{
			$policyInfo=array(
						'name'=>$policy_name,
						'value'=>serviceHelper::bool2str($config->get('addInPolicies/'.$policy_name)),
						'type'=>'boolean'
					);
			
			$returnPolicies[$policy_name] =$policyInfo;
		}
		
		$languages=$this->get_languages(true);
		
		$metadata=array('totalProperty'=>'resultsCounter', 'root'=>'languages', 'fields'=>array('isoCode', 'language'));
		
		$finalArray=array();
		$finalArray['metaData']=$metadata;
		$finalArray['policies']=$returnPolicies;
		$finalArray['languages']=$languages['languages'];
		$finalArray['defaultLanguage']=$languages['defaultLanguage'];
		$finalArray['resultsCounter']=$languages['count'];
		
		
		$this->setResponse($finalArray);
		return true;
	}

    function get_all_explorer_policies(){
		$config=KTConfig::getSingleton();
		$this->addDebug('KTConfig Singleton',$config);
		
		$policies=array('allowRememberPassword', 'explorerMetadataCapture', 'officeMetadataCapture', 'captureReasonsCheckin', 'captureReasonsCheckout', 'captureReasonsDelete', 'captureReasonsCancelCheckout', 'captureReasonsCopyInKT', 'captureReasonsMoveInKT');
		
		$returnPolicies=array();
		$test = $config->get('clientToolPolicies/allowRememberPassword');
		global $default;
		$default->log->error('I am here-'.$test);
		foreach ($policies as $policy_name)
		{
			$policyInfo=array(
						'name'=>$policy_name,
						'value'=>serviceHelper::bool2str($config->get('clientToolPolicies/'.$policy_name)),
						'type'=>'boolean'
					);
			
			$returnPolicies[$policy_name] =$policyInfo;
		}
		
		$languages=$this->get_languages(true);
		
		$metadata=array('totalProperty'=>'resultsCounter', 'root'=>'languages', 'fields'=>array('isoCode', 'language'));
		
		$finalArray=array();
		$finalArray['metaData']=$metadata;
		$finalArray['policies']=$returnPolicies;
		$finalArray['languages']=$languages['languages'];
		$finalArray['defaultLanguage']=$languages['defaultLanguage'];
		$finalArray['resultsCounter']=$languages['count'];
		
		
		$this->setResponse($finalArray);
		return true;
	}
	
	public function switchlang($params){
		setcookie("kt_language", $params['lang'], 2147483647, '/');
	}


    function add_document_params($params){
        $folder_id=$params['folder_id'];
        $title=$params['title'];
        $filename=$params['filename'];
        $documenttype=$params['documenttype'];
        $tempfilename=$params['tempfilename'];
        $application=$params['application'];

    	$this->addDebug('','Entered add_document');
    	$kt=&$this->KT;

    	$upload_manager=new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename)) 	{
    		$this->addError('Temporary File Not Valid');
			$this->setResponse(array('status_code'=>1));
			return false;
    	}
    	$this->addDebug('','Exited is_valid_temporary file');

    	$folder=&$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder)){
			$this->addError('Could not find Folder '.$folder_id);
    		$this->setResponse(array('status_code'=>1));
    		return false;
		}

		$this->addDebug('','Exited get_folder_by_id');

    	$document=&$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document)){
			$this->addError("Could add Document [title:{$title},filename:{$filename},documenttype:{$documenttype},tempfilename:{$tempfilename}]");
    		$this->setResponse(array('status_code'=>1));
    		return false;
		}

		$this->addDebug('','Exited folder add_document');

    	$detail=$document->get_detail();
    	$detail['status_code']=0;
		$detail['message']='';

    	$this->setResponse($detail);
   }
	
    function delete_document($params){
		$session_id = $params['session_id'];
		$document_id = $params['document_id'];
		$reason = $params['reason'];
		$application = $params['application'];
		
    	$kt=&$this->KT;

    	$document=&$kt->get_document_by_id($document_id);
		if (PEAR::isError($document)){
			$this->addError("Invalid document {$document_id}");
    		$this->setResponse(array('status_code'=>1));
    		return false;
    	}

    	$result=$document->delete($reason);
		if (PEAR::isError($result))	{
			$this->addError("Could not delete document {$document_id}");
    		$this->setResponse(array('status_code'=>1));
    		return false;
    	}
    	$this->setResponse(array('status_code'=>0));
    	return true;
    }



	private function update_document_metadata($session_id, $document_id, $metadata, $application, $sysdata=null){
		$this->addDebug('update_document_metadata','entered update_document_metadata');
    	$kt=&$this->KT;
   		$responseType='kt_document_detail';

    	$document=&$kt->get_document_by_id($document_id);
		if (PEAR::isError($document)){
    		return array('status_code'=>1, 'error'=>'Error getting document');
    	}

    	$result=$document->update_metadata($metadata);
    	if (PEAR::isError($result)){
    		return array('status_code'=>1, 'error'=>'Error updating metadata');
    	}

		$result=$document->update_sysdata($sysdata);
		if (PEAR::isError($result)){
	 			return array('status_code'=>1, 'error'=>'Error update_sysdata');
		}

		return array('status_code'=>0);
	}

	function get_client_policy($arr){
		$policy_name=$arr['policy_name'];

		$config=KTConfig::getSingleton();

		$policy=array(
						'name'=>$policy_name,
						'value'=>serviceHelper::bool2str($config->get($policy_name)),
						'type'=>'boolean'
					);

		$response['policy']=$policy;
		$response['message']='Knowledgetree client policies retrieval succeeded.';
		$response['status_code']=0;

		$this->setResponse($response);
		return true;
	}


	function search($arr){
		$kt=&$this->KT;

		$listing=processSearchExpression("(GeneralText contains \"".$arr['query']."\")");

		$result=$this->_processListing($listing, 'search', $arr);

		if(!count($result)) {
			$result[]=array(
				'text'=>$this->xlate("No results found"),
				'id'=>($listing[$i]['item_type']=='F' ? $listing[$i]['item_type']."_" : "").$listing[$i]['id'],
				'leaf'=>true,
				'relevance'=>0,
				'qtip'=> $this->xlate("Please retry your search")
			);
		}else{
			$result=array_slice($result, 0, 200);
		}

		//$this->setResponse($result);
		$this->setResponse(array('totalCount'=>count($listing), 'items'=>$result));

		return true;
	}


	public function update_metadata($arr){
		$metadata=array();
		$meta=$arr['metadata'];

		$this->addDebug('','Entered add_document_with_metadata');
		$this->addDebug('metadata received',$meta);

		$special=array();
//		foreach($apacked as $packed){
//			foreach($packed as $key=>$val) {
//				if(substr($val->name,0,2) != '__') {
//					if(!is_array($metadata[$val->fieldset])) {
//						$metadata[$val->fieldset]['fieldset']=$val->fieldset;
//						$metadata[$val->fieldset]['fields']=array();
//					}
//					$metadata[$val->fieldset]['fields'][]=array(
//						'name'=>$val->name,
//						'value'=>$val->value
//					);
//				}else{
//					$special[$val->name]=$val->value;
//				}
//			}
//		}

		/**

Fatal error:  Cannot unset string offsets in on line 981
		 */

//		foreach($meta as $item){
//			$isSpecial=substr($item['name'],0,2)=='__';
//			if($isSpecial){
//				$special[$item['name']]=$item['value'];
//			}else{
//				$fieldSet=$item['fieldset'];
//				unset($item['fieldset']);
//				$metadata[$fieldSet]['fieldset']=$fieldSet;
//				$metadata[$fieldSet]['fields'][]=$item;
//			}		
//		}
		
		$metadata=array();
		$special=array();
		
		foreach($meta as $item){
			if(substr($item['name'],0,2)=='__'){
				$special[$item['name']]=$item['value'];
			}else{
				$metadata[$item['fieldset']]['fieldset']=$item['fieldset'];
				$metadata[$item['fieldset']]['fields'][]=array('name'=>$item['name'],'value'=>$item['value']);
			}
		}
		
		
		
		$this->addDebug('after processing',array('metadata'=>$metadata,'special'=>$special));
		
		$document_id=$arr['document_id'];

		$update_result=$this->update_document_metadata($arr['session_id'], $document_id, $metadata, $arr['application'], array());
		$this->addDebug('','$this->response= from update_document_metadata');

		$status_code=$update_result['status_code'];
		if ($status_code != 0){
			$this->setResponse($update_result);
		}

		$kt=&$this->KT;

    	if(!empty($special)) {
    		if($document_id > 0) {
	    		$document=$kt->get_document_by_id($document_id);

	    		if(isset($special['__title'])) {
	    			$this->addDebug("Renaming to {$special['__title']}");
	    			$res=$document->rename($special['__title']);
	    		}
    		}
    	}

		$this->setResponse(array('status_code'=>0, 'document_id'=>$document_id));
    }



 	function check_document_title($arr){
    	$kt=&$this->KT;

    	$folder=$kt->get_folder_by_id($arr['folder_id']);
    	if(PEAR::isError($folder)) {
    		$this->setResponse(array('status_code'=>1, 'reason'=>'No such folder'));
    		return false;    		
    	}

    	$doc=$folder->get_document_by_name($arr['title']);
    	if(PEAR::isError($doc)) {
    		$this->setResponse(array('status_code'=>1, 'reason'=>'No document with that title '.$arr['title']));
    		return false;
    	}

    	$this->setResponse(array('status_code'=>0));
    	return true;
	}


	function cancel_checkout($params){
    	$kt=&$this->KT;

    	$document=&$kt->get_document_by_id($params['document_id']);
		if (PEAR::isError($document)){
    		$this->setResponse(array('status_code'=>1, 'message'=>$document->getMessage()));
    		return false;
    	}

    	$result=$document->undo_checkout($params['reason']);
		if (PEAR::isError($result)){
			$this->setResponse(array('status_code'=>1, 'message'=>$result->getMessage()));
			return false;
    	}
    	$response['status_code']=0;
    	$this->setResponse($response);
    }


	public function get_users_groups($params){
		$kt=&$this->KT;
		$query=$params['query'];
		//$start=$params['start'];
		//$page=$params['page'];

		$results=KTAPI_User::getList('name LIKE "%'.$query.'%" AND id>0');
		$returnArray=array();
		if (count($results) > 0){
			foreach ($results as $user){
				$returnArray[]=array('emailid'=>'u_'.$user->getId(), 'name'=> $user->getName(), 'to'=>preg_replace('/('.$query.')/i', '<b>${0}</b>', $user->getName()));
			}
		}
		
		$groups=KTAPI_Group::getList('name LIKE "%'.$query.'%"');
		if (count($groups) > 0){
			foreach ($groups as $group){
				$returnArray[]=array('emailid'=>'g_'.$group->getId(), 'name'=> $group->getName(), 'to'=>preg_replace('/('.$query.')/i', '<b>${0}</b>', $group->getName()));
			}
		}
		
		$sendArray=array ('emails'=>$returnArray, 'metaData'=>array('count'=>count($finalArray), 'root'=>'emails', fields=>array('name', 'to', 'emailid')));
		$this->setResponse($sendArray);
		return true;
	}
    
	
	function send_email($params){
		$kt=&$this->KT;
		
		$message=$params['message'];
		$list=$params['users'];
		$list=explode(',', $list);
		
		$recipientsList=array();
		
		foreach ($list as $recipient){
			if (trim($recipient) != ''){ // check that value is present
				// if @ sign is present, signifies email address
				if(strpos($recipient, '@') === false) { 
					$recipient=trim($recipient);
					switch (substr($recipient, 0, 2)){
						case 'u_':
							$id=substr($recipient, 2);
							$user=KTAPI_User::getById($id);
							if ($user != null){
								$recipientsList[]=$user;
							}
							break;
						case 'g_':
							$id=substr($recipient, 2);
							$group=KTAPI_Group::getById($id);
							if ($group != null) {
								$recipientsList[]=$group;
							}
							break;
					}
				}else{ // Email - just add to list
					$recipientsList[]=trim($recipient);
				}
			}
		}
		
		$document=$kt->get_document_by_id($params['document']);
		if (count($recipientsList)==0) {
			$this->setResponse(array('status'=>'norecipients'));
			return false;
		}else{
			$result = $document->email($recipientsList, $message, TRUE); // true to attach document
			if (PEAR::isError($result)) {
                $this->setResponse(array('status'=>$result->getMessage()));;
                return false;
            }
			$this->setResponse(array('status'=>'documentemailed'));
		}
		return true;
	}


	function is_latest_version($params){
		$kt=&$this->KT;
		
		$documentId=$params['document_id'];
		$contentId=$params['content_id'];
		
		$result=$kt->is_latest_version($documentId, $contentId);
		
		$this->setResponse($result);
		return true;
	}
	
	function check_permission($params){
		$kt=&$this->KT;
		
		$user=$kt->get_user();
		$document=$kt->get_document_by_id($params['document_id']);
		$folder=&$kt->get_folder_by_id($document->ktapi_folder->folderid);
		$folderDetail=$folder->get_detail();
		$permissions=$folderDetail['permissions'];
		if ($user->getId()==$document->document->getCheckedOutUserID()){
			$permissions .= 'E';
		}
		
		$this->setResponse(array('status_code'=>0, 'permissions'=>$permissions));
		return true;
	}

    function copydocument($params){
        $kt=&$this->KT;

        $response=$kt->copy_document($params['documentid'], $params['destfolderid'], $params['reason']);
        if ($response['status_code']==0) {
            $this->setResponse(array('status_code'=>0, 'status'=>'itemupdated', 'icon'=>'success', 'title'=>$this->xlate('Document Copied'), 'message'=>$this->xlate('Document has been successfully copied')));
            return true;
        }else{
            $this->setResponse(array('status_code'=>1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to copy document'), 'message'=>$this->xlate('Unable to copy document')));
            return false;
        }
    }

    function movedocument($params){
        $kt=$this->KT;

        $response=$kt->move_document($params['documentid'], $params['destfolderid'], $params['reason']);
        if ($response['status_code']==0) {
            $this->setResponse(array('status_code'=>0, 'status'=>'itemupdated', 'icon'=>'success', 'title'=>$this->xlate('Document Moved'), 'message'=>$this->xlate('Document has been successfully moved')));
            return true;
        }else{
            $this->setResponse(array('status_code'=>1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to move document'), 'message'=>$this->xlate('Unable to move document'))); 
            return false;
        }
        
    }
    
    function copyfolder($params){
        $kt=&$this->KT;

        $response=$kt->copy_folder($params['sourcefolderid'], $params['destfolderid'], $params['reason']);
        if ($response['status_code']==0) {
            $this->setResponse(array('status_code'=>0, 'status'=>'itemupdated', 'icon'=>'success', 'title'=>$this->xlate('Folder Copied'), 'message'=>$this->xlate('Folder has been successfully copied')));
            return true;
        }else{
            $this->setResponse(array('status_code'=>1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to copy folder'), 'message'=>$this->xlate('Unable to copy folder')));
            return false;
        }
        
    }

    function movefolder($params){
        $kt=&$this->KT;

        $response=$kt->move_folder($params['sourcefolderid'], $params['destfolderid'], $params['reason']);
        if ($response['status_code']==0) {
            $this->setResponse(array('status_code'=>0, 'status'=>'itemupdated', 'icon'=>'success', 'title'=>$this->xlate('Folder Moved'), 'message'=>$this->xlate('Folder has been successfully moved')));
            return true;
        }else{
            $this->setResponse(array('status_code'=>1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to move folder'), 'message'=>$this->xlate('Unable to move folder')));
            return false;
        }
    }

    
    function renamefolder($params){
        $kt=&$this->KT;
        
        $response=$kt->rename_folder($params['currentfolderid'], $params['newname']);
        if ($response['status_code']==0) {
            $this->setResponse(array('status_code'=>0, 'status'=>'folderupdated', 'icon'=>'success', 'title'=>$this->xlate('Folder Renamed'), 'message'=>$this->xlate('Folder has been successfully renamed')));
            return true;
        }else{
            $this->setResponse(array('status_code'=>1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to rename folder'), 'message'=>$this->xlate('Unable to rename folder')));
            return false;
        }
    }

    function addfolder($params)    {
        $kt=&$this->KT;
        $this->addDebug('parameters',$params);
        $response=$kt->create_folder($params['currentfolderid'], $params['newname']);
		$this->setResponse($response);
		return true;
    }
    
    function deletefolder($params){
        $kt=&$this->KT;

        $response=$kt->delete_folder($params['folderid'], $params['reason']);
        if ($response['status_code']==0) {
            $this->setResponse(array('status_code'=>0, 'status'=>'folderdeleted', 'icon'=>'success', 'title'=>$this->xlate('Folder Deleted'), 'message'=>$this->xlate('Folder has been successfully deleted')));
            return true;
        }else{
            $this->setResponse(array('status_code'=>1, 'status'=>'error', 'icon'=>'failure', 'title'=>$this->xlate('Unable to delete folder'), 'message'=>$this->xlate('Unable to delete folder'))); 
            return false;
        }
    }
    
    function candeletefolder($arr){
        $kt=&$this->KT;
        
        $folder=&$kt->get_folder_by_id($arr['folderid']);
        if (PEAR::isError($folder)){
            $this->setResponse('error 1');
            return false;
        }
        
        $listing=$folder->get_listing(1, 'DF');
        if (count($listing)==0) {
            $this->setResponse(array('status_code'=>0, 'candelete'=>TRUE));
            return true;
        }else{
            $this->setResponse(array('status_code'=>0, 'candelete'=>FALSE));
            return true;
        }
    }
}
?>