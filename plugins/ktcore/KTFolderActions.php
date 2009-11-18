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

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

// {{{ KTDocumentDetailsAction
class KTFolderViewAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.view';

    function do_main() {
        redirect(KTBrowseUtil::getUrlForFolder($this->oFolder));
        exit(0);
    }

    function getDisplayName() {
        return _kt('Display Details');
    }
}
// }}}

require_once(KT_LIB_DIR . "/widgets/forms.inc.php");

class KTFolderAddFolderAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.addFolder';

    var $_sShowPermission = "ktcore.permissions.addFolder";

    function getDisplayName() {
        return _kt('Add a Folder');
    }


    function form_main() {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'context' => &$this,
            'identifier' => 'ktcore.folder.add',
            'action' => 'addFolder',
            'fail_action' => 'main',
            'cancel_url' => KTBrowseUtil::getUrlForFolder($this->oFolder),
            'label' => _kt('Add a folder'),
            'submit_label' => _kt('Add Folder'),
            'extraargs' => $this->meldPersistQuery("","", true),
        ));

        // widgets
        $oForm->setWidgets(array(
            array('ktcore.widgets.string', array(
                'label' => _kt('Folder name'),
                'description' => _kt('The name for the new folder.'),
                'required' => true,
                'name' => 'name')),
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
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name',
            )),
            array('ktcore.validators.illegal_char', array(
                'test' => 'name',
                'output' => 'name',
            )),
        ));

        if($default->enableESignatures){
            $oForm->addValidator(array('electonic.signatures.validators.authenticate', array(
                'object_id' => $this->oFolder->getId(),
                'type' => 'folder',
                'action' => 'ktcore.transactions.add_folder',
                'test' => 'info',
                'output' => 'info'
            )));
        }

        return $oForm;

    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("add folder"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/addFolder');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
            'context' => &$this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_addFolder() {

        $oForm = $this->form_main();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }
        $res = $res['results'];

    	if(KTFolderUtil::exists($this->oFolder, $res['name'])) {
	        $oForm->handleError(null, array('name' => _kt('A folder with that name already exists.')));
	    }

        $this->startTransaction();

        $res = KTFolderUtil::add($this->oFolder, $res['name'], $this->oUser);

        $aErrorOptions['defaultmessage'] = _kt("Could not create folder in the document management system");
        $this->oValidator->notError($res, $aErrorOptions);

        $this->commitTransaction();
        controllerRedirect('browse', sprintf('fFolderId=%d', $res->getId()));
        exit(0);
    }
}

?>
