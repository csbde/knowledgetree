<?php
/*
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_DIR . '/search2/documentProcessor/documentProcessor.inc.php');
require_once(KT_DIR . '/search2/indexing/lib/XmlRpcLucene.inc.php');

/**
 * @todo Check if the trigger should be called on download
 *
 */
class pdfConverter extends BaseProcessor
{
    public $order = 2;

    public function pdfConverter()
    {
        $config =& KTConfig::getSingleton();
		$javaServerUrl = $config->get('indexer/javaLuceneURL');

		$this->xmlrpc = XmlRpcLucene::get($javaServerUrl);
    }

    public function processDocument()
    {
        $oStorage = KTStorageManagerUtil::getSingleton();
        $path = $oStorage->temporaryFile($this->document);

        if(!file_exists($path)){
            global $default;
            $default->log->debug('Document, id: '.$this->document->iId.', does not exist at given storage path: '.$path);
            return false;
        }

        // do pdf conversion
        $res = $this->convertFile($path);

        if($res === false){
            global $default;
            $default->log->debug('Document, id: '.$this->document->iId.', could not be converted to pdf.');
            return false;
        }

        return true;
    }

    /**
     * The supported mime types for the converter.
     *
     * @return array
     */
	public function getSupportedMimeTypes()
	{
	    // support all for now...
		return true; //array();
	}

	function convertFile($filename)
	{
	    // Get contents and send to converter
        $buffer = file_get_contents($filename);
        $buffer = $this->xmlrpc->convertDocument($buffer, 'pdf');

        if($buffer === false){
            return false;
        }

        global $default;
        $dir = $default->varDirectory . '/pdf/';
        $filename = $dir . $this->document->iId.'.pdf';

        file_put_contents($filename, $buffer);
        unset($buffer);

        return $filename;

    }
}
?>