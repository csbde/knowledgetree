<?php

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/helpentity.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

$oConfig =& KTConfig::getSingleton();
$fsPath = $oConfig->get('urls/documentRoot');

$aIgnore = array(
    '.', '..',
    'CVS',
    '.empty',
    '.htaccess',
    '.cvsignore',
);

$aFoldersToRemove = array();
$aFilesToRemove = array();
$aRepoDocumentProblems = array();
$aRepoFolderProblems = array();
$aRepoVersionProblems = array();

function checkFileVersion($path, $version) {
    $fod = KTBrowseUtil::folderOrDocument($path);
    if ($fod === false) {
        // No document by that name, so no point checking version
        // information.
        return;
    }
    return true;
}

function checkFile($path, $first = true) {
    $pattern = "/^(.*)-((?:\d+)\.(?:\d+))$/";
    if (preg_match($pattern, $path, $matches)) {
        if (checkFileVersion($matches[1], $matches[2])) {
            // If it's a version, then don't check for full path
            // below...
            return;
        }
    }
    $fod = KTBrowseUtil::folderOrDocument($path);
    if ($fod === false) {
        $GLOBALS["aFilesToRemove"][] = $path;
        return;
    }
}

function checkDirectory($path) {
    global $fsPath, $aIgnore;
    $fullpath = sprintf("%s/%s", $fsPath, $path);

    if (!is_dir($fullpath)) {
        print "Not a directory: $fullpath\n";
    }

    if ($path === '/Deleted') {
        // Deleted files handled separately.
        return;
    }

    if (!empty($path)) {
        $fod = KTBrowseUtil::folderOrDocument($path);
        if ($fod === false) {
            $GLOBALS["aFoldersToRemove"][] = $path;
            return;
        }
    }

    $dh = @opendir($fullpath);
    if ($dh === false) {
        print "Could not open directory: $fullpath\n";
    }
    while (($filename = readdir($dh)) !== false) {
        if (in_array($filename, $aIgnore)) { continue; }
        $subrelpath = sprintf("%s/%s", $path, $filename);
        $subfullpath = sprintf("%s/%s", $fsPath, $subrelpath);
        if (is_dir($subfullpath)) {
            checkDirectory($subrelpath);
        }
        if (is_file($subfullpath)) {
            checkFile($subrelpath);
        }
    }
}

function checkRepoFolder($oFolder) {
    global $fsPath, $aRepoFolderProblems;
    $sFolderPath = sprintf("%s/%s", $oFolder->getFullPath(), $oFolder->getName());
    $sFullPath = sprintf("%s/%s", $fsPath, $sFolderPath);
    if (!is_dir($sFullPath)) {
        $aRepoFolderProblems[] = $sFolderPath;
    }
}

function checkRepoDocument($oDocument) {
    global $fsPath, $aRepoDocumentProblems;
    $sDocumentPath = $oDocument->getStoragePath();
    $sFullPath = sprintf("%s/%s", $fsPath, $sDocumentPath);
    if (!is_file($sFullPath)) {
        $aRepoDocumentProblems[] = $sDocumentPath;
    }
    checkRepoVersions($oDocument);
}

function checkRepoVersions($oDocument) {
    global $fsPath, $aRepoVersionProblems;
    $table = "document_transactions";
    $aVersions = DBUtil::getResultArrayKey(array("SELECT DISTINCT version FROM $table WHERE document_id = ?", array($oDocument->getID())), "version");     foreach($aVersions as $sVersion) {
        if ($sVersion == $oDocument->getVersion()) {
            continue;
        }
        $sDocumentPath = $oDocument->getStoragePath();
        $sFullPath = sprintf("%s/%s-%s", $fsPath, $sDocumentPath, $sVersion);
        if (!is_file($sFullPath)) {
            $aRepoVersionProblems[] = array($sDocumentPath, $sVersion);
            continue;
        }
    }
}

class ManageCleanupDispatcher extends KTAdminDispatcher {
    function do_main() {
        global $aFoldersToRemove;
        global $aFilesToRemove;
        global $aRepoDocumentProblems;
        global $aRepoFolderProblems;
        global $aRepoVersionProblems;

        checkDirectory("");
        $aFolders =& Folder::getList();
        foreach ($aFolders as $oFolder) {
            checkRepoFolder($oFolder);
        }
        $aDocuments =& Document::getList(array("status_id = ?", array(LIVE)));
        foreach ($aDocuments as $oDocument) {
            checkRepoDocument($oDocument);
        }
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/document/cleanup');
        $oTemplate->setData(array(
            'aFoldersToRemove' => $aFoldersToRemove,
            'aFilesToRemove' => $aFilesToRemove,
            'aRepoDocumentProblems' => $aRepoDocumentProblems,
            'aRepoFolderProblems' => $aRepoFolderProblems,
            'aRepoVersionProblems' => $aRepoVersionProblems,
        ));
        return $oTemplate->render();
    }
}

?>
