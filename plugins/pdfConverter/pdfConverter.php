<?php
/**
 * $Id: $
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
    protected $namespace = 'pdf.converter.processor';
    private $ooHost = '127.0.0.1';
    private $ooPort = 8100;

    /**
     * Constructor gets the connection to the java server
     *
     * @return pdfConverter
     */
    public function pdfConverter()
    {
        $config =& KTConfig::getSingleton();
		$javaServerUrl = $config->get('indexer/javaLuceneURL');
		$this->ooHost = $config->get('openoffice/host','127.0.0.1');
		$this->ooPort = $config->get('openoffice/port','8100');

		$this->xmlrpc = XmlRpcLucene::get($javaServerUrl);



    }

    /**
     * Check that open office is running
     *
     * @return boolean
     */
    private function checkOO()
    {
        $available = SearchHelper::checkOpenOfficeAvailablity();

        if(is_null($available)){
            return true;
        }

        return false;
    }

    /**
     * Gets the document path and calls the conversion function
     *
     * @return boolean
     */
    public function processDocument()
    {
        $oStorage = KTStorageManagerUtil::getSingleton();
        $path = $oStorage->temporaryFile($this->document);
        $ext = KTMime::getFileType($this->document->getMimeTypeID());

        if(!file_exists($path)){
            global $default;
            $default->log->debug('PDF Converter: Document, id: '.$this->document->iId.', does not exist at given storage path: '.$path);
            return sprintf(_kt("The document, id: %s, does not exist at the given storage path: %s") , $this->document->iId,$path);
        }

        // check for OO
        $available = $this->checkOO();

        // do pdf conversion
        if(!$available){
            global $default;
            $default->log->error("PDF Converter: Cannot connect to Open Office Server on host {$this->ooHost} : {$this->ooPort}");
            return _kt('Cannot connect to Open Office Server.');
        }

        $res = $this->convertFile($path, $ext);

        if($res !== true){
            global $default;
            $default->log->debug('PDF Converter: Document, id: '.$this->document->iId.', could not be converted to pdf.');
            return sprintf(_kt("The document, id: %s, could not be converted to pdf format. The following error occurred: \"%s\".") , $this->document->iId,$res);
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
//	    $aAcceptedMimeTypes = array('doc', 'ods', 'odt', 'ott', 'txt', 'rtf', 'sxw', 'stw',
//            //                                    'html', 'htm',
//            'xml' , 'pdb', 'psw', 'ods', 'ots', 'sxc',
//            'stc', 'dif', 'dbf', 'xls', 'xlt', 'slk', 'csv', 'pxl',
//            'odp', 'otp', 'sxi', 'sti', 'ppt', 'pot', 'sxd', 'odg',
//            'otg', 'std', 'asc');

        // work around for ms office xp and 2003 templates - the mime type is identical but the templates aren't supported
        if(!empty($fileType)){
            $types = array('dot', 'xlt', 'pot', 'htm');
            if(in_array($fileType, $types)){
                return false;
            }
        }

        // taken from the original list of accepted types in the pdf generator action
        $mime_types = array();
        $mime_types[] = 'text/plain';
        $mime_types[] = 'text/html';
        $mime_types[] = 'text/csv';
        $mime_types[] = 'text/rtf';

        // Office OLE2 - 2003, XP, etc
        $mime_types[] = 'application/msword';
        $mime_types[] = 'application/vnd.ms-powerpoint';
        $mime_types[] = 'application/vnd.ms-excel';

        // Star Office
        $mime_types[] = 'application/vnd.sun.xml.writer';
        //$mime_types[] = 'application/vnd.sun.xml.writer.template';
        $mime_types[] = 'application/vnd.sun.xml.calc';
        //$mime_types[] = 'application/vnd.sun.xml.calc.template';
        $mime_types[] = 'application/vnd.sun.xml.draw';
        //$mime_types[] = 'application/vnd.sun.xml.draw.template';
        $mime_types[] = 'application/vnd.sun.xml.impress';
        //$mime_types[] = 'application/vnd.sun.xml.impress.template';

        // Open Office
        $mime_types[] = 'application/vnd.oasis.opendocument.text';
        //$mime_types[] = 'application/vnd.oasis.opendocument.text-template';
        $mime_types[] = 'application/vnd.oasis.opendocument.graphics';
        //$mime_types[] = 'application/vnd.oasis.opendocument.graphics-template';
        $mime_types[] = 'application/vnd.oasis.opendocument.presentation';
        //$mime_types[] = 'application/vnd.oasis.opendocument.presentation-template';
        $mime_types[] = 'application/vnd.oasis.opendocument.spreadsheet';
        //$mime_types[] = 'application/vnd.oasis.opendocument.spreadsheet-template';

        /* OO3 */
        // Office 2007
        $mime_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
    	/* */

        return $mime_types;
	}

	/**
	 * Converts the given file to a pdf
	 *
	 * @param string $filename The full path to the file
	 * @param string $ext The extension of the file
	 * @return boolean
	 */
	function convertFile($filename, $ext)
	{
	    global $default;
	    $tempDir = $default->tmpDirectory;

	    // Create temporary copy of document
	    $sourceFile = tempnam($tempDir, 'pdfconverter') . '.' .$ext;
	    $res = @copy($filename, $sourceFile);

	    // Create a temporary file to store the converted document
	    $targetFile = tempnam($tempDir, 'pdfconverter') . '.pdf';

	    // Get contents and send to converter
        $result = $this->xmlrpc->convertDocument($sourceFile, $targetFile, $this->ooHost, $this->ooPort);

        if(is_string($result)){
            $default->log->error('PDF Converter Plugin: Conversion to PDF Failed');
            @unlink($sourceFile);
            @unlink($targetFile);
            return $result;
        }

        $pdfDir = $default->pdfDirectory;

        // Ensure the PDF directory exists
        if(!file_exists($pdfDir)){
            mkdir($pdfDir, 0755);
            touch($pdfDir.'/index.html');
            file_put_contents($pdfDir.'/index.html', 'You do not have permission to access this directory.');
        }

        $pdfFile = $pdfDir .'/'. $this->document->iId.'.pdf';

        // if a previous version of the pdf exists - delete it
        if(file_exists($pdfFile)){
            @unlink($pdfFile);
        }

        // Copy the generated pdf into the pdf directory
        $res = @copy($targetFile, $pdfFile);
        @unlink($sourceFile);
        @unlink($targetFile);
        return true;

    }
}
?>