<?

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
	public function __construct()
	{
		parent::__construct();
		$config =& KTConfig::getSingleton();
		$indexPath = $config->get('indexer/luceneDirectory');
		$this->lucene = new Zend_Search_Lucene($indexPath, false);
	}

	/**
	 * Creates an index to be used.
	 *
	 */
	public static function createIndex()
	{
		$config =& KTConfig::getSingleton();
		$indexPath = $config->get('indexer/luceneDirectory');
		$lucene = new Zend_Search_Lucene($indexPath, true);
	}


	/**
	 * A refactored method to add the document to the index..
	 *
	 * @param int $docid
	 * @param string $content
	 * @param string $discussion
	 */
	private function addDocument($docid, $content, $discussion, $title='')
	{
		$doc = new Zend_Search_Lucene_Document();
		$doc->addField(Zend_Search_Lucene_Field::Text('DocumentID', PHPLuceneIndexer::longToString($docid)));
		$doc->addField(Zend_Search_Lucene_Field::Text('Content', $content, 'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::Text('Discussion', $discussion, 'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::Text('Title', $title, 'UTF-8'));
		$this->lucene->addDocument($doc);
	}

	/**
	 * Indexes a document based on a text file.
	 *
	 * @param int $docid
	 * @param string $textfile
	 * @return boolean
	 */
    protected function indexDocument($docid, $textfile, $title='')
    {
    	global $default;

    	if (!is_file($textfile))
    	{
    		$default->log->error("Attempting to index $docid $textfile but it is not available.");
    		return false;
    	}

    	list($content, $discussion) = $this->deleteDocument($docid);

    	$this->addDocument($docid, file_get_contents($textfile), $discussion, $title);

		return true;
    }

    /**
     * Indexes the content and discussions on a document.
     *
     * @param int $docid
     * @param string $textfile
     * @return boolean
     */
    protected function indexDocumentAndDiscussion($docid, $textfile, $title='')
    {
		global $default;

    	if (!is_file($textfile))
    	{
    		$default->log->error("Attempting to index $docid $textfile but it is not available.");
    		return false;
    	}

    	$this->deleteDocument($docid);

    	$this->addDocument($docid, file_get_contents($textfile), Indexer::getDiscussionText($docid), $title);

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
		list($content, $discussion, $title) = $this->deleteDocument($docid);

		$this->addDocument($docid, $content, Indexer::getDiscussionText($docid), $title);

		return true;
    }

    /**
     * Optimise the lucene index.
     * This can be called periodically to optimise performance and size of the lucene index.
     *
     */
    public function optimise()
    {
    	$this->lucene->optimize();
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
    		$title='';

    		$this->lucene->delete($hit);
    	}
    	return array($content, $discussion, $title);
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
        $query = Zend_Search_Lucene_Search_QueryParser::parse($query);

        $hits  = $this->lucene->find($query);
        foreach ($hits as $hit)
        {
            $document = $hit->getDocument();

            $document_id = PHPLuceneIndexer::stringToLong($document->DocumentID);
            $content =  $document->Content ;
            $discussion =  $document->Discussion ;
            $title = $document->Title;
            $score = $hit->score;

            // avoid adding duplicates. If it is in already, it has higher priority.
            if (!array_key_exists($document_id, $results) || $score > $results[$document_id]->Score)
            {
                $results[$document_id] = new QueryResultItem($document_id,  $score, $title,  $content, $discussion);
            }
        }
        return $results;
    }
}
?>