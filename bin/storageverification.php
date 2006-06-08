<?php

require_once(dirname(__FILE__) . '/../config/dmsDefaults.php');
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

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


        $this->checkDirectory("");

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
$oDispatcher = new VerifyDispatcher;
$oDispatcher->do_main();

?>
