<?php

/**
 * $Id:$
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
 * Contributor( s): ______________________________________
 *
 */

require_once(realpath(dirname(__FILE__) . '/../../config/dmsDefaults.php'));

function orderProcessors($a, $b)
{
     if ($a->order == $b->order) {
        return 0;
    }
    return ($a->order < $b->order) ? -1 : 1;
}

/**
 * The processor runs all document processing tasks in the background.
 * New tasks can be added using the plugin architecture and creating a trigger that the Document Processor picks up and calls.
 *
 */
class DocumentProcessor
{
    /**
     * The indexer class
     */
    private $indexer = false;

    /**
     * Document processors
     */
    private $processors = false;

    /**
     * Number of documents per batch to be processed
     */
    private $limit = 20;

    /**
     * Initialise the indexer and processors
     *
     */
    public function __construct()
    {
        global $default;

        // Set the number of documents in a batch (config setting: indexer/batchDocuments)
        $max = $default->batchDocuments;
        $this->limit = (is_numeric($max)) ? $max : $this->limit;

        // Load processors
        $this->processors = $this->loadProcessors();

    	// Initialise the indexer
    	$this->indexer = Indexer::get();
    }

	/**
	 * Returns a reference to the main class
	 *
	 * @return DocumentProcessor
	 */
	public static function get()
	{
		static $singleton = null;

		if (is_null($singleton))
		{
			$singleton = new DocumentProcessor();
		}

		return $singleton;
	}

    private function loadProcessors()
    {
        // Get list of registered processors (plugins)
        $query = 'SELECT h.* FROM plugin_helper h
            INNER JOIN plugins p ON (p.namespace = h.plugin)
        	WHERE p.disabled = 0 AND h.classtype = "processor"';

        $results = DBUtil::getResultArray($query);

        if(PEAR::isError($results)){
            global $default;
            $default->log->debug('documentProcessor: error loading processors').' - '.$results->getMessage();
            return false;
        }

        if(empty($results)){
            return false;
        }

        $processors = array();

        foreach ($results as $item){
            $path = KTUtil::isAbsolutePath($item['pathname']) ? $item['pathname'] : KT_DIR . DIRECTORY_SEPARATOR . $item['pathname'];

            require_once($path);

            $processors[] = new $item['classname'];
        }

        usort($processors, 'orderProcessors');

        return $processors;
    }

    public function processQueue()
    {
        global $default;
        $default->log->debug('documentProcessor: starting');

        // Check for lock file to ensure processor is not currently running
        $cacheDir = $default->cacheDirectory;
        $lockFile = $cacheDir . DIRECTORY_SEPARATOR . 'document_processor.lock';

        if(file_exists($lockFile)){
            // lock file exists, exit
            $default->log->debug('documentProcessor: stopping, lock file in place '.$lockFile);
            return ;
        }

        if($default->enableIndexing){
            // Setup indexing - load extractors, run diagnostics
            if($this->indexer->preIndexingSetup() === false){
                $default->log->debug('documentProcessor: stopping - indexer setup failed.');
                return;
            }
        }

        // Get document queue
        $queue = $this->indexer->getDocumentsQueue($this->limit);

        if(empty($queue)){
            $default->log->debug('documentProcessor: stopping - no documents in processing queue');
            return ;
        }

        // indexing starting - create lock file
        touch($lockFile);


        // Process queue
        foreach($queue as $item){

            // Get the document object
            $document = Document::get($item['document_id']);

    	    if (PEAR::isError($document))
    	    {
    	        Indexer::unqueueDocument($docId, sprintf(_kt("indexDocuments: Cannot resolve document id %d: %s."),$docId, $document->getMessage()), 'error');
    	        continue;
    	    }

            // index document
            if($default->enableIndexing){
                $this->indexer->processDocument($document, $item);
            }

            // loop through processors
            if($this->processors !== false){
                foreach($this->processors as $processor){
                    $default->log->debug('documentProcessor: running processor: '.$processor->getNamespace());

                    // Check document mime type against supported types
                    if(!$this->isSupportedMimeType($item['mimetypes'], $processor->getSupportedMimeTypes())){
                        $default->log->debug('documentProcessor: not a supported mimetype: '.$item['mimetypes']);
                        continue;
                    }

                    // Process document
                    $processor->setDocument($document);
                    $processor->processDocument();
                }
            }
        }

        // update the indexer statistics
        $this->indexer->updateIndexStats();

        // Remove lock file to indicate processing has completed
        if(file_exists($lockFile)){
            @unlink($lockFile);
        }

        $default->log->debug('documentProcessor: stopping');
    }

    /**
     * Determines whether the document is a supported mime type
     *
     * @param string $mimeType
     * @param array $processorTypes
     * @return boolean
     */
    private function isSupportedMimeType($mimeType, $processorTypes){
        // Check $processorTypes is an array
        if(is_array($processorTypes)){
            if(!in_array($mimeType, $processorTypes)){
                return false;
            }
            return true;
        }

        // True if it supports all types, false if it supports none.
        return $processorTypes;
    }
}

abstract class BaseProcessor
{
    public $order;
    protected $document;
    protected $namespace;

    public function BaseProcessor()
    {
        // Constructor
    }

    /**
     * Returns the namespace of the processor
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the document object
     *
     * @param unknown_type $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }

    abstract public function processDocument();

    abstract public function getSupportedMimeTypes();

}

?>
