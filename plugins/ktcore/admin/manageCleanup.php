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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');
class ManageCleanupDispatcher extends KTAdminDispatcher {
    function ManageCleanupDispatcher() {
        $this->aIgnore = array(
            '.', '..',
            'CVS',
            '.DS_Store',
            '.empty',
            '.htaccess',
            '.cvsignore',
            '.svn',
            '.git'
        );

        $oConfig =& KTConfig::getSingleton();
        $this->fsPath = $oConfig->get('urls/documentRoot');

        return parent::KTAdminDispatcher();
    }

    function do_main()
    {

    	 $oForm = new KTForm;
        $oForm->setOptions(array(
            'label' => _kt('Document Storage Verification'),
            'description' => _kt('This process performs a check to see if the documents in your repositories all are stored on the back-end storage (usually on disk). This process can take many minutes or hours depending on the size of your repository.'),
            'submit_label' => _kt('verify document storage'),
            'action' => 'verify',
        ));

          return $oForm->render();
    }


    function do_verify() {
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
                // Check for backup file
                if(substr($subrelpath, -4) == '.bak'){
                    $this->checkBackUpFile($subrelpath, $filename);
                }else{
                    $this->checkFile($subrelpath);
                }
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

    function checkBackUpFile($path, $filename) {
        $pos = strpos($filename, '.bak');
        $doc = substr($filename, 0, $pos);

        $oDocument = Document::get($doc);

        if($oDocument instanceof Document || $oDocument instanceof DocumentProxy){
            return;
        }
        $this->aFilesToRemove[] = $path;
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
