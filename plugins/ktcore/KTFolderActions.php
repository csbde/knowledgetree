<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
        
        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name')),
        ));
        
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
