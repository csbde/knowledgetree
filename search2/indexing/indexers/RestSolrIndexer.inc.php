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

require_once('indexing/lib/RestSolr.inc.php');

class RestSolrIndexer extends Indexer
{
	/**
	 * @var $solr connection instance
	 */
	private $solr;
	private $solrServerUrl;

	/**
	 * The constructor for REST SOLR class
	 */
	public function __construct()
	{
		parent::__construct();
		
		// TODO solr config from config_settings
		$config =& KTConfig::getSingleton();
		$this->solrServerUrl = $config->get('indexer/javaLuceneURL');
		// need to replace any instance of http(s):// at the start of the url, and split the port number and /solr/ part if present
		preg_match('/h?t?t?p?s?:?\/?\/?([^:]*)\:?([^\/]*)(\/?\w*\/?)/', $this->solrServerUrl, $matches);
		$host = $matches[1];
		$port = !empty($matches[2]) ? $matches[2] : '';
		$solrBase = !empty($matches[3]) ? $matches[3] : '/solr/';
		if (ACCOUNT_ROUTING_ENABLED) {
            $solrCore = ACCOUNT_NAME;
		} else {
		    $solrCore = !empty($matches[4]) ? $matches[4] : null;
		}
		$this->solr = new RestSolr($host, $port, $solrBase, $solrCore);
	}

	/**
	 * Creates an index to be used.
	 *
	 */
	public static function createIndex()
	{
		// do nothing. The java lucene indexer will create the indexes if required
	}
	
	/**
	 * Process a document - extract text and index it
	 * Refactored from indexDocuments()
	 *
	 * @param unknown_type $docinfo
	 */
	public function processDocument($document, $docinfo, $extract = false)
	{
	    // if $extractorClass is TikaApacheExtractor, don't need to do any extraction - check this further down
	    $extractorClass = $docinfo['extractor'];
	    if ($extractorClass != 'TikaApacheExtractor') {
	        $extract = true;
	    }
	    
	    $this->solr->setExtract(!$extract);
	    
	    parent::processDocument($document, $docinfo, $extract);
	}

	/**
	 * Indexes a document based on a text file.
	 *
	 * @param int $docId
	 * @param string $textfile
	 * @return boolean
	 */
    public function indexDocument($docId, $textfile, $title, $version)
    {
    	try
    	{                        
            //Indexing the document with Tika extraction.
            $this->logPreIndex($docinfo, $textfile, $indexDiscussion);	
            $result = $this->solr->addDocument($docId, $textfile, '', $title, $version);
            $this->logPostIndex($textfile, $result);
            
            return $result;
    	}
    	catch(Exception $e)
    	{
    		return false;
    	}
    }
    
    /**
     * Temporary function for logging info during implementation and testing
     */
    private function logPreIndex($docinfo, $textfile, $indexDiscussion)
    {
        global $default;
        $default->log->info('SOLR - document id : ' . var_export($docinfo['document_id'], true));
        $default->log->info('SOLR - document path : ' . var_export($textfile, true));
        $default->log->info('SOLR - document discussion : ' . var_export($indexDiscussion, true));
        $default->log->info('SOLR - document title : ' . var_export($docinfo, true));
        $default->log->info('SOLR - document version : ' . var_export($docinfo, true));
    }
    
    /**
     * Temporary function for logging info during implementation and testing
     */
    private function logPostIndex($textfile, $result)
    {
        global $default;
        $default->log->info('SOLR - SENDIN FILE : ' . $textfile);
        $default->log->info('SOLR - POST RESULT : ' . var_export($result, true));
    }

    /**
     * Indexes the content and discussions on a document.
     *
     * @param int $docId
     * @param string $textfile
     * @return boolean
     */
    protected function indexDocumentAndDiscussion($docId, $textfile, $title, $version)
    {
    	try
    	{
	    	$discussion = Indexer::getDiscussionText($docId);
	    	$this->logPreIndex($docinfo, $textfile, $indexDiscussion);	
            $result = $this->solr->addDocument($docId, $textfile, $discussion, $title, $version);
            $this->logPostIndex($textfile, $result);
    		return $result;
    	}
    	catch(Exception $e)
    	{
    		return false;
    	}
    }

    /**
     * Indexes a discussion on a document..
     *
     * @param int $docId
     * @return boolean
     */
    // FIXME this won't work with solar most likely (without modification, that is)
    protected function indexDiscussion($docId)
    {
    	try
    	{
    		$discussion = Indexer::getDiscussionText($docId);
    		if (empty($discussion))
    		{
    			return true;
    		}
    		return $this->solr->updateDiscussion($docId, $discussion);
    	}
    	catch(Exception $e)
    	{
    		return false;
    	}

		return true;
    }

    /**
     * Optimise the solr index.
     * This can be called periodically to optimise performance and size of the solr index.
     *
     * TODO chances are fair this does not work?
     */
    public function optimise()
    {
    	parent::optimise();
    	$this->solr->optimise();
    }

    /**
     * Removes a document from the index.
     *
     * @param int $docId
     * @return array containing (content, discussion, title)
     */
    public function deleteDocument($docId)
    {
    	return $this->solr->deleteDocument($docId);
    }

    /**
     * Shut down the solr server
     *
     * NOTE this probably doesn't work, comes from JavaXMLRPCLuceneIndexer
     */
    public function shutdown()
    {
    	$this->solr->shutdown();
    }


    /**
     * Enter description here...
     *
     * @param string $query
     * @return array
     */
    public function query($query)
    {
    	$results = array();
    	$hits = $this->solr->query($query);
    	if (is_array($hits))
    	{
    		foreach ($hits as $hit)
    		{
    			$document_id 	= $hit->DocumentID;

    			// avoid adding duplicates. If it is in already, it has higher priority.
    			if (!array_key_exists($document_id, $results) || $score > $results[$document_id]->Rank)
    			{
    				try
    				{
    					$item = new DocumentResultItem($document_id, $hit->Rank, $hit->Title, $hit->Content, null, $this->inclStatus);

    					if ($item->CanBeReadByUser)
    					{
    						$results[$document_id] = $item;
    					}
    				}
    				catch(IndexerInconsistencyException $ex)
    				{
    				    // if the status is not set to 1 (LIVE) and the document is not in the DB then delete from the index
    				    // if the status is set to 1 then the document may be archived or deleted in the DB so leave in the index
    				    if(!$this->inclStatus){
        					$this->deleteDocument($document_id);
        					$default->log->info("Document Indexer inconsistency: $document_id has been found in document index but is not in the database.");
    				    }
    				}
    			}
    		}
    	}
    	else
    	{
			 $_SESSION['KTErrorMessage'][] = _kt('The Document Indexer did not respond correctly. Your search results will not include content results. Please notify the system administrator to investigate why the Document Indexer is not running.');
    	}
        return $results;
    }

    /**
     * Diagnose the indexer. e.g. Check that the indexing server is running.
     *
     */
    public function diagnose()
    {
		$connection = $this->solr->ping();
		if (false === $connection)
		{
			return sprintf(_kt("Cannot connect to the %s on '%s'."), $this->getDisplayName(), $this->solrServerUrl);
		}

		return null;

    }

    /**
     * Returns the name of the indexer.
     *
     * @return string
     */
	public function getDisplayName()
	{
		return _kt('Solr Document Indexer Service');
	}


    /**
     * Returns the number of non-deleted documents in the index.
     *
     * @return int
     */
    public function getDocumentsInIndex()
    {
    	try {
    	    $stats = $this->solr->getStatistics();
    	}
    	catch (Exception $e) {
    	    return _kt('Not Available');
    	}
    	
    	if ($stats === false || !is_object($stats))
    	{
    		return _kt('Not Available');
    	}
    	
    	return $stats->index->numDocs;
    }

    /**
     * Returns the path to the index directory
     *
     * @return string
     */
    public function getIndexDirectory()
    {
    	try {
    	    $stats = $this->solr->getStatistics();
    	}
    	catch (Exception $e) {
    	    return false;
    	}
    	
    	if ($stats === false || !is_object($stats))
    	{
    		return false;
    	}
    	return $stats->index->directory;
    }

    // TODO from JavaXMLRPCLucene and not likely to work here without modification
    public function isDocumentIndexed($document_id)
    {
    	return $this->solr->documentExists($document_id);
    }


}
?>
