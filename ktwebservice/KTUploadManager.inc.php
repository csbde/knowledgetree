<?php

/**
 *
 * $Id$
 *
 * KTUploadManager manages files in the uploaded_files table.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
		$oStorage = KTStorageManagerUtil::getSingleton();
		$tempfilename = $oStorage->tempnam($this->temp_dir,$prefix);

		return $tempfilename;
	}

    function is_valid_temporary_file($tempfilename)
    {
    	$oStorage = KTStorageManagerUtil::getSingleton();
        $tempdir = substr($tempfilename, 0, strlen($this->temp_dir));
        $tempdir = str_replace('\\', '/', $tempdir);

        // NOTE this can break on S3 files
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

        // if using S3 storage manager, then a match there also counts
        if (ACCOUNT_ROUTING_ENABLED) {
            $check = ($tempdir == $main_temp_dir) || (preg_match('/^' . ACCOUNT_NAME . '\/upload/', $tempdir));
        }
        else {
            $check = ($tempdir == $main_temp_dir);
        }

        /*
        Removing the return, if the file is not directly in the temp directory then it may be a security risk, for instance a file can be uploaded using the following tempfilename: /var/www/var/uploads/../../../../etc/passwd
        Checking the basename of the file should negate this risk.
        if($check){
            return $check;
        }
        */

        $path = '';
        // if using S3 storage manager then the path is already fine...
        if (ACCOUNT_ROUTING_ENABLED) {
            $path = $tempfilename;
        }
        else {
            // in case of a symlinked directory, check if the file exists and is in the uploads directory
            $file = basename($tempfilename);
            $path = $this->temp_dir . DIRECTORY_SEPARATOR . $file;
        }

        if($oStorage->file_exists($path)) {
            // Added check - if file name contains ../ to get down a few levels into the root filesystem
            if(strpos($tempfilename, '../') !== false) {
                global $default;
                $default->log->error('Upload Manager: temporary filename contains relative path: '.$tempfilename .' could be attempting to access root level files');
                return false;
            }

            return true;
        }

        // log the error
        global $default;
        $default->log->error('Upload Manager: can\'t resolve temporary filename: '.$tempfilename .' in uploads directory: '.$this->temp_dir);

        return false;
    }

	function store_base64_file($base64, $prefix= 'sa_')
	{
		$oStorage = KTStorageManagerUtil::getSingleton();
		$tempfilename = $this->get_temp_filename($prefix);
		if (!$oStorage->is_writable($tempfilename)) {
			return new PEAR_Error("Cannot write to file: $tempfilename");
		}

		if (!$this->is_valid_temporary_file($tempfilename)) {
			return new PEAR_Error("Invalid temporary file: $tempfilename. There is a problem with the temporary storage path: $this->temp_dir.");
		}

		$fp = $oStorage->write_file($tempfilename, 'wb', base64_decode($base64));
		if ($fp === false) {
			return new PEAR_Error("Cannot write content to temporary file: $tempfilename.");
		}

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
		$oStorage = KTStorageManagerUtil::getSingleton();
		$tempfilename = $this->get_temp_filename($prefix);
		if (!$oStorage->is_writable($tempfilename)) {
			return new PEAR_Error("Cannot write to file: $tempfilename");
		}

		if (!$this->is_valid_temporary_file($tempfilename)) {
			return new PEAR_Error("Invalid temporary file: $tempfilename. There is a problem with the temporary storage path: $this->temp_dir.");
		}

		$fp = $oStorage->write_file($tempfilename, 'wb', $content);
		if ($fp === false) {
			return new PEAR_Error("Cannot write content to temporary file: $tempfilename.");
		}

		return $tempfilename;
	}

	/**
	 * This tells the manager to manage a file that has been uploaded.
	 *
	 * @param string $filename
	 * @param string $tempfile
	 * @param string $action
	 */
	function uploaded($filename, $tempfile, $action, $unique_file_id = null)
	{
		$oStorage = KTStorageManagerUtil::getSingleton();
		$filename=basename($filename);
		$now=date('Y-m-d H:i:s');
		$now_str=date('YmdHis') + rand(0, 32768);

		// Ensure the temp directory exists otherwise an error is thrown.
        if (realpath($this->temp_dir) == FALSE) {
			$oStorage->mkdir($this->temp_dir, 0777, true);
        }

		$newtempfile = $oStorage->realpath($this->temp_dir) . '/' . $_SESSION['userID'] . '-'. $now_str;
		if (OS_WINDOWS)
		{
			$tempfile = str_replace('/','\\',$tempfile);
			$newtempfile = str_replace('\\','/',$newtempfile);
		}

		if(!empty($unique_file_id) && !$this->check_unique_id($unique_file_id)) {
		    // If the unique_file_id is not unique then return an error
		    return PEAR::raiseError(_kt('Unique file id already exists.'));
		}

		DBUtil::startTransaction();
		$id = DBUtil::autoInsert('uploaded_files',
			array(
				'tempfilename'=>$newtempfile,
				'filename'=>$filename,
				'userid'=>$_SESSION['userID'],
				'uploaddate'=>$now,
				'action'=>$action,
				'unique_file_id'=>$unique_file_id
				),
				array('noid'=>true)
			);

		if (PEAR::isError($id)) {
			DBUtil::rollback();
			return $id;
		}
		
		global $php_errormsg;
		if (is_uploaded_file($tempfile)) {
			$result = $oStorage->move_uploaded_file($tempfile, $newtempfile);
		}
		else
		{
		    $options['copy_rename'] = true;
			$result = @$oStorage->rename($tempfile, $newtempfile, $options);
		}

		$tmp = $php_errormsg;

		if ($result == false) {
			DBUtil::rollback();
			return new PEAR_Error($tmp);
		}

		DBUtil::commit();

		return $newtempfile;
	}

	/**
	 * Ensure the unique file id is unique for the uploaded file
	 *
	 * @param string $unique_file_id
	 * @return bool
	 */
	private function check_unique_id($unique_file_id)
	{
	    $unique = addslashes($unique_file_id);
	    $sql = "SELECT tempfilename FROM uploaded_files WHERE unique_file_id = '$unique'";
	    $result = DBUtil::getResultArray($sql);

	    if(PEAR::isError($result) || empty($result)) {
	        return true;
	    }

	    return false;
	}

	function get_tempfile_from_unique_id($unique_file_id)
	{
	    $unique = addslashes($unique_file_id);
	    $sql = "SELECT tempfilename FROM uploaded_files WHERE unique_file_id = '$unique'";
	    $result = DBUtil::getResultArray($sql);

	    if(PEAR::isError($result)) {
	        return $result;
	    }

	    if(empty($result)) {
	        PEAR::raiseError(_kt('No file has been uploaded with the unique file id: ').$unique_file_id);
	    }

	    return $result[0]['tempfilename'];
	}

	/**
	 * This is a list of all all managed files.
	 *
	 * @param string $action
	 */
	function get_uploaded_list($action=null)
	{
		$sql = "SELECT id, tempfilename, filename, userid, action FROM uploaded_files WHERE userid=$this->userid";
		if (!is_null($action)) {
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
		if (PEAR::isError($rs)) {
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
		$oStorage = KTStorageManagerUtil::getSingleton();
		list($year,$mon,$day,$hour, $min) = explode(':', date('Y:m:d:H:i'));
		$expirydate = date('Y-m-d H:i:s', mktime($hour, $min - $this->age, 0, $mon, $day, $year));

		$sql = "SELECT tempfilename FROM uploaded_files WHERE uploaddate<'$expirydate'";
		$rows = DBUtil::getResultArray($sql);

		foreach($rows as $record) {
			$tempfilename=addslashes($record['tempfilename']);

			$sql = "DELETE FROM uploaded_files WHERE tempfilename='$tempfilename'";
			$rs = DBUtil::runQuery($sql);
			if (PEAR::isError($rs)) {
				continue;
			}

			$oStorage->unlink($tempfilename);
		}
	}
}
?>
