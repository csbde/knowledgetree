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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php'); 
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

class KTImmutableActionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.immutableaction.plugin";

    function KTImmutableActionPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Immutable action plugin');
        return $res;
    }

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentImmutableAction', 'ktcore.actions.document.immutable');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTImmutableActionPlugin', 'ktstandard.immutableaction.plugin', __FILE__);

class KTDocumentImmutableAction extends KTDocumentAction {
    var $sName = "ktcore.actions.document.immutable";
    var $_sShowPermission = 'ktcore.permissions.security';
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Make immutable');
    }

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }

        return parent::getInfo();
    }

    /*
     * Checks if document is Checked Out
     * Not really needed, since the action is removed on Check Out
     * Still a good precaution though
     *
     */
    function check() {
        $res = parent::check();
        if ($res !== true) {
            return $res;
        }
        if ($this->oDocument->getIsCheckedOut()) {
            $_SESSION['KTErrorMessage'][]= _kt('This document can\'t be made immutable because it is checked out');
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }

        return true;
    }

	function form_confirm() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt('Are you sure?'),
            'description' => '',
            'action' => 'main',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt('Make Immutable'),
            'context' => &$this,
        ));


        return $oForm;
    }

    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
			'label' => _kt('Make Immutable'),
            'action' => 'immutable',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
            'submit_label' => _kt('Make Immutable'),
            'context' => &$this,
        ));

        // Electronic Signature if enabled
        global $default;
        if($default->enableESignatures){
            $widgets[] = array('ktcore.widgets.info', array(
                    'label' => _kt('This action requires authentication'),
                    'description' => _kt('Please provide your user credentials as confirmation of this action.'),
                    'name' => 'info'
                ));
            $widgets[] = array('ktcore.widgets.string', array(
                    'label' => _kt('Username'),
                    'name' => 'sign_username',
                    'required' => true
                ));
            $widgets[] = array('ktcore.widgets.password', array(
                    'label' => _kt('Password'),
                    'name' => 'sign_password',
                    'required' => true
                ));
        }

        $widgets[] = array('ktcore.widgets.reason', array(
                'label' => _kt('Reason'),
                'description' => _kt('Please specify why you are making this document immutable.  Please bear in mind that you can use a maximum of <strong>250</strong> characters.'),
                'name' => 'reason',
            ));

        $oForm->setWidgets($widgets);

        $validators[] = array('ktcore.validators.string', array(
                'test' => 'reason',
                'min_length' => 1,
                'max_length' => 250,
                'output' => 'reason',
            ));

        if($default->enableESignatures){
            $validators[] = array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oDocument->iId,
                'type' => 'document',
                'action' => 'ktcore.transactions.immutable',
                'test' => 'info',
                'output' => 'info'
            ));
        }

        $oForm->setValidators($validators);

        return $oForm;
    }
    
    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Immutable'));
    	//check if we need confirmation for symblolic links linking to this document
		if(count($this->oDocument->getSymbolicLinks())>0 && KTutil::arrayGet($_REQUEST,'postReceived') != 1){
        	$this->redirectTo("confirm");
        }
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/immutable');
        $oForm = $this->form_main();
        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }
    
    function do_confirm(){
    	$this->oPage->setBreadcrumbDetails(_kt('Confirm Making Document Immutable'));
    	$oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/immutable_confirm');
        $oForm = $this->form_confirm();
    	$oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_immutable() {
        $oForm = $this->form_main();
        $res = $oForm->validate();
        $data = $res['results'];
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        $sReason = $data['reason'];
        $fFolderId = $this->oDocument->getFolderId();
        $reason = KTUtil::arrayGet($_REQUEST['data'], 'reason');
        $this->oDocument->setImmutable(true);
        $this->oDocument->update();
        // create the document transaction record
        $oDocumentTransaction = new DocumentTransaction($this->oDocument, $reason, 'ktcore.transactions.immutable');
        $oDocumentTransaction->create();
        controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());

        exit(0);
    }

    // No validation main()
//    function do_main() {
//        if(!$this->oDocument->getIsCheckedOut())
//        {
//	        $this->oDocument->setImmutable(true);
//	        $this->oDocument->update();
//	        controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
//        }
//        else
//        {
//        	$this->addErrorMessage(_kt('Document is checked out and cannot be made immutable'));
//        	controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
//        }
//    }

}

