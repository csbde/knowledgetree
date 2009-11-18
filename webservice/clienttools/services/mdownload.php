<?php

/**
 *
 * This does the download of a file based on the download_files table.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 * Contributor( s): Mark Holtzhausen
 *
 */



/*****************************************************************************************
 	Includes
 *****************************************************************************************/
require_once ('../../../config/dmsDefaults.php');
require_once ('../../../ktapi/ktapi.inc.php');
require_once ('../../../ktwebservice/KTDownloadManager.inc.php');



/*****************************************************************************************
 	Configuration
 *****************************************************************************************/
define('MPDL_MIN_CHUNK_SIZE',1024);				//The Minimum Chunk Size
define('MPDL_JSON_PREFIX','JSON::');				//A Prefix to JSON Responses




/*****************************************************************************************
 	Function Definitions
 *****************************************************************************************/

/**
 * Creates a JSON Error and outputs it
 * 
 * @param $errorCode				Unique Error Identifier
 * @param $errorTitle				Error Title
 * @param $errorDescription			Error Description
 * @param $data						Additional Data Object for Error Tracing
 * @return void
 */
function error($errorCode=NULL,$errorTitle=NULL,$errorDescription=NULL,$data=NULL){
	$ret=array(
		'code'		=>$errorCode,
		'title'		=>$errorTitle,
		'description'=>$errorDescription,
		'data'		=>$data
	);
	JSON_Out($ret);
}


/**
 * Outputs a JSON Response
 * 
 * @param $obj				The object comprising the response
 * @return void
 */
function JSON_Out($obj=NULL){
	echo MPDL_JSON_PREFIX.json_encode($obj);
	exit;
}


/**
 * Get the storage location of the document in question
 * 
 * @param $docId			The document ID
 * @param $hash				The download code (hash)
 * @param $session			The session ID
 * @param $appType			The Application Type
 * @return String			The physical location of the file
 */
function getFileName($docId=NULL,$hash=NULL,$session=NULL,$appType=NULL){
	$ktapi = &new KTAPI ( );
		if (PEAR::isError ( $ktapi ))error(14,'KTAPI Load Error','KTAPI Could not be loaded.',$ktapi);
	$res = $ktapi->get_active_session ( $session, null, $appType );
		if (PEAR::isError ( $res ))error(15,'KTAPI Session Error','KTAPI Could not be load session.',$res);
	
	$sql = "SELECT 1 FROM download_files WHERE hash=? AND session=? AND document_id=?";
	$rows = DBUtil::getResultArray ( array ($sql, array ($hash, $session, $docId ) ) );

	if (PEAR::isError ( $rows )) {error(99,'Unknown Error' , 'The SQL rendered an unknown error',$rows);} 
	if (count ( $rows ) == 0) {	error(1,'Not Authorized' , 'The requested download is not authorised.'); }
	
	$doc=$ktapi->get_document_by_id ( $docId );
	if (PEAR::isError ( $doc )) {error(16,'Document Load Error' , 'KTAPI Could not find the specified file.',$doc);} 
	
	$fileName=$GLOBALS['default']->documentRoot.'/'.$doc->document->getStoragePath();
		
	return $fileName; 
}

/**
 * Shuts down the download, returns status and clears the database download entry.
 * 
 * @param $docId			The document Id
 * @param $hash				The download code (hash)
 * @param $session			The Session Id
 * @return void
 */
function shutDownDownload($docId=NULL,$hash=NULL,$session=NULL){
	$sql = "DELETE FROM download_files WHERE hash='$hash' AND session='$session' AND document_id=$docId";
	$result = DBUtil::runQuery ( $sql );
	$ret=array(
		'completed'		=>true
	);
	JSON_Out($ret);
	exit;
}

/**
 * Perform the download of a segment of the indicated file
 * 
 * @param $fileName		The file to download
 * @param $part			The part to stream
 * @param $length		The length of a part
 * @return void	
 */
function downloadPart($fileName=NULL,$part=1,$length=1024){
	//Open file and send the bitstream representing the chosen segment
	try{
		$sPos=($part-1)*$length;
		$fp=fopen($fileName,'r');
		fseek($fp,$sPos,SEEK_SET);
		$res=fread($fp,$length);
		echo $res;
		exit;
	}catch(Exception $e){
		error(99,"Download Failed","Part {$fileName}({$part}) Failed",$e);
	}
}







/*****************************************************************************************
 	Process The download(s)
 *****************************************************************************************/

//Get download information
$docId=$_GET['d'];
$chunkSize=(int)(($_GET['chunkSize']>MPDL_MIN_CHUNK_SIZE)?$_GET['chunkSize']:MPDL_MIN_CHUNK_SIZE);
$session_id=$_GET['u'];
$hash=$_GET['code'];
$part=(int)$_GET['part'];
$appType = (isset ( $_GET ['apptype'] )) ? $_GET ['apptype'] : 'ws';


//Error Checking Inputs
if(!$docId)error(10,'Document ID Not Supplied');
if(!$session_id)error(11,'Session ID Not Supplied');
if(!$hash)error(12,'Hash (Code) Not Supplied');
if(!$appType)error(13,'Application Type Not Supplied');


//Get additional download information
$fileName=getFileName($docId,$hash,$session_id,$appType);
$fileSize=filesize($fileName);
$partCount=ceil($fileSize/$chunkSize);


//Handle the download request by means of watching parts
if($part==0){
	//send back json obect with info about 
	$response=array(
		'docId'		=>$docId,
		'chunkSize'	=>$chunkSize,
		'fileSize'	=>$fileSize,
		'partCount'	=>$partCount,
		'fileName'	=>$fileName
	);
	JSON_Out($response);
}elseif($part<=$partCount){
	//Get the relevant part and stream it to the browser
	downloadPart($fileName,$part,$chunkSize);
	exit;
}else{
	//After maxparts, remove temp file, do database cleanup
	shutdownDownload($docId,$hash,$session_id);
	exit;
}


?>