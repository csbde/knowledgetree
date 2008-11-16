<?php

/**
 *
 * $Id$
 *
 * KTUploadManager manages files in the uploaded_files table.
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
 * Contributor( s): ______________________________________
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

		$newtempfile = realpath($this->temp_dir) . '/' . $this->userid . '-'. $now_str;
		if (DIRECTORY_SEPARATOR == '\\') {
			$tempfile = str_replace('/','\\',$tempfile);
			$newtempfile = str_replace('\\','/',$newtempfile);
		}

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
		$sql = "SELECT id, tempfilename, filename, userid, action FROM uploaded_files WHERE userid=$this->userid";
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