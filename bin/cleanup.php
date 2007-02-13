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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/config/config.inc.php');
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
        $GLOBALS['aFilesToRemove'][] = $path;
        return;
    }
}

function checkDirectory($path) {
    global $fsPath, $aIgnore;
    $fullpath = sprintf('%s/%s', $fsPath, $path);

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
            $GLOBALS['aFoldersToRemove'][] = $path;
            return;
        }
    }
    
    $dh = @opendir($fullpath);
    if ($dh === false) {
        print "Could not open directory: $fullpath\n";
    }
    while (($filename = readdir($dh)) !== false) {
        if (in_array($filename, $aIgnore)) { continue; }
        $subrelpath = sprintf('%s/%s', $path, $filename);
        $subfullpath = sprintf('%s/%s', $fsPath, $subrelpath);
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
    $sFolderPath = sprintf('%s/%s', $oFolder->getFullPath(), $oFolder->getName());
    $sFullPath = sprintf('%s/%s', $fsPath, $sFolderPath);
    if (!is_dir($sFullPath)) {
        $aRepoFolderProblems[] = $sFolderPath;
    }
}

function checkRepoDocument($oDocument) {
    global $fsPath, $aRepoDocumentProblems;
    $sDocumentPath = $oDocument->getStoragePath();
    $sFullPath = sprintf('%s/%s', $fsPath, $sDocumentPath);
    if (!is_file($sFullPath)) {
        $aRepoDocumentProblems[] = $sDocumentPath;
    }
    checkRepoVersions($oDocument);
}

function checkRepoVersions($oDocument) {
    global $fsPath, $aRepoVersionProblems;
    $table = 'document_transactions';
    $aVersions = DBUtil::getResultArrayKey(array("SELECT DISTINCT version FROM $table WHERE document_id = ?", array($oDocument->getID())), "version");
    foreach($aVersions as $sVersion) {
        if ($sVersion == $oDocument->getVersion()) {
            continue;
        }
        $sDocumentPath = $oDocument->getStoragePath();
        $sFullPath = sprintf('%s/%s-%s', $fsPath, $sDocumentPath, $sVersion);
        if (!is_file($sFullPath)) {
            $aRepoVersionProblems[] = array($sDocumentPath, $sVersion);
            continue;
        }
    }
}

checkDirectory('');

print "\n";
print "Would remove these folders (and all their contents):\n";
foreach ($aFoldersToRemove as $path) {
    print "\t$path\n";
}
print "\n";
print "Would remove these files:\n";
foreach ($aFilesToRemove as $path) {
    print "\t$path\n";
}
print "\n";

$aFolders =& Folder::getList();
foreach ($aFolders as $oFolder) {
    checkRepoFolder($oFolder);
}

print "These folders are not on the filesystem:\n";
foreach ($aRepoFolderProblems as $path) {
    print "\t$path\n";
}

$aDocuments =& Document::getList(array('status_id = ?', array(LIVE)));
foreach ($aDocuments as $oDocument) {
    checkRepoDocument($oDocument);
}
print "\n";

print "These documents are not on the filesystem:\n";
foreach ($aRepoDocumentProblems as $path) {
    print "\t$path\n";
}
print "\n";

print "These documents have versions not on the filesystem:\n";
foreach ($aRepoVersionProblems as $path) {
    list($path, $version) = $path;
    print "\t$path - version $version\n";
}
print "\n";

