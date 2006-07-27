<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
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

class KTFolderAddFolderAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.addFolder';

    var $_sShowPermission = "ktcore.permissions.addFolder";

    function getDisplayName() {
        return _kt('Add a Folder');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("add folder"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/addFolder');
        $fields = array();
        $fields[] = new KTStringWidget(_kt('Folder name'), _kt('The name for the new folder.'), 'name', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_addFolder() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $sFolderName = KTUtil::arrayGet($_REQUEST, 'name');
        $aErrorOptions['defaultmessage'] = _kt("No name given");
        $sFolderName = $this->oValidator->validateString($sFolderName, $aErrorOptions);
	
	if(KTFolderUtil::exists($this->oFolder, $sFolderName)) {
	    $this->errorRedirectToMain(_kt('A folder with that name already exists.'), $aErrorOptions['redirect_to'][1]);
	    exit(0);
	}

        $this->startTransaction();

        $res = KTFolderUtil::add($this->oFolder, $sFolderName, $this->oUser);
        $aErrorOptions['defaultmessage'] = _kt("Could not create folder in the document management system");
        $this->oValidator->notError($res, $aErrorOptions);

        $this->commitTransaction();
        controllerRedirect('browse', sprintf('fFolderId=%d', $res->getId()));
        exit(0);
    }
}

?>
