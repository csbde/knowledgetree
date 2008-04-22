<?php
/**
 * $Id$
 *    
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
 */

require_once(dirname(__FILE__) . '/../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
$sectionName = 'Administration';
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class VerifyDispatcher extends KTDispatcher {
    function VerifyDispatcher() {
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

        return parent::KTDispatcher();
    }

    function do_main() {
        global $aFoldersToRemove;
        global $aFilesToRemove;
        global $aRepoDocumentProblems;
        global $aRepoFolderProblems;
        global $aRepoVersionProblems;


        $this->checkDirectory('');

        $aDocuments =& Document::getList();
        foreach ($aDocuments as $oDocument) {
            $this->checkRepoDocument($oDocument);
        }

        if (!($this->aFilesToRemove or $this->aRepoDocumentProblems)) {
            return;
        }

        $oTemplate =&
        $this->oValidator->validateTemplate('ktcore/document/cleanup_script');
        $oTemplate->setData(array(
            'aFilesToRemove' => $this->aFilesToRemove,
            'aRepoDocumentProblems' => $this->aRepoDocumentProblems,
        ));
        print $oTemplate->render();
        exit(0);
    }

    function checkDirectory($path) {
        $fullpath = sprintf('%s/%s', $this->fsPath, $path);

        if (!is_dir($fullpath)) {
            print "Not a directory: $fullpath\n";
        }

        $dh = @opendir($fullpath);
        if ($dh === false) {
            print "Could not open directory: $fullpath\n";
        }
        while (($filename = readdir($dh)) !== false) {
            if (in_array($filename, $this->aIgnore)) { continue; }
            $subrelpath = sprintf('%s/%s', $path, $filename);
            if (substr($subrelpath, 0, 1) == '/') {
                $subrelpath = substr($subrelpath, 1);
            }
            $subfullpath = sprintf('%s/%s', $this->fsPath, $subrelpath);
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
            $sFullPath = sprintf('%s/%s', $this->fsPath, $sDocumentPath);
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
$oDispatcher = new VerifyDispatcher;
$oDispatcher->do_main();

?>
