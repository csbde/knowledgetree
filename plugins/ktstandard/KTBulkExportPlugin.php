<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');

require_once(KT_LIB_DIR . '/config/config.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/compressionArchiveUtil.inc.php');

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

    function getDisplayName() {
        return _kt('Bulk Export');
    }

    function do_main() {
        $folderName = $this->oFolder->getName();
        $this->oZip = new ZipFolder($folderName);

        if(!$this->oZip->checkConvertEncoding()) {
            redirect(KTBrowseUtil::getUrlForFolder($oFolder));
            exit(0);
        }

        $oStorage =& KTStorageManagerUtil::getSingleton();
        $aQuery = $this->buildQuery();
        $this->oValidator->notError($aQuery);
        $aDocumentIds = DBUtil::getResultArrayKey($aQuery, 'id');

        /* Modified 07/09/2007 by megan_w */
        // Get all the folders within the current folder
        $sCurrentFolderId = $this->oFolder->getId();
        $sWhereClause = "parent_folder_ids = '{$sCurrentFolderId}' OR
        parent_folder_ids LIKE '{$sCurrentFolderId},%' OR
        parent_folder_ids LIKE '%,{$sCurrentFolderId},%' OR
        parent_folder_ids LIKE '%,{$sCurrentFolderId}'";

        $aFolderList = $this->oFolder->getList($sWhereClause);
        /* End modified */

        $this->startTransaction();

        $oKTConfig =& KTConfig::getSingleton();
        $bNoisy = $oKTConfig->get("tweaks/noisyBulkOperations");
        $bNotifications = ($oKTConfig->get('export/enablenotifications', 'on') == 'on') ? true : false;

        // Redirect if there are no documents and no folders to export
        if (empty($aDocumentIds) && empty($aFolderList)) {
            $this->addErrorMessage(_kt("No documents found to export"));
            redirect(KTBrowseUtil::getUrlForFolder($oFolder));
            exit(0);
        }

        $this->oPage->requireJSResource('thirdpartyjs/MochiKit/Base.js');
        $this->oPage->requireJSResource('thirdpartyjs/MochiKit/Async.js');
        $this->oPage->template = "kt3/minimal_page";
        $this->handleOutput("");

        if(!empty($aDocumentIds)){
            foreach ($aDocumentIds as $iId) {
                $oDocument = Document::get($iId);

                if ($bNoisy) {
                    $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                    $oDocumentTransaction->create();
                }

                // fire subscription alerts for the downloaded document
                if($bNotifications){
                    $oSubscriptionEvent = new SubscriptionEvent();
                    $oFolder = Folder::get($oDocument->getFolderID());
                    $oSubscriptionEvent->DownloadDocument($oDocument, $oFolder);
                }

                $this->oZip->addDocumentToZip($oDocument);
            }
        }

        // Export the folder structure to ensure the export of empty directories
        foreach($aFolderList as $k => $oFolderItem){
            $this->oZip->addFolderToZip($oFolderItem);
        }

        $sExportCode = $this->oZip->createZipFile(TRUE);

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

        $folderName = $this->oFolder->getName();
        $this->oZip = new ZipFolder($folderName);

        if(!$this->oZip->downloadZipFile($sCode)){
            redirect(generateControllerUrl("browse", "fBrowseType=folder&fFolderId=" . $this->oFolder->getId()));
        }
        exit(0);
    }
}
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTBulkExportPlugin', 'ktstandard.bulkexport.plugin', __FILE__);

?>