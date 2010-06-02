<?php

/**
 * $Id:$
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

require_once(KT_DIR . '/thirdparty/solr-php-client/Apache/Solr/Service.php');

class RestSolr
{
    /**
	 * Reference to the SOLR client
	 *
	 * @var client
	 */
    var $client;

    /**
	 * Identifier for the KT instance
	 *
	 * @var string
	 */
    var $ktid;

    /**
	 * Identifier for the solr server
	 *
	 * @var string
	 */
    var $authToken;

    /**
	 * Tells the solr client whether to extract text content or if it is receiving already extracted content
	 *
	 * @var boolean
	 */
    private $extract = true;

    /**
	 * The constructor for the SOLR initialization.
	 *
	 * @param string $url
	 * @param int $port
	 */
    public function __construct($host, $port = 8983, $base = '/solr/', $solrCore = null)
    {
        if (!is_null($solrCore)) {
            $base = $base . $solrCore . '/';
        }

        //TODO: Remove library, use cloudfusion rather. (Solr Lib doesn't support extractingRequest Handler)
        $this->client = new Apache_Solr_Service($host, $port, $base);

    }

    public static function get($url)
    {
        static $singleton = null;

        if(is_null($singleton)){
            $singleton = new RestSolr($url);
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
        //$this->client->setDebug($level);
    }

    public function setExtract($extract)
    {
        $this->extract = $extract;
    }

    /**
	 * Logs errors to the log file
	 *
	 * @param result $result
	 * @param string $function
	 */
    function error($result, $function)
    {
        global $default;
        $default->log->error('SOLR Indexer - ' . $function . ' - Code: ' . htmlspecialchars($result->faultCode()));
        $default->log->error('SOLR Indexer - ' . $function . ' - Reason: ' . htmlspecialchars($result->faultString()));
    }

    /**
	 * Optimise the Solr index.
	 */
    function optimise()
    {
        $this->client->optimize();
    }

    public function ping() {
        return $this->client->ping();
    }

    /**
	 * Add a document to solr
	 *
	 * @param int $documentid
	 * @param string $contentFile
	 * @param string $discussion
	 * @param string $title
	 * @param string $version
	 * @return boolean
	 */
    // TODO add document with already extracted content
    function addDocument($documentid, $contentFile, $discussion, $title, $version)
    {
        global $default;
        $oStorage = KTStorageManagerUtil::getSingleton();
        $default->log->info('SOLR ADD: ' . $contentFile);
        /*
        $this->ktid
        $this->authToken
        */

        try {
            if ($this->extract) {
                // add document which must be extracted by SOLR
                $result = $this->client->addExtractDocument($contentFile,
                                                            array('id' => $documentid, 'title' => $title,
                                                                  'version' => $version, 'discussion' => $discussion));

                $default->log->info('SOLR ADD Document: '.$documentid);
            }
            else {
                // add document with pre-extracted content
                $document = new Apache_Solr_Document();
                $document->id = $documentid; // MUST be suitably unique
                $document->title = $title;
                $document->discussion = $discussion;
                $document->version = $version;
                //$document->content = file_get_contents($contentFile); // MUST be pre-extracted content
                $document->text = $oStorage->file_get_contents($contentFile); // MUST be pre-extracted content

                $result = $this->client->addDocument($document); 	//if you're going to be adding documents in bulk using addDocuments
                //with an array of documents is faster
                $this->client->commit(); //commit to see the document

                $default->log->info('SOLR ADD Document: '.$documentid);
            }
            $default->log->info('SOLR ADD RESULT: ' . print_r($result, true));
        }
        catch (Exception $e) {
            $default->log->info('SOLR INDEX ERROR: ' . print_r($result, true));
            return false;
        }
        return true;
    }

    /**
	 * Remove the document from the index.
	 *
	 * @param int $documentid
	 * @return boolean
	 */
    function deleteDocument($documentid)
    {
        global $default;

        try {
            $result = $this->client->deleteById($documentid);
            $this->client->commit();
            $default->log->info('SOLR DELETE RESULT: ' . print_r($result, true));
        }
        catch (Exception $e) {
            $default->log->info('SOLR INDEX ERROR: ' . print_r($result, true));
            return false;
        }

        return true;
    }

    /**
	 * Does the document exist?
	 *
	 * @param int $documentid
	 * @return boolean
	 */
    function documentExists($documentid)
    {
        /*
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
        */
    }

    /**
	 * Get statistics from the indexer
	 *
	 * @return array
	 */
    function getStatistics()
    {
        return $this->client->getStatistics();
    }

    /**
	 * Run a query on the lucene index
	 *
	 * @param string $query
	 * @return boolean
	 */
    function query($query)
    {
        $query = str_replace('Content:', 'text:', $query);
        $query = strtolower($query);
        $offset = 0;
        $limit = 10;
        $result = $this->client->search($query, $offset, $limit, array('hl.fl' => 'text', 'hl' => 'true'));
        $result = json_decode($result->getRawResponse(), true);

        //formatting the response to be compatible with current search struct:
        /*
        	["DocumentID"]=>	int(239)
            ["Rank"]=>  float(1)
            ["Title"]=>  string(32) "OpenDocument 2.4 Spreadsheet.ods"
            ["Version"]=>  string(3) "0.1"
            ["Content"]=>  string(83) " This is my <b>test</b> text in the OpenOffice.org Calc filehttp://www.google.com/ "
         */

        //var_dump($result['response']['docs']); exit;
        $retDocs = array();
        $count = 0;
        foreach($result['response']['docs'] as $document) {
            //var_dump($document);
            $retDocs[$count]->DocumentID = $document['id'];
            $retDocs[$count]->Rank = $document['boost'];
            $retDocs[$count]->Title = $document['title'][0];
            $retDocs[$count]->Version = $document['version'];
            //$retDocs[$count]->Content = $document['text'][0];
            $retDocs[$count]->Content = $result['highlighting'][$document['id']]['text'][0];
            $retDocs[$count]->Content = str_replace('<em>', '<b>', $retDocs[$count]->Content);
            $retDocs[$count]->Content = str_replace('</em>', '</b>', $retDocs[$count]->Content);

            $count++;
        }
        //var_dump($retDocs); exit;
        return $retDocs;
        //return json_decode($result);
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
        /*
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
        */
        return;
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
        /*
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
        */
        return;
    }

    /**
	 * Extracts the text from a given document stream
	 *
	 * @param string $content The document content
	 * @return string The extracted text on success | false on failure
	 */
    function extractTextContentByStreaming($content)
    {
        /*
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
        */
        return;
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
        /*
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
        */
        return;
    }

    /**
     * Read the custom document properties
     *
	 * @param string $sourceFile The full path to the document
     * @return array The properties as an associative array | False on failure
     */
    function readProperties($sourceFile, $property)
    {
        /*
        $function = new xmlrpcmsg('metadata.readMetadata',
        array(
        php_xmlrpc_encode((string) $sourceFile),
        php_xmlrpc_encode((string) $property)
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
        */
        return;
    }

    /**
     * Writes a given set of custom properties to an OOXML document (MS Office 2007)
     *
	 * @param string $sourceFile The full path to the document
	 * @param string $targetFile The full path to the target / output file
	 * @param int $type 1 = docx, 2 = xlsx, 3 = pptx
     * @param array $properties Associative array of the properties to be added
	 * @return boolean true on success | false on failure
     */
    function writeOOXMLProperties($sourceFile, $targetFile, $type, $properties)
    {
        /*
        $function = new xmlrpcmsg('metadata.writeOOXMLProperty',
        array(
        php_xmlrpc_encode((string) $sourceFile),
        php_xmlrpc_encode((string) $targetFile),
        php_xmlrpc_encode((int) $type),
        php_xmlrpc_encode($properties)
        ));

        $result =& $this->client->send($function);

        if($result->faultCode()) {
            $this->error($result, 'writeOOXMLProperties');
            return false;
        }

        return php_xmlrpc_decode($result->value()) == 0;
        */
        return;
    }

    /**
     * Read the custom document properties of an OOXML document (MS Office 2007)
     *
	 * @param string $sourceFile The full path to the document
	 * @param int $type 1 = docx, 2 = xlsx, 3 = pptx
	 * @param string $property The name of the property to read
     * @return array The properties as an associative array | False on failure
     */
    function readOOXMLProperty($sourceFile, $type, $property)
    {
        /*
        $function = new xmlrpcmsg('metadata.readOOXMLProperty',
        array(
        php_xmlrpc_encode((string) $sourceFile),
        php_xmlrpc_encode((int) $type),
        php_xmlrpc_encode((string) $property)
        ));

        $result =& $this->client->send($function);

        if($result->faultCode()) {
            $this->error($result, 'readOOXMLProperty');
            return false;
        }

        $obj = php_xmlrpc_decode($result->value());

        if($obj['status'] != '0') {
            return false;
        }

        return $obj['metadata'];
        */
        return;
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
        /*
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
        */
        return;
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
        /*
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
        */
        return;
    }

    function shutdown()
    {
        /*
        $function=new xmlrpcmsg('control.shutdown',array(
        php_xmlrpc_encode((string) $this->ktid),
        php_xmlrpc_encode((string) $this->authToken)));

        $result=&$this->client->send($function);
        if($result->faultCode())
        {
            $this->error($result, 'shutdown');
            return false;
        }
        */
        return true;
    }


}

?>
