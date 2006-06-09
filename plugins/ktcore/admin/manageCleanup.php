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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class ManageCleanupDispatcher extends KTAdminDispatcher {
    function ManageCleanupDispatcher() {
        $this->aIgnore = array(
            '.', '..',
            'CVS',
            '.empty',
            '.htaccess',
            '.cvsignore',
            '.svn',
        );

        $oConfig =& KTConfig::getSingleton();
        $this->fsPath = $oConfig->get('urls/documentRoot');

        return parent::KTAdminDispatcher();
    }

    function do_main() {
        global $aFoldersToRemove;
        global $aFilesToRemove;
        global $aRepoDocumentProblems;
        global $aRepoFolderProblems;
        global $aRepoVersionProblems;


        $this->checkDirectory("");

        $aDocuments =& Document::getList();
        foreach ($aDocuments as $oDocument) {
            $this->checkRepoDocument($oDocument);
        }

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/document/cleanup');
        $oTemplate->setData(array(
            'aFilesToRemove' => $this->aFilesToRemove,
            'aRepoDocumentProblems' => $this->aRepoDocumentProblems,
        ));
        return $oTemplate->render();
    }

    function checkDirectory($path) {
        $fullpath = sprintf("%s/%s", $this->fsPath, $path);

        if (!is_dir($fullpath)) {
            print "Not a directory: $fullpath\n";
        }

        $dh = @opendir($fullpath);
        if ($dh === false) {
            print "Could not open directory: $fullpath\n";
        }
        while (($filename = readdir($dh)) !== false) {
            if (in_array($filename, $this->aIgnore)) { continue; }
            $subrelpath = sprintf("%s/%s", $path, $filename);
            if (substr($subrelpath, 0, 1) == "/") {
                $subrelpath = substr($subrelpath, 1);
            }
            $subfullpath = sprintf("%s/%s", $this->fsPath, $subrelpath);
            if (is_dir($subfullpath)) {
                $this->checkDirectory($subrelpath);
            }
            if (is_file($subfullpath)) {
                $this->checkFile($subrelpath);
            }
        }
    }

    function checkFile($path, $first = true) {
        $oDocument = KTEntityUtil::getByDict('KTDocumentContentVersion', array(
            'storage_path' => $path,
        ));
        if (is_a($oDocument, 'ktentitynoobjects')) {
            $this->aFilesToRemove[] = $path;
            return;
        }
    }

    function checkRepoDocument($oDocument) {
        global $aRepoDocumentProblems;
        $aDCVs = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aDCVs as $oDCV) {
            $sDocumentPath = $oDCV->getStoragePath();
            $sFullPath = sprintf("%s/%s", $this->fsPath, $sDocumentPath);
            if (!is_file($sFullPath)) {
                $this->aRepoDocumentProblems[] = array(
                    'document' => $oDocument,
                    'content' => $oDCV,
                    'path' => $sDocumentPath,
                    'doclink' => KTBrowseUtil::getUrlForDocument($oDocument),
                );
            }
        }
    }
}

?>
