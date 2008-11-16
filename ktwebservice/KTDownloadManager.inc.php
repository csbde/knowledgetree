<?php

/**
 *
 * $Id$
 *
 * KTDownloadManager manages files in the download_files table.
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

class KTDownloadManager
{
	var $session;
	var $age;
	var $download_url;
	var $random;

	/**
	 * Constructor for the download manager.
	 *
	 * @param KTAPI_Session $session
	 * @return KTDownloadManager
	 */
	function KTDownloadManager()
	{
		$config = &KTConfig::getSingleton();

		$this->age = $config->get('webservice/downloadExpiry',5);
		$this->download_url = $config->get('webservice/downloadUrl');
		$this->random=$config->get('webservice/randomKeyText','jhsdf8q1jkjpoiudfs7sd3ds1');
	}

	/**
	 * Sets the current session.
	 *
	 * @param string $session
	 */
	function set_session($session)
	{
		$this->session = $session;
	}

	/**
	 * This returns
	 *
	 * @access public
	 * @param KTAPI_Document $document
	 * @return string
	 */
	function allow_download($document, $content_version=null)
	{
		assert(!is_null($document));
		assert(is_a($document, 'KTAPI_Document'));

		$hash = sha1("$document->documentid $this->session $this->random");


		$id = DBUtil::autoInsert('download_files',
			array(
				'document_id'=>$document->documentid,
				'session'=>$this->session,
				'download_date'=>date('Y-m-d H:i:s'),
				'hash'=>$hash
				),
				array('noid'=>true)
			);

		return $this->build_url($hash, $document->documentid );
	}

	/**
	 * This returns the url used to download a document.
	 *
	 * @param string $hash
	 * @param int $documentid
	 * @param int $userid
	 * @return string
	 */
	function build_url($hash, $documentid )
	{
		return $this->download_url . "?code=$hash&d=$documentid&u=$this->session";
	}


	/**
	 * This starts a download.
	 *
	 * @access public
	 */
	function download($document_id, $hash, $version = null)
	{
		$sql = "SELECT 1 FROM download_files WHERE hash='$hash' AND session='$this->session' AND document_id=$document_id";
		$rows = DBUtil::getResultArray($sql);
		if (PEAR::isError($rows))
		{
			return $rows;
		}

		if (count($rows) == 0)
		{
			return new PEAR_Error('Invalid session.');
		}

		$storage =& KTStorageManagerUtil::getSingleton();


        $ktapi = &new KTAPI();
        $res = $ktapi->get_active_session($this->session);
        if (PEAR::isError($res))
        {
        	return $res;
        }

        $document = $ktapi->get_document_by_id($document_id);
        if (PEAR::isError($document))
        {
        	return $document;
        }

        if (!empty($version))
        {
            $version = KTDocumentContentVersion::get($version);

            $res = $storage->downloadVersion($document->document, $version);
        }
        else
        {
            $res = $storage->download($document->document);
        }
        if (PEAR::isError($res))
        {
        	return $res;
        }

        $sql = "DELETE FROM download_files WHERE hash='$hash' AND session='$this->session' AND document_id=$document_id";
        $result = DBUtil::runQuery($sql);

        return true;
	}

	/**
	 * This will remove any temporary files that have not been dealt with in the correct timeframe.
	 *
	 * @access public
	 */
	function cleanup()
	{
		list($year,$mon,$day,$hour, $min) = explode(':', date('Y:m:d:H:i'));
		$expirydate = date('Y-m-d H:i:s', mktime($hour, $min - $this->age, 0, $mon, $day, $year));
		$sql = "DELETE FROM download_files WHERE download_date<'$expirydate'";
		DBUtil::runQuery($sql);
	}
}
?>