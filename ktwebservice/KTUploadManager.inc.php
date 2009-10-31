<?php

/**
 *
 * $Id$
 *
 * KTUploadManager manages files in the uploaded_files table.
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
		$this->temp_dir = $config->get('webservice/uploadDirectory');
		$this->temp_dir = str_replace('\\','/', $this->temp_dir);
	}

	/**
	 * Sets the current session.
	 *
	 * @param KTAPI_Session $session
	 */
	function set_session($session)
	{
		$user = &$session->get_user();
		$this->userid=$_SESSION['userID'];
		$this->session = $session->get_session();
	}

	function get_temp_filename($prefix)
	{
		$tempfilename = tempnam($this->temp_dir,$prefix);

		return $tempfilename;
	}

    function is_valid_temporary_file($tempfilename)
    {
        $tempdir = substr($tempfilename,0,strlen($this->temp_dir));
        $tempdir = str_replace('\\','/', $tempdir);

        $tempdir = preg_replace_callback(
            '/\A(.*?):/i',
            create_function(
                // single quotes are essential here,
                // or alternative escape all $ as \$
                '$matches',
                'return strtolower($matches[0]);'
            ),
            $tempdir
        );

        $main_temp_dir = preg_replace_callback(
            '/\A(.*?):/i',
            create_function(
                // single quotes are essential here,
                // or alternative escape all $ as \$
                '$matches',
                'return strtolower($matches[0]);'
            ),
            $this->temp_dir
        );

        return ($tempdir == $main_temp_dir);
        /*
        $tempdir = substr($tempfilename,0,strlen($this->temp_dir));
		$tempdir = str_replace('\\','/', $tempdir);
		return ($tempdir == $this->temp_dir);
		*/
    }
    
	function store_base64_file($base64, $prefix= 'sa_')
	{
		$tempfilename = $this->get_temp_filename($prefix);
		if (!is_writable($tempfilename))
		{
			return new PEAR_Error("Cannot write to file: $tempfilename");
		}

		if (!$this->is_valid_temporary_file($tempfilename))
		{
			return new PEAR_Error("Invalid temporary file: $tempfilename. There is a problem with the temporary storage path: $this->temp_dir.");
		}

		$fp=fopen($tempfilename, 'wb');
		if ($fp === false)
		{
			return new PEAR_Error("Cannot write content to temporary file: $tempfilename.");
		}
		fwrite($fp, base64_decode($base64));
		fclose($fp);

		return $tempfilename;
	}
    
    /**
     * 
     * @param string $content file content NOT base64 encoded (may be string, may be binary)
     * @param string $prefix [optional]
     * @return $tempfilename the name of the temporary file created
     */
    function store_file($content, $prefix= 'sa_')
	{
		$tempfilename = $this->get_temp_filename($prefix);
		if (!is_writable($tempfilename))
		{
			return new PEAR_Error("Cannot write to file: $tempfilename");
		}

		if (!$this->is_valid_temporary_file($tempfilename))
		{
			return new PEAR_Error("Invalid temporary file: $tempfilename. There is a problem with the temporary storage path: $this->temp_dir.");
		}

		$fp=fopen($tempfilename, 'wb');
		if ($fp === false)
		{
			return new PEAR_Error("Cannot write content to temporary file: $tempfilename.");
		}
		fwrite($fp, $content);
		fclose($fp);

		return $tempfilename;
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

		// Ensure the temp directory exists otherwise an error is thrown.
        if (realpath($this->temp_dir) == FALSE) {
            mkdir($this->temp_dir, 0777, true);
        }

		$newtempfile = realpath($this->temp_dir) . '/' . $_SESSION['userID'] . '-'. $now_str;
		if (OS_WINDOWS)
		{
			$tempfile = str_replace('/','\\',$tempfile);
			$newtempfile = str_replace('\\','/',$newtempfile);
		}

		DBUtil::startTransaction();
		$id = DBUtil::autoInsert('uploaded_files',
			array(
				'tempfilename'=>$newtempfile,
				'filename'=>$filename,
				'userid'=>$_SESSION['userID'],
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
		global $php_errormsg;
		if (is_uploaded_file($tempfile))
		{
			$result = @move_uploaded_file($tempfile, $newtempfile);
		}
		else
		{
			$result = @rename($tempfile, $newtempfile);
		}

		$tmp = $php_errormsg;

		if ($result == false)
		{
			DBUtil::rollback();
			return new PEAR_Error($tmp);
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

	function temporary_file_imported($tempfilename)
	{
		$tempfilename = addslashes(str_replace('\\','/',$tempfilename));
		$sql = "DELETE FROM uploaded_files WHERE tempfilename='$tempfilename'";
		$rs = DBUtil::runQuery($sql);
		if (PEAR::isError($rs))
		{
			DBUtil::rollback();
			return false;
		}


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
