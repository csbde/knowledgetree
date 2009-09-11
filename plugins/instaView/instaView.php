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

/**
 * Generates the flash viewer for inline document viewing
 * Depends on the thumbnail generation plugin and the pdf converter plugin
 */
class instaView extends BaseProcessor
{
    public $order = 4;
    protected $namespace = 'instaview.generator.processor';

    /**
     * Constructor
     *
     * @return instaView
     */
    public function instaView()
    {
    }

    /**
     * Calls the function to generate the flash preview
     *
     * @return boolean
     */
    public function processDocument()
    {
        // do swf conversion
        $res = $this->generateFlash();
        return $res;
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
        $mime_types[] = 'application/vnd.sun.xml.writer.template';
        $mime_types[] = 'application/vnd.sun.xml.calc';
        $mime_types[] = 'application/vnd.sun.xml.calc.template';
        $mime_types[] = 'application/vnd.sun.xml.draw';
        $mime_types[] = 'application/vnd.sun.xml.draw.template';
        $mime_types[] = 'application/vnd.sun.xml.impress';
        $mime_types[] = 'application/vnd.sun.xml.impress.template';

        // Open Office
        $mime_types[] = 'application/vnd.oasis.opendocument.text';
        $mime_types[] = 'application/vnd.oasis.opendocument.text-template';
        $mime_types[] = 'application/vnd.oasis.opendocument.graphics';
        $mime_types[] = 'application/vnd.oasis.opendocument.graphics-template';
        $mime_types[] = 'application/vnd.oasis.opendocument.presentation';
        $mime_types[] = 'application/vnd.oasis.opendocument.presentation-template';
        $mime_types[] = 'application/vnd.oasis.opendocument.spreadsheet';
        $mime_types[] = 'application/vnd.oasis.opendocument.spreadsheet-template';

        /* OO3
        // Office 2007
        $mime_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
    	*/

        return $mime_types;
	}

	/**
	 * Merges the flash template with the pdf of the document
	 *
	 * @return boolean
	 */
	private function generateFlash()
	{
	    /*
	       The template can be stored in a resources folder under the plugin and merged in with the pdf below.
	    */
	    global $default;

	    $pdfDir = $default->pdfDirectory;
        $pdfFile = $pdfDir .'/'. $this->document->iId.'.pdf';
        $swfFile = $pdfDir .'/'. $this->document->iId.'.swf';
       
         // if a previous version of the swf file exists - delete it
        if(file_exists($swfFile)){
            @unlink($swfFile);
        }
        //check if the pdf exists
        if(!file_exists($pdfFile)){
            global $default;
            $default->log->debug('InstaView Plugin: PDF file does not exist, cannot generate the flash view');
            return false;
        }

        // do merge
        $output = shell_exec("pdf2swf $pdfFile -o $swfFile");
        return true;
    }
}
?>