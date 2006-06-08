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

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

class KTFolderRenameAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.rename';
    var $_sShowPermission = "ktcore.permissions.write";

    function getDisplayName() {
        return _kt('Rename');
    }
    
    function getInfo() {
        if ($this->oFolder->getId() == 1) { return null; }
        return parent::getInfo();
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("rename"));
        $this->oPage->setTitle(_kt('Rename folder'));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/rename');

        $fields = array();
        $fields[] = new KTStringWidget(_kt('New folder name'), _kt('The name to which the current folder should be renamed.'), 'foldername', "", $this->oPage, true);
        
        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
            'sFolderName' => $this->oFolder->getName(),
        ));
        return $oTemplate->render();
    }

    function do_rename() {
        $aErrorOptions = array(
            'redirect_to' => array('', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $sFolderName = KTUtil::arrayGet($_REQUEST, 'foldername');
        $aErrorOptions['defaultmessage'] = _kt("No folder name given");
        $sFolderName = $this->oValidator->validateString($sFolderName, $aErrorOptions);
	$sOldFolderName = $this->oFolder->getName();

        $oParentFolder =& Folder::get($this->oFolder->getParentID());
	if(PEAR::isError($oParentFolder)) {
	    $this->errorRedirectToMain(_kt('Unable to retrieve parent folder.'), $aErrorOptions['redirect_to'][1]);
	    exit(0);
	}

	if(KTFolderUtil::exists($oParentFolder, $sFolderName)) {
	    $this->errorRedirectToMain(_kt('A folder with that name already exists.'), $aErrorOptions['redirect_to'][1]);
	    exit(0);
	}

        $res = KTFolderUtil::rename($this->oFolder, $sFolderName, $this->oUser);

        if (PEAR::isError($res)) {
            $_SESSION['KTErrorMessage'][] = $res->getMessage();
            redirect(KTBrowseUtil::getUrlForFolder($this->oFolder));
            exit(0);
        } else {
            $_SESSION['KTInfoMessage'][] = sprintf(_kt('Folder "%s" renamed to "%s".'), $sOldFolderName, $sFolderName);
        }

        $this->commitTransaction();
        redirect(KTBrowseUtil::getUrlForFolder($this->oFolder));
        exit(0);
    }

}

?>
