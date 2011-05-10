<?php
/**
 * $Id: $
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
 */

require_once(KT_LIB_DIR . '/actions/documentviewlet.inc.php');
require_once(KT_LIB_DIR . '/browse/advancedcolumns.inc.php');
require_once(KT_DIR . '/search2/documentProcessor/documentProcessor.inc.php');

/**
 * Generates thumbnails of documents using the pdf converter output
 * Dependent on the pdfConverter
 */
class thumbnailGenerator extends BaseProcessor {

    public $order = 3;
    protected $namespace = 'thumbnails.generator.processor';

    /**
     * Gets the document path and calls the generator function
     *
     * @return boolean
     */
    public function processDocument()
    {
        return $this->generateThumbnail();
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
        if (!empty($fileType)) {
            $types = array('dot', 'xlt', 'pot');
            if (in_array($fileType, $types)) {
                return false;
            }
        }

        // taken from the original list of accepted types in the pdf generator action
        $mimeTypes = array();
        $mimeTypes[] = 'text/plain';
        $mimeTypes[] = 'text/html';
        $mimeTypes[] = 'text/csv';
        $mimeTypes[] = 'text/rtf';

        // Office OLE2 - 2003, XP, etc
        $mimeTypes[] = 'application/msword';
        $mimeTypes[] = 'application/vnd.ms-powerpoint';
        $mimeTypes[] = 'application/vnd.ms-excel';

        // Star Office
        $mimeTypes[] = 'application/vnd.sun.xml.writer';
        //$mimeTypes[] = 'application/vnd.sun.xml.writer.template';
        $mimeTypes[] = 'application/vnd.sun.xml.calc';
        //$mimeTypes[] = 'application/vnd.sun.xml.calc.template';
        $mimeTypes[] = 'application/vnd.sun.xml.draw';
        //$mimeTypes[] = 'application/vnd.sun.xml.draw.template';
        $mimeTypes[] = 'application/vnd.sun.xml.impress';
        //$mimeTypes[] = 'application/vnd.sun.xml.impress.template';

        // Open Office
        $mimeTypes[] = 'application/vnd.oasis.opendocument.text';
        //$mimeTypes[] = 'application/vnd.oasis.opendocument.text-template';
        $mimeTypes[] = 'application/vnd.oasis.opendocument.graphics';
        //$mimeTypes[] = 'application/vnd.oasis.opendocument.graphics-template';
        $mimeTypes[] = 'application/vnd.oasis.opendocument.presentation';
        //$mimeTypes[] = 'application/vnd.oasis.opendocument.presentation-template';
        $mimeTypes[] = 'application/vnd.oasis.opendocument.spreadsheet';
        //$mimeTypes[] = 'application/vnd.oasis.opendocument.spreadsheet-template';

        // OO3
        // Office 2007
        $mimeTypes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        //$mimeTypes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
        //$mimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.template';
        //$mimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
        $mimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        $mimeTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        //$mimeTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';

        // In addition PDF and (standard) Image files are also supported
        $mimeTypes[] = 'application/pdf';

        $query = "SELECT DISTINCT mimetypes FROM mime_types WHERE mimetypes LIKE 'image/%'";
        $tempRes = DBUtil::getResultArray($query);
        $count = count($tempRes);
        for ($i = 0; $i < $count; ++$i) {
            $mimeTypes[] = $tempRes[$i]['mimetypes'];
        }

        return $mimeTypes;
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
        $storageManager = KTStorageManagerUtil::getSingleton();
        global $default;

        $type = 'pdf'; // default type expected
        $mimeTypeId = $this->document->getMimeTypeID();
        $mimeType = KTMime::getMimeTypeName($mimeTypeId);

        // Check document type: Image or PDF
        if (strstr($mimeType, 'image')) {
            $type = 'image';
            $srcDir = $default->documentRoot;
            $srcFile = $srcDir . DIRECTORY_SEPARATOR . $this->document->getStoragePath();
        }
        // Get the pdf source file - if the document is a pdf then use the document as the source
        else if ($mimeType == 'application/pdf') {
            $pdfDir = $default->documentRoot;
            $srcFile = $pdfDir . DIRECTORY_SEPARATOR . $this->document->getStoragePath();
        }
        else {
            $pdfDir = $default->pdfDirectory;
            $srcFile = $pdfDir  . DIRECTORY_SEPARATOR .  $this->document->iId.'.pdf';
        }

        $thumbnailDir = $default->varDirectory . DIRECTORY_SEPARATOR . 'thumbnails';

        if (stristr(PHP_OS,'WIN')) {
            $thumbnailDir = str_replace('/', '\\', $thumbnailDir);
            $srcFile = str_replace('/', '\\', $srcFile);
        }

        $thumbnailFile = $thumbnailDir . DIRECTORY_SEPARATOR . $this->document->iId . '.jpg';
        //if thumbail dir does not exist, generate one and add an index file to block access
        if (!$storageManager->file_exists($thumbnailDir)) {
            $storageManager->mkdir($thumbnailDir, 0755);
        }

        if (!$storageManager->file_exists($thumbnailDir . DIRECTORY_SEPARATOR . 'index.html')) {
            $storageManager->touch($thumbnailDir . DIRECTORY_SEPARATOR . 'index.html');
            $storageManager->file_put_contents($thumbnailDir . DIRECTORY_SEPARATOR . 'index.html', 'You do not have permission to access this directory.');
        }

        // if there is no pdf that exists - hop out
        if (!$storageManager->file_exists($srcFile)) {
            $default->log->debug('Thumbnail Generator Plugin: Source file for conversion does not exist, cannot generate a thumbnail');
            return false;
        }

        // if a previous version of the thumbnail exists - delete it
        if ($storageManager->file_exists($thumbnailFile)) {
            $storageManager->unlink($thumbnailFile);
        }

        // do generation
        $pathConvert = (!empty($default->convertPath)) ? $default->convertPath : 'convert';
        // If its a pdf or tiff, just convert first page.
        $pageNumber = ($type == 'pdf' ? "[0]" : ($mimeType == 'image/tiff' ? "[0]" : ""));

        $cmd = "\"$pathConvert\" -thumbnail 200 -limit area 10mb \"$srcFile" . $pageNumber . "\" \"$thumbnailFile\"";

        $default->log->debug($cmd);

        $output = KTUtil::pexec($cmd);

        // Log the output
        if (isset($output['out'])) {
            $out = $output['out'];
            if (is_array($out)) {
                $out = array_pop($out);
            }

            if (strpos($out, 'ERROR') === 0) {
                $default->log->error('Thumbnails Plugin: error in creation of document thumbnail '.$this->document->iId.': '. $out);
                return false;
            }
        }

        // Check thumbnail exists and set the flag in the DB
        if ($storageManager->file_exists($thumbnailFile)) {
            // 0 = nothing, 1 = pdf, 2 = thumbnail, 4 = flash
            // 1+2 = 3: pdf & thumbnail; 1+4 = 5: pdf & flash; 2+4 = 6: thumbnail & flash; 1+2+4 = 7: all
            $flag = $this->document->getHasRendition();

            if (is_numeric($flag)) {
                if (in_array($flag, array(0, 1, 4, 5))) {
                    $flag = $flag + 2;
                }
            }
            else {
                $flag = 2;
            }

            $this->document->setHasRendition($flag);
            $this->document->update();
        }

        return true;
    }

}

class ThumbnailViewlet extends KTDocumentViewlet {

    var $sName = 'thumbnail.viewlets';

    var $bShowIfReadShared = true;
    var $bShowIfWriteShared = true;

    public function display_viewlet() {
        // Get the document id
        $documentId = $this->oDocument->getId();
        if (!is_numeric($documentId)) {
            return '';
        }

        // Get the CSS to render the thumbnail
        global $main;
        $main->requireCSSResource('plugins/thumbnails/resources/thumbnails.css');

        return $this->renderThumbnail($documentId);
    }

    public function renderThumbnail($documentId, $height = null, $modal = null)
    {

        global $default;
        $thumbnailCheck = $default->varDirectory . '/thumbnails/' . $documentId . '.jpg';

        // Use correct slashes for windows
        if (strpos(PHP_OS, 'WIN') !== false) {
            $thumbnailCheck = str_replace('/', '\\', $thumbnailCheck);
        }

        // if the thumbnail doesn't exist, and this is an on-premise installation, try to create it
        $storageManager = KTStorageManagerUtil::getSingleton();
        if (!$storageManager->file_exists($thumbnailCheck) && !ACCOUNT_ROUTING_ENABLED) {
            $thumbnailer = new thumbnailGenerator();
            $thumbnailer->setDocument($this->oDocument);
            $thumbnailer->processDocument();

            // if it still doesn't exist, return an empty string
            if (!$storageManager->file_exists($thumbnailCheck)) {
                // TODO: This is where we differentiate between failed thumbnails and in process thumbnails
                return '';
            }
        }
        else {
            return '';
        }

        // QUESTION Is this still relevant?
        // check for existence and status of the instant view plugin
        $url = '';
        $title = '';
        if (KTPluginUtil::pluginIsActive('instaview.processor.plugin')) {
            require_once KTPluginUtil::getPluginPath('instaview.processor.plugin') . 'instaViewLinkAction.php';
            $ivLinkAction = new instaViewLinkAction();

            if (is_null($modal)) {
                $modal = $ivLinkAction->isImage($documentId);
            }

            if ($modal) { // If it requires a modal window, it only needs the document content
                $url = $ivLinkAction->getViewLink($documentId, 'document_content');
                $this->loadLightBox(); // Load lightbox effects
            }
            else { // Needs the file content
                $url = $ivLinkAction->getViewLink($documentId, 'document');
            }

            $title = $ivLinkAction->getName($documentId);
        }

        $hostPath = KTUtil::kt_url();
        $pluginPath = KTPluginUtil::getPluginPath('thumbnails.generator.processor.plugin');
        $thumbnailUrl = $pluginPath . 'thumbnail_view.php?documentId='.$documentId;
        $thumbnailUrl = str_replace('\\', '/', $thumbnailUrl);
        $thumbnailUrl = str_replace(KT_DIR, $hostPath, $thumbnailUrl);

        $templateData = array(
            'documentId' => $documentId,
            'thumbnail' => $thumbnailUrl,
            'url' => $url,
            'modal' => $modal,
            'title' => $title
       );

        if (is_numeric($height)) {
            $templateData['height'] = $height;
        }

        $template->setData($templateData);

        return $template->render();
    }

    public function loadLightBox()
    {
        global $main;
        // jQuery and lightbox
        $main->requireJSResource('resources/lightbox/js/jquery.js');
        $main->requireJSResource('resources/lightbox/js/jquery.lightbox-0.5.min.js');
        $main->requireCSSResource('resources/lightbox/css/lightbox.css');
    }

    // determines whether the image exists and returns the maximum aspect to display;
    // this is used for anywhere which might require display resizing based on the presence or absence of the thumbnail
    public function getDisplaySize($documentId)
    {
        global $default;
        $storageManager = KTStorageManagerUtil::getSingleton();
        $thumbnailFile = $default->varDirectory . '/thumbnails/' . $documentId . '.jpg';
        if ($storageManager->file_exists($thumbnailFile)) {
            return 200;
        }

        return 0;
    }

}

/**
 * Displays a column in the Browse view of the document thumbnail
 */
class ThumbnailColumn extends AdvancedColumn {

    public $name = 'thumnailcolumn';
    public $namespace = 'thumbnails.generator.column';

    public function ThumbnailColumn()
    {
        $this->label = _kt('Thumbnail');
    }

    public function renderHeader($sReturnURL)
    {
        // Get the CSS to render the thumbnail
        global $main;
        $main->requireCSSResource('plugins/thumbnails/resources/thumbnails.css');
        return '&nbsp;';
    }

    /**
     * Render the thumbnail for the given document
     *
     * @param array $dataRow
     * @return string HTML
     */
    public function renderData($dataRow)
    {
        if ($dataRow['type'] == 'document') {
            $storageManager = KTStorageManagerUtil::getSingleton();
            $docId = $dataRow['docid'];
            $document = $dataRow['document'];

            $config = KTConfig::getSingleton();
            $height = $config->get('browse/thumbnail_height', 75);
            $rootUrl = $config->get('KnowledgeTree/rootUrl');

            // Check if the thumbnail exists
            global $default;
            $thumbnailCheck = $default->varDirectory . '/thumbnails/' . $docId . '.jpg';

            // Use correct slashes for windows
            if (strpos(PHP_OS, 'WIN') !== false) {
                $thumbnailCheck = str_replace('/', '\\', $thumbnailCheck);
            }

            // We won't try generate one - will slow down browsing too much
            if (!$storageManager->file_exists($thumbnailCheck)) {
                //TODO: Differentiate
                $noPreviewSrc="{$rootUrl}/resources/graphics/no_preview.png";
                $tag = '
    		      <div class="thumb-shadow">
    		          <img src="' . $noPreviewSrc . '" height="' . $height . '" />
		          </div>';

                return $tag;
            }

            // hook into thumbnail plugin to get display for thumbnail
            $thumbnailer = new ThumbnailViewlet();
            $thumbnailer->setDocument($document);
            $thumbnailDisplay = $thumbnailer->renderThumbnail($docId, $height);

            return $thumbnailDisplay;
        }

        return '';
    }

}

?>
