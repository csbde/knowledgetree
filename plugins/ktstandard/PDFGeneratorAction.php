<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
        // We need to handle Windows differently - as usual ;)
        if (substr( PHP_OS, 0, 3) == 'WIN') {
            $cmdpath = KT_DIR . "/../openoffice/openoffice/program/python.bat";
            $cmdpath = str_replace( '/','\\',$cmdpath);   
        } else {
            $cmdpath = "../openoffice/program/python";
        }
        // Check if openoffice and python are available
        if(file_exists($cmdpath)) {
            $sDocType = $this->getMimeExtension();
            // make sure that the selected document id of an acceptable extension
            foreach($this->aAcceptedMimeTypes as $acceptType){
                if($acceptType == $sDocType){
                    return _kt('Generate PDF') . "&nbsp;<a href=\"" . KTUtil::ktLink( 'action.php', 'ktstandard.pdf.generate', array( "fDocumentId" => $this->oDocument->getId(), "action" => "pdfdownload") ) . "\" <img src='resources/mimetypes/pdf.png' alt='PDF' border=0/></a>";
                }
            }
        }
        return '';
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
    }

    /**
     * Method for downloading the document as a pdf.
     *
     * @return true on success else false 
     */
    function do_pdfdownload() {

        $oDocument = $this->oDocument;
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oConfig =& KTConfig::getSingleton();

        //get the actual path to the document on the server
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oStorage->getPath($oDocument));

        if (file_exists($sPath)) {

            // Get a tmp file
            $sTempFilename = tempnam('/tmp', 'ktpdf');

            // We need to handle Windows differently - as usual ;)
            if (substr( PHP_OS, 0, 3) == 'WIN') {

                $cmd = "\"" . KT_DIR . "/../openoffice/openoffice/program/python.bat\" \"". KT_DIR . "/bin/openoffice/pdfgen.py\" \"" . $sPath . "\" \"" . $sTempFilename . "\"";
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
                $cmd = '../openoffice/program/python bin/openoffice/pdfgen.py ' . escapeshellcmd($sPath) . ' ' . escapeshellcmd($sTempFilename);
                $res = shell_exec($cmd." 2>&1");
                //print($res);
                //print($cmd);
                //exit;

            }

            // Check the tempfile exists and the python script did not return anything (which would indicate an error) 
            if (file_exists($sTempFilename) && $res == '') {

                $sUrlEncodedFileName = substr($oDocument->getFileName(), 0, strrpos($oDocument->getFileName(), '.') );
                $browser = $_SERVER['HTTP_USER_AGENT'];
                if ( strpos( strtoupper( $browser), 'MSIE') !== false) {
                    $sUrlEncodedFileName = rawurlencode($sUrlEncodedFileName);
                }
                // Set the correct headers
                header("Content-Type: application/pdf");
                header("Content-Length: ". filesize($sTempFilename));
                header("Content-Disposition: attachment; filename=\"" . $sUrlEncodedFileName . ".pdf\"");
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                // HTTP/1.1
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);

                // HTTP/1.0
                // header("Pragma: no-cache"); // Don't send this header! It breaks IE.
                
                // Get a filelike object and send it to the browser
                $oFile = new KTFSFileLike($sTempFilename);
                KTFileLikeUtil::send_contents($oFile);
                // Remove the tempfile
                unlink($sTempFilename);

                // Create the document transaction
                $oDocumentTransaction = & new DocumentTransaction($oDocument, 'Document downloaded as PDF', 'ktcore.transactions.download', $aOptions);
                $oDocumentTransaction->create();
                // Just stop here - the content has already been sent.
                exit(0);  

            } else {
                // Set the error messsage and redirect to view document
                $this->addErrorMessage(_kt('An error occurred generating the PDF - please contact the system administrator.<br>' . $res));
                redirect(generateControllerLink('viewDocument',sprintf(_kt('fDocumentId=%d'),$oDocument->getId())));
                exit(0);  
            }

        } else {
            // Set the error messsage and redirect to view document
            $this->addErrorMessage(_kt('An error occurred generating the PDF - please contact the system administrator.<br>The path to the document did not exist.'));
            redirect(generateControllerLink('viewDocument',sprintf(_kt('fDocumentId=%d'),$oDocument->getId())));
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
