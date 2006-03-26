<?php
require_once(KT_LIB_DIR . '/security/Permission.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/documentmanagement/MetaData.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');

/**
 * $Id$
 *
 * Contains document browsing business logic.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @version $Revision$
 * @author Neil Blakey-Milner <nbm@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.browse
 */

class BrowseCriterion {
    var $sDisplay;
    var $sDocumentField;
    var $sSortField;
    var $aLookup = null;
    var $bFolderCriterion = false;
    var $aOptions = array();
    var $iID;
    var $bString = false;
    var $sSearchTable = "D";
    var $bVisible = true;

    function BrowseCriterion ($sDisplay, $sDocumentField, $sSortField, $iID) {
        $this->sDisplay =& $sDisplay;
        $this->sDocumentField =& $sDocumentField;
        $this->sSortField =& $sSortField;
        $this->iID = $iID;
    }

    function headerDisplay () {
        return $this->sDisplay;
    }

    // dummy function
    function documentDisplay ($oDocument) {
        return $this->sDisplay;
    }

    function folderDisplay ($oDocument) {
        return "&nbsp;";
    }

    function folderQuery ($iParentID, $sSortDirection) {
        global $default;
        $sFolderQuery = "SELECT f.id FROM $default->folders_table AS f ";/*ok*/
        if (!$this->bFolderCriterion) {
            $sFolderQuery .= "WHERE parent_id = ? ORDER BY f.name asc";
            $aParams = array($iParentID);
            return array($sFolderQuery, $aParams);
        }

        if (!is_null($this->aLookup)) {
            $sFolderQuery .= "INNER JOIN " . $this->aLookup["table"] . " lt ON f.$this->sDocumentField = lt.id WHERE parent_id = ?";
            $sFolderQuery .= " ORDER BY lt." . $this->aLookup["field"] . " " . $sSortDirection;
            $aParams = array($iParentID);
            return array($sFolderQuery, $aParams);
        }

        $sFolderQuery .= "WHERE parent_id = ? ORDER BY " . $this->getFolderSortField() . " " . $sSortDirection;
        $aParams = array($iParentID);
        return array($sFolderQuery, $aParams);
    }

    function documentQuery ($iFolderID, $sSortDirection) {
        global $default;
        // create query to retrieve documents in this folder
        $documentQuery  = "SELECT d.id as id FROM $default->documents_table AS d ";/*wc*/

        if (!is_null($this->aLookup)) {
            $sDocumentJoinField = $this->getDocumentField();
            $documentQuery .= "INNER JOIN " . $this->aLookup["table"] . " lt ON ";
            if (array_key_exists('joinColumn', $this->aLookup)) {
                $documentQuery .= "d.$sDocumentJoinField" . " = lt." . $this->aLookup["joinColumn"];
            } else {
                $documentQuery .= "d.$sDocumentJoinField" . " = lt.id";
            }
        }

        $documentQuery .= " WHERE d.folder_id = ? ";
        $aParams = array($iFolderID);
        if (!is_null($this->aLookup)) {
            if (array_key_exists("whereClause", $this->aLookup)) {
                $documentQuery .= "AND lt." . $this->aLookup["whereClause"] . " ";
            }

            $documentQuery .= "ORDER BY lt." . $this->aLookup["field"] . " " . $sSortDirection;
        } else {
            $sDocumentJoinField = $this->getDocumentField();
            // $sSortField = $this->getSortField();
            $documentQuery .= "ORDER BY " . $this->getSortField() . " " . $sSortDirection;
        }
        
        return array($documentQuery, $aParams);
    }

    function getDocumentField () {
        return $this->sDocumentField;
    }

    function getSortField () {
        return $this->sSortField;
    }

    function getFolderSortField () {
        return $this->sSortField;
    }

    function getSearchField () {
        return $this->sDocumentField;
    }

    function getLookup () {
        return $this->aLookup;
    }


    function getName() {
        return $this->sDocumentField;
    }

    function getID() {
        return $this->iID;
    }

    function setOptions($aOptions) {
        $this->aOptions = $aOptions;
    }

    function searchDisplay($aRequest) {
        return "<tr><td>" . $this->headerDisplay() . ": </td><td>" . $this->searchWidget($aRequest) . "</td></tr>\n";
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@!(*&!@#
            $k = array_keys($aPreValue);
            $k = $k[0];
            $preval = $aPreValue[$k];
            return "<input type=\"text\" size=\"50\" name=\"" . $this->getWidgetBase() . "\" value=\"" . $preval . "\"/>";
        } else {
            return "<input type=\"text\" size=\"50\" name=\"" . $this->getWidgetBase() . "\" />";
        }
    }

    function getWidgetBase () {
        return "bmd" . strtr($this->getID(), '-', '_');
    }

    function getSearchTable() {
        return $this->sSearchTable;
    }

    function searchSQL ($aRequest) {
        if ($this->bString) {
            return array($this->getSearchTable() . "." . $this->getSearchField() . " LIKE '%" . DBUtil::escapeSimple($aRequest[$this->getWidgetBase()]) . "%'", array());
        } else {
            return array($this->getSearchTable() . "." . $this->getSearchField() . " = ?", array($aRequest[$this->getWidgetBase()]));
        }
    }

    function searchJoinSQL () {
        return null;
    }
}

class NameCriterion extends BrowseCriterion {
    var $bFolderCriterion = true;
    var $bString = true;
    var $sSearchTable = "DC";
    function documentDisplay ($oDocument) {
        $aOptions = $this->aOptions;
        if (array_key_exists('displayFullPath', $aOptions)) {
            $bDisplayFullPath = $aOptions['displayFullPath'];
        } else {
            $bDisplayFullPath = false;
        }
        if (array_key_exists('templateBrowsing', $aOptions)) {
            $bTemplateBrowsing = $aOptions['templateBrowsing'];
        } else {
            $bTemplateBrowsing = false;
        }

        if ($bTemplateBrowsing) {
            return displayDocumentLinkForTemplateBrowsing($oDocument, $bDisplayFullPath);
        } else {
            return displayDocumentLink($oDocument, $bDisplayFullPath);
        }
    }

    function folderDisplay($oFolder) {
        return displayFolderLink($oFolder);
    }

    function getFolderSortField() {
        return 'name';
    }
}

class IDCriterion extends BrowseCriterion {
    var $bFolderCriterion = true;
    function documentDisplay ($oDocument) {
        return $oDocument->getID();
    }
    function folderDisplay($oFolder) {
        return $oFolder->getID();
    }
}

class TitleCriterion extends BrowseCriterion {
    var $bFolderCriterion = true;
    var $bString = true;
    var $sSearchTable = "DM";
    function documentDisplay ($oDocument) {
        return $oDocument->getName();
    }
    function folderDisplay($oFolder) {
        return $oFolder->getDescription();
    }

    function getFolderSortField() {
        return 'description';
    }
}

class CreatorCriterion extends BrowseCriterion {
    var $sSearchTable = "D";
    var $bFolderCriterion = true;
    var $aLookup = array(
        "table" => "users",
        "field" => "name",
    );
    function documentDisplay ($oDocument) {
        $oCreator = User::get($oDocument->getCreatorID());
        if ($oCreator) {
            return $oCreator->getName();
        }
        return "&nbsp;";
    }
    function folderDisplay($oFolder) {
        return $this->documentDisplay($oFolder);
    }
    function searchWidget ($aRequest, $aPreValue = null) {
        $preval = null;
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@!(*&!@#
            $k = array_keys($aPreValue);
            $k = $k[0];
            $preval = $aPreValue[$k];
        }
        $sRet = "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
        $aUsers = User::getList();/*ok*/
        $sRet .= "<option value=\"-1\">None</option>\n";
        foreach ($aUsers as $oUser) {
            $sSelStr = '';
            if ($preval == $oUser->getId()) { $sSelStr = ' selected="true"'; }
            $sRet .= "<option value=\"" . $oUser->getID() . "\"" . $sSelStr . ">" . $oUser->getName() . "</option>\n";
        }
        $sRet .= "</select>\n";
        return $sRet;
    }
}

class DateCreatedCriterion extends BrowseCriterion {
    var $sSearchTable = "D";
    function documentDisplay ($oDocument) {
        return $oDocument->getCreatedDateTime();
    }
    function getName() {
        return "created";
    }
    function searchWidget ($aRequest, $aPreValue = null) {
        global $default;
        
        // IMPORTANT:  this requires the presence of kt3-calendar.js
        
        $sStartWidget = $this->getWidgetBase() . "_start";
        $sEndWidget = $this->getWidgetBase() . "_end";
        /* // legacy code.
        $sToRender = "After date: <input type=\"text\" size=\"10\" name=\"" . $sStartWidget . "\" />";
        $sToRender .= "&nbsp;&nbsp;<a href=\"javascript:show_calendar('MainForm." . $sStartWidget . "',null,null,'YYYY-MM-DD', false);\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img src=\"$default->graphicsUrl/calendar/calendar.gif\" name=\"imgCalendar\" width=\"34\" height=\"21\" border=\"0\" alt=\"\"></a>";
        $sToRender .= " &nbsp; Before date: <input type=\"text\" size=\"10\" name=\"" . $sEndWidget . "\" />";
        $sToRender .= "&nbsp;&nbsp;<a href=\"javascript:show_calendar('MainForm." . $sEndWidget . "',null,null,'YYYY-MM-DD', false);\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img src=\"$default->graphicsUrl/calendar/calendar.gif\" name=\"imgCalendar\" width=\"34\" height=\"21\" border=\"0\" alt=\"\"></a>";
        */
        $sToRender = 'After Date: <span class="kt_calendar_holder"><strong class="kt_calendar_datetext">' . $aPreValue[$sStartWidget] . '</strong><input type="hidden" name="' . $sStartWidget . '" class="kt_calendar_value" value="' . $aPreValue[$sStartWidget] . '"/> <input type="button" onclick="init_kt_calendar(this);" value="select"></span> &mdash; ';
        $sToRender .= 'Before Date: <span class="kt_calendar_holder"><strong class="kt_calendar_datetext">' . $aPreValue[$sStartWidget] . '</strong><input type="hidden" name="' . $sEndWidget . '" class="kt_calendar_value" value="' . $aPreValue[$sEndWidget] . '"/> <input type="button" onclick="init_kt_calendar(this);" value="select"></span><br />';
        return $sToRender;
    }
    function searchSQL ($aRequest) {
        $sStartWidget = $this->getWidgetBase() . "_start";
        $sEndWidget = $this->getWidgetBase() . "_end";
        // XXX: DateTimeFixup: Should be more intelligent with handling
        // end date - should be end of day on that day.
        if (!array_key_exists($this->getWidgetBase() . "_start", $aRequest)) {
            $sStart = null;
        } else {
            $sStart = $aRequest[$this->getWidgetBase() . "_start"];
        }
        if (!array_key_exists($this->getWidgetBase() . "_end", $aRequest)) {
            $sEnd = null;
        } else {
            $sEnd = $aRequest[$this->getWidgetBase() . "_end"];
        }
        if ($sStart && $sEnd) {
            return array($this->getSearchTable() . "." . $this->getSearchField() . " BETWEEN ? AND ?", array($sStart, $sEnd));
        }
        if ($sStart) {
            return array($this->getSearchTable() . "." . $this->getSearchField() . " > ?", array($sStart));
        }
        if ($sEnd) {
            return array($this->getSearchTable() . "." . $this->getSearchField() . " < ?", array($sEnd));
        }
        return null;
    }

}

class DocumentTypeCriterion extends BrowseCriterion {
    var $sSearchTable = "DM";
    var $aLookup = array(
        "table" => "document_types_lookup",
        "field" => "name"
    );

    function documentDisplay ($oDocument) {
        $oDocumentType = DocumentType::get($oDocument->getDocumentTypeID());
        if ($oDocumentType) {
            return $oDocumentType->getName();
        }
        return "&nbsp;";
    }
    function searchWidget ($aRequest, $aPreValue = null) {
        $preval = null;
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@!(*&!@#
            $k = array_keys($aPreValue);
            $k = $k[0];
            $preval = $aPreValue[$k];
        }
        $sRet = "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
        $aUsers = DocumentType::getList();/*ok*/
        $sRet .= "<option value=\"-1\">None</option>\n";
        foreach ($aUsers as $oUser) {
            $sSelStr = '';
            if ($preval == $oUser->getId()) { $sSelStr = ' selected="true"'; }
            $sRet .= "<option value=\"" . $oUser->getID() . "\"" . $sSelStr . ">" . $oUser->getName() . "</option>\n";
        }
        $sRet .= "</select>\n";
        return $sRet;
    }
}

class GenericMetadataCriterion extends BrowseCriterion {
    var $aLookup = array(
        "table" => "document_fields_link",
        "field" => "value",
        "joinColumn" => "document_id",
    );
    var $bString = true;

    function GenericMetadataCriterion ($sDisplay, $sDocumentField, $sSortField, $iID) {
        $this->iID = $iID;
        $this->BrowseCriterion($sDisplay, $sDocumentField, $sSortField, $iID);
        $this->aLookup['whereClause'] = 'document_field_id = ' .  $iID;
        $this->oField =& DocumentField::get($iID);
        $this->sSearchTable = "DFL" . $iID;
    }

    function documentDisplay ($oDocument) {
        global $default;
        $sQuery = "SELECT DFL.value as value " .
              "FROM $default->document_fields_link_table AS DFL " .
              "WHERE DFL.metadata_version_id = ? " .
              "AND DFL.document_field_id = ?";
        $aParams = array($oDocument->getMetadataVersionId(), $this->getID());

        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'value');
        if (PEAR::isError($res)) {
            // WARN: Add log warning
            return "&nbsp;";
        }
        return $res;
    }

    function getName() {
        global $default;
        $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($this->getID())); /*ok*/
        return "gmd_" . DBUtil::getOneResultKey($aQuery, 'name');
    }

    function getSearchField () {
        return $this->aLookup['field'];
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        $preval = null;
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@!(*&!@#
            $k = array_keys($aPreValue);
            $k = $k[0];
            $preval = $aPreValue[$k];
        }
        // If there's no lookup, just use the standard text input
        if ($this->oField->getHasLookup() == false) {
            return parent::searchWidget($aRequest, $aPreValue);
        }

        $sRet = "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
        $aSearch = array('document_field_id = ?', $this->getID());
        $aMetaData = MetaData::getByDocumentField(DocumentField::get($this->getID()));/*ok*/
        $sRet .= "<option value=\"-1\">None</option>\n";
        foreach ($aMetaData as $oMetaData) {
            $sSelStr = '';
            if ($preval == $oMetaData->getName()) { $sSelStr = ' selected="true"'; }
            $sRet .= "<option value=\"" . $oMetaData->getName() . "\" " . $sSelStr . ">" . $oMetaData->getName() . "</option>\n";
        }
        $sRet .= "</select>\n";
        return $sRet;
    }

    function searchSQL ($aRequest) {
        $p = parent::searchSQL($aRequest);
        $p[0] = join(' AND ', array($p[0], "$this->sSearchTable.document_field_id = ?"));
        $p[1] = array_merge($p[1], array($this->iID));
        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "LEFT JOIN $default->document_fields_link_table AS $this->sSearchTable ON $this->sSearchTable.metadata_version_id = D.metadata_version_id";
    }
}

class DateModifiedCriterion extends DateCreatedCriterion {
    function documentDisplay ($oDocument) {
        return $oDocument->getLastModifiedDate();
    }
    function getName() {
        return "datemodified";
    }
}

class SizeCriterion extends BrowseCriterion {
    var $sSearchTable = "DC";
    var $bVisible = false;
    
    function documentDisplay ($oDocument) {
        return $oDocument->getFileSize();
    }
    function getName() {
        return "size";
    }
    function searchDisplay($aRequest) {
        return "";
    }
}

class ContentCriterion extends BrowseCriterion {
    function documentDisplay ($oDocument) {
        return "Content";
    }
    function getName() {
        return "content";
    }

    function searchSQL ($aRequest) {
        // XXX: Icky MySQL-specific stuff.
        $sVersion = DBUtil::getOneResultKey("SHOW VARIABLES LIKE 'version'", "Value");
        if ((int)substr($sVersion, 0, 1) >= 4) {
            $boolean_mode = "IN BOOLEAN MODE";
        } else {
            $boolean_mode = "";
        }

        $p = array();
        $p[0] = "MATCH(DT.document_text) AGAINST (? $boolean_mode)";
        $p[1] = $aRequest[$this->getWidgetBase()];
        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "INNER JOIN $default->document_text_table AS DT ON D.id = DT.document_id";
    }
}

class WorkflowStateCriterion extends BrowseCriterion {
    var $sSearchTable = "DM";
    function documentDisplay ($oDocument) {
        $oState =& KTWorkflowState::getByDocument($oDocument);
        if ($oState) {
            $oWorkflow = KTWorkflow::get($oState->getWorkflowId());
            return $oWorkflow->getName() . " - " . $oState->getName();
        }
        return "Not in workflow";
    }

    function getName() {
        return "state";
    }

    function searchSQL ($aRequest) {
        $p = array();
        $p[0] = "WD.state_id = ?";
        $p[1] = $aRequest[$this->getWidgetBase()];
        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "INNER JOIN $default->workflow_documents_table AS WD ON D.id = WD.document_id";
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        $preval = null;
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@!(*&!@#
            $k = array_keys($aPreValue);
            $k = $k[0];
            $preval = $aPreValue[$k];
        }
        
        $sRet = "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
        $aStates = KTWorkflowState::getList("ORDER BY workflow_id");
        $sRet .= "<option value=\"-1\">None</option>\n";
        foreach ($aStates as $oState) {
            $oWorkflow =& KTWorkflow::get($oState->getWorkflowId());
            $sSelStr = '';
            if ($preval == $oState->getId()) { $sSelStr = ' selected="true"'; }            
            $sRet .= "<option value=\"" . $oState->getId() . "\"" . $sSelStr . ">" . $oWorkflow->getName() . " - " . $oState->getName() . "</option>\n";
        }
        $sRet .= "</select>\n";
        return $sRet;
    }
}

class TransactionTextCriterion extends BrowseCriterion {
    function documentDisplay ($oDocument) {
        return "Transaction text";
    }
    function getName() {
        return "transactiontext";
    }

    function searchSQL ($aRequest) {
        // XXX: Icky MySQL-specific stuff.
        $sVersion = DBUtil::getOneResultKey("SHOW VARIABLES LIKE 'version'", "Value");
        if ((int)substr($sVersion, 0, 1) >= 4) {
            $boolean_mode = "IN BOOLEAN MODE";
        } else {
            $boolean_mode = "";
        }

        $p = array();
        $p[0] = "MATCH(DTT.document_text) AGAINST (? $boolean_mode)";
        $p[1] = $aRequest[$this->getWidgetBase()];
        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "INNER JOIN $default->document_transaction_text_table AS DTT ON D.id = DTT.document_id";
    }
}

class SearchableTextCriterion extends BrowseCriterion {
    function documentDisplay ($oDocument) {
        return "Searchable text";
    }
    function getName() {
        return "searchabletext";
    }

    function searchSQL ($aRequest) {
        // XXX: Icky MySQL-specific stuff.
        $sVersion = DBUtil::getOneResultKey("SHOW VARIABLES LIKE 'version'", "Value");
        if ((int)substr($sVersion, 0, 1) >= 4) {
            $boolean_mode = "IN BOOLEAN MODE";
        } else {
            $boolean_mode = "";
        }

        $p = array();
        $p[0] = "MATCH(DST.document_text) AGAINST (? $boolean_mode)";
        $p[1] = $aRequest[$this->getWidgetBase()];
        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "INNER JOIN $default->document_searchable_text_table AS DST ON D.id = DST.document_id";
    }
}

class Criteria {
    function &_getBaseCriterionByNumber($iID) {
        global $default;
        switch ($iID) {
            case -1:
                $oCriterion =& new NameCriterion(_kt("Name"), 'filename', 'filename', -1);
                break;
            case -2:
                $oCriterion =& new TitleCriterion(_kt("Title"), 'name', 'name', -2);
                break;
            case -3:
                $oCriterion =& new CreatorCriterion(_kt("Creator"), 'creator_id', 'creator_id', -3);
                break;
            case -4:
                $oCriterion =& new DateCreatedCriterion(_kt("Date Created"), 'created', 'created', -4);
                break;
            case -5:
                $oCriterion =& new DocumentTypeCriterion(_kt("Document Type"), 'document_type_id', 'document_type_id', -5);
                break;
            case -6:
                $oCriterion =& new IDCriterion(_kt("ID"), 'id', 'id', -6);
                break;
            case -7:
                $oCriterion =& new DateModifiedCriterion(_kt("Date Modified"), 'modified', 'modified', -7);
                break;
            case -8:
                $oCriterion =& new SizeCriterion(_kt("File Size"), 'size', 'size', -8);
                break;
            case -9:
                $oCriterion =& new ContentCriterion(_kt("Document Contents"), 'content', 'content', -9);
                break;
            case -10:
                $oCriterion =& new WorkflowStateCriterion(_kt("Workflow state"), 'state', 'state', -10);
                break;
            case -11:
                $oCriterion =& new TransactionTextCriterion(_kt("Transaction Text"), 'transactiontext', 'transactiontext', -11);
                break;
            case -12:
                $oCriterion =& new SearchableTextCriterion(_kt("Searchable Text"), 'searchabletext', 'searchabletext', -12);
                break;
        }
        return $oCriterion;
    }

    function initCriteria () {
        global $aBaseCriteria;
        if (isset($aBaseCriteria)) {
            return;
        }
        $aBaseCriteria = array();
        $aIDs = range(-1, -12); 
        foreach ($aIDs as $iID) {
            $oCriterion =& Criteria::_getBaseCriterionByNumber($iID);
            $aBaseCriteria[$iID] =& $oCriterion;
        }
    }

    function &getBaseCriteria () {
        global $aBaseCriteria;
        Criteria::initCriteria();
        return $aBaseCriteria;
    }

    function &getGenericCriteria () {
        global $aGenericCriteria;
        global $default;
        $aQuery = array("SELECT id FROM $default->document_fields_table", array()); /*ok*/
        $aIDs = DBUtil::getResultArrayKey($aQuery, 'id');
        foreach ($aIDs as $iID) {
            $oCriterion =& Criteria::getCriterionByNumber($iID);
            $aGenericCriteria[$iID] =& $oCriterion;
        }
        return $aGenericCriteria;
    }

    function &getAllCriteria () {
        global $aAllCriteria;
        global $aBaseCriteria;
        global $default;
        Criteria::initCriteria();
        if (isset($aAllCriteria)) {
            return $aAllCriteria;
        }
        $aQuery = array("SELECT id FROM $default->document_fields_table", array()); /*ok*/
        $aIDs = DBUtil::getResultArrayKey($aQuery, 'id');
        $aAllCriteriaIDs = array_merge(array_keys($aBaseCriteria), $aIDs);
        foreach ($aAllCriteriaIDs as $iID) {
            $oCriterion =& Criteria::getCriterionByNumber($iID);
            $aAllCriteria[$iID] =& $oCriterion;
        }
        return $aAllCriteria;
    }

    function &getCriterionByNumber($iID) {
        global $aBaseCriteria;
        global $default;
        Criteria::initCriteria();

        // array_key_exists 4.3.2 (at least, tested on RHEL3) returns
        // false if '-1' (the string) is the key given, and the real key
        // is -1 (the integer).
        $iID = (int)$iID;

        if (array_key_exists($iID, $aBaseCriteria)) {
            return $aBaseCriteria[$iID];
        } else {
            $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($iID)); /*ok*/
            $sName = DBUtil::getOneResultKey($aQuery, 'name');
            return new GenericMetadataCriterion($sName, 'id', 'id', $iID);
        }
    }
}

?>
