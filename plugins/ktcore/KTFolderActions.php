<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
