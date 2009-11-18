<?php
/**
 * $Id$
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

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

require_once(KT_DIR . '/plugins/pdfConverter/pdfConverter.php');

class PDFGeneratorAction extends KTDocumentAction {
    var $sName = 'ktstandard.pdf.generate';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = 'Generate PDF';
    // Note: 'asc' below seems to be a catchall for plain text docs.
    //       'htm' and 'html' should work but are not so have been removed for now.
    var $aAcceptedMimeTypes = array('doc', 'ods', 'odt', 'ott', 'txt', 'rtf', 'sxw', 'stw',
            //                                    'html', 'htm',
            'xml' , 'pdb', 'psw', 'ods', 'ots', 'sxc',
            'stc', 'dif', 'dbf', 'xls', 'xlt', 'slk', 'csv', 'pxl',
            'odp', 'otp', 'sxi', 'sti', 'ppt', 'pot', 'sxd', 'odg',
            'otg', 'std', 'asc');

    function getDisplayName() {
        global $default;
        // The generation of the pdf is done through the PDF Converter plugin.
        // The PDF's are generated in the background by the document processor

        if(!empty($this->oDocument)){
            $iDocId = $this->oDocument->iId;

            // Build the display name and url
            $sDisplayName = _kt('Generate PDF');

            $sHostPath = KTUtil::kt_url();
            $icon = "<img src='{$sHostPath}/resources/mimetypes/pdf.gif' alt='PDF' border=0 />";
            $link = KTUtil::ktLink('action.php', 'ktstandard.pdf.generate', array( 'fDocumentId' => $this->oDocument->getId(), 'action' => 'pdfdownload'));
            $sDisplayLink = "&nbsp;<a href=\"{$link}\">{$icon}</a>";

            // First check if the pdf has already been generated
            $dir = $default->pdfDirectory;
            $file = $dir .'/'. $iDocId . '.pdf';

            if(file_exists($file)){
                // Display the download link
                return $sDisplayName . $sDisplayLink;
            }

            // If the file does not exist, check if the document has the correct mimetype
            $converter = new pdfConverter();
            $mimeTypes = $converter->getSupportedMimeTypes();
            $docType = $this->getMimeExtension();

            if($mimeTypes === true || in_array($docType, $mimeTypes)){
                // Display the download link
                return $sDisplayName . $sDisplayLink;
            }
        }else{
            // If the document is empty then we are probably in the workflow admin - action restrictions section, so we can display the name.
            return $sDisplayName;
        }

        return '';

        /*
		//$cmdpath = KTUtil::findCommand('externalBinary/python');
        // Check if openoffice and python are available

        if($cmdpath != false && file_exists($cmdpath) && !empty($cmdpath)) {
            $sDocType = $this->getMimeExtension();
            // make sure that the selected document is of an acceptable extension
            foreach($this->aAcceptedMimeTypes as $acceptType){
                if($acceptType == $sDocType){
    	            // build server path
    	            $sHostPath = KTUtil::kt_url();
                    // create image
                    $icon = "<img src='{$sHostPath}/resources/mimetypes/pdf.gif' alt='PDF' border=0 />";
                    $link = KTUtil::ktLink('action.php', 'ktstandard.pdf.generate', array( 'fDocumentId' => $this->oDocument->getId(), 'action' => 'pdfdownload'));
                    return _kt('Generate PDF') . "&nbsp;<a href=\"{$link}\">{$icon}</a>";
                }
            }
            // If the document is empty then we are probably in the workflow admin - action restrictions section, so we can display the name.
            if(empty($this->oDocument)){
                return _kt('Generate PDF');
            }
        }
        return '';
        */
    }

    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'label' => _kt('Convert Document to PDF'),
                    'action' => 'selectType',
                    'fail_action' => 'main',
                    'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
                    'submit_label' => _kt('Convert Document'),
                    'context' => &$this,
                    ));

        $oForm->setWidgets(array(
                    array('ktcore.widgets.selection', array(
                            'label' => _kt("Type of conversion"),
                            'description' => _kt('The following are the types of conversions you can perform on this document.'),
                            //'important_description' => _kt('QA NOTE: Permissions checks are required here...'),
                            'name' => 'convert_type',
                            //'vocab' => array('Download as PDF', 'Duplicate as PDF', 'Replace as PDF'),
                            'vocab' => array('Download as PDF'),
                            'simple_select' => true,
                            'required' => true,
                            )),
                    ));

        return $oForm;
    }

    function do_selectType() {

        switch($_REQUEST[data][convert_type]){
            case '0':
                $this->do_pdfdownload();
                break;
            case '1':
                $this->do_pdfduplicate();
                break;
            case '2':
                $this->do_pdfreplace();
                break;
            default:
                $this->do_pdfdownload();
        }
        redirect(KTUtil::ktLink( 'action.php', 'ktstandard.pdf.generate', array( "fDocumentId" => $this->oDocument->getId() ) ) );
        exit(0);
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        return $oTemplate->render();
    }

    /**
     * Method for getting the MIME type extension for the current document.
     *
     * @return string mime time extension
     */
    function getMimeExtension() {

        if($this->oDocument == null || $this->oDocument == "" || PEAR::isError($this->oDocument) ) return _kt('Unknown Type');

        $oDocument = $this->oDocument;
        $iMimeTypeId = $oDocument->getMimeTypeID();
        $mimetypename = KTMime::getMimeTypeName($iMimeTypeId); // mime type name

        // the pdf converter uses the mime type and not the extension.
        return $mimetypename;

        /*
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT filetypes FROM " . $sTable . " WHERE mimetypes = ?";
        $aQuery = array($sQuery, array($mimetypename));
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        } else if (count($res) != 0){
            return $res[0]['filetypes'];
        }

        return _kt('Unknown Type');
        */
    }

    /**
     * Method to download the pdf.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public function do_pdfdownload()
    {
        global $default;
        $iDocId = $this->oDocument->iId;

        // Check if pdf has already been created
        $dir = $default->pdfDirectory;
        $file = $dir .'/'. $iDocId . '.pdf';
        $mimetype = 'application/pdf';
        $size = filesize($file);

        // Set the filename
        $name = $this->oDocument->getFileName();
        $aName = explode('.', $name);
        array_pop($aName);
        $name = implode('.', $aName) . '.pdf';


        if(file_exists($file)){
            if(KTUtil::download($file, $mimetype, $size, $name) === false){
                $default->log->error('PDF Generator: PDF file could not be downloaded because it doesn\'t exist');
                $this->errorRedirectToMain(_kt('PDF file could not be downloaded because it doesn\'t exist'));
            }
            exit();
        }

        // If not - create one
        $converter = new pdfConverter();
        $converter->setDocument($this->oDocument);
        $res = $converter->processDocument();

        if($res !== true){
            // please contact your System Administrator
            $default->log->error('PDF Generator: PDF file could not be generated');
            $this->errorRedirectToMain($res . ' ' . _kt('Please contact your System Administrator for assistance.'));
            exit();
        }

        if(file_exists($file)){
            if(KTUtil::download($file, $mimetype, $size, $name) === false){
                $default->log->error('PDF Generator: PDF file could not be downloaded because it doesn\'t exist');
                $this->errorRedirectToMain(_kt('PDF file could not be downloaded because it doesn\'t exist'));
            }
            exit();
        }

        // Check if this is a office 2007 doc
        $mime = $this->getMimeExtension();

        $o2007_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    	$o2007_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    	$o2007_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        if(in_array($mime, $o2007_types)){
            $error = _kt('The document is an MS Office 2007 format. This may not be supported by your version of OpenOffice. Please contact your System Administrator for assistance');
            $default->log->error('PDF Generator: Document is an MS Office 2007 format. OpenOffice must be version 3.0 or higher to support this format. Please upgrade to the latest version.');
        }else{
            $error = _kt('PDF file could not be generated. The format may not be supported by your version of OpenOffice. Please contact your System Administrator for assistance');
            $default->log->error('PDF Generator: PDF file could not be generated. The format may not be supported by your version of OpenOffice. Please check that you have the latest version installed.');
        }
        $this->errorRedirectToMain($error);
        exit();
    }

    /**
     * Method for downloading the document as a pdf.
     *
     * @deprecated
     * @return true on success else false
     */
    function do_pdfdownload_deprecated() {

        $oDocument = $this->oDocument;
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oConfig =& KTConfig::getSingleton();
        $default = realpath(str_replace('\\','/',KT_DIR . '/../openoffice/program'));
        putenv('ooProgramPath=' . $oConfig->get('openoffice/programPath', $default));
		$cmdpath = KTUtil::findCommand('externalBinary/python');
        // Check if openoffice and python are available
        if($cmdpath == false || !file_exists($cmdpath) || empty($cmdpath)) {
            // Set the error messsage and redirect to view document
            $this->addErrorMessage(_kt('An error occurred generating the PDF - please contact the system administrator. Python binary not found.'));
            redirect(generateControllerLink('viewDocument',sprintf('fDocumentId=%d',$oDocument->getId())));
            exit(0);
        }

        //get the actual path to the document on the server
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oStorage->getPath($oDocument));

        if (file_exists($sPath)) {

            // Get a tmp file
            $sTempFilename = tempnam('/tmp', 'ktpdf');

            // We need to handle Windows differently - as usual ;)
            if (substr( PHP_OS, 0, 3) == 'WIN') {

                $cmd = "\"" . $cmdpath . "\" \"". KT_DIR . "/bin/openoffice/pdfgen.py\" \"" . $sPath . "\" \"" . $sTempFilename . "\"";
                $cmd = str_replace( '/','\\',$cmd);

                // TODO: Check for more errors here
                // SECURTIY: Ensure $sPath and $sTempFilename are safe or they could be used to excecute arbitrary commands!
                // Excecute the python script. TODO: Check this works with Windows
                $res = `"$cmd" 2>&1`;
                //print($res);
                //print($cmd);
                //exit;

            } else {

                // TODO: Check for more errors here
                // SECURTIY: Ensure $sPath and $sTempFilename are safe or they could be used to excecute arbitrary commands!
                // Excecute the python script.
                $cmd = $cmdpath . ' ' . KT_DIR . '/bin/openoffice/pdfgen.py ' . escapeshellcmd($sPath) . ' ' . escapeshellcmd($sTempFilename);
                $res = shell_exec($cmd." 2>&1");
                //print($res);
                //print($cmd);
                //exit;

            }

            // Check the tempfile exists and the python script did not return anything (which would indicate an error)
            if (file_exists($sTempFilename) && $res == '') {

                $mimetype = 'application/pdf';
                $size = filesize($sTempFilename);
                $name = substr($oDocument->getFileName(), 0, strrpos($oDocument->getFileName(), '.') ) . '.pdf';
                KTUtil::download($sTempFilename, $mimetype, $size, $name);

                // Remove the tempfile
                unlink($sTempFilename);

                // Create the document transaction
                $oDocumentTransaction = & new DocumentTransaction($oDocument, 'Document downloaded as PDF', 'ktcore.transactions.download', $aOptions);
                $oDocumentTransaction->create();
                // Just stop here - the content has already been sent.
                exit(0);

            } else {
                // Set the error messsage and redirect to view document
                $this->addErrorMessage(sprintf(_kt('An error occurred generating the PDF - please contact the system administrator. %s') , $res));
                redirect(generateControllerLink('viewDocument',sprintf('fDocumentId=%d',$oDocument->getId())));
                exit(0);
            }

        } else {
            // Set the error messsage and redirect to view document
            $this->addErrorMessage(_kt('An error occurred generating the PDF - please contact the system administrator. The path to the document did not exist.'));
            redirect(generateControllerLink('viewDocument',sprintf('fDocumentId=%d',$oDocument->getId())));
            exit(0);
        }


    }

    /**
     * Method for duplicating the document as a pdf.
     *
     */
    function do_pdfduplicate() {

        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        $this->addErrorMessage(_kt('NOT IMPLEMENTED YET: This will create a pdf copy of the document as a new document.'));
        return $oTemplate->render();

    }

    /**
     * Method for replacing the document as a pdf.
     *
     */
    function do_pdfreplace() {

        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        $this->addErrorMessage(_kt('NOT IMPLEMENTED YET: This will replace the document with a pdf copy of the document.'));
        return $oTemplate->render();

    }
}
?>
