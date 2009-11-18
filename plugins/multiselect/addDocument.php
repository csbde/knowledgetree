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

/* The file adds a document with multiselect fields.The main code for fetching multiselect
 * fields is written in this file.
 */

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class MultiDocumentAddAction extends KTFolderAction {
    var $sName = 'inet.multiselect.actions.document.addDocument';
    var $_sShowPermission = "ktcore.permissions.write";
    var $oDocumentType = null;

	/**
	 * returns a display name 'Add Document' 
	 * @return 
	 * 
	 * iNET Process
	 */
    function getDisplayName() {
        return _kt('Add Document');
    }

	/**
	 * get the button
	 * @return 
	 * 
	 * iNET Process
	 */
    function getButton(){
        $btn = array();
        $btn['display_text'] = _kt('Upload Document');
        $btn['arrow_class'] = 'arrow_upload';
        return $btn;
    }
	/**
	 * check if document can be added or not
	 * @return 
	 * 
	 * iNET Process
	 */
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
	/**
	 * Initializes for data
	 * @return 
	 * 
	 * iNET Process
	 */
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
	 * Get the fieldsets for a particular document type
	 * @return array
	 * @param $iTypeId Object
	 * 
	 * iNET Process
	 */
    function getFieldsetsForType($iTypeId) {
        $typeid = KTUtil::getId($iTypeId);
        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($typeid, array('ids' => false));

        $fieldsets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
        return $fieldsets;
    }
	/**
	 * Main default function
	 * @param
	 * @return form
	 * iNET Process
	 */
    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Add a document"));
        $oForm = $this->form_initialdata();
        return $oForm->renderPage(_kt('Add a document to: ') . $this->oFolder->getName());
    }
	/**
	 * Checks for validations and errors
	 * @return 
	 * 
	 * iNET Process
	 */
    function do_processInitialData() {
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

        $sFilename = tempnam($sBasedir, 'kt_storecontents');

        //$oContents = new KTFSFileLike($data['file']['tmp_name']);
        //$oOutputFile = new KTFSFileLike($sFilename);
        //$res = KTFileLikeUtil::copy_contents($oContents, $oOutputFile);

        //if (PEAR::isError($res)) {
        //    $oForm->handleError(sprintf(_kt("Failed to store file: %s"), $res->getMessage()));
        //}


        $oStorage =& KTStorageManagerUtil::getSingleton();
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
	/**
	 * get the metadata
	 * @return form
	 * @param $sess_key Object
	 * 
	 * iNET Process
	 */
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
	/**
	 * render metadata for a document
	 * @return form
	 * 
	 * iNET Process
	 */
    function do_metadata() {
        $this->persistParams(array('fFileKey'));

        $oForm = $this->form_metadata($_REQUEST['fFileKey']);
        return $oForm->render();
    }
	/**
	 * finally adds a document
	 * @return.
	 * 
	 * iNET Process
	 */
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
                	// multiselect change start
                	if(is_array($val) && $oField->getHasInetLookup())
					{
						$val = join(", ",$val);
					}
					// multiselect change end
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

?>
