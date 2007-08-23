<?php

/**
 *
 * KTDownloadManager manages files in the download_files table.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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