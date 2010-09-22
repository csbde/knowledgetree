<?php
class siteapi extends client_service{
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
	 * Get all fields for the specified DocType
	 * @param $params
	 * @return unknown_type
	 */
	public function docTypeFields($params){
		$type=$params['type'];
		$filter=is_array($params['filter'])?$params['filter']:NULL;
		$oDT=DocumentType::get($type);
		$fieldSets=$oDT->getFieldsets();
		$ret=array();
		foreach($fieldSets as $fieldSet){
			$ret[$fieldSet->getID()]['properties']=$fieldSet->getProperties();
			$fields=$fieldSet->getFields();
			foreach($fields as $field){
				$properties=$field->getProperties();
				
				//TODO: Add Data for lookup-type fields
				if(isset($properties['has_lookup']))if($properties['has_lookup']==1){
					
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
			$ancestors=($this->ext_explode(",",$oFolder->getParentFolderIDs()));
			$ancestors=Folder::getList(array('id IN ('.join(',',$ancestors).')'),array());
			$parents=array();
			foreach($ancestors as $obj){
				$parents[$obj->getID()]=$this->filter_array($obj->aFieldArr,$filter,false);
			}
		}
		
		$this->addResponse('currentFolder',$this->filter_array($oFolder->_fieldValues(),$filter,false));
		$this->addResponse('parents',$parents);
		$this->getSubFolders($params);
	}
	
}