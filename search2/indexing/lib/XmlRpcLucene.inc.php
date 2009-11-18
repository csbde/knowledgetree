<?php

/**
 * $Id:$
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

require_once('xmlrpc.inc');

class XmlRpcLucene
{
	/**
	 * Reference to the xmlrpc client
	 *
	 * @var xmlrpc_client
	 */
	var $client;

	/**
	 * Identifier for the KT instance
	 *
	 * @var string
	 */
	var $ktid;

	/**
	 * Identifier for the lucene server
	 *
	 * @var string
	 */
	var $authToken;

	/**
	 * The constructoor for the lucene XMLRPC client.
	 *
	 * @param string $url
	 * @param int $port
	 */
	public function __construct($url)
	{
		$this->client=new xmlrpc_client("$url/xmlrpc");
		$this->client->request_charset_encoding = 'UTF-8';
		$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';

		$config = KTConfig::getSingleton();
		$this->authToken = $config->get('indexer/luceneAuthToken','');
		$this->ktid = $config->get('indexer/luceneID','');
	}

	public static function get($url)
	{
	    static $singleton = null;

	    if(is_null($singleton)){
	        $singleton = new XmlRpcLucene($url);
	    }
        return $singleton;
	}

	/**
	 * Set a level for debugging.
	 *
	 * @param int $level
	 */
	function debug($level)
	{
		$this->client->setDebug($level);
	}

	/**
	 * Logs errors to the log file
	 *
	 * @param xmlrpcresult $result
	 * @param string $function
	 */
	function error($result, $function)
	{
		global $default;
		$default->log->error('XMLRPC Lucene - ' . $function . ' - Code: ' . htmlspecialchars($result->faultCode()));
		$default->log->error('XMLRPC Lucene - ' . $function . ' - Reason: ' . htmlspecialchars($result->faultString()));
	}

	/**
	 * Optimise the lucene index.
	 *
	 * @return boolean
	 */
	function optimise()
	{
		$function=new xmlrpcmsg('indexer.optimise',
			array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken)
			));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'optimise');
			return false;
		}
		return php_xmlrpc_decode($result->value()) == 0;
	}

	/**
	 * Add a document to lucene
	 *
	 * @param int $documentid
	 * @param string $contentFile
	 * @param string $discussion
	 * @param string $title
	 * @param string $version
	 * @return boolean
	 */
	function addDocument($documentid, $contentFile, $discussion, $title, $version)
	{
		$function=new xmlrpcmsg('indexer.addDocument',
			array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken),
				php_xmlrpc_encode((int) $documentid),
				php_xmlrpc_encode((string) $contentFile),
				php_xmlrpc_encode((string) $discussion),
				php_xmlrpc_encode((string) $title),
				php_xmlrpc_encode((string) $version)
			));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'addDocument');
			return false;
		}
		return php_xmlrpc_decode($result->value()) == 0;
	}

	/**
	 * Remove the document from the index.
	 *
	 * @param int $documentid
	 * @return boolean
	 */
	function deleteDocument($documentid)
	{
		$function=new xmlrpcmsg('indexer.deleteDocument',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken),
				php_xmlrpc_encode((int) $documentid)));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'deleteDocument');
			return false;
		}
		return php_xmlrpc_decode($result->value()) == 0;
	}

	/**
	 * Does the document exist?
	 *
	 * @param int $documentid
	 * @return boolean
	 */
	function documentExists($documentid)
	{
		$function=new xmlrpcmsg('indexer.documentExists',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken),
				php_xmlrpc_encode((int) $documentid)));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'documentExists');
			return false;
		}
		return php_xmlrpc_decode($result->value());
	}

	/**
	 * Get statistics from the indexer
	 *
	 * @return array
	 */
	function getStatistics()
	{
		$function=new xmlrpcmsg('indexer.getStatistics',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken)));


		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'getStatistics');
			return false;
		}

		$result = php_xmlrpc_decode($result->value());

		//print $result;

		return json_decode($result);
	}

	/**
	 * Run a query on the lucene index
	 *
	 * @param string $query
	 * @return boolean
	 */
	function query($query)
	{
		$function=new xmlrpcmsg('indexer.query',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken),
				php_xmlrpc_encode((string) $query)));

		$result=&$this->client->send($function, 60);
		if($result->faultCode())
		{
			$this->error($result, 'query');
			return false;
		}

		$result = php_xmlrpc_decode($result->value());
		return json_decode($result);
	}

	/**
	 * Updates the discussion text on a given document.
	 *
	 * @param int $docid
	 * @param string $discussion
	 * @return boolean
	 */
	function updateDiscussion($docid, $discussion)
	{
		$function=new xmlrpcmsg('indexer.updateDiscussion',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken),
				php_xmlrpc_encode((int) $docid),
				php_xmlrpc_encode((string) $discussion)));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'updateDiscussion');
			return false;
		}
		return php_xmlrpc_decode($result->value()) == 0;
	}

	/**
	 * Extracts the text from a given document and writes it to the target file
	 *
	 * @param string $sourceFile The full path to the document
	 * @param string $targetFile The full path to the target / output file
	 * @return boolean true on success | false on failure
	 */
	function extractTextContent($sourceFile, $targetFile)
    {
        $function = new xmlrpcmsg('textextraction.getTextFromFile',
            array(
                php_xmlrpc_encode((string) $sourceFile),
                php_xmlrpc_encode((string) $targetFile)
            )
        );

        $result =& $this->client->send($function, 120);

        if($result->faultCode()) {
            $this->error($result, 'extractTextContent');
            return false;
        }
		return php_xmlrpc_decode($result->value()) == 0;
    }

    /**
	 * Extracts the text from a given document stream
	 *
	 * @param string $content The document content
	 * @return string The extracted text on success | false on failure
	 */
    function extractTextContentByStreaming($content)
    {
        $function = new xmlrpcmsg('textextraction.getText',
            array(
                new xmlrpcval($content, 'base64'))
            );
        $result =& $this->client->send($function, 120);

        unset($content);

        if($result->faultCode()) {
            $this->error($result, 'extractTextContent');
            return false;
        }

        $obj = php_xmlrpc_decode($result->value());

        $extractedText = trim($obj['text']);
        return $extractedText;
    }

    /**
     * Writes a given set of custom properties to a document
     *
	 * @param string $sourceFile The full path to the document
	 * @param string $targetFile The full path to the target / output file
     * @param array $properties Associative array of the properties to be added
	 * @return boolean true on success | false on failure
     */
    function writeProperties($sourceFile, $targetFile, $properties)
    {
        $function = new xmlrpcmsg('metadata.writeProperty',
        array(
            php_xmlrpc_encode((string) $sourceFile),
            php_xmlrpc_encode((string) $targetFile),
            php_xmlrpc_encode($properties)
        ));

        $result =& $this->client->send($function);

        if($result->faultCode()) {
            $this->error($result, 'writeProperties');
            return false;
        }

        return php_xmlrpc_decode($result->value()) == 0;
    }

    /**
     * Read the custom document properties
     *
	 * @param string $sourceFile The full path to the document
     * @return array The properties as an associative array | False on failure
     */
    function readProperties($sourceFile)
    {
        $function = new xmlrpcmsg('metadata.readMetadata',
        array(
            php_xmlrpc_encode((string) $sourceFile)
        ));

        $result =& $this->client->send($function);

        if($result->faultCode()) {
            $this->error($result, 'readProperties');
            return false;
        }

        $obj = php_xmlrpc_decode($result->value());

        if($obj['status'] != '0') {
            return false;
        }

        return $obj['metadata'];
    }

    /**
     * Converts a document to the format of the given target file based on the extension of both files.
     *
     * @param string $sourceFile The full path of the document to be converted, with extension.
     * @param string $targetFile The full path of the file to save the converted document with the desired extension.
     * @param string $ooHost The host domain or IP address on which OpenOffice is running
     * @param string $ooPort The port on which OpenOffice is listening.
     * @return boolean
     */
    function convertDocument($sourceFile, $targetFile, $ooHost, $ooPort)
    {
        $function = new xmlrpcmsg('openoffice.convertDocument',
            array(
                php_xmlrpc_encode((string) $sourceFile),
                php_xmlrpc_encode((string) $targetFile),
                php_xmlrpc_encode((string) $ooHost),
                php_xmlrpc_encode((int) $ooPort)
            )
        );

        $result=&$this->client->send($function, 120);

        if($result->faultCode()) {
            $this->error($result, 'convertDocument');
            return $result->faultString();
        }
        return php_xmlrpc_decode($result->value()) == 0;
    }

    /**
     * Convert document to given format. Defaults to pdf
     *
     * @deprecated
     * @param $content
     * @param $toExtension
     * @return unknown_type
     */
    function convertDocumentStreamed($content, $toExtension = 'pdf')
    {
        $function = new xmlrpcmsg('openoffice.convertDocument',
            array(
                new xmlrpcval($content, 'base64'),
                php_xmlrpc_encode((string)$toExtension)
            ));

        $result=&$this->client->send($function, 120);

        unset($content);

        if($result->faultCode()) {
            $this->error($result, 'convertDocument');
            return false;
        }

        $obj = php_xmlrpc_decode($result->value());

        if($obj['status'] != '0') {
            return false;
        }

        return $obj['data'];
    }

    function shutdown()
	{
		$function=new xmlrpcmsg('control.shutdown',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken)));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'shutdown');
			return false;
		}
		return true;
	}


}

?>
