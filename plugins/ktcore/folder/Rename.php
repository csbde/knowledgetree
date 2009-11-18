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
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTFolderRenameAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.rename';
    var $_sShowPermission = "ktcore.permissions.folder_rename";

    function getDisplayName() {
        return _kt('Rename');
    }

    function getInfo() {
        return parent::getInfo();
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("rename"));
        $this->oPage->setTitle(_kt('Rename folder'));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/rename');

        $fields = array();
        $fields[] = new KTStringWidget(_kt('New folder name'), _kt('The name to which the current folder should be renamed.'), 'foldername', $this->oFolder->getName(), $this->oPage, true);

        global $default;
        if($default->enableESignatures){
            $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to rename a folder');
            $input['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.rename', 'folder', 'rename_folder_form', 'submit', {$this->oFolder->getId()});";
            $input['type'] = 'button';
        }else{
            $input['onclick'] = '';
            $input['type'] = 'submit';
        }

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
            'input' => $input,
            'folderName' => $this->oFolder->getName(),
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
        $sFolderName = $this->oValidator->validateIllegalCharacters($sFolderName, $aErrorOptions);
        $sOldFolderName = $this->oFolder->getName();

        if ($this->oFolder->getId() != 1) {
            $oParentFolder =& Folder::get($this->oFolder->getParentID());
            if(PEAR::isError($oParentFolder)) {
                $this->errorRedirectToMain(_kt('Unable to retrieve parent folder.'), $aErrorOptions['redirect_to'][1]);
                exit(0);
            }

            if(KTFolderUtil::exists($oParentFolder, $sFolderName)) {
                $this->errorRedirectToMain(_kt('A folder with that name already exists.'), $aErrorOptions['redirect_to'][1]);
                exit(0);
            }
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
