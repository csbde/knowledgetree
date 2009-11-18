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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');

require_once(KT_LIB_DIR . '/config/config.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/compressionArchiveUtil.inc.php');
require_once(KT_LIB_DIR . '/actions/bulkaction.php');

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
        return _kt('Bulk Download');
    }

    function do_main() {
        $config = KTConfig::getSingleton();
        $useQueue = $config->get('export/useDownloadQueue', true);

        // Create the export code
        $exportCode = KTUtil::randomString();
        $this->oZip = new ZipFolder('', $exportCode);

        if(!$this->oZip->checkConvertEncoding()) {
            redirect(KTBrowseUtil::getUrlForFolder($this->oFolder));
            exit(0);
        }

        $bNoisy = $config->get("tweaks/noisyBulkOperations");
        $bNotifications = ($config->get('export/enablenotifications', 'on') == 'on') ? true : false;

        $sCurrentFolderId = $this->oFolder->getId();
        $url = KTUtil::addQueryStringSelf(sprintf('action=downloadZipFile&fFolderId=%d&exportcode=%s', $sCurrentFolderId, $exportCode));
        $folderurl = KTBrowseUtil::getUrlForFolder($this->oFolder);

        if($useQueue){
            DownloadQueue::addItem($exportCode, $sCurrentFolderId, $sCurrentFolderId, 'folder');

            $task_url = KTUtil::kt_url() . '/presentation/lookAndFeel/knowledgeTree/bulkdownload/downloadTask.php';

          	$oTemplating =& KTTemplating::getSingleton();
          	$oTemplate = $oTemplating->loadTemplate('ktcore/action/bulk_download');

          	$aParams = array(
                    'folder_url' => $folderurl,
                    'url' => $task_url,
                    'code' => $exportCode,
                    'download_url' => $url
                );
            return $oTemplate->render($aParams);
        }

        // Get all folders and sub-folders
        $sWhereClause = "parent_folder_ids = '{$sCurrentFolderId}' OR
        parent_folder_ids LIKE '{$sCurrentFolderId},%' OR
        parent_folder_ids LIKE '%,{$sCurrentFolderId},%' OR
        parent_folder_ids LIKE '%,{$sCurrentFolderId}'";

        $aFolderList = $this->oFolder->getList($sWhereClause);

        // Get any folder shortcuts within the folders
		$aLinkedFolders = KTBulkAction::getLinkingEntities($aFolderList);
		$aFolderList = array_merge($aFolderList, $aLinkedFolders);

        // Add the folders to the zip file
        $aFolderObjects = array($sCurrentFolderId => $this->oFolder);
        if(!empty($aFolderList)){
            foreach ($aFolderList as $oFolderItem){
                $itemId = $oFolderItem->getId();
                $linkedFolder = $oFolderItem->getLinkedFolderId();
                // If the folder has been added or is a shortcut then skip
                // The shortcut folders don't need to be added as their targets will be added.
                if(array_key_exists($itemId, $aFolderObjects) || !empty($linkedFolder)){
                    continue;
                }
                $this->oZip->addFolderToZip($oFolderItem);
                $aFolderObjects[$oFolderItem->getId()] = $oFolderItem;
            }
        }

        // Get the list of folder ids
        $aFolderIds = array_keys($aFolderObjects);

        // Get all documents in the folder list
        $aQuery = $this->buildQuery($aFolderIds);
        $aDocumentIds = DBUtil::getResultArrayKey($aQuery, 'id');

        if(PEAR::isError($aDocumentIds)){
            $this->addErrorMessage(_kt('There was a problem exporting the documents: ').$aDocumentIds->getMessage());
            redirect(KTBrowseUtil::getUrlForFolder($this->oFolder));
            exit(0);
        }

        // Redirect if there are no documents and no folders to export
        if (empty($aDocumentIds) && empty($aFolderList)) {
            $this->addErrorMessage(_kt("No documents found to export"));
            redirect(KTBrowseUtil::getUrlForFolder($this->oFolder));
            exit(0);
        }

        $this->oPage->template = "kt3/minimal_page";
        $this->handleOutput("");

        // Add the documents to the zip file
        if(!empty($aDocumentIds)){
            foreach ($aDocumentIds as $iId) {
                $oDocument = Document::get($iId);
                $sFolderId = $oDocument->getFolderID();

                if(!KTWorkflowUtil::actionEnabledForDocument($oDocument, 'ktcore.actions.document.view')){
                    $this->addErrorMessage($oDocument->getName().': '._kt('Document cannot be exported as it is restricted by the workflow.'));
                    continue;
                }

                $oFolder = isset($aFolderObjects[$sFolderId]) ? $aFolderObjects[$sFolderId] : Folder::get($sFolderId);

                if ($bNoisy) {
                    $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document part of bulk export", 'ktstandard.transactions.bulk_export', array());
                    $oDocumentTransaction->create();
                }

                // fire subscription alerts for the downloaded document
                if($bNotifications){
                    //$oSubscriptionEvent = new SubscriptionEvent();
                    //$oSubscriptionEvent->DownloadDocument($oDocument, $oFolder);
                }

                $this->oZip->addDocumentToZip($oDocument, $oFolder);
            }
        }

        $sExportCode = $this->oZip->createZipFile(TRUE);

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Bulk export",
            'transactionNS' => 'ktstandard.transactions.bulk_export',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));

        $sReturn = '<p>'._kt('Creating zip file. Compressing and archiving in progress ...').'</p>';
        $sReturn .= "<p style='margin-bottom: 10px;'><br /><b>".
                _kt('Warning! Please wait for archiving to complete before closing the page.').'</b><br />'.
                _kt('Note: Closing the page before the download link displays will cancel your Bulk Download.').'</p>';

        $sReturn .= '<p>' . _kt('Once your download is complete, click <a href="'.$folderurl.'">here</a> to return to the original folder') . "</p>\n";

        print($sReturn);
        printf("</div></div></body></html>\n");
        printf('<script language="JavaScript">
                function kt_bulkexport_redirect() {
                    document.location.href = "%s";
                }
                callLater(2, kt_bulkexport_redirect);

                </script>', $url);

        exit(0);
    }

    function buildQuery($aFolderIds) {
        $sFolderList = implode(', ', $aFolderIds);

        // First we get any document shortcuts
        $query = "SELECT linked_document_id FROM documents
            WHERE linked_document_id IS NOT NULL
            AND folder_id IN ({$sFolderList})";

        $aLinkedDocIds = DBUtil::getResultArrayKey($query, 'linked_document_id');
        if(PEAR::isError($aLinkedDocIds) || empty($aLinkedDocIds)){
            $sDocList = '';
        }else{
            $sDocList = implode(', ', $aLinkedDocIds);
        }

        // Get the permissions sql
        $oUser = User::get($_SESSION['userID']);
        $res = KTSearchUtil::permissionToSQL($oUser, $this->sPermissionName);
        if (PEAR::isError($res)) {
            return $res;
        }

        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;

        // Create the "where" criteria
        $sWhere = "WHERE {$sPermissionString} AND (D.folder_id IN ({$sFolderList})";
        $sWhere .= (!empty($sDocList)) ? " OR D.id IN ({$sDocList}))" : ')';
        $sWhere .= ' AND D.status_id = 1 AND linked_document_id IS NULL';

        // Create the query
        $sQuery = "SELECT DISTINCT(D.id) FROM documents AS D
                LEFT JOIN document_metadata_version AS DM ON D.metadata_version_id = DM.id
                LEFT JOIN document_content_version AS DC ON DM.content_version_id = DC.id
                $sPermissionJoin $sWhere";

        return array($sQuery, $aPermissionParams);
    }

    function do_downloadZipFile() {
        $sCode = $this->oValidator->validateString($_REQUEST['exportcode']);

        $this->oZip = new ZipFolder('', $sCode);

        $res = $this->oZip->downloadZipFile($sCode);

        if(PEAR::isError($res)){
        	$this->addErrorMessage($res->getMessage());
            redirect(generateControllerUrl("browse", "fBrowseType=folder&fFolderId=" . $this->oFolder->getId()));
        }
        exit(0);
    }
}
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTBulkExportPlugin', 'ktstandard.bulkexport.plugin', __FILE__);

?>
