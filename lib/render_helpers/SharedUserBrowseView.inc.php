<?php

require_once(KT_LIB_DIR . '/render_helpers/BrowseView.inc.php');
require_once('sharedContent.inc');

/**
 * Shared user browse view class
 */
class SharedUserBrowseView extends BrowseView {

    private $oUser = null;

    /**
     * Get the folder listing
     *
     * @param string $folderId
     * @param $totalItems
     * @param string $sortField
     * @param string $asc
     * @return mixed $ret
     */
    public function getFolderContent($folderId, &$totalItems = 0, $sortField = 'title', $asc = true)
    {
        $userId = $_SESSION['userID'];
        $this->oUser = is_null($this->oUser) ? User::get($userId) : $this->oUser;
        $disabled = $this->oUser->getDisabled();

        $sharedContent = new SharedContent();
        $content = $sharedContent->getUsersSharedContents($userId, $folderId);
        $ret = array('folders' => array(), 'documents'=>array());

        foreach ($content['documents'] as $item) {
            $item['user_id'] = $userId;
            $item['user_disabled'] = $disabled;
            $item['item_type'] = 'D';
            $item['version'] = $item['major_version'] . '.' . $item['minor_version'];
            $ret['documents'][] = $this->browseViewItems($item, $folderId);
        }

        foreach ($content['folders'] as $item) {
            $item['item_type'] = 'F';
            $item['mime_type'] = 'folder';
            $item['mime_icon_path'] = 'folder';
            $item['mime_display'] = 'Folder';
            $ret['folders'][] = $this->browseViewItems($item, $folderId);
        }

        if (isset($sortField)) {
            $ret['documents'] = ktvar::sortArrayMatrixByKeyValue($ret['documents'], $sortField, $asc);
            $ret['folders'] = ktvar::sortArrayMatrixByKeyValue($ret['folders'], $sortField, $asc);
        }

        return $ret;
    }

    public function renderBulkActionMenu($items, $folder)
    {
        return '';
    }

    public function renderDocumentItem($item = null, $empty = false, $shortcut = false)
    {
        $fileNameCutoff = 100;
        $namespace = ' not_supported';
        $permissions = SharedContent::canAccessDocument($item['user_id'], $item['id'], null, 1);
        $hasCheckedOut = ($_SESSION['userID'] == $item['checked_out_by_id']);

        // Icons
        $iconFile = 'resources/mimetypes/newui/' . KTMime::getIconPath($item['mimetypeid']) . '.png';
        $item['icon_exists'] = file_exists(KT_DIR . '/' . $iconFile);
        $item['icon_file'] = $iconFile;
        if ($item['icon_exists']) {
            $item['mimeicon'] = str_replace('\\', '/', $GLOBALS['default']->rootUrl . '/' . $iconFile);
            $item['mimeicon'] = 'background-image: url(' . $item['mimeicon'] . ')';
        }
        else {
            $item['mimeicon'] = '';
        }

        // Create link, which will always be of a document and not a shortcut
        $item['document_link'] = KTUtil::buildUrl('view.php', array('fDocumentId'=> $item['id']));
        $item['filename'] = (strlen($item['filename']) > $fileNameCutoff) ? substr($item['filename'], 0, $fileNameCutoff - 3) . "..." : $item['filename'];
        $item['has_workflow'] = '';
        $item['is_immutable'] = ($item['is_immutable'] == 1) ? true : false;
        $item['is_immutable'] = $item['is_immutable'] ? '' : $namespace;
        $item['is_checkedout'] = $item['checked_out_date'] ? '' : $namespace;

        // Check parent folder if user type is shared (disabled == 4)
        if (isset($item['object_permissions'])) {
            // check permissions based on object_permissions, if set, or shared user access if shared user
            // and check if the user has checkd out the document
            $item['actions.checkout'] = ($item['object_permissions'] == 0) ? $namespace : ($item['checked_out_date'] ? $namespace : '');
            $item['actions.checkin'] = ($item['object_permissions'] == 0 || !$hasCheckedOut) ? $namespace : ($item['is_checked_out'] == 0 ? $namespace : '');
            $item['actions.cancel_checkout'] = ($item['object_permissions'] == 0 || !$hasCheckedOut) ? $namespace : ($item['is_checked_out'] == 0 ? $namespace : '');
        }
        else if ($item['user_disabled'] == 4) {
            // check permissions on parent folder if document not present in shared content for user
            $item['actions.checkout'] = ($permissions == false) ? $namespace : $item['checked_out_date'] ? '' : $namespace;
            $item['actions.checkin'] = ($permissions == false) ? $namespace : (($item['is_checked_out'] == 0) ? '' : $namespace);
            $item['actions.cancel_checkout'] = ($permissions == false) ? $namespace : (($item['is_checked_out'] == 0) ? '' : $namespace);
        }

        //Modifications to perform when the document has been checked out
        if ($item['checked_out_date']) {
            list($item['checked_out_date_d'], $item['checked_out_date_t']) = split(" ", $item['checked_out_date']);
        }

        if ($item['is_immutable'] == '') {
            $item['actions.checkin'] = $namespace;
            $item['actions.checkout'] = $namespace;
            $item['actions.cancel_checkout'] = $namespace;
            $item['actions.alerts'] = $namespace;
            $item['actions.email'] = $namespace;
            $item['actions.change_owner'] = $namespace;
            $item['actions.finalize_document'] = $namespace;
        }

        $item['separatorA'] = $item['actions.copy'] == '' ? '' : $namespace;
        $item['separatorB'] = $item['actions.download'] == '' || $item['actions.instantview'] == '' ? '' : $namespace;
        $item['separatorC'] = $item['actions.checkout'] == '' || $item['actions.checkin'] == '' || $item['actions.cancel_checkout'] == '' ? '' : $namespace;
        $item['separatorD'] = $item['actions.alert'] == '' || $item ['actions.email'] == '' ? '' : $namespace;

        if ($item['is_immutable'] == '') {
            $item['separatorB'] = $item['separatorC'] = $item['separatorD'] = $namespace;
        }

        // Check if the thumbnail exists
        $devNoThumbs = (isset($_GET['noThumbs']) || $_SESSION['browse_no_thumbs']) ? true : false;
        $_SESSION['browse_no_thumbs'] = $devNoThumbs;
        $item['thumbnail'] = '';
        $item['thumbnailclass'] = 'nopreview';

        // When item is null, thumbnails won't exist so skip the check
        if (!$devNoThumbs) {
            // Check if the document has a thumbnail rendition -> has_rendition = 2, 3, 6, 7
            // 0 = nothing, 1 = pdf, 2 = thumbnail, 4 = flash
            // 1+2 = 3: pdf & thumbnail; 1+4 = 5: pdf & flash; 2+4 = 6: thumbnail & flash; 1+2+4 = 7: all
            // If the flag hasn't been set, check against storage and update the flag - for documents where the flag hasn't been set
            $check = false;
            if (is_null($item['has_rendition'])) {
                $storageManager = KTStorageManagerUtil::getSingleton();
                $document = Document::get($item['id']);
                if (!PEAR::isError($document)) {
                    $varDir = $GLOBALS['default']->varDirectory;
                    $thumbnailCheck = $varDir . '/thumbnails/' . $item['id'] . '.jpg';
                    if ($storageManager->file_exists($thumbnailCheck)) {
                        $document->setHasRendition(2);
                        $check = true;
                    }
                    else {
                        $document->setHasRendition(0);
                    }

                    $document->update();
                }
            }

            if ($check || in_array($item['has_rendition'], array(2, 3, 6, 7))) {
                $item['thumbnail'] = '<img src="plugins/thumbnails/thumbnail_view.php?documentId=' . $item['id'] . '" onClick="document.location.replace(\'view.php?fDocumentId=' . $item['id'] . '#preview\');">';
                $item['thumbnailclass'] = 'preview';
            }
        }

        // Default - hide edit online
        $item['allowdoczohoedit'] = '';
        if ($this->zohoEnabled) {
            if (Zoho::resolve_type($document)) {
                if ($item['actions.checkout'] != $namespace) {
                    $item['allowdoczohoedit'] = '<li class="action_zoho_document"><a href="javascript:;" onclick="zohoEdit(\'' . $item['id'] . '\')">Edit Document Online</a></li>';
                }
            }
        }

        // Get the name of the user that checked out document
        if (!is_null($item['checked_out_by_id'])) {
            $checkedOutUser = User::get($item['checked_out_by_id']);
            $item['checked_out_by'] = $checkedOutUser->getName();
        }

        // Sanitize document title
        $item['title'] = sanitizeForHTML($item['title']);
        $item['filesize'] = KTUtil::filesizeToString($item['filesize'], 'KB');

        $template = $this->getDocumentTemplate(2);

        if ($empty) { return '<span class="fragment document" style="display:none;">' . $template . '</span>'; }

        return ktVar::parseString($template, $item);
    }

    public function renderFolderItem($item = null, $empty = false, $shortcut = false)
    {
        $item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=> $item['id']));
        // Sanitize folder title
        $item['title'] = sanitizeForHTML($item['title']);
        $template = $this->getFolderTemplate(false);

        if ($empty) {
            return '<span class="fragment folder" style="display:none;">' . $template . '</span>';
        }

        return ktVar::parseString($template, $item);
    }

    protected function getDocumentActionMenu($shareSeparator = null)
    {
        return '<ul class="doc actionMenu">
                                <!-- li class="actionIcon comments"></li -->
                                <li class="actionIcon actions">
                                    <ul>
                                        <li class="action_download [actions.download]"><a href="action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=[id]">Download</a></li>
                                        <li class="action_instant_view [actions.instant_view]"><a href="[document_link]#preview">Instant View</a></li>
                                        [allowdoczohoedit]

                                        <li class="action_checkout [actions.checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.checkout&fDocumentId=[id]">Check-out</a></li>
                                        <li class="action_cancel_checkout [actions.cancel_checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.cancelcheckout&fDocumentId=[id]">Cancel Check-out</a></li>
                                        <li class="action_checkin [actions.checkin]"><a href="action.php?kt_path_info=ktcore.actions.document.checkin&fDocumentId=[id]">Check-in</a></li>
                                    </ul>
                                </li>
                            </ul>';
    }

    /**
     * Sanitize a browse view items attributes
     *
     * @param array $item
     * @param int $folderId
     * @return array $item
     */

    private function browseViewItems($item, $folderId)
    {
        foreach ($item as $key=> $value) {
            if ($value == 'n/a') {
                $item[$key] = null;
            }
        }

        $item['container_folder_id'] = $folderId;

        return $item;
    }

}

?>
