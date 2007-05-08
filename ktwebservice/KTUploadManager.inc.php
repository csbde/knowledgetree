<?

/**
 *
 * KTUploadManager manages files in the uploaded_files table.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

class KTUploadManager
{
	var $userid;
	var $age;
	var $temp_dir;
	var $session;
	
 
	function KTUploadManager()
	{
		$config = KTConfig::getSingleton();		 
		
		$this->age = $config->get('webservice/uploadExpiry',60);
		$this->temp_dir= $config->get('webservice/uploadDirectory');
	} 
	
	/**
	 * Sets the current session.
	 *
	 * @param KTAPI_Session $session
	 */
	function set_session($session)
	{
		$user = &$session->get_user();
		$this->userid=$user->getId();
		$this->session = $session->get_session();		
	}

	/**
	 * This tells the manager to manage a file that has been uploaded.
	 *
	 * @param string $filename
	 * @param string $tempfile
	 * @param string $action
	 */
	function uploaded($filename, $tempfile, $action, $relatedid = null)
	{
		$filename=basename($filename);
		$now=date('Y-m-d H:i:s');
		$now_str=date('YmdHis');
		
		$tempfile = str_replace('/','\\',$tempfile);
		$newtempfile = str_replace('\\','/',realpath($this->temp_dir) . '/' .  $this->userid  . '-'. $now_str);
		
		DBUtil::startTransaction();
		$id = DBUtil::autoInsert('uploaded_files',
			array(
				'tempfilename'=>$newtempfile,
				'filename'=>$filename,
				'userid'=>$this->userid,
				'uploaddate'=>$now,
				'action'=>$action,
			//	'related_uploadid'=>$relatedid				
				),
				array('noid'=>true)	
			);
			
		if (PEAR::isError($id))
		{
			DBUtil::rollback();
			return $id;
		}

		$result = move_uploaded_file($tempfile, $newtempfile);
		if ($result == false)
		{

			DBUtil::rollback();
			return false;
		}

		DBUtil::commit();

		return $newtempfile;
	}
	
	/**
	 * This is a list of all all managed files.
	 *
	 * @param string $action
	 */
	function get_uploaded_list($action=null)
	{
		$sql = "SELECT id, tempfilename, filename, userid, action, related_uploadid FROM uploaded_files WHERE userid=$this->userid";
		if (!is_null($action))
		{
			$sql .= " AND action='$action'";
		}
		$result = DBUtil::getResultArray($sql);
		return $result;
	}
	
	function imported_file($action, $filename, $documentid)
	{
		DBUtil::startTransaction();
		$filename=basename($filename);
		$sql = "DELETE FROM uploaded_files WHERE action='$action' AND filename='$filename'";
		$rs = DBUtil::runQuery($sql);
		if (PEAR::isError($rs))
		{
			DBUtil::rollback();
			return false;
		}
				
		$sql = "INSERT INTO index_files(document_id, user_id) VALUES($documentid, $this->userid)";
		DBUtil::runQuery($sql);
		if (PEAR::isError($rs))
		{
			DBUtil::rollback();
			return false;
		}
		
		DBUtil::commit();
		return true;
	}
	
	/**
	 * This will remove any temporary files that have not been dealt with in the correct timeframe.
	 *
	 */
	function cleanup()
	{
		list($year,$mon,$day,$hour, $min) = explode(':', date('Y:m:d:H:i'));
		$expirydate = date('Y-m-d H:i:s', mktime($hour, $min - $this->age, 0, $mon, $day, $year));
		
		$sql = "SELECT tempfilename FROM uploaded_files WHERE uploaddate<'$expirydate'";
		$rows = DBUtil::getResultArray($sql);
		
		foreach($rows as $record)
		{
			$tempfilename=addslashes($record['tempfilename']);
			
			$sql = "DELETE FROM uploaded_files WHERE tempfilename='$tempfilename'";
			$rs = DBUtil::runQuery($sql);
			if (PEAR::isError($rs))
			{
				continue;	
			}
			
			@unlink($tempfilename);		
		}
	}
}
?>