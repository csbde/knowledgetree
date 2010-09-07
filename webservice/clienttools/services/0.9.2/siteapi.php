<?php
class siteapi extends client_service{
	public function hello($params){
		$name=$params['firstName'];
		
		$ret=array("hello {$name}");
		$this->setResponse($ret);
	}

	/**
	 * Check whether the specified document type has required fields
	 * @param $params
	 * @return unknown_type
	 */
	public function docTypeHasRequiredFields($params){
		$docType=$params['docType'];
		$this->addResponse('hasRequiredFields',false);
	}
	
	/**
	 * Request a form for the documentType
	 * @param $params
	 * @return unknown_type
	 */
	public function getDocTypeForm($params){
		$docType=$params['docType'];
		$this->addResponse('form',"form template data");
	}
	
	/**
	 * Get the subfolders of the specified folder
	 * @param $params
	 * @return unknown_type
	 */
	public function getSubFolders($params){
		$folderId=$params['folderId'] || 1;
		$options= array( 'orderby'=>'name' );
		$folders= Folder::getList ( array ('parent_id = ?', $folderId ), $options );
		$subfolders=array();
		foreach($folders as $folder){
			$subfolders[$folder->aFieldArr['id']]=$folder->aFieldArr;
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
		
		$this->addResponse('ancestors',array());
		$this->getSubFolders($params);
	}
	
}