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
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTFolderAddDocumentAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.addDocument';
    var $_sShowPermission = "ktcore.permissions.write";
    var $oDocumentType = null;

    function getDisplayName() {
        return _kt('Add Document');
    }

    function check() {
        $res = parent::check();
        if (empty($res)) {
            return $res;
        }
        
        $postExpected = KTUtil::arrayGet($_REQUEST, "postExpected");
        $postReceived = KTUtil::arrayGet($_REQUEST, "postReceived");
        if (!empty($postExpected)) {
            $aErrorOptions = array(
                'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
                'message' => _kt('Upload larger than maximum POST size (post_max_size variable in .htaccess or php.ini)'),
            );
            $this->oValidator->notEmpty($postReceived, $aErrorOptions);
        }
        return true;
    }

    function form_initialdata() {
        $oForm = new KTForm;
        
        $oForm->setOptions(array(
            'label' => _kt("Add a document"),
            'action' => 'processInitialData',
            'actionparams' => 'postExpected=1&fFolderId='.$this->oFolder->getId(),
        //    'cancel_action' => KTBrowseUtil::getUrlForFolder($this->oFolder),
            'fail_action' => 'main',
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery("","",true),
            'submit_label' => _kt("Add"),
            'file_upload' => true,
        ));
        
        $aTypes;
        foreach (DocumentType::getListForUserAndFolder($this->oUser, $this->oFolder) as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aTypes[] = $oDocumentType;
            }
        }        
        $oForm->setWidgets(array(
            array('ktcore.widgets.file',array(
                'label' => _kt('File'),
                'description' => _kt('The contents of the document to be added to the document management system.'),
                'name' => 'file',
                'required' => true,
            )),
            array('ktcore.widgets.string',array(
                'label' => _kt('Document Title'),
                'description' => sprintf(_kt('The document title is used as the main name of a document throughout %s&trade;.'), APP_NAME),
                'name' => 'document_name',
                'required' => true,
            )),
            array('ktcore.widgets.entityselection',array(
                'label' => _kt('Document Type'),
                'description' => _kt('Document Types, defined by the administrator, are used to categorise documents. Please select a Document Type from the list below.'),
                'name' => 'document_type',
                'required' => true,
                'vocab' => $aTypes,
                'initial_string' => _kt('- Please select a document type -'),
                'id_method' => 'getId',
                'label_method' => 'getName',
                'simple_select' => false,
            )),                        
        ));
        
        $oForm->setValidators(array(
            array('ktcore.validators.file', array(
                'test' => 'file',
                'output' => 'file',
            )),
            array('ktcore.validators.string', array(
                'test' => 'document_name',
                'output' => 'document_name',
            )),
            array('ktcore.validators.entity', array(
                'test' => 'document_type',
                'output' => 'document_type',
                'class' => 'DocumentType',
                'ids' => true,
            )),                        
        ));
        
        return $oForm;
    }
    
    function getFieldsetsForType($iTypeId) {
        $typeid = KTUtil::getId($iTypeId);
        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($typeid, array('ids' => false));
        
        $fieldsets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
        return $fieldsets;        
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Add a document"));
        $oForm = $this->form_initialdata();
        return $oForm->renderPage(_kt('Add a document to: ') . $this->oFolder->getName());
    }    
    
    function do_processInitialData() {
        $oForm = $this->form_initialdata();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }        
        $data = $res['results'];
        $key = KTUtil::randomString(32);

        
        // joy joy, we need to store the file first, or PHP will (helpfully) 
        // clean it up for us

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");        
        
        $sFilename = tempnam($sBasedir, 'kt_storecontents');
        $oContents = new KTFSFileLike($data['file']['tmp_name']);
        $oOutputFile = new KTFSFileLike($sFilename);
        $res = KTFileLikeUtil::copy_contents($oContents, $oOutputFile);   
        $data['file']['tmp_name'] = $sFilename;     

        if (PEAR::isError($res)) {
            $oForm->handleError(sprintf(_kt("Failed to store file: %s"), $res->getMessage()));
        }
        $_SESSION['_add_data'] = array($key => $data);        
        
        // if we don't need metadata
        $fieldsets = $this->getFieldsetsForType($data['document_type']);
        if (empty($fieldsets)) {
            return $this->successRedirectTo('finalise', _kt("File uploaded successfully. Processing."), sprintf("fFileKey=%s", $key));        
        }
        
        // if we need metadata
        
        $this->successRedirectTo('metadata', _kt("File uploaded successfully.  Please fill in the metadata below."), sprintf("fFileKey=%s", $key));
    }
    
    function form_metadata($sess_key) {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'identifier' => 'ktcore.document.add',
            'label' => _kt('Specify Metadata'),
            'submit_label' => _kt('Save Document'),
            'action' => 'finalise',
            'fail_action' => 'metadata',
         //   'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery("","",true),
        ));
    
        $oFReg =& KTFieldsetRegistry::getSingleton();
        
        $doctypeid = $_SESSION['_add_data'][$sess_key]['document_type'];
        
        $widgets = array();
        $validators = array();
        $fieldsets = $this->getFieldsetsForType($doctypeid);
        
        foreach ($fieldsets as $oFieldset) {
            $widgets = kt_array_merge($widgets, $oFReg->widgetsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
            $validators = kt_array_merge($validators, $oFReg->validatorsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));                
        }
        
        $oForm->setWidgets($widgets);
        $oForm->setValidators($validators);                
    
        return $oForm;
    }
    
    function do_metadata() {
        $this->persistParams(array('fFileKey'));
        
        $oForm = $this->form_metadata($_REQUEST['fFileKey']);
        return $oForm->render();
    }
    
    function do_finalise() {
        $this->persistParams(array('fFileKey'));    
        $sess_key = $_REQUEST['fFileKey'];
        $oForm = $this->form_metadata($sess_key);
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();        
        }
        $data = $res['results'];
        
        $extra_d = $_SESSION['_add_data'][$sess_key];
        $doctypeid = $extra_d['document_type'];
        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($doctypeid, array('ids' => false));
        $fieldsets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
        

        $MDPack = array();
        foreach ($fieldsets as $oFieldset) {
            $fields = $oFieldset->getFields();
            $values = (array) KTUtil::arrayGet($data, 'fieldset_' . $oFieldset->getId());

            foreach ($fields as $oField) {
                $val = KTUtil::arrayGet($values, 'metadata_' . $oField->getId());        
                // ALT.METADATA.LAYER.DIE.DIE.DIE
                if (!is_null($val)) {    
                    $MDPack[] = array(
                        $oField,
                        $val
                    );
                }

            }
        }
        // older code 

        $mpo =& new JavascriptObserver($this);
        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->addObserver($mpo);
        
        require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
        require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
    	    'max_str_len' => 200,
        );
        
        $aFile = $this->oValidator->validateFile($extra_d['file'], $aErrorOptions);
        $sTitle = sanitizeForSQL($extra_d['document_name']);
        
        $iFolderId = $this->oFolder->getId();
        $aOptions = array(
            'contents' => new KTFSFileLike($aFile['tmp_name']),
            'documenttype' => DocumentType::get($extra_d['document_type']),
            'metadata' => $MDPack,
            'description' => $sTitle,
            'cleanup_initial_file' => true,
        );

        $mpo->start();
        $this->startTransaction();
        $oDocument =& KTDocumentUtil::add($this->oFolder, basename($aFile['name']), $this->oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $message = $oDocument->getMessage();
            $this->errorRedirectTo('main',sprintf(_kt("Unexpected failure to add document - %s"), $message), 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }
        $this->addInfoMessage(_kt("Document added"));

        $this->commitTransaction();
        $mpo->redirectToDocument($oDocument->getId());
        exit(0);

    }
   
}

?>
