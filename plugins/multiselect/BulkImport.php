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

require_once(KT_LIB_DIR . "/actions/folderaction.inc.php");
require_once(KT_LIB_DIR . "/import/fsimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");


require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");

require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

class InetBulkImportFolderMultiSelectAction extends KTFolderAction {
    var $sName = 'inet.actions.folder.bulkImport.multiselect';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAutomaticTransaction = false;

	/**
	 * hide existing url and return name to new url | iNET Process
	 * @Return string.
	 * @param.
	 */
    function getDisplayName() {
        if(!KTPluginUtil::pluginIsActive('inet.foldermetadata.plugin'))
		{
			$js .= "<script src='plugins/multiselect/js/hidelink.js' type='text/javascript'></script>";
			
			$aJavascript[] = 'thirdpartyjs/jquery/jquery-1.3.2.js';
			$aJavascript[] = 'thirdpartyjs/jquery/jquery_noconflict.js';
			
			$oPage =& $GLOBALS['main'];			
			if (method_exists($oPage, 'requireJSResources')) {
				$oPage->requireJSResources($aJavascript);
			}
	        return $js._kt('Import from Server Location');
		}
		else
		{
			return null;
		}
    }

	/**
	 * check for permissions | iNET Process
	 * @Return unknown.
	 * @param.
	 */
    function getInfo() {
        global $default;
        if($default->disableBulkImport){
        	return null;
        }

        if (!Permission::userIsSystemAdministrator($this->oUser->getId())) {
            return null;

        }
        return parent::getInfo();
    }

    
	/**
	 * Returns the main Bulk Upload Form
	 * @return KTForm 
	 *
	 */

	function getBulkImportForm() {
		$this->oPage->setBreadcrumbDetails(_kt("bulk import"));

		//Adding the required Bulk Upload javascript includes
		$aJavascript[] = 'resources/js/taillog.js';
		$aJavascript[] = 'resources/js/conditional_usage.js';
		$aJavascript[] = 'resources/js/kt_bulkupload.js';
		
		//Loading the widget js libraries to support dynamic "Ajax Loaded" widget rendering
		//FIXME: The widgets can support this via dynamic call to place libs in the head if they aren't loaded
		//       jQuery can do this but need time to implement/test.
		
		$aJavascript[] = 'thirdpartyjs/jquery/jquery-1.3.2.js';
		$aJavascript[] = 'thirdpartyjs/tinymce/jscripts/tiny_mce/tiny_mce.js';
		$aJavascript[] = 'resources/js/kt_tinymce_init.js';
    	$aJavascript[] = 'thirdpartyjs/tinymce/jscripts/tiny_mce/jquery.tinymce.js';
		
		$this->oPage->requireJSResources($aJavascript);		
		
		$oForm = new KTForm;
		$oForm->setOptions(array(
            'identifier' => 'ktcore.folder.bulkUpload',
            'label' => _kt('Import from Server Location'),
            'submit_label' => _kt('Import'),
            'action' => 'import',
            'fail_action' => 'main',
            'encoding' => 'multipart/form-data',
		//  'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'context' => &$this,
            'extraargs' => $this->meldPersistQuery("","",true),
			'description' => _kt('The bulk import facility allows for a number of documents to be added to the document management system easily. Provide a path on the server, and all documents and folders within that path will be added to the document management system.')		
		));
		
		$oWF =& KTWidgetFactory::getSingleton();
		
		$widgets = array();
		$validators = array();
		
		// Adding the File Upload Widget
		
		//Legacy kt3 widgets don't conform to ktcore type widgets by virtue of the 'name' attribute.
		//$widgets[] = new KTFileUploadWidget(_kt('Archive file'), , 'file', "", $this->oPage, true, "file");

		$widgets[] = $oWF->get('ktcore.widgets.string', array(
                        'label' => _kt('Path'),
                        'required' => true,
                        'name' => 'path',
        				'id' => 'path',
                        'value' => '',
                        'description' => _kt('The path containing the documents to be added to the document management system.'),
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
		
		//Adding the quick "add" button for when no meta data needs to be added.
		//FIXME: This widget should only display if there are any "required" fields for the given document type
		//       Default/general document field type must also be taken into consideration
		
		$widgets[] = $oWF->get('ktcore.widgets.button',array(
                'value' => _kt('Add'),
				'id' => 'quick_add',
                'description' => _kt('If you do not need to modify any the metadata for this document (see below), then you can simply click "Add" here to finish the process and add the document.'),
                'name' => 'btn_quick_submit',
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
	 * This is Default function to be called load template for import action. | iNET Process
	 * @Return template.
	 * @param.
	 */
    function do_main() {
		return $this->getBulkImportForm()->render();
    }    
	
	/**
	 * Performs actual import action. | iNET Process
	 * @Return.
	 * @param.
	 */
    function do_import() {

        set_time_limit(0);

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $aErrorOptions['message'] = _kt('Invalid document type provided');

		$requestDocumentType = $_REQUEST['fDocumentTypeId']; //Backwards compatibility
		if ($requestDocumentType == '' || $requestDocumentType == NULL) {
			$requestDocumentType = $_REQUEST['data'];
			$requestDocumentType = $requestDocumentType['fDocumentTypeId']; //New elements come through as arrays
		}

		$oDocumentType = $this->oValidator->validateDocumentType($requestDocumentType, $aErrorOptions);        

        $aErrorOptions['message'] = _kt('Invalid path provided');
        
        $tmpPath = $_REQUEST['path']; //Backwards compatibility
        if ($tmpPath == '') {
        	$tmpPath = $_REQUEST['data'];
        	$tmpPath = $tmpPath['path'];
        }
        
        $sPath = $this->oValidator->validateString($tmpPath, $aErrorOptions);

        /*
        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
               
				 // multiselect change start
				$oDocField = DocumentField::get($matches[1]);
				if(KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin') && $oDocField->getHasInetLookup() && is_array($v))
				{
					$v = join(", ", $v);
				}
                $aFields[] = array($oDocField, $v);
				
				// previously it was just one line which is commented, just above line
				// multiselect change end
            }
        }

        $aOptions = array(
            'documenttype' => $oDocumentType,
            'metadata' => $aFields,
            'copy_upload' => 'true',
        );
		*/
        
		// Newer metadata form field catcher that works with ktcore form array type fields named like
		// name='metadata[fieldset][metadata_9]'
		
		$aData = $_REQUEST['data'];
		$data = $aData;
		
		/*
		$oForm = $this->getBulkImportForm();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        $data = $res['results'];
        */
		
        $doctypeid = $requestDocumentType;
        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($doctypeid, array('ids' => false));
        $fieldsets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
		
		$MDPack = array();
        foreach ($fieldsets as $oFieldset) {
            $fields = $oFieldset->getFields();
            $values = (array) KTUtil::arrayGet($data, 'fieldset_' . $oFieldset->getId());

            foreach ($fields as $oField) {
                $val = KTUtil::arrayGet($values, 'metadata_' . $oField->getId());
                
                //Fix for multiselect not submitting data due to the value not being flat.
                $sVal = '';
                if (is_array($val)) {
                    foreach ($val as $v) {
                        $sVal .= $v . ", ";
                    }
                    $sVal = substr($sVal, 0, strlen($sVal) - 2);
                    $val = $sVal;
                }

				if ($oFieldset->getIsConditional())
                {
                	if ($val == _kt('No selection.'))
                	{
                		$val = null;
                	}
                }
				
                if (!is_null($val)) {
                    $MDPack[] = array(
                        $oField,
                        $val
                    );
                }
			
            }
        }
        
		$aOptions = array(
            'documenttype' => $oDocumentType,
            'metadata' => $MDPack,
			'copy_upload' => 'true',
		);        
        
        $po =& new JavascriptObserver($this);
        $po->start();
        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->addObserver($po);
		
        $fs =& new KTFSImportStorage($sPath);
        $bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
       if(KTPluginUtil::pluginIsActive('inet.foldermetadata.plugin'))
		{
			require_once(KT_DIR . "/plugins/foldermetadata/import/bulkimport.inc.php");
			$bm =& new KTINETBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
		}
		else
		{
			$bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
		}
        DBUtil::startTransaction();
        $res = $bm->import();
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            $_SESSION["KTErrorMessage"][] = _kt("Bulk import failed") . ": " . $res->getMessage();
        } else {
            DBUtil::commit();
            $this->addInfoMessage(_kt("Bulk import succeeded"));
        }

        $po->redirectToFolder($this->oFolder->getId());
        exit(0);
    }
}
