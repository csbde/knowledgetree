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

require_once(KT_LIB_DIR . "/actions/documentviewlet.inc.php");
require_once(KT_DIR . '/search2/documentProcessor/documentProcessor.inc.php');

/**
 * Generates thumbnails of documents using the pdf converter output
 * Dependent on the pdfConverter
 */
class thumbnailGenerator extends BaseProcessor
{
    public $order = 3;
    protected $namespace = 'thumbnails.generator.processor';

    /**
     * Constructor
     *
     * @return thumbnailGenerator
     */
    public function thumbnailGenerator()
    {
    }

    /**
     * Gets the document path and calls the generator function
     *
     * @return boolean
     */
    public function processDocument()
    {
        // do the generation
        $res = $this->generateThumbnail();
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

        // work around for ms office xp and 2003 templates - the mime type is identical but the templates aren't supported
        if(!empty($fileType)){
            $types = array('dot', 'xlt', 'pot');
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

        // OO3
        // Office 2007
        $mime_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.template';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    	$mime_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    	//$mime_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';

    	// In addition PDF files are also supported
    	$mime_types[] = 'application/pdf';

        return $mime_types;
	}

	/**
	 * Generates the thumbnail from the pdf
	 *
	 * @return boolean
	 */
	private function generateThumbnail()
	{
	    /*
	       The thumbnail is displayed in the info panel and the document view
	       The info panel is in the plugin ktcore/documentpreview/
	           - add a hook in there but build the functionality in this plugin ie keep the plugins separate and don't create dependencies
	           - if the thumbnail plugin is disabled then maybe display a normal sized info panel

           The document view will display the thumbnail on the right in a document viewlet similar to the workflow viewlet
               - check out ktcore/KTDocumentViewlets.php
               - viewlet class is below
	    */
		global $default;

        $mimeTypeId = $this->document->getMimeTypeID();
        $mimeType = KTMime::getMimeTypeName($mimeTypeId);

	    // Get the pdf source file - if the document is a pdf then use the document as the source
	    if($mimeType == 'application/pdf') {
	        $pdfDir = $default->documentRoot;
            $pdfFile = $pdfDir . DIRECTORY_SEPARATOR . $this->document->getStoragePath();
	    } else {
    	    $pdfDir = $default->pdfDirectory;
            $pdfFile = $pdfDir .DIRECTORY_SEPARATOR. $this->document->iId.'.pdf';
	    }

        $thumbnaildir = $default->varDirectory.DIRECTORY_SEPARATOR.'thumbnails';

		if (stristr(PHP_OS,'WIN')) {
            $thumbnaildir = str_replace('/', '\\', $thumbnaildir);
            $pdfFile = str_replace('/', '\\', $pdfFile);
		}

        $thumbnailfile = $thumbnaildir.DIRECTORY_SEPARATOR.$this->document->iId.'.jpg';
        //if thumbail dir does not exist, generate one and add an index file to block access
        if (!file_exists($thumbnaildir)) {
        	 mkdir($thumbnaildir, 0755);
        	 touch($thumbnaildir.DIRECTORY_SEPARATOR.'index.html');
        	 file_put_contents($thumbnaildir.DIRECTORY_SEPARATOR.'index.html', 'You do not have permission to access this directory.');
        }

        // if there is no pdf that exists - hop out
        if(!file_exists($pdfFile)){
            $default->log->debug('Thumbnail Generator Plugin: PDF file does not exist, cannot generate a thumbnail');
            return false;
        }

        // if a previous version of the thumbnail exists - delete it
		if (file_exists($thumbnailfile)) {
			@unlink($thumbnailfile);
		}
        // do generation
        $pathConvert = (!empty($default->convertPath)) ? $default->convertPath : 'convert';
        // windows path may contain spaces
        if (stristr(PHP_OS,'WIN')) {
			$cmd = "\"{$pathConvert}\" \"{$pdfFile}[0]\" -resize 200x200 \"$thumbnailfile\"";
        }
		else {
			$cmd = "{$pathConvert} {$pdfFile}[0] -resize 200x200 $thumbnailfile";
		}

		$result = KTUtil::pexec($cmd);
        return true;
    }
}

class ThumbnailViewlet extends KTDocumentViewlet {
    var $sName = 'thumbnail.viewlets';

    public function display_viewlet() {
        // Get the document id
    	$documentId = $this->oDocument->getId();
    	if(!is_numeric($documentId)){
    	    return '';
    	}

    	return $this->renderThumbnail($documentId);
    }

    public function renderThumbnail($documentId) {
        // Set up the template
        $oKTTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oKTTemplating->loadTemplate('thumbnail_viewlet');
        if (is_null($oTemplate)){
            return '';
        }

        // Check that the thumbnail exists on disk
        global $default;
		$varDir = $default->varDirectory;
		$thumbnailCheck = $varDir . '/thumbnails/'.$documentId.'.jpg';

		// Use correct slashes for windows
		if (strpos(PHP_OS, 'WIN') !== false) {
			$thumbnailCheck = str_replace('/', '\\', $thumbnailCheck);
		}

		// if the thumbnail doesn't exist try to create it
		if (!file_exists($thumbnailCheck)){
            $thumbnailer = new thumbnailGenerator();
            $thumbnailer->setDocument($this->oDocument);
            $thumbnailer->processDocument();

            // if it still doesn't exist, return an empty string
			if (!file_exists($thumbnailCheck)) {
                return '';
            }
		}

		// check for existence and status of the instant view plugin
		$url = '';
        if (KTPluginUtil::pluginIsActive('instaview.processor.plugin'))
        {
             require_once KTPluginUtil::getPluginPath('instaview.processor.plugin') . 'instaViewLinkAction.php';
             $ivLinkAction = new instaViewLinkAction();
             $url = $ivLinkAction->getViewLink($documentId, 'document');
        }

        // Get the url to the thumbnail and render it
        // Ensure url has correct slashes
		$sHostPath = KTUtil::kt_url();
		$plugin_path = KTPluginUtil::getPluginPath('thumbnails.generator.processor.plugin');
		$thumbnailUrl = $plugin_path . 'thumbnail_view.php?documentId='.$documentId;
		$thumbnailUrl = str_replace('\\', '/', $thumbnailUrl);
		$thumbnailUrl = str_replace(KT_DIR, $sHostPath, $thumbnailUrl);

		$oTemplate->setData(array(
            'thumbnail' => $thumbnailUrl,
            'url' => $url
            ));
        return $oTemplate->render();
    }

    public function get_width($documentId){
    	global $default;
    	$varDir = $default->varDirectory;
		$thumbnailfile = $varDir . '/thumbnails/'.$documentId.'.jpg';
		if(file_exists($thumbnailfile)){
		    return 200;
		}
		return 0;
		//$size = getimagesize($thumbnailfile);
		//return $size[0];
    }
}

?>
