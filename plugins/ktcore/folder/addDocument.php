<?php
/**
 * $Id$
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

    function getButton(){
        $btn = array();
        $btn['display_text'] = _kt('Upload');
        $btn['arrow_class'] = 'arrow_upload';
        return $btn;
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
        //  'cancel_action' => KTBrowseUtil::getUrlForFolder($this->oFolder),
            'fail_action' => 'main',
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery("","",true),
            'submit_label' => _kt("Add"),
            'file_upload' => true,
        	'onload' => true,
        ));

        $aTypes = array();
        foreach (DocumentType::getListForUserAndFolder($this->oUser, $this->oFolder) as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aTypes[] = $oDocumentType;
            }
        }

        // Onchange gets the name of the file and inserts it as the document title.
        $sFileOnchange = "javascript:
            var doc = document.getElementById('document_name');
            if(doc.value == ''){
                var arrPath=this.value.split('/');
                if(arrPath.length == 1){
                    var arrPath=this.value.split('\\\');
                }
                var name=arrPath[arrPath.length-1];
                var name=name.split('.');
                var len = name.length;
                if(len > 1){
                    if(name[len-1].length <= 4){
                        name.pop();
                    }
                }
                var title=name.join('.');
                doc.value=title;
            }";

        $oForm->setWidgets(array(
            array('ktcore.widgets.file',array(
                'label' => _kt('File'),
                'description' => _kt('The contents of the document to be added to the document management system.'),
                'name' => 'file',
                'required' => true,
                'onchange' => $sFileOnchange,
            )),
            array('ktcore.widgets.string',array(
                'label' => _kt('Document Title'),
                'description' => sprintf(_kt('The document title is used as the main name of a document throughout %s.'), APP_NAME),
                'name' => 'document_name',
                'required' => true,
                'id' => 'document_name',
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

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $oForm->addWidget(array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                )));
            $oForm->addWidget(array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                )));
            $oForm->addWidget(array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                )));
            $oForm->addWidget(array('ktcore.widgets.reason', array(
                'label' => _kt('Reason'),
                'description' => _kt('Please specify why you are checking out this document.  It will assist other users in understanding why you have locked this file.  Please bear in mind that you can use a maximum of <strong>250</strong> characters.'),
                'name' => 'reason',
                )));
        }

        $oForm->setValidators(array(
            array('ktcore.validators.file', array(
                'test' => 'file',
                'output' => 'file',
            )),
            array('ktcore.validators.fileillegalchar', array(
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

        if($default->enableESignatures){
            $oForm->addValidator(array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getId(),
                'type' => 'folder',
                'action' => 'ktcore.transactions.add_document',
                'test' => 'info',
                'output' => 'info'
            )));
        }

        return $oForm;
    }

    /**
     * The merged upload form for ktlive Bulk and Single upload actions. It makes use of the swf upload utility
     * TODO: Fall back to the original file upload widget if flash isn't available
     * 
     * @return KTForm
     */
	function getUploadForm() {
	    global $default;
	    
	    $default->log->info('KTCore - sessid : ' . session_id());
	    
		$this->oPage->setBreadcrumbDetails(_kt("Upload"));

		//Adding the required Bulk Upload javascript includes
		$aJavascript[] = 'resources/js/taillog.js';
		$aJavascript[] = 'resources/js/conditional_usage.js';
		$aJavascript[] = 'resources/js/kt_bulkupload.js';

		//Loading the widget js libraries to support dynamic "Ajax Loaded" widget rendering
		//FIXME: The widgets can support this via dynamic call to place libs in the head if they aren't loaded
		//       jQuery can do this but need time to implement/test. Browsers might not like this due to security
        //       concerns though.		

		$aJavascript[] = 'thirdpartyjs/jquery/jquery-1.3.2.js';
		$aJavascript[] = 'thirdpartyjs/tinymce/jscripts/tiny_mce/tiny_mce.js';
		$aJavascript[] = 'resources/js/kt_tinymce_init.js';
    	$aJavascript[] = 'thirdpartyjs/tinymce/jscripts/tiny_mce/jquery.tinymce.js';
    	$aJavascript[] = 'resources/js/conditional_selection.js';
    	$aJavascript[] = 'resources/js/kt_upload.js';
    	
		$this->oPage->requireJSResources($aJavascript);

		//FIXME: Might really not need to load these styles, will check l8r
		//$this->oPage->requireCSSResource('resources/css/kt-treewidget.css')}

		//Getting the supported types to display in message
        $fs =& new KTZipImportStorage($fileName, $fileData);
        $sFormats = $fs->getFormats();
		
        //Creating the form
		$oForm = new KTForm;
		$oForm->setOptions(array(
            'identifier' => 'ktcore.folder.upload',
            'label' => _kt('Upload'),
            'submit_label' => _kt('Submit'),
            'action' => 'upload',
            'fail_action' => 'main',
            'encoding' => 'multipart/form-data',
            'file_upload' => true,
		//  'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery("","",true),
            'description' => _kt("The upload facility allows for one or a number of documents to be added to the document management system. Provide a single document or an archive (ZIP) file from your local computer. The maximum upload size limit is 4GB per file. The following (ZIP) files are supported ($sFormats)"),
		));

		$oWF =& KTWidgetFactory::getSingleton();

		$widgets = array();
		$validators = array();

        $widgets[] = $oWF->get('ktcore.widgets.swffileselect',array(
                'label' => _kt('File'),
                'description' => _kt('The document(s) to be added to the document management system.'),
                'name' => 'swffile',
                'fFolderId' => $this->oFolder->getId(),        
                'required' => true,
                'onchange' => $sFileOnchange,
        ));
        
        $aVocab[] = ' I would like the documents to be extracted.';
        
		//Adding option to provide user with a choice to extract documents on server or just upload the archive as is.
		$widgets[] = $oWF->get('ktcore.widgets.selection',array(
                'label' => _kt('Would you like the documents to be extracted on the server.'),
				'id' => 'extract-documents',
                'description' => _kt('If you choose not to extract the archive will be uploaded as is.'),
                'name' => 'fExtractDocuments',
                'required' => false,
                'vocab' => $aVocab,
                'simple_select' => true,
                'multi' => true,
		));
        
		$aVocab = array('' => _kt('- Please select a document type -'));
        foreach (DocumentType::getListForUserAndFolder($this->oUser, $this->oFolder) as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
            }
        }
		
		//Adding document type lookup widget
		$widgets[] = $oWF->get('ktcore.widgets.selection',array(
                'label' => _kt('Document Type'),
				'id' => 'add-document-type',
                'description' => _kt('Document Types, defined by the administrator, are used to categorise documents. Please select a Document Type from the list below.'),
                'name' => 'fDocumentTypeId',
                'required' => true,
                'vocab' => $aVocab,
                'id_method' => 'getId',
                'label_method' => 'getName',
                'simple_select' => false,
		));

		$oFReg =& KTFieldsetRegistry::getSingleton();

		$activesets = KTFieldset::getGenericFieldsets();
		foreach ($activesets as $oFieldset) {
			$widgets = kt_array_merge($widgets, $oFReg->widgetsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
			$validators = kt_array_merge($validators, $oFReg->validatorsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
		}

		//Adding the type_metadata_fields layer to be updated via ajax for non generic metadata fieldsets
		$widgets[] = $oWF->get('ktcore.widgets.layer',array(
                'value' => '',
				'id' => 'type_metadata_fields',
		));

		$oForm->setWidgets($widgets);
		$oForm->setValidators($validators);

		// Implement an electronic signature for accessing the admin section, it will appear every 10 minutes
		global $default;
		$iFolderId = $this->oFolder->getId();
		if($default->enableESignatures){
			$sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
			$heading = _kt('You are attempting to perform a bulk upload');
			$submit['type'] = 'button';
			$submit['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.bulk_upload', 'bulk', 'bulk_upload_form', 'submit', {$iFolderId});";
		}else{
			$submit['type'] = 'submit';
			$submit['onclick'] = '';
		}

		return $oForm;
	}
	
	/**
	 * Handles the SWF intemediary upload for KTLive
	 * 
	 */
	public function do_liveDocumentUpload() {
	    global $default;
	    
	    $default->log->info('KTCore - Performing Live Upload');
	    
        $oStorage = KTStorageManagerUtil::getSingleton();
        
        /*
        //TODO: Finish validation for KTLive upload
	    $default->log->info('KTCore - StorageManager::getSingleton()');
	            
        $oForm = $this->form_initialdata();
	    $default->log->info('KTCore - form::form_initiaildata()');
        
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $default->log->info('KTCore - Validation Error | ' . var_export($res['errors'], true)); 
                        
            if(!isset($res['errors']['file'])){
                $aError['file'] = array(_kt('Please reselect the file to upload.'));
	            $default->log->error('KTCore - Please reselect the file to upload |' . var_dump($res, true));
            }
            $default->log->error('KTCore - Validation Error |' . var_dump($aError, true));
            $default->log->error('KTCore - Validation Error |' . var_dump($res, true));
            
            return $oForm->handleError('', $aError);
        }
        */
        
        //$data = $res['results'];
        $aData = $_REQUEST['data'];

        $performExtractOnServer = $aData['fExtractDocuments'];
        $default->log->info('KTCore Live Upload : Weather or not to perform upload : ' . $performExtractOnServer);
        
        $fileData = $_FILES;
        $key = KTUtil::randomString(32);

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");

        $sFilename = $oStorage->tempnam($sBasedir, 'kt_storecontents');

        $oStorage->uploadTmpFile($fileData['file']['tmp_name'], $sFilename);
        
        $fileData['file']['tmp_name'] = $sFilename;
        $_SESSION['_add_data'] = array($key => $fileData);

        exit(0);
	}	

	/**
	 * Handles the Submit of the KTLive Form 
	 */
	public function do_liveDocumentSubmit() {
	    global $default;
	    
	    $default->log->info('KTCore - Performing Live Submit');
	    
        //$data = $res['results'];
        $aData = $_REQUEST['data'];
        
        $performExtractOnServer = $aData['fExtractDocuments'];
        $default->log->info('KTCore Live Upload : Weather or not to perform upload : ' . $performExtractOnServer);
        
        $fileData = $_FILES;
        $key = KTUtil::randomString(32);

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");

        $sFilename = $oStorage->tempnam($sBasedir, 'kt_storecontents');

        $oStorage->uploadTmpFile($fileData['file']['tmp_name'], $sFilename);
        
        $fileData['file']['tmp_name'] = $sFilename;
        $_SESSION['_add_data'] = array($key => $fileData);

        exit(0);
	}		
	
    function getFieldsetsForType($iTypeId) {
        $typeid = KTUtil::getId($iTypeId);
        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($typeid, array('ids' => false));

        $fieldsets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
        return $fieldsets;
    }

    function do_main() {
        //$this->oPage->setBreadcrumbDetails(_kt("Add a document"));
        
        if (ACCOUNT_ROUTING_ENABLED) {
            $oForm = $this->getUploadForm();
            $result = $oForm->renderPage(_kt('Upload to : ') . $this->oFolder->getName());
        } else {
            $oForm = $this->form_initialdata();
            $result = $oForm->renderPage(_kt('Add a document to: ') . $this->oFolder->getName());
        }
        return $result;
    }

    function do_processInitialData() {
    	$oStorage = KTStorageManagerUtil::getSingleton();
        $oForm = $this->form_initialdata();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            if(!isset($res['errors']['file'])){
                $aError['file'] = array(_kt('Please reselect the file to upload.'));
            }
            return $oForm->handleError('', $aError);
        }
        $data = $res['results'];
        $key = KTUtil::randomString(32);


        // joy joy, we need to store the file first, or PHP will (helpfully)
        // clean it up for us

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");

        $sFilename = $oStorage->tempnam($sBasedir, 'kt_storecontents');

        //$oContents = new KTFSFileLike($data['file']['tmp_name']);
        //$oOutputFile = new KTFSFileLike($sFilename);
        //$res = KTFileLikeUtil::copy_contents($oContents, $oOutputFile);

        //if (PEAR::isError($res)) {
        //    $oForm->handleError(sprintf(_kt("Failed to store file: %s"), $res->getMessage()));
        //}


        $oStorage->uploadTmpFile($data['file']['tmp_name'], $sFilename);

        $data['file']['tmp_name'] = $sFilename;
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
				if ($oFieldset->getIsConditional())
                {
                	if ($val == _kt('No selection.'))
                	{
                		$val = null;
                	}
                }
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
        //require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
    	    'max_str_len' => 200,
        );

        $aFile = $this->oValidator->validateFile($extra_d['file'], $aErrorOptions);
        $sTitle = $extra_d['document_name'];

        $iFolderId = $this->oFolder->getId();
        $aOptions = array(
            // 'contents' => new KTFSFileLike($aFile['tmp_name']),
            'temp_file' => $aFile['tmp_name'],
            'documenttype' => DocumentType::get($extra_d['document_type']),
            'metadata' => $MDPack,
            'description' => $sTitle,
            'cleanup_initial_file' => true,
        );

        $mpo->start();
        //$this->startTransaction();
        $oDocument =& KTDocumentUtil::add($this->oFolder, $aFile['name'], $this->oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $message = $oDocument->getMessage();
            $this->errorRedirectTo('main',sprintf(_kt("Unexpected failure to add document - %s"), $message), 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }
        $this->addInfoMessage(_kt("Document added"));

        //$this->commitTransaction();
        $mpo->redirectToDocument($oDocument->getId());
        exit(0);

    }

}

//global $main;
//$main->setBodyOnload("javascript: alert('hallo');");

?>
