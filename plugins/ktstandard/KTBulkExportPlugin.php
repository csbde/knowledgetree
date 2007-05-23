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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/config/config.inc.php');

class KTBulkExportPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.bulkexport.plugin";

    function KTBulkExportPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Bulk Export Plugin');
        return $res;
    }        

    function setup() {
        $this->registerAction('folderaction', 'KTBulkExportAction', 'ktstandard.bulkexport.action');
    }
}

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

class KTBulkExportAction extends KTFolderAction {
    var $sName = 'ktstandard.bulkexport.action';
    var $sPermissionName = "ktcore.permissions.read";
    var $_sShowPermission = "ktcore.permissions.read";
    var $sOutputEncoding = 'UTF-8';

    function _checkConvertEncoding() {
        if(!function_exists("iconv")) {
            $this->addErrorMessage(_kt('IConv PHP extension not installed. Bulk export could not handle output filename encoding conversion !'));
            return false;
        }
        $oKTConfig = KTConfig::getSingleton();
        $this->sOutputEncoding = $oKTConfig->get('export/encoding', 'UTF-8');

        // Test the specified encoding
        if(iconv("UTF-8", $this->sOutputEncoding, "") === FALSE) {
            $this->addErrorMessage(_kt('Specified output encoding for bulk export does not exists !'));
            return false;
        }
        return true;
    }

	/**
	 * Convert encoding to defined character encoding
	 *
	 * @param string the string to convert
	 * @param boolean encode(true) or decode(false) string
	 * @return string the encoded string
	 */
    function _convertEncoding($sMystring, $bEncode) {
    	if (strcasecmp($this->sOutputEncoding, "UTF-8") === 0) {
    		return $sMystring;
    	}
    	if ($bEncode) {
    		return iconv("UTF-8", $this->sOutputEncoding, $sMystring);
    	} else {
    		return iconv($this->sOutputEncoding, "UTF-8", $sMystring);
    	}
    }

    function getDisplayName() {
        return _kt('Bulk Export');
    }

    function do_main() {
        if(!$this->_checkConvertEncoding()) {
            redirect(KTBrowseUtil::getUrlForFolder($oFolder));
            exit(0);
        }

        $oStorage =& KTStorageManagerUtil::getSingleton();
        $aQuery = $this->buildQuery();
        $this->oValidator->notError($aQuery);
        $aDocumentIds = DBUtil::getResultArrayKey($aQuery, 'id');
        
        $this->startTransaction();

        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");
        $bNoisy = $oKTConfig->get("tweaks/noisyBulkOperations");

        if (empty($aDocumentIds)) {
            $this->addErrorMessage(_kt("No documents found to export"));
            redirect(KTBrowseUtil::getUrlForFolder($oFolder));
            exit(0);
        }

        $this->oPage->requireJSResource('thirdpartyjs/MochiKit/Base.js');
        $this->oPage->requireJSResource('thirdpartyjs/MochiKit/Async.js');
        $this->oPage->template = "kt3/minimal_page";
        $this->handleOutput("");

        $sTmpPath = tempnam($sBasedir, 'kt_export');
        unlink($sTmpPath);
        mkdir($sTmpPath, 0700);
        $this->sTmpPath = $sTmpPath;
        $aPaths = array();
        $aReplace = array(
            "[" => "[[]",
            " " => "[ ]",
            "*" => "[*]",
            "?" => "[?]",
        );
        $aReplaceKeys = array_keys($aReplace);
        $aReplaceValues = array_values($aReplace);
        foreach ($aDocumentIds as $iId) {
            $oDocument = Document::get($iId);
            
            if ($bNoisy) {
                $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                $oDocumentTransaction->create();     
            }
            
            $sParentFolder = sprintf('%s/%s', $sTmpPath, $oDocument->getFullPath());
            $newDir = $this->sTmpPath;
            $sFullPath = $this->_convertEncoding($oDocument->getFullPath(), true);
            foreach (split('/', $sFullPath) as $dirPart) { 
                $newDir = sprintf("%s/%s", $newDir, $dirPart); 
                if (!file_exists($newDir)) {
                    mkdir($newDir, 0700);
                }
            }
            $sOrigFile = $oStorage->temporaryFile($oDocument);
            $sFilename = sprintf("%s/%s", $sParentFolder, $oDocument->getFileName());
            $sFilename = $this->_convertEncoding($sFilename, true);
            copy($sOrigFile, $sFilename);
            $sPath = sprintf("%s/%s", $oDocument->getFullPath(), $oDocument->getFileName());
            $sPath = str_replace($aReplaceKeys, $aReplaceValues, $sPath);
            $sPath = $this->_convertEncoding($sPath, true);
            $aPaths[] = $sPath;
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
                print nl2br($this->_convertEncoding($contents, false));
            }
            $i++;
        }
        pclose($fh);

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Bulk export",
            'transactionNS' => 'ktstandard.transactions.bulk_export',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));

        $url = KTUtil::addQueryStringSelf(sprintf('action=downloadZipFile&fFolderId=%d&exportcode=%s', $this->oFolder->getId(), $sExportCode));
        printf('<p>' . _kt('Go <a href="%s">here</a> to download the zip file if you are not automatically redirected there') . "</p>\n", $url);
        $folderurl = KTBrowseUtil::getUrlForFolder($this->oFolder);
        printf('<p>' . _kt('Once downloaded, return to the original <a href="%s">folder</a>') . "</p>\n", $folderurl);
        printf("</div></div></body></html>\n");
        printf('<script language="JavaScript">
                function kt_bulkexport_redirect() {
                    document.location.href = "%s";
                }
                callLater(1, kt_bulkexport_redirect);

                </script>', $url);
                
        $this->commitTransaction(); 
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
        $aParams = kt_array_merge($aParams,  $aPermissionParams);
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
        
        if (!file_exists($sZipFile)) {
            $this->addErrorMessage(_kt('The ZIP can only be downloaded once - if you cancel the download, you will need to reload the Bulk Export page.'));
            redirect(generateControllerUrl("browse", "fBrowseType=folder&fFolderId=" . $this->oFolder->getId()));
            exit(0);
        }        
        
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
