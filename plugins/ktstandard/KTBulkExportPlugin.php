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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class KTBulkExportPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.bulkexport.plugin";

    function setup() {
        $this->registerAction('folderaction', 'KTBulkExportAction', 'ktstandard.bulkexport.action');
    }
}

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

class KTBulkExportAction extends KTFolderAction {
    var $sName = 'ktstandard.bulkexport.action';
    var $sPermissionName = "ktcore.permissions.read";

    function getDisplayName() {
        return _kt('Bulk Export');
    }

    function do_main() {
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $aQuery = $this->buildQuery();
        $this->oValidator->notError($aQuery);
        $aDocumentIds = DBUtil::getResultArrayKey($aQuery, 'id');

        if (empty($aDocumentIds)) {
            $this->addErrorMessage(_kt("No documents found to export"));
            redirect(KTBrowseUtil::getUrlForFolder($oFolder));
            exit(0);
        }

        $this->oPage->requireJSResource('thirdpartyjs/MochiKit/Base.js');
        $this->oPage->requireJSResource('thirdpartyjs/MochiKit/Async.js');
        $this->oPage->template = "kt3/minimal_page";
        $this->handleOutput("");

        $sTmpPath = tempnam('/tmp', 'kt_export');
        unlink($sTmpPath);
        mkdir($sTmpPath, 0700);
        $this->sTmpPath = $sTmpPath;
        $aPaths = array();
        foreach ($aDocumentIds as $iId) {
            $oDocument = Document::get($iId);
            $sParentFolder = sprintf('%s/%s', $sTmpPath, $oDocument->getFullPath());
            $newDir = $this->sTmpPath;
            foreach (split('/', $oDocument->getFullPath()) as $dirPart) {
                $newDir = sprintf("%s/%s", $newDir, $dirPart); 
                if (!file_exists($newDir)) {
                    mkdir($newDir, 0700);
                }
            }
            $sOrigFile = $oStorage->temporaryFile($oDocument);
            $sFilename = sprintf("%s/%s", $sParentFolder, $oDocument->getFileName());
            copy($sOrigFile, $sFilename);
            $aPaths[] = sprintf("%s/%s", $oDocument->getFullPath(), $oDocument->getFileName());
        }
        $sManifest = sprintf("%s/%s", $this->sTmpPath, "MANIFEST");
        file_put_contents($sManifest, join("\n", $aPaths));
        $sZipFile = sprintf("%s/%s.zip", $this->sTmpPath, $this->oFolder->getName());
        $_SESSION['bulkexport'] = KTUtil::arrayGet($_SESSION, 'bulkexport', array());
        $sExportCode = KTUtil::randomString();
        $_SESSION['bulkexport'][$sExportCode] = array(
            'file' => $sZipFile,
            'dir' => $this->sTmpPath,
        );
        $sZipCommand = KTUtil::findCommand("export/zip", "zip");
        $aCmd = array(
            $sZipCommand,
            "-r",
            $sZipFile,
            ".",
            "-i@MANIFEST",
        );
        $sOldPath = getcwd();
        chdir($this->sTmpPath);
        $aOptions = array('popen' => 'r');
        $fh = KTUtil::pexec($aCmd, $aOptions);
        $last_beat = time();
        while(!feof($fh)) {
            if ($i % 1000 == 0) {
                $this_beat = time();
                if ($last_beat + 1 < $this_beat) {
                    $last_beat = $this_beat;
                    print "&nbsp;";
                }
            }
            $contents = fread($fh, 4096);
            if ($contents) {
                print nl2br($contents);
            }
            $i++;
        }
        pclose($fh);

        $url = KTUtil::addQueryStringSelf(sprintf('action=downloadZipFile&fFolderId=%d&exportcode=%s', $this->oFolder->getId(), $sExportCode));
        printf('Go <a href="%s">here</a> to download the zip file if you are not automatically redirected there', $url);
        printf("</div></div></body></html>\n");
        printf('<script language="JavaScript">
                function kt_bulkexport_redirect() {
                    document.location.href = "%s";
                }
                callLater(1, kt_bulkexport_redirect);

                </script>', $url);
        exit(0);
    }

    function buildQuery() {
        $oUser = User::get($_SESSION['userID']);
        $res = KTSearchUtil::permissionToSQL($oUser, $this->sPermissionName);
        if (PEAR::isError($res)) {
            return $res;
        }
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;
        $aPotentialWhere = array($sPermissionString, 'D.parent_folder_ids = ? OR D.parent_folder_ids LIKE ?', 'D.status_id = 1');
        $aWhere = array();
        foreach ($aPotentialWhere as $sWhere) {
            if (empty($sWhere)) {
                continue;
            }
            if ($sWhere == "()") {
                continue;
            }
            $aWhere[] = sprintf("(%s)", $sWhere);
        }
        $sWhere = "";
        if ($aWhere) {
            $sWhere = "\tWHERE " . join(" AND ", $aWhere);
        }

        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'D.id');

        $sQuery = sprintf("SELECT %s FROM %s AS D
                LEFT JOIN %s AS DM ON D.metadata_version_id = DM.id
                LEFT JOIN %s AS DC ON DM.content_version_id = DC.id
                %s %s",
                $sSelect, KTUtil::getTableName("documents"),
                KTUtil::getTableName("document_metadata_version"),
                KTUtil::getTableName("document_content_version"),
                $sPermissionJoin, $sWhere);
        $aParams = array();
        $aParams = array_merge($aParams,  $aPermissionParams);
        $aParentFolderIds = split(',', $this->oFolder->getParentFolderIds());
        $aParentFolderIds[] = $this->oFolder->getId();
        if ($aParentFolderIds[0] == 0) {
            array_shift($aParentFolderIds);
        }
        $sParentFolderIds = join(',', $aParentFolderIds);
        $aParams[] = $sParentFolderIds;
        $aParams[] = $sParentFolderIds . ",%";
        return array($sQuery, $aParams);
    }

    function do_downloadZipFile() {
        $sCode = $this->oValidator->validateString($_REQUEST['exportcode']);
        $aData = KTUtil::arrayGet($_SESSION['bulkexport'], $sCode);
        $this->oValidator->notEmpty($aData);
        $sZipFile = $aData['file'];
        header("Content-Type: application/zip");
        header("Content-Length: ". filesize($sZipFile));
        header("Content-Disposition: attachment; filename=\"" . $this->oFolder->getName() . ".zip" . "\"");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate");
        readfile($sZipFile);
        $sTmpDir = $aData['dir'];
        KTUtil::deleteDirectory($sTmpDir);
        exit(0);
    }
}
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTBulkExportPlugin', 'ktstandard.bulkexport.plugin', __FILE__);

?>
