<?php
/**
 * Bulk Actions API for KnowledgeTree
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
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 */

/**
 * API for the handling bulk actions on documents and folders within KnowledgeTree
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_BulkActions
{
    /**
     * Instance of the KTAPI object
     *
     * @access private
     */
    private $ktapi;

    /**
     * Constructs the bulk actions object
     *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi Instance of the KTAPI object
     */
    public function __construct(&$ktapi)
    {
        $this->ktapi = $ktapi;
    }

    /**
     * Bulk copies a list of folders and/or documents into the target folder.
     * If any documents or folders fail to copy, an array is returned containing the document/folder object and the failure message.
     *
     * <code>
	 * $ktapi = new KTAPI();
     * $session = $ktapi->start_system_session();
	 * $document = $ktapi->get_document_by_id($documentid);
	 *
	 * $root = $ktapi->get_root_folder();
     * $newFolder = $root->add_folder("New target folder");
     * if(PEAR::isError($newFolder)) return;
     *
     * $aItems = array($document);
     * $bulk = new KTAPI_BulkActions($ktapi);
     * $res = $bulk->copy($aItems, $newFolder, 'Bulk copy');
     *
     * // if documents / folders failed
     * if(!empty($res)) {
     *     // display reason for documents failure
     *     foreach($res['docs'] as $failedDoc){
     *         echo '<br>' . $failedDoc['object']->get_title() . ' - reason: '.$failedDoc['reason'];
     *     }
     *     // display reason for folders failure
     *     foreach($res['folders'] as $failedDoc){
     *         echo '<br>' . $failedFolder['object']->get_folder_name() . ' - reason: '.$failedFolder['reason'];
     *     }
     * }
     * </code>
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @param KTAPI_Folder $target_folder The target folder object
     * @param string $reason The reason for performing the copy
     * @return array|PEAR_Error Returns an array of documents and folders that couldn't be copied | PEAR_Error on failure
     */
    function copy($items, &$target_folder, $reason)
    {
        if(empty($items)) return;
        assert(!is_null($target_folder));
		assert($target_folder instanceof KTAPI_FOLDER);

		if(!is_array($items)){
		    $items = array($items);
		}

        // Check user has write permission on target folder
        $result = $this->ktapi->can_user_access_object_requiring_permission($target_folder->get_folder(), KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}

        // Copy the document or folder
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            assert($item instanceof KTAPI_Document || $item instanceof KTAPI_Folder);

            $docOrFolder = ($item instanceof KTAPI_Document) ? 'docs' : 'folders';

            $res = $item->copy($target_folder, $reason);

            if(PEAR::isError($res)){
                $failed[$docOrFolder][] = array('object' => $item, 'reason' => $res->getMessage());
                continue;
            }
        }

        return $failed;
    }

    /**
     * Bulk moves a list of folders and/or documents into the target folder
     *
     * <code>
	 * $ktapi = new KTAPI();
     * $session = $ktapi->start_system_session();
	 * $document = $ktapi->get_document_by_id($documentid);
	 *
	 * $root = $ktapi->get_root_folder();
     * $newFolder = $root->add_folder("New target folder");
     * if(PEAR::isError($newFolder)) return;
     *
     * $aItems = array($document);
     * $bulk = new KTAPI_BulkActions($ktapi)
     * $res = $bulk->move($aItems, $newFolder, 'Bulk move');
     *
     * // if documents / folders failed
     * if(!empty($res)) {
     *     // display reason for documents failure
     *     foreach($res['docs'] as $failedDoc){
     *         echo '<br>' . $failedDoc['object']->get_title() . ' - reason: '.$failedDoc['reason'];
     *     }
     *     // display reason for folders failure
     *     foreach($res['folders'] as $failedDoc){
     *         echo '<br>' . $failedFolder['object']->get_folder_name() . ' - reason: '.$failedFolder['reason'];
     *     }
     * }
     * </code>
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @param KTAPI_Folder $target_folder The target folder object
     * @param string $reason The reason for performing the move
     * @return void|PEAR_Error Nothing on success | PEAR_Error on failure
     */
    function move($items, &$target_folder, $reason)
    {
        if(empty($items)) return;
        assert(!is_null($target_folder));
		assert($target_folder instanceof KTAPI_FOLDER);

		if(!is_array($items)){
		    $items = array($items);
		}

        // Check user has write permission on target folder
        $result = $this->ktapi->can_user_access_object_requiring_permission($target_folder->get_folder(), KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}

        // Move the document or folder
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            assert($item instanceof KTAPI_Document || $item instanceof KTAPI_Folder);

            $docOrFolder = ($item instanceof KTAPI_Document) ? 'docs' : 'folders';

            $res = $item->move($target_folder, $reason);

            if(PEAR::isError($res)){
                $failed[$docOrFolder][] = array('object' => $item, 'reason' => $res->getMessage());
                continue;
            }
        }

        return $failed;
    }

    /**
     * Performs a bulk checkout on a list of folders and/or documents
     *
     * <code>
	 * $ktapi = new KTAPI();
     * $session = $ktapi->start_system_session();
	 * $document = $ktapi->get_document_by_id($documentid);
	 * $folder = $ktapi->get_folder_by_name('New test folder');
	 *
     * $aItems = array($document, $folder);
     * $bulk = new KTAPI_BulkActions($ktapi)
     * $res = $bulk->checkout($aItems, 'Bulk archive');
     *
     * // if documents / folders failed
     * if(!empty($res)) {
     *     // display reason for documents failure
     *     foreach($res['docs'] as $failedDoc){
     *         echo '<br>' . $failedDoc['object']->get_title() . ' - reason: '.$failedDoc['reason'];
     *     }
     *     // display reason for folders failure
     *     foreach($res['folders'] as $failedDoc){
     *         echo '<br>' . $failedFolder['object']->get_folder_name() . ' - reason: '.$failedFolder['reason'];
     *     }
     * }
     * </code>
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @param string $reason The reason for performing the checkout
     * @return void|PEAR_Error Nothing with download set to false | PEAR_Error on failure
     */
    function checkout($items, $reason)
    {
        if(empty($items)) return;

		if(!is_array($items)){
		    $items = array($items);
		}

        // Checkout the document or folder
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            // Documents
            if($item instanceof KTAPI_Document){
                $res = $item->checkout($reason);

                if(PEAR::isError($res)){
                    $failed['docs'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
            }else if($item instanceof KTAPI_Folder){
                // Folders - need to recurse in
                DBUtil::startTransaction();
                $res = $this->recurseFolder($item, $reason, 'checkout');

                if(PEAR::isError($res)){
                    DBUtil::rollback();
                    $failed['folders'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
                DBUtil::commit();
            }
        }

        return $failed;
    }

    /**
     * Performs a bulk cancel checkout on a list of folders and/or documents
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @param string $reason The reason for cancelling the checkout
     * @return void|PEAR_Error Nothing with download set to false | PEAR_Error on failure
     */
    function undo_checkout($items, $reason)
    {
        if(empty($items)) return;

		if(!is_array($items)){
		    $items = array($items);
		}

        // Cancel checkout on the document or folder contents
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            // Documents
            if($item instanceof KTAPI_Document){
                $res = $item->undo_checkout($reason);

                if(PEAR::isError($res)){
                    $failed['docs'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
            }else if($item instanceof KTAPI_Folder){
                // Folders - need to recurse in
                DBUtil::startTransaction();
                $res = $this->recurseFolder($item, $reason, 'undo_checkout');

                if(PEAR::isError($res)){
                    DBUtil::rollback();
                    $failed['folders'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
                DBUtil::commit();
            }
        }

        return $failed;
    }

    /**
     * Bulk immutes a list of documents
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @return void|PEAR_Error Nothing on success | PEAR_Error on failure
     */
    function immute($items)
    {
        if(empty($items)) return;

		if(!is_array($items)){
		    $items = array($items);
		}

        // Immute the documents
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            // Documents
            if($item instanceof KTAPI_Document){
                $res = $item->immute();

                if(PEAR::isError($res)){
                    $failed['docs'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
            }else if($item instanceof KTAPI_Folder){
                // Folders - need to recurse in
                DBUtil::startTransaction();
                $res = $this->recurseFolder($item, null, 'immute');

                if(PEAR::isError($res)){
                    DBUtil::rollback();
                    $failed['folders'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
                DBUtil::commit();
            }
        }

        return $failed;
    }

    /**
     * Bulk deletes a list of folders and/or documents
     *
     * <code>
	 * $ktapi = new KTAPI();
     * $session = $ktapi->start_system_session();
	 * $document = $ktapi->get_document_by_id($documentid);
	 * $folder = $ktapi->get_folder_by_name('New test folder');
	 *
     * $aItems = array($document, $folder);
     * $bulk = new KTAPI_BulkActions($ktapi)
     * $res = $bulk->delete($aItems, 'Bulk delete');
     *
     * // if documents / folders failed
     * if(!empty($res)) {
     *     // display reason for documents failure
     *     foreach($res['docs'] as $failedDoc){
     *         echo '<br>' . $failedDoc['object']->get_title() . ' - reason: '.$failedDoc['reason'];
     *     }
     *     // display reason for folders failure
     *     foreach($res['folders'] as $failedDoc){
     *         echo '<br>' . $failedFolder['object']->get_folder_name() . ' - reason: '.$failedFolder['reason'];
     *     }
     * }
     * </code>
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @param string $reason The reason for performing the deletion
     * @return void|PEAR_Error Nothing on success | PEAR_Error on failure
     */
    function delete($items, $reason)
    {
        if(empty($items)) return;

		if(!is_array($items)){
		    $items = array($items);
		}

        // Delete the document or folder
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            assert($item instanceof KTAPI_Document || $item instanceof KTAPI_Folder);

            $docOrFolder = ($item instanceof KTAPI_Document) ? 'docs' : 'folders';

            $res = $item->delete($reason);

            if(PEAR::isError($res)){
                $failed[$docOrFolder][] = array('object' => $item, 'reason' => $res->getMessage());
                continue;
            }
        }

        return $failed;
    }

    /**
     * Bulk archives a list of folders and/or documents
     *
     * <code>
	 * $ktapi = new KTAPI();
     * $session = $ktapi->start_system_session();
	 * $document = $ktapi->get_document_by_id($documentid);
	 * $folder = $ktapi->get_folder_by_name('New test folder');
	 *
     * $aItems = array($document, $folder);
     * $bulk = new KTAPI_BulkActions($ktapi)
     * $res = $bulk->archive($aItems, 'Bulk archive');
     *
     * // if documents / folders failed
     * if(!empty($res)) {
     *     // display reason for documents failure
     *     foreach($res['docs'] as $failedDoc){
     *         echo '<br>' . $failedDoc['object']->get_title() . ' - reason: '.$failedDoc['reason'];
     *     }
     *     // display reason for folders failure
     *     foreach($res['folders'] as $failedDoc){
     *         echo '<br>' . $failedFolder['object']->get_folder_name() . ' - reason: '.$failedFolder['reason'];
     *     }
     * }
     * </code>
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $items The folders and/or documents
     * @param string $reason The reason for performing the archival
     * @return void|PEAR_Error Nothing on success | PEAR_Error on failure
     */
    function archive($items, $reason)
    {
        if(empty($items)) return;

		if(!is_array($items)){
		    $items = array($items);
		}

        // Archive the document or folder
        // Items that fail are returned in an array with the reason for failure.

        $failed = array();
        foreach ($items as $item){
            // Documents
            if($item instanceof KTAPI_Document){
                $res = $item->archive($reason);

                if(PEAR::isError($res)){
                    $failed['docs'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
            }else if($item instanceof KTAPI_Folder){
                // Folders - need to recurse in
                DBUtil::startTransaction();
                $res = $this->recurseFolder($item, $reason, 'archive');

                if(PEAR::isError($res)){
                    DBUtil::rollback();
                    $failed['folders'][] = array('object' => $item, 'reason' => $res->getMessage());
                    continue;
                }
                DBUtil::commit();
            }
        }

        return $failed;
    }

    /**
     * Recursive function to perform a given action on a folder and contained documents.
     *
	 * @author KnowledgeTree Team
	 * @access private
     * @param KTAPI_Folder $folder The instance of the folder object being archived
     * @param string $reason The reason for archiving
     * @param string $action The action to be performed on the documents
     * @return void|PEAR_Error Returns nothing on success | a PEAR_Error on failure
     */
    private function recurseFolder($folder, $reason = '', $action = 'archive')
    {
        if(!$folder instanceof KTAPI_Folder){
            return PEAR::raiseError('Object is not an instance of KTAPI_Folder');
        }

        // Archive contained documents
        $listDocs = $folder->get_listing(1, 'D');
        if(!empty($listDocs)) {

            foreach ($listDocs as $docInfo){
                $doc = $this->ktapi->get_document_by_id($docInfo['id']);

                switch ($action){
                    case 'archive':
                        $res = $doc->archive($reason);
                        break;

                    case 'checkout':
                        $res = $doc->checkout($reason);
                        break;

                    case 'undo_checkout':
                        $res = $doc->undo_checkout($reason);
                        break;

                    case 'immute':
                        $res = $doc->immute();
                        break;
                }


                if(PEAR::isError($res)){
                    return $res;
                }
            }
        }

        // Archive contained folders
        $listFolders = $folder->get_listing(1, 'F');
        if(!empty($listFolders)) {
            foreach ($listFolders as $folderItem){
                $res = $this->archiveFolder($folderItem, $reason);

                if(PEAR::isError($res)){
                    return $res;
                }
            }
        }
        return;
    }
}
?>
