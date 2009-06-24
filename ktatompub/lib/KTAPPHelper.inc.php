<?php
/**
 * Framework for an Atom Publication Protocol Service
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
 * Contributor( s): 
 * 				Mark Holtzhausen <mark@knowledgetree.com>
 *
 */

class KTAPPHelper{
	private static $FOLDER_LIST_PROPERTIES=array('id','title','permissions','mime_icon_path');
	private static $FILE_LIST_PROPERTIES=array('id','title','document_type','created_by','created_date','checked_out_by','checked_out_date','modified_by','modified_date','owned_by','mime_type','mime_icon_path','mime_display');
	private static $FOLDER_RECURSION_LEVEL=100;
	private static $kt=NULL;
	
	/**
	 * Make sure the class is always treated statically and never instantiated.
	 * 
	 * @return void
	 */
	public function __construct(){
		die('KTAPPHelper should not be instantiated. Only use as a static class');
	}

	
	/**
	 * Get the KT singleton instance
	 *
	 * @return object
	 */
	public static function getKt(){
		if(!isset(self::$kt)){
			self::$kt=new KTAPI();
			self::$kt->get_active_session(session_id());
		}
		return self::$kt;
	}
	
	
	/**
	 * Get the subfolders of the indicated folder
	 *
	 * @param integer $folderId
	 * @return array
	 */
	public static function getSubFolders($folderId=NULL){
		if(!(int)$folderId)$folderId=1;		//Default to root folder
		$folderInfo=self::getKT()->get_folder_contents($folderId,1);
		$subfolders=array();
		foreach($folderInfo['results']['items'] as $item){
			if($item['item_type']=='F'){
				$subfolders[$item[id]]=self::extractFromArray($item,self::$FOLDER_LIST_PROPERTIES);
			}
		}
		return $subfolders;		
	}
	
	
	/**
	 * Get every folder & document in the repository
	 *
	 * @param integer $parent the id of the folder to start recursing from - defaults to root folder [1]
	 * @return array
	 */
	public static function getFullTree($parent=NULL){
		if(!(int)$parent)$parent=1;
		$ktTree=self::getKT()->get_folder_contents($parent,1);
		$appTree=array();
		foreach($ktTree['results']['items'] as $item){
			$newItem=array();
			$newItem['parent']=$parent;
			$newItem['type']=$item['item_type'];
			$newItem['title']=$item['title'];
			$newItem['filename']=$item['filename'];
			$newItem['id']=$item['id'];
//			$newItem['fullrecord']=$item;
			$appTree[]=$newItem;
			if($newItem['type']=='F')$appTree=array_merge($appTree,self::getFullTree($item['id']));
		}
		return $appTree;
	}
	
	
	/**
	 * Get detail about the folder
	 *
	 * @param integer $folderId The id of the folder to get detail on.
	 * @return array 
	 */
	public static function getFolderDetail($folderId=NULL){
		$ktInfo=self::getKT()->get_folder_by_id($folderId);
		return $ktInfo->get_detail();
	}
	
	
	/**
	 * Get detail about the indicated document
	 *
	 * @param integer $docId The document Id
	 * @return array
	 */
	public static function getDocumentDetail($docId=NULL){
		$ktInfo=self::getKT()->get_document_detail($docId);
		return $ktInfo;
	}
	
	
	/**
	 * Get a list of all the documents in a folder.
	 *
	 * @param integer $folderId The id of the folder
	 * @return array
	 */
	public static function getFileList($folderId=NULL){
		$folderContents=self::getKt()->get_folder_contents($folderId,1);
		$folderFiles=array();
		foreach($folderContents['results']['items'] as $item){
			if($item['item_type']=='D'){
				$folderFiles[$item['id']]=self::extractFromArray($item,self::$FILE_LIST_PROPERTIES);
			}
		}
		return $folderFiles;
	}
	
	
	/**
	 * Returns an array containing only the associated values from $array where the keys were found in $keyArray
	 *
	 * @param array $array The array to be processed
	 * @param array $keyArray The list of keys to extract from the array
	 * @return array
	 */
	public static function extractFromArray($array,$keyArray){
		$newArray=array();
		foreach($keyArray as $key){
			$newArray[$key]=isset($array[$key])?$array[$key]:NULL;
		}
		return $newArray;
	}
	
	/**
	 * Log in to KT easily
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $ip
	 * @return object Containing the status_code of the login and session id
	 */
	function login($username, $password, $ip=null){
		$kt = self::getKt();
	
		$session = $kt->start_session($username,$password, $ip);
		if (PEAR::isError($session)){
			$response['status_code']=KTAPP_FAILURE;
			$response['session_id']='';
		}else{
			$session= $session->get_session();
			$response['status_code'] = KTAPP_SUCCESS;
			$response['session_id'] = $session;
		}
		return $response;
	}
	
	
	/**
	 * Log out of KT using the session id
	 *
	 * @param string $session_id
	 * @return object Containing the status_code of the logout attempt
	 */
	function logout($session_id){
		$kt = self::getKt();	
		$session = $kt->get_active_session($session_id, null);
	
		if (PEAR::isError($session)){
			$response['status_code']=KTAPP_FAILURE;
		}else{
			$session->logout();
			$response['status_code'] = KTAPP_SUCCESS;
		}
		return $response;
	}
	
	/**
	 * Check whether the session_id is logged into KT
	 *
	 * @param string $session_id
	 * @return boolean
	 */
	function isLoggedIn($session_id){
		$kt=self::getKt();
		$session=$kt->get_active_session($session_id);
		return !PEAR::isError($session);
	}

}
?>