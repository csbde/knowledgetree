<?php

require_once(KT_DIR . '/tests/test.php');

class DocumentExtractorsTestCase extends KTUnitTestCase {

    function setup()
    {
        //Indexer::checkForRegisteredTypes();
        $this->indexer = Indexer::get();
        $this->path = KT_DIR . '/tests/documentProcessor/dataset/';
        $this->tempPath = KT_DIR . '/var/tmp';
    }

    function tearDown()
    {
    }

    /**
     * Extract the text from a word (.doc) document
     */
    function testExtractWord()
    {
        // Get extracted test
        $text = $this->extractText('word_doc', 'doc', 'application/msword');

        // Run test
        $this->assertTrue(strpos($text, 'Human reason, in one sphere of its cognition, is called upon') !== false);

        // unset text
        unset($text);
    }

    /**
     * Extract text from an excel document (.xls)
     */
    function testExtractExcel()
    {
        // Get extracted test
        $text = $this->extractText('excel_doc', 'xls', 'application/vnd.ms-excel');

        // Run test
        $this->assertTrue(strpos($text, 'The time has come the walrus sang') !== false);

        unset($text);
    }

    /**
     * Extract text from a powerpoint document (.ppt)
     */
    function testExtractPowerPoint()
    {
        // Get extracted test
        $text = $this->extractText('powerpoint_doc', 'ppt', 'application/vnd.ms-powerpoint');

        // Run test
        $this->assertTrue(strpos($text, 'Human reason, in one sphere of its cognition, is called upon') !== false);

        unset($text);
    }

    function testExtractPDF()
    {
        // Get extracted test
        $text = $this->extractText('pdf_doc', 'pdf', 'application/pdf');

        // Run test
        $this->assertTrue(strpos($text, 'Human reason, in one sphere of its cognition, is called upon') !== false);

        unset($text);
    }

    function testI18N()
    {
        // Get extracted test
        $text = $this->extractText('i18n_doc', 'doc', 'application/msword');
        //$text = utf8_decode($text);

        // extracted text is utf-8 encoded - compare to utf-8 encoded text
        // Run tests
        // arabic
        $this->assertTrue(strpos($text, 'فلووةتبنيمشسغداأ') !== false);

        // bulgarian
        $this->assertTrue(strpos($text, 'фвсеийнскят') !== false);

        // chinese - simplified
        $this->assertTrue(strpos($text, '外遇的家伙德国莫泊桑') !== false);

        // hebrew
        $this->assertTrue(strpos($text, 'הערבמתגן') !== false);

        // russian
        $this->assertTrue(strpos($text, 'ДлдвспинтГт') !== false);

        unset($text);
    }

    function extractText($sourceFile, $extension, $mimeType)
    {
        static $extractors = array();

        // get extractor
        $query = "select me.id, me.name from mime_types mt
            INNER JOIN mime_extractors me ON mt.extractor_id = me.id
            WHERE filetypes = '{$extension}'";

        $res = DBUtil::getOneResult($query);

        // On first run the mime_extractors table is empty - populate it for the tests
        if(empty($res) || PEAR::isError($res)){
            $this->indexer->registerTypes(true);

            $query = "select me.id, me.name from mime_types mt
                INNER JOIN mime_extractors me ON mt.extractor_id = me.id
                WHERE filetypes = '{$extension}'";

            $res = DBUtil::getOneResult($query);
        }

        // Instantiate extractor
        if(array_key_exists($res['name'], $extractors)){
            $extractor = $extractors[$res['name']];
        }else{
            $extractor = $this->indexer->getExtractor($res['name']);
            $extractors[$res['name']] = $extractor;
        }

        $this->assertNotNull($extractor);
        if(empty($extractor)) return '';

        // Extract content
        $targetFile = tempnam($this->tempPath, 'ktindexer');

        $extractor->setSourceFile($this->path . $sourceFile);
        $extractor->setTargetFile($targetFile);
        $extractor->setMimeType($mimeType);
        $extractor->setExtension($extension);

        $extractor->extractTextContent();

        $text = file_get_contents($targetFile);
        $text = $this->filterText($text);

        @unlink($targetFile);
        return $text;
    }

    function filterText($text)
    {
        $src = array("([\r\n])","([\n][\n])","([\n])","([\t])",'([ ][ ])');
    	$tgt = array("\n","\n",' ',' ',' ');

    	// shrink what is being stored.
    	do
    	{
    		$orig = $text;
    		$text = preg_replace($src, $tgt, $text);
    	} while ($text != $orig);

    	return $text;
    }
}

?>
