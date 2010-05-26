<?php

require_once(KT_DIR . '/tests/test.php');
require_once(KT_DIR . '/search2/indexing/lib/XmlRpcLucene.inc.php');

class GuidInserterTestCase extends KTUnitTestCase {

    function setup()
    {
        $config =& KTConfig::getSingleton();
		$javaServerUrl = $config->get('indexer/javaLuceneURL');
		$this->xmlrpc = XmlRpcLucene::get($javaServerUrl);

		$this->path = KT_DIR . '/tests/documentProcessor/dataset/';
        $this->tempPath = KT_DIR . '/var/tmp/';
    }

    function tearDown()
    {
    }

    /**
     * Insert into a word (.doc) document
     */
    function testInsertWord()
    {
        $guid = 'doc_1897';
        $file = 'word_doc';

        // Insert guid
        $this->insertGuid($file, $guid);

        // read guid
        $metadata = $this->readMetadata($file);

        // Run test
        $this->assertTrue(isset($metadata['KTGuid']));
        $this->assertTrue($metadata['KTGuid'] == $guid);

        unset($metadata);
    }

    /**
     * Insert into an excel (.xls) document
     */
    function testInsertExcel()
    {
        $guid = 'excel_2304';
        $file = 'excel_doc';

        // Insert guid
        $this->insertGuid($file, $guid);

        // read guid
        $metadata = $this->readMetadata($file);

        // Run test
        $this->assertTrue(isset($metadata['KTGuid']));
        $this->assertTrue($metadata['KTGuid'] == $guid);

        unset($metadata);
    }

    /**
     * Insert into a powerpoint (.ppt) document
     */
    function testInsertPowerPoint()
    {
        $guid = 'powerpoint_9328';
        $file = 'powerpoint_doc';

        // Insert guid
        $this->insertGuid($file, $guid);

        // read guid
        $metadata = $this->readMetadata($file);

        // Run test
        $this->assertTrue(isset($metadata['KTGuid']));
        $this->assertTrue($metadata['KTGuid'] == $guid);

        unset($metadata);
    }

    function insertGuid($filename, $guid)
    {
    	$oStorage = KTStorageManagerUtil::getSingleton();
        $buffer = $oStorage->file_get_contents($this->path . $filename);

        $metadata = array(
            "KTGuid" => $guid,
        );

        $modified = $this->xmlrpc->writeProperties($buffer, $metadata);
        unset($buffer);

        if($modified === false){
            return false;
        }

        $oStorage->file_put_contents($this->tempPath . $filename, $modified);
        unset($modified);
        return true;
    }

    function readMetadata($filename)
    {
    	$oStorage = KTStorageManagerUtil::getSingleton();
        $buffer = $oStorage->file_get_contents($this->tempPath . $filename);

        $metadata = $this->xmlrpc->readProperties($buffer);

        unset($buffer);
        $oStorage->unlink($this->tempPath . $filename);
        return $metadata;
    }
}

?>