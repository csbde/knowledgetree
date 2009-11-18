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

/**
 * TODO: must fix text higlighting!
 * TODO: must check that expunge does not bomb out...
 */

require_once 'Zend/Search/Lucene.php';

class PHPLuceneIndexer extends Indexer
{
	/**
	 * @var Zend_Search_Lucene
	 */
	private $lucene;

	/**
	 * The constructor for PHP Lucene
	 *
	 * @param boolean $create Optional. If true, the lucene index will be recreated.
	 */
	public function __construct($catchException=false)
	{
		parent::__construct();
		$config =& KTConfig::getSingleton();
		$indexPath = $config->get('indexer/luceneDirectory');
		try
		{
			$this->lucene = new Zend_Search_Lucene($indexPath, false);
		}
		catch(Exception $ex)
		{
            if($ex->getMessage() == "Index doesn't exists in the specified directory."){
                $this->lucene = new Zend_Search_Lucene($indexPath, true);
            }else {
                $this->lucene = null;
                if (!$catchException)
                    throw $ex;
            }
		}
	}

	/**
	 * Creates an index to be used.
	 *
	 */
	public static function createIndex()
	{
		$config =& KTConfig::getSingleton();
		$indexPath = $config->get('indexer/luceneDirectory');
		new Zend_Search_Lucene($indexPath, true);
	}


	/**
	 * A refactored method to add the document to the index..
	 *
	 * @param int $docid
	 * @param string $content
	 * @param string $discussion
	 */
	private function addDocument($docid, $content, $discussion, $title, $version)
	{
        $teaser = substr($content, 0, 250);

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('DocumentID', PHPLuceneIndexer::longToString($docid)));
        $doc->addField(Zend_Search_Lucene_Field::Text('Content', $content, 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::unStored('Discussion', $discussion, 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::Text('Title', $title, 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::Text('Version', $version, 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::unIndexed('Summary', $teaser, 'UTF-8'));
        $this->lucene->addDocument($doc);
	}

	/**
	 * Indexes a document based on a text file.
	 *
	 * @param int $docid
	 * @param string $textfile
	 * @return boolean
	 */
    protected function indexDocument($docid, $textfile, $title, $version)
    {
    	global $default;

    	if (!is_file($textfile))
    	{
    		$default->log->error(sprintf(_kt("Attempting to index %d %s but it is not available."),$docid, $textfile));
    		return false;
    	}

    	list($content, $discussion, $title2, $version2) = $this->deleteDocument($docid);

    	$this->addDocument($docid, file_get_contents($textfile), $discussion, $title, $version);

		return true;
    }

    /**
     * Indexes the content and discussions on a document.
     *
     * @param int $docid
     * @param string $textfile
     * @return boolean
     */
    protected function indexDocumentAndDiscussion($docid, $textfile, $title, $version)
    {
		global $default;

    	if (!is_file($textfile))
    	{
    		$default->log->error(sprintf(_kt("Attempting to index %d %s but it is not available."),$docid, $textfile));
    		return false;
    	}

    	$this->deleteDocument($docid);

    	$this->addDocument($docid, file_get_contents($textfile), Indexer::getDiscussionText($docid), $title, $version);

    	return true;
    }

    /**
     * Indexes a discussion on a document..
     *
     * @param int $docid
     * @return boolean
     */
    protected function indexDiscussion($docid)
    {
		list($content, $discussion, $title, $version) = $this->deleteDocument($docid);

		$this->addDocument($docid, $content, Indexer::getDiscussionText($docid), $title, $version);

		return true;
    }

    /**
     * Optimise the lucene index.
     * This can be called periodically to optimise performance and size of the lucene index.
     *
     */
    public function optimise()
    {
    	parent::optimise();
    	$this->lucene->optimize();
    }

    /**
     * Returns the number of non-deleted documents in the index.
     *
     * @return int
     */
    public function getDocumentsInIndex()
    {
    	return $this->lucene->numDocs();
    }

    /**
     * Removes a document from the index.
     *
     * @param int $docid
     * @return array containing (content, discussion, title)
     */
    public function deleteDocument($docid)
    {
    	$content = '';
    	$discussion = '';
    	$query = Zend_Search_Lucene_Search_QueryParser::parse('DocumentID:' . PHPLuceneIndexer::longToString($docid));
    	$hits  = $this->lucene->find($query);
    	// there should only be one, but we'll loop for safety
    	foreach ($hits as $hit)
    	{
    		$content = $hit->Content;
    		$discussion = $hit->Discussion;
    		$title = $hit->Title;
    		$version = $hit->Version;

    		$this->lucene->delete($hit);
    	}
    	return array($content, $discussion, $title, $version);
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
        $queryDiscussion = stripos($query,'discussion') !== false;
        $queryContent = stripos($query,'content') !== false;
        $query = Zend_Search_Lucene_Search_QueryParser::parse($query);

        $hits  = $this->lucene->find($query);
        foreach ($hits as $hit)
        {
            $document = $hit->getDocument();

            $document_id = PHPLuceneIndexer::stringToLong($document->DocumentID);

            /*
            $coreText = '';
            if ($queryContent)
            {
            	$coreText .= $document->Content;
            }
            if ($queryDiscussion)
            {
            	$coreText .= $document->Discussion;
            }

            $content = $query->highlightMatches($coreText);
            */

            $teaser = $document->Summary;

            $content = $query->highlightMatches($teaser);

            $title = $document->Title;
            $score = $hit->score;

            // avoid adding duplicates. If it is in already, it has higher priority.
            if (!array_key_exists($document_id, $results) || $score > $results[$document_id]->Score)
            {
                $item = new DocumentResultItem($document_id, $score, $title, $content);
                $item = new QueryResultItem($document_id,  $score, $title,  $content);
                if ($item->CanBeReadByUser)
                {
                	$results[$document_id] = $item;
                }
            }
        }
        return $results;
    }

    /**
     * Diagnose the indexer. e.g. Check that the indexing server is running.
     *
     */
    public function diagnose()
    {
    	if ($this->lucene == null)
    	{
    		$indexer = $this->getDisplayName();
    		return sprintf(_kt("The %s has not been initialised correctly. Please review the documentation on how to setup the indexing."),$indexer);
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
		return _kt('Document Indexer Library');
	}

    public function isDocumentIndexed($documentId)
	{
        // do something
    }
}
?>