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
 */

// more advanced, intelligent columns.

require_once(KT_LIB_DIR . '/browse/advancedcolumns.inc.php');

class AdvancedTitleColumn extends AdvancedColumn {
    var $name = 'title';
    var $namespace = 'ktcore.columns.title';
    var $sortable = true;
    var $aOptions = array();
    var $aIconPaths = array();
    var $link_folders = true;
    var $link_documents = true;

    function setOptions($aOptions) {
        $this->link_folders = KTUtil::arrayGet($aOptions, 'link_folders', $this->link_folders, false);
        $this->link_documents = KTUtil::arrayGet($aOptions, 'link_documents', $this->link_documents, false);
        parent::setOptions($aOptions);
    }

    function AdvancedTitleColumn() {
        $this->label = _kt("Title");
    }

    // what is used for sorting
    // query addition is:
    //    [0] => join claus
    //    [1] => join params
    //    [2] => ORDER

    function addToFolderQuery() {
        return array(null,
            null,
            "F.name",
        );
    }
    function addToDocumentQuery() {
            return array(null,
            null,
            "DM.name"
        );
    }


    function renderFolderLink($aDataRow) {
        /* this check has to be done so that any titles longer than 40 characters is not displayed incorrectly.
         as mozilla cannot wrap text without white spaces */
        global $default;
        $charLength = (isset($default->titleCharLength)) ? $default->titleCharLength : 40;

        if (mb_strlen($aDataRow["folder"]->getName(), 'UTF-8') > $charLength) {
        	mb_internal_encoding("UTF-8");
            $outStr = htmlentities(mb_substr($aDataRow["folder"]->getName(), 0, $charLength, 'UTF-8')."...", ENT_NOQUOTES, 'UTF-8');
        }else{
            $outStr = htmlentities($aDataRow["folder"]->getName(), ENT_NOQUOTES, 'UTF-8');
        }

        if($this->link_folders) {
            $outStr = '<a href="' . $this->buildFolderLink($aDataRow) . '">' . $outStr . '</a>';
        }
        return $outStr;
    }

    function renderDocumentLink($aDataRow) {
        /* this check has to be done so that any titles longer than 40 characters is not displayed incorrectly.
         as mozilla cannot wrap text without white spaces */
        global $default;
        $charLength = (isset($default->titleCharLength)) ? $default->titleCharLength : 40;

        if (mb_strlen($aDataRow["document"]->getName(), 'UTF-8') > $charLength) {
        	mb_internal_encoding("UTF-8");
            $outStr = htmlentities(mb_substr($aDataRow["document"]->getName(), 0, $charLength, 'UTF-8')."...", ENT_NOQUOTES, 'UTF-8');
        }else{
            $outStr = htmlentities($aDataRow["document"]->getName(), ENT_NOQUOTES, 'UTF-8');
        }

        if($this->link_documents) {
            $outStr = '<a href="' . $this->buildDocumentLink($aDataRow) . '" title="' . htmlentities($aDataRow["document"]->getFilename(), ENT_QUOTES, 'UTF-8').'">' .
                $outStr . '</a>';
        }
        return $outStr;
    }

    function buildDocumentLink($aDataRow) {
    	if($aDataRow['document']->isSymbolicLink()){
    		$iDocId = $aDataRow['document']->getRealDocumentId();
    	}else{
    		$iDocId = $aDataRow["document"]->getId();
    	}

        $url = KTBrowseUtil::getUrlForDocument($iDocId);
        if($aDataRow['document']->isSymbolicLink()){
        	$aDataRow['document']->switchToRealCore();
        	$url .= "&fShortcutFolder=".$aDataRow['document']->getFolderId();
        }
        return $url;
    }


    // 'folder_link' allows you to link to a different URL when you're connecting, instead of addQueryStringSelf
    // 'direct_folder' means that you want to go to 'browse folder'
    // 'qs_params' is an array (or string!) of params to add to the link

    function buildFolderLink($aDataRow) {
        if (is_null(KTUtil::arrayGet($this->aOptions, 'direct_folder'))) {
           $dest = KTUtil::arrayGet($this->aOptions, 'folder_link');
           if($aDataRow['folder']->isSymbolicLink()){
           		$params = array('fFolderId' => $aDataRow['folder']->getLinkedFolderId(),
				     'fShortcutFolder' => $aDataRow['folder']->getParentID());
           }else{
          		$params = array('fFolderId' => $aDataRow['folder']->getId());
           }
	   		$params = kt_array_merge(KTUtil::arrayGet($this->aOptions, 'qs_params', array()),
				     $params);

            if (empty($dest)) {
                return KTUtil::addQueryStringSelf($params);
            } else {
                return KTUtil::addQueryString($dest, $params);
            }

        } else {
        	if($aDataRow['folder']->isSymbolicLink()){
        		return KTBrowseUtil::getUrlForFolder($aDataRow['folder']->getLinkedFolder())."&fShortcutFolder=".$aDataRow['folder']->getParentID();
        	}else{
            	return KTBrowseUtil::getUrlForFolder($aDataRow['folder']);
        	}
        }
    }

    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) {
        if ($aDataRow["type"] == "folder") {
            $contenttype = 'folder';
            $link = $this->renderFolderLink($aDataRow);

            // If folder is a shortcut display the shortcut mime icon
            if($aDataRow['folder']->isSymbolicLink()){
                $contenttype .= '_shortcut';
            }
            // Separate the link from the mime icon to allow for right-to-left languages
            return "<div style='float: left' class='contenttype $contenttype'>&nbsp;</div>$link";
        } else {
            $type = '';
            $size = '';
            if($aDataRow['document']->isSymbolicLink()){
                // If document is a shortcut - display the shortcut mime type
                $type = 'shortcut';
            }else{
                // Display the document size if it is not a shortcut
                $size = $this->prettySize($aDataRow["document"]->getSize());
                $size = "&nbsp;($size)";
            }

            $link = $this->renderDocumentLink($aDataRow);
            $contenttype = $this->_mimeHelper($aDataRow["document"]->getMimeTypeId(), $type);

            // Separate the link from the mime icon and the size to allow for right-to-left languages
            return "<div style='float: left' class='contenttype $contenttype'>&nbsp;</div><div style='float: left'>$link</div>$size";
        }
    }

    function prettySize($size) {
        $finalSize = $size;
        $label = 'b';

        if ($finalSize > 1000) { $label='Kb'; $finalSize = floor($finalSize/1000); }
        if ($finalSize > 1000) { $label='Mb'; $finalSize = floor($finalSize/1000); }
        return $finalSize . $label;
    }

    function _mimeHelper($iMimeTypeId, $type = null) {
        require_once(KT_LIB_DIR . '/mime.inc.php');
        return KTMime::getIconPath($iMimeTypeId, $type);
    }
}

/*
 * Column to handle dates
 */

class AdvancedDateColumn extends AdvancedColumn {
    var $name = 'datecolumn';

    var $document_field_function;
    var $folder_field_function;
    var $sortable = true;
    var $document_sort_column;
    var $folder_sort_column;
    var $namespace = 'ktcore.columns.genericdate';

    function AdvancedDateColumn() {
        $this->label = _kt('Generic Date Function');
    }

    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) {
        $outStr = '';
        if (($aDataRow["type"] == "folder") && (!is_null($this->folder_field_function))) {
            $res = call_user_func(array($aDataRow["folder"],  $this->folder_field_function));
            $dColumnDate = strtotime($res);

            // now reformat this into something "pretty"
            return date("Y-m-d H:i", $dColumnDate);

        } else if (($aDataRow["type"] == "document") && (!is_null($this->document_field_function))) {
            $res = call_user_func(array($aDataRow["document"],  $this->document_field_function));
            $dColumnDate = strtotime($res);

            // now reformat this into something "pretty"
            return date("Y-m-d H:i", $dColumnDate);
        } else {
            return '&mdash;';
        }
        return $outStr;
    }

    function addToFolderQuery() {
        return array(null, null, null);
    }
    function addToDocumentQuery() {
        return array(null, null, $this->document_sort_column);
    }
}

class CreationDateColumn extends AdvancedDateColumn {
    var $document_field_function = 'getCreatedDateTime';
    var $folder_field_function = null;

    var $document_sort_column = "D.created";
    var $folder_sort_column = null;
    var $namespace = 'ktcore.columns.creationdate';

    function CreationDateColumn() {
        $this->label = _kt('Created');
    }
}

class ModificationDateColumn extends AdvancedDateColumn {
    var $document_field_function = 'getLastModifiedDate';
    var $folder_field_function = null;

    var $document_sort_column = "D.modified";
    var $folder_sort_column = null;
    var $namespace = 'ktcore.columns.modificationdate';

    function ModificationDateColumn() {
        $this->label = _kt('Modified');
    }
}

class AdvancedUserColumn extends AdvancedColumn {
    var $document_field_function;
    var $folder_field_function;
    var $sortable = false; // by default
    var $document_sort_column = 'creator_id';
    var $folder_sort_column = 'creator_id';
    var $namespace = 'ktcore.columns.genericuser';

    function AdvancedUserColumn() {
        $this->label = null; // abstract.
    }

    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) {
        $iUserId = null;
        if (($aDataRow["type"] == "folder") && (!is_null($this->folder_field_function))) {
            if (method_exists($aDataRow['folder'], $this->folder_field_function)) {
                $iUserId = call_user_func(array($aDataRow['folder'], $this->folder_field_function));
            }
        } else if (($aDataRow["type"] == "document") && (!is_null($this->document_field_function))) {
            if (method_exists($aDataRow['document'], $this->document_field_function)) {
                $iUserId = call_user_func(array($aDataRow['document'], $this->document_field_function));
            }
        }
        if (is_null($iUserId)) {
            return '&mdash;';
        }
        $oUser = User::get($iUserId);
        if (PEAR::isError($oUser) || $oUser == false) {
            return '&mdash;';
        } else {
            return htmlentities($oUser->getName(), ENT_NOQUOTES, 'UTF-8');
        }
    }

    function addToFolderQuery() {
        $sUsersTable = KTUtil::getTableName('users');
        $sJoinSQL = "LEFT JOIN {$sUsersTable} AS users_order_join ON F.{$this->folder_sort_column} = users_order_join.id";
        return array($sJoinSQL, null, 'users_order_join.name');
    }

    function addToDocumentQuery() {
        $sUsersTable = KTUtil::getTableName('users');
        $sJoinSQL = "LEFT JOIN {$sUsersTable} AS users_order_join ON D.{$this->document_sort_column} = users_order_join.id";
        return array($sJoinSQL, null, 'users_order_join.name');
    }
}

class CreatorColumn extends AdvancedUserColumn {
    var $document_field_function = "getCreatorID";
    var $folder_field_function = "getCreatorID";
    var $sortable = true; // by default
    var $document_sort_column = 'creator_id';
    var $folder_sort_column = 'creator_id';
    var $namespace = 'ktcore.columns.creator';

    function CreatorColumn() {
        $this->label = _kt("Creator"); // abstract.
    }
}

class AdvancedSelectionColumn extends AdvancedColumn {
    var $rangename = null;
    var $show_folders = true;
    var $show_documents = true;

    var $namespace = "ktcore.columns.selection";

    function AdvancedSelectionColumn() {
        $this->label = '';
    }

    function setOptions($aOptions) {
        AdvancedColumn::setOptions($aOptions);
        $this->rangename = KTUtil::arrayGet($this->aOptions, 'rangename', $this->rangename);
        $this->show_folders = KTUtil::arrayGet($this->aOptions, 'show_folders', $this->show_folders, false);
        $this->show_documents = KTUtil::arrayGet($this->aOptions, 'show_documents', $this->show_documents, false);
    }

    function renderHeader($sReturnURL) {
        global $main;
        $main->requireJSResource("resources/js/toggleselect.js");

        return sprintf('<input type="checkbox" title="toggle all" onclick="toggleSelectFor(this, \'%s\')" />', $this->rangename);

    }

    // only include the _f or _d IF WE HAVE THE OTHER TYPE.
    function renderData($aDataRow) {
        $localname = htmlentities($this->rangename,ENT_QUOTES,'UTF-8');

        if (($aDataRow["type"] === "folder") && ($this->show_folders)) {
            if ($this->show_documents) {
                $localname .= "_f[]";
            }
            $v = $aDataRow["folderid"];
        } else if (($aDataRow["type"] === "document") && $this->show_documents) {
            if ($this->show_folders) {
                $localname .= "_d[]";
            }
            $v = $aDataRow["docid"];
        } else {
            return '&nbsp;';
        }

        return sprintf('<input type="checkbox" name="%s" onclick="activateRow(this)" value="%s"/>', $localname, $v);
    }


    // no label, but we do have a title
    function getName() {
        return _kt("Multiple Selection");
    }
}


class AdvancedSingleSelectionColumn extends AdvancedSelectionColumn {
    var $namespace = 'ktcore.columns.singleselection';

    function AdvancedSingleSelectionColumn() {
        parent::AdvancedSelectionColumn();
        $this->label = null;
    }

    function renderHeader() {
    	global $main;
        //include some javascript to force real single selections
        if($this->show_folders && $this->show_documents){
        	$main->requireJSResource("resources/js/singleselect.js");
        }
        return '&nbsp;';
    }

    // only include the _f or _d IF WE HAVE THE OTHER TYPE.
    function renderData($aDataRow) {
        $localname = $this->rangename;

        if (($aDataRow["type"] === "folder") && ($this->show_folders)) {
            if ($this->show_documents) {
                $localname .= "_f";
            }
            $v = $aDataRow["folderid"];
        } else if (($aDataRow["type"] === "document") && $this->show_documents) {
            if ($this->show_folders) {
                $localname .= "_d";
            }
            $v = $aDataRow["docid"];
        } else {
            return '&nbsp;';
        }

        $return =  '<input type="radio" name="' . $localname . '" value="' . $v . '" ';
        if($this->show_folders && $this->show_documents){
        	$return .= 'onClick="forceSingleSelect(this)" ';
        }
        $return .='/>';
        return $return;
    }

    // no label, but we do have a title
    function getName() {
        return _kt("Single Selection");
    }
}


class AdvancedWorkflowColumn extends AdvancedColumn {
    var $namespace = 'ktcore.columns.workflow_state';
    var $sortable = false;

    function AdvancedWorkflowColumn() {
        $this->label = _kt("Workflow State");
        $this->sortable = false;
    }

    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) {
        // only _ever_ show this for documents.
        if ($aDataRow["type"] === "folder") {
            return '&nbsp;';
        }

        $oWorkflow = KTWorkflowUtil::getWorkflowForDocument($aDataRow['document']);
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($aDataRow['document']);
        if (($oState == null) || ($oWorkflow == null)) {
            return '&mdash;';
        } else {
            return sprintf('%s <span class="descriptive">%s</span>',
                htmlentities($oState->getName(), ENT_NOQUOTES, 'UTF-8'),
                htmlentities($oWorkflow->getName(), ENT_NOQUOTES, 'UTF-8')
            );
        }
    }
}

class CheckedOutByColumn extends AdvancedColumn {
    var $namespace = 'ktcore.columns.checkedout_by';
    var $sortable = false;

    function CheckedOutByColumn() {
        $this->label = _kt('Checked Out By');
        $this->sortable = false;
    }

    function renderData($aDataRow) {
        // only show this for documents.
        if ($aDataRow['type'] === 'folder') {
            return '&nbsp;';
        }

        // Check if document is checked out
        $bIsCheckedOut = $aDataRow['document']->getIsCheckedOut();

        if($bIsCheckedOut){
            // Get the user id
            $iUserId = $aDataRow['document']->getCheckedOutUserID();
            $oUser = User::get($iUserId);
            $sUser = $oUser->getName();

            return '<span class="descriptive">'.htmlentities($sUser, ENT_NOQUOTES, 'UTF-8').'</span>';
        }
        return '&mdash;';
    }
}

class DocumentTypeColumn extends AdvancedColumn {
    var $namespace = 'ktcore.columns.document_type';
    var $sortable = false;

    function DocumentTypeColumn() {
        $this->label = _kt('Document Type');
        $this->sortable = false;
    }

    function renderData($aDataRow) {
        // only show this for documents.
        if ($aDataRow['type'] === 'folder') {
            return '&nbsp;';
        }

        // Check if document is checked out
        $iDocTypeId = $aDataRow['document']->getDocumentTypeID();

        if(!empty($iDocTypeId)){
            $oDocumentType = DocumentType::get($iDocTypeId);
            $sType = $oDocumentType->getName();

            return '<span class="descriptive">'.htmlentities($sType, ENT_NOQUOTES, 'UTF-8').'</span>';
        }
        return '&mdash;';
    }
}

class AdvancedDownloadColumn extends AdvancedColumn {

    var $namespace = 'ktcore.columns.download';

    function AdvancedDownloadColumn() {
        $this->label = null;
    }

    function renderHeader($sReturnURL) {
        return '&nbsp;';
    }

    function renderData($aDataRow) {
        // only _ever_ show this for documents.
        if ($aDataRow["type"] === "folder") {
            return '&nbsp;';
        }

        $link = KTUtil::ktLink('action.php','ktcore.actions.document.view', 'fDocumentId=' . $aDataRow['document']->getId());
        return sprintf('<a href="%s" class="ktAction ktDownload" title="%s">%s</a>', $link, _kt('Download Document'), _kt('Download Document'));
    }

    function getName() { return _kt('Download'); }
}


class DocumentIDColumn extends AdvancedColumn {
    var $bSortable = false;
    var $namespace = 'ktcore.columns.docid';

    function DocumentIDColumn() {
        $this->label = _kt("Document ID");
    }

    function renderData($aDataRow) {
        // only _ever_ show this for documents.
        if ($aDataRow["type"] === "folder") {
            return '&nbsp;';
        }

        return htmlentities($aDataRow['document']->getId(), ENT_NOQUOTES, 'UTF-8');
    }
}

class ContainingFolderColumn extends AdvancedColumn {

    var $namespace = 'ktcore.columns.containing_folder';

    function ContainingFolderColumn() {
        $this->label = _kt("View Folder");
    }

    function renderData($aDataRow) {
        // only _ever_ show this for documents.
        if ($aDataRow["type"] === "folder") {
            return '&nbsp;';
        }

        $link = KTBrowseUtil::getUrlForFolder($aDataRow['document']->getFolderId());
        return sprintf('<a href="%s" class="ktAction ktMoveUp" title="%s">%s</a>', $link, _kt('View Folder'), _kt('View Folder'));
    }

    function getName() { return _kt('Opening Containing Folder'); }
}

?>
