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

class TikaApacheExtractor extends DocumentExtractor
{
    public function __construct()
    {
		$config =& KTConfig::getSingleton();
		$javaServerUrl = $config->get('indexer/javaLuceneURL');
		$this->xmlrpc = XmlRpcLucene::get($javaServerUrl);
    }

    /**
     * Display name for the extractor
     *
     * @return string
     */
    public function getDisplayName()
    {
        return _kt('Tika Apache Extractor');
    }

	public function needsIntermediateSourceFile()
	{
		return true;
	}

    /**
     * The mime types supported by the extractor
     *
     * @return array
     */
    public function getSupportedMimeTypes()
    {
        return array(
                // pdf
                'application/pdf',
                // office OLE2 format - 2003, xp, etc
                'application/vnd.ms-excel',
                'application/vnd.ms-powerpoint',
                'application/msword',
                // msg files
                'application/vnd.ms-outlook',
                // rtf
                'text/rtf',
                // staroffice
                'application/vnd.sun.xml.writer',
                'application/vnd.sun.xml.writer.template',
                'application/vnd.sun.xml.calc',
                'application/vnd.sun.xml.calc.template',
                // text
    			'text/plain',
    			'text/csv',
    			'text/tab-separated-values',
    			'text/css',
    			// open xml
    			/*
    			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    			'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
    			'application/vnd.openxmlformats-officedocument.presentationml.template',
    			'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
    			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    			'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
    			// openoffice
           		'application/vnd.oasis.opendocument.presentation',
           		'application/vnd.oasis.opendocument.presentation-template',
           		'application/vnd.oasis.opendocument.spreadsheet',
           		'application/vnd.oasis.opendocument.spreadsheet-template',
        		'application/vnd.oasis.opendocument.text',
        		'application/vnd.oasis.opendocument.text-template',
        		'application/vnd.oasis.opendocument.text-master',
        		// xml
				'text/xml',
				'application/xml',
				'text/html',
				'text/enriched'
				*/
            );
    }

    /**
     * Method to extract the content
     *
     * @return boolean
     */
    public function extractTextContent()
    {
        $filename = $this->sourcefile;
        $targetFile = $this->targetfile;

        $result = $this->xmlrpc->extractTextContent($filename, $targetFile);

        if($result === false){
            $this->output = _kt('Tika Extractor: XML-RPC failed to extract text.');
            return false;
        }
        return true;

        /* Using streamed content
        // stream document content
        $filename = $this->sourcefile;
        $buffer = file_get_contents($filename);

        if(empty($buffer)){
            $this->output =  _kt('Document contained no content');
            return false;
        }

        // Pass the content stream to the XML-RPC for extraction
        $extractedText = $this->xmlrpc->extractTextContent($buffer);
        unset($buffer);

        if($extractedText === false){
            $this->output = _kt('Tika Extractor: XML-RPC failed to extract text.');
            return false;
        }

        file_put_contents($this->targetfile, $extractedText);
        unset($extractedText);
        return true;
        */
    }

    /**
     * Method to determine whether a connection can be established with the java server
     */
    public function diagnose()
    {
        // check that the java server is running and can be accessed
        $config =& KTConfig::getSingleton();

		$javaLuceneURL = $config->get('indexer/javaLuceneURL');

		list($protocol, $host, $port) = explode(':', $javaLuceneURL);
		if (empty($port)) $port == 8875;
		if (substr($host, 0, 2) == '//') $host = substr($host, 2);

		$connection = @fsockopen($host, $port, $errno, $errstr, 2);
		if (false === $connection)
		{
			return sprintf(_kt("Cannot connect to the Tika Extractor on '%s'."), $javaLuceneURL);
		}
		fclose($connection);

		return null;
    }
}

?>
