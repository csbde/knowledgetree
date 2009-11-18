<?php
/**
 * $Id$
 *
 * Contains document browsing business logic.
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

require_once(KT_LIB_DIR . '/security/Permission.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/documentmanagement/MetaData.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');

require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');

require_once(KT_LIB_DIR . '/browse/criteriaregistry.php');

require_once(KT_LIB_DIR . "/util/sanitize.inc");


class BrowseCriterion {
    var $sDisplay;
    var $aLookup = null;
    var $bFolderCriterion = false;
    var $aOptions = array();
    var $iID;
    var $bString = false;
    var $sSearchTable = "D";
    var $bVisible = true;
    var $bContains = false;
    var $bHandleNot = true;

    var $sDocumentField = null;
    var $sSortField = null;
    var $sNamespace = null;

    function BrowseCriterion() {
        $this->sDisplay = '';
    }


    function headerDisplay () {
        return $this->sDisplay;
    }


    // for final display
    function documentDisplay ($oDocument) {
        return $this->sDisplay;
    }

    function folderDisplay ($oDocument) {
        return "&nbsp;";
    }

    // for parameter display
    function baseParameterDisplay() {
	$sDisp = sprintf("<b>%s</b>: ", $this->sDisplay);
	$bNot = KTUtil::arrayGet($aData, $this->getWidgetBase().'_not', null);
	if($bNot !== null) {
	    if((bool)$bNot) { $sDisp .= _kt('NOT'); }
	}
	return $sDisp;
    }

    function parameterDisplay($aData) {
	return sprintf("%s %s", $this->baseParameterDisplay(), htmlentities($aData[$this->getWidgetBase()],ENT_QUOTES, 'UTF-8'));
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
        return sanitizeForSQLtoHTML($this->sDocumentField);
    }

    function getID() {
        return $this->iID;
    }

    function getNameSpace() {
	return $this->sNamespace;
    }

    function setOptions($aOptions) {
        $this->aOptions = $aOptions;
    }

    function searchDisplay($aRequest) {
        return "<tr><td>" . $this->headerDisplay() . ": </td><td>" . $this->searchWidget($aRequest) . "</td></tr>\n";
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@NOT (*&!@#
            $k = array_keys($aPreValue);
            $k = $this->getWidgetBase();

	    if(array_key_exists($k, $aPreValue)) {
		$preval = $aPreValue[$k];
	    }
            return $this->getNotWidget($aPreValue) . "<input type=\"text\" size=\"50\" name=\"" . $this->getWidgetBase() . "\" value=\"" . $preval . "\"/>";
        } else {
            return $this->getNotWidget($aPreValue) . "<input type=\"text\" size=\"50\" name=\"" . $this->getWidgetBase() . "\" />";
        }
    }

    function getNotWidget($aPreValue=null) {
        if (!$this->bHandleNot) { return ''; }

        // not perfect, but acceptable.
        $form_name = $this->getWidgetBase() . '_not';
        $pos_select = '';
        $neg_select = '';
        if (is_null($aPreValue)) {
            $is_positive = true;
        } else {
	    if(array_key_exists($form_name, $aPreValue)) {
		$preval = KTUtil::arrayGet($aPreValue, $form_name, "0"); // by default, use "is" not "is not"
	    }
	    $is_positive = ($preval == "0"); // 0 or empty or similar.
        }
        if ($is_positive) {
            $pos_select = ' selected="true"';
        } else {
            $neg_select = ' selected="true"';
        }
        if (!$this->bContains) {
            $not_string = _kt('is not');
            $is_string = _kt('is');
        } else {
            $not_string = _kt('does not contain');
            $is_string = _kt('contains');
        }
        $widget = sprintf('<select name="%s"><option value="0"%s>%s</option><option value="1"%s>%s</option></select>&nbsp;', $form_name, $pos_select, $is_string, $neg_select, $not_string);
        return $widget;
    }

    function getWidgetBase () {
        //return strtr($this->getNamespace(), '-', '_');
        return $this->getNamespace();
    }

    function getSearchTable() {
        return $this->sSearchTable;
    }

    function searchSQL ($aRequest, $handle_not = true) {
        $val = null;
        if ($this->bString) {
            $val = array($this->getSearchTable() . "." .  $this->getSearchField() . " LIKE '%!%'", array(DBUtil::escapeSimple($aRequest[$this->getWidgetBase()])));
        } else {
            $val = array($this->getSearchTable() . "." . $this->getSearchField() . " = ?", array($aRequest[$this->getWidgetBase()]));
        }

        // handle the boolean "not" stuff UNLESS our caller is doing so already.
        if ($handle_not) {
            $want_invert = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');

            if (is_null($want_invert) || ($want_invert == "0")) { // use explicit "0" check
                return $val;
            } else {
                $val[0] = '(NOT (' . $val[0] . '))';
            }
        }

        return $val;
    }

    function searchJoinSQL () {
        return null;
    }
}


class NameCriterion extends BrowseCriterion {
    var $bFolderCriterion = true;
    var $bString = true;
    var $sSearchTable = "DC";
    var $bContains = true;

    var $sDocumentField = 'filename';
    var $sSortField = 'filename';
    var $sNamespace = 'ktcore.criteria.name';

    function NameCriterion() {
        $this->sDisplay = _kt('Document Filename');
    }

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
    var $sDocumentField = 'id';
    var $sSortField = 'id';
    var $sNamespace = 'ktcore.criteria.id';

    function IDCriterion() {
        $this->sDisplay = _kt('Document ID');
    }
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
    var $bContains = true;
    var $sDocumentField = 'name';
    var $sSortField = 'name';
    var $sNamespace = 'ktcore.criteria.title';

    function TitleCriterion() {
        $this->sDisplay = _kt('Document Title');
    }

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
    var $sDocumentField = 'creator_id';
    var $sSortField = 'creator_id';
    var $sNamespace = 'ktcore.criteria.creator';

    function CreatorCriterion() {
        $this->sDisplay = _kt('Creator');
    }

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

    function parameterDisplay($aData) {
	$sBase = $this->baseParameterDisplay();
	$oUser =& User::get($aData[$this->getWidgetBase()]);
	if(PEAR::isError($oUser)) {
	    return $sBase . 'unknown user';
	}
	return $sBase . $oUser->getName();
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        $preval = null;
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@NOT (*&!@#
            $k = array_keys($aPreValue);
            $k = $this->getWidgetBase();
            $preval = $aPreValue[$k];
        }
        $sRet = $this->getNotWidget($aPreValue) . "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
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
    var $sDocumentField = 'created';
    var $sSortField = 'created';
    var $sNamespace = 'ktcore.criteria.datecreated';

    function DateCreatedCriterion() {
        $this->sDisplay = _kt('Date Created');
    }

    function documentDisplay ($oDocument) {
        return $oDocument->getCreatedDateTime();
    }

    function parameterDisplay($aData) {
	$sDisp = $this->baseParameterDisplay();
	$sStart = KTUtil::arrayGet($aData, $this->getWidgetBase() . '_start', false);
	$sEnd = KTUtil::arrayGet($aData, $this->getWidgetBase() . '_end', false);

	if($sStart) {
	    $sDisp .= _kt('after ') .$sStart;
	}
	if($sStart && $sEnd) {
	    $sDisp .= _kt(' and ');
	}
	if($sEnd) {
	    $sDisp .= _kt('before ') .$sEnd;
	}
	return $sDisp;
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
        $sToRender = $this->getNotWidget($aPreValue);
        $sToRender .= _kt('after') . ': <span class="kt_calendar_holder"><strong class="kt_calendar_datetext">' . $aPreValue[$sStartWidget] . '</strong><input type="hidden" name="' . $sStartWidget . '" class="kt_calendar_value" value="' . $aPreValue[$sStartWidget] . '"/> <input type="button" onclick="init_kt_calendar(this);" value="select"></span> and/or ';
        $sToRender .= _kt('before') . ': <span class="kt_calendar_holder"><strong class="kt_calendar_datetext">' . $aPreValue[$sEndWidget] . '</strong><input type="hidden" name="' . $sEndWidget . '" class="kt_calendar_value" value="' . $aPreValue[$sEndWidget] . '"/> <input type="button" onclick="init_kt_calendar(this);" value="select"></span><br />';
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


        $val = null;
        if ($sStart && $sEnd) {
            $val = array($this->getSearchTable() . "." . $this->getSearchField() . " BETWEEN ? AND ?", array($sStart, $sEnd));
        } else if ($sStart) {
            $val = array($this->getSearchTable() . "." . $this->getSearchField() . " > ?", array($sStart));
        } else if ($sEnd) {
            $val = array($this->getSearchTable() . "." . $this->getSearchField() . " < ?", array($sEnd));
        } else {
           return null;
        }

        // handle the boolean "not" stuff.
        $want_invert = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');

        if (is_null($want_invert) || ($want_invert == "0")) {
            return $val;
        } else {
            $val[0] = '(NOT (' . $val[0] . '))';
        }

        // finally
        return $val;
    }

}

class DocumentTypeCriterion extends BrowseCriterion {
    var $sSearchTable = "DM";
    var $aLookup = array(
        "table" => "document_types_lookup",
        "field" => "name"
    );
    var $sDocumentField = 'document_type_id';
    var $sSortField = 'document_type_id';
    var $sNamespace = 'ktcore.criteria.documenttype';

    function DocumentTypeCriterion() {
        $this->sDisplay = _kt('Document Type');
    }

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
            // !#@&)*( (*&!@# *(&@NOT (*&!@#
            $k = array_keys($aPreValue);
            $k = $this->getWidgetBase();
            $preval = $aPreValue[$k];
        }
        $sRet = $this->getNotWidget($aPreValue);
        $sRet .= "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
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
    var $sDocumentField = null;
    var $sSortField = null;
    var $sNamespace = 'ktcore.criteria.generic';

    function initialize($sDisplay, $sDocumentField, $sSortField, $iID, $sNamespace) {
	$this->sDisplay = $sDisplay;
	$this->sDocumentField = $sDocumentField;
	$this->sSortField = $sSortField;

        $this->iID = $iID;
        $this->aLookup['whereClause'] = 'document_field_id = ' .  $iID;
        $this->oField = DocumentField::get($iID);
	$this->sNamespace = $sNamespace;
        $this->sSearchTable = "DFL" . $iID;
    }

    function getID() {
        return $this->iID;
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
            // !#@&)*( (*&!@# *(&@NOT (*&!@#
            $k = array_keys($aPreValue);
            $k = $this->getWidgetBase();
            $preval = $aPreValue[$k];
        }

        // If there's no lookup, just use the standard text input
        if ($this->oField->getHasLookup() == false && $this->oField->getHasInetLookup() == false) {
            $this->bContains = true; // contains
            return parent::searchWidget($aRequest, $aPreValue);
        }
        
        if($this->oField->getHasInetLookup() == true)
        {

            //$this->bContains = true;
            $this->bString = false;
            $sRet = $this->getNotWidget($aPreValue);
            
            $innerJoin = $aPreValue[$this->getWidgetBase()."_join"];
            
            $andSelected = "";
            $orSelected = "";
            if($innerJoin == "AND")
            {
                $andSelected = "selected=\"selected\"";
            }
            elseif($innerJoin == "OR")
            {
                $orSelected = "selected=\"selected\"";
            }
            
            $sRet .= "<select size=\"1\" name=\"".$this->getWidgetBase()."_join\">\n";
            $sRet .= "<option value=\"AND\" ".$andSelected.">all</option>\n";
            $sRet .= "<option value=\"OR\" ".$orSelected.">any</option>\n";
            $sRet .= "</select>\n";
            
            if($this->oField->getInetLookupType() == "multiwithlist")
            {
                $sRet .= "<select size=\"4\" name=\"" . $this->getWidgetBase() . "\" multiple=\"multiple\">\n";
                $aSearch = array('document_field_id = ?', $this->getID());
                $aMetaData = MetaData::getByDocumentField(DocumentField::get($this->getID()));/*ok*/

                foreach ($aMetaData as $oMetaData) {

                    $sSelStr = '';
                    if (in_array($oMetaData->getName(), $preval)) { $sSelStr = ' selected="true"'; }
                    $sRet .= "<option value=\"" . $oMetaData->getName() . "\" " . $sSelStr . ">" . $oMetaData->getName() . "</option>\n";
                }
                $sRet .= "</select>\n"; 
            }
            elseif($this->oField->getInetLookupType() == "multiwithcheckboxes")
            {
                $sRet .= "<div style=\"float:right;\">";
                $aSearch = array('document_field_id = ?', $this->getID());
                $aMetaData = MetaData::getByDocumentField(DocumentField::get($this->getID()));/*ok*/

                foreach ($aMetaData as $oMetaData) {
                    $sSelStr = '';
                    if (in_array($oMetaData->getName(), $preval)) { $sSelStr = ' checked="true"'; }
                    $sRet .= "<input type=\"checkbox\" name=\"" . $this->getWidgetBase() . "\" class=\"multiple\" value=\"" . $oMetaData->getName() . "\"" . $sSelStr . " /> ". $oMetaData->getName()."<br />";
                }
                $sRet .= "</div>";
            }
            return $sRet;
        }
        
        $this->bContains = false; // is
        $sRet = $this->getNotWidget($aPreValue);
        
        $sRet .= "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
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
        $p = parent::searchSQL($aRequest, false); // handle not ourselves.
        $p[0] = join(' AND ', array($p[0], "$this->sSearchTable.document_field_id = ?"));
        $p[1] = kt_array_merge($p[1], array($this->iID));

        // handle the boolean "not" stuff.
        $want_invert = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');
        if (is_null($want_invert) || ($want_invert == "0")) {
            return $p;
        } else {
            $p[0] = '(NOT (' . $p[0] . '))';
        }

        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "LEFT JOIN $default->document_fields_link_table AS $this->sSearchTable ON $this->sSearchTable.metadata_version_id = D.metadata_version_id";
    }
}

class GeneralMetadataCriterion extends BrowseCriterion {
    var $aLookup = array(
        "table" => "document_fields_link",
        "field" => "value",
        "joinColumn" => "document_id",
    );
    var $bString = true;
    var $sDocumentField = 'value';
    var $sSortField = null;
    var $bContains = true;
    var $sNamespace = 'ktcore.criteria.generalmetadata';
    var $sSearchTable = "DFL" ;
 	var $bHandleNot = false;
    function GeneralMetadataCriterion()
    {
    	$this->sDisplay = _kt('General Metadata');
    }

    function documentDisplay ($oDocument) {

        return 'General Metadata';
    }


    function getSearchField () {
        return $this->aLookup['field'];
    }


    function searchSQL ($aRequest) {
        $val = array('('.$this->getSearchTable() . "." .  $this->getSearchField() . " LIKE '%!%' OR DM.name LIKE '%!%'  )",
        array(DBUtil::escapeSimple($aRequest[$this->getWidgetBase()]),DBUtil::escapeSimple($aRequest[$this->getWidgetBase()])));

        return $val;
    }

    function searchJoinSQL () {
        global $default;
        return "LEFT JOIN $default->document_fields_link_table AS $this->sSearchTable ON $this->sSearchTable.metadata_version_id = D.metadata_version_id";
    }
}

class DateModifiedCriterion extends DateCreatedCriterion {
    var $sDocumentField = 'modified';
    var $sSortField = 'modified';
    var $sNamespace = 'ktcore.criteria.datemodified';

    function DateModifiedCriterion() {
	$this->sDisplay = _kt('Date Modified');
    }

    function getName() {
	return 'datemodified';
    }

    function documentDisplay ($oDocument) {
        return $oDocument->getLastModifiedDate();
    }
}

class SizeCriterion extends BrowseCriterion {
    var $sSearchTable = "DC";
    var $sDocumentField = 'size';
    var $sSortField = 'size';
    var $sNamespace = 'ktcore.criteria.size';

    var $aTypes = array('B'=>'Bytes',
			'KB'=>'Kilobytes',
			'M'=>'Megabytes');

    var $aTypeAssocs = array('B' => 1, 'KB' => 1024, 'M' => 1048576);

    var $aCmps = array();

    var $aCmpAssocs = array();

    function SizeCriterion() {
		$this->sDisplay = _kt('File Size');

		$this->aCmps = array('LT'=>_kt('Less than'),
		       'GT'=>_kt('Greater than'),
		       'EQ'=>_kt('Equal to'),
		       'NEQ'=>_kt('Not equal to'));

		$this->aCmpAssocs = array('LT' => '<', 'GT' => '>', 'EQ' => '=', 'NEQ' => '!=');

    }

    function documentDisplay ($oDocument) {
        return $oDocument->getFileSize();
    }

    function searchDisplay($aRequest) {
        return "";
    }

    function parameterDisplay($aData) {
	$sBase = $this->getWidgetBase();
	return sprintf("%s %s %s %s", $this->baseParameterDisplay(), $this->aCmps[$aData[$sBase.'_not']], htmlentities($aData[$sBase.'_num'],ENT_QUOTES,'UTF-8'), $this->aTypes[$aData[$sBase.'_type']]);
    }

    function searchWidget ($aRequest, $aPreValue = null) {
	$sBase = $this->getWidgetBase();
	$sCmpWidget = $sBase . '_not';
        $sNumWidget = $sBase . '_num';
        $sTypeWidget = $sBase . '_type';

	// build gt/lt/eq/neq widget
	$sCmpSelect = '<select name="'.$sCmpWidget.'">';
	$sCmpSelect .= KTUtil::buildSelectOptions($this->aCmps, KTUtil::arrayGet($aPreValue, $sCmpWidget, 'LT'));
	$sCmpSelect .= '</select>';

	// build number
	$sNumInput = sprintf('<input type="text" name="%s" value="%s"/>', $sNumWidget, KTUtil::arrayGet($aPreValue, $sNumWidget, ''));


	// build type selection widget
	$sTypeSelect = '<select name="'.$sTypeWidget.'">';
	$sTypeSelect .= KTUtil::buildSelectOptions($this->aTypes, KTUtil::arrayGet($aPreValue, $sTypeWidget, 'M'));
	$sTypeWidget .= '</select>';

	$sToRender = sprintf("%s %s %s", $sCmpSelect, $sNumInput, $sTypeSelect);
        return $sToRender;
    }

    function searchSQL ($aRequest) {
	$sCmp = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');
	$sNum = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_num');
	$sType = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_type');

	if(!is_numeric($sNum)) {
	    return null;
	}

	$sQry = sprintf("%s.%s %s ?", $this->getSearchTable(), $this->getSearchField(), $this->aCmpAssocs[$sCmp]);
	$val = array($sQry, (int)$sNum * $this->aTypeAssocs[$sType]);

        return $val;
    }

}

class WorkflowStateCriterion extends BrowseCriterion {
    var $sSearchTable = "DM";
    var $sDocumentField = 'state';
    var $sSortField = 'state';
    var $sNamespace = 'ktcore.criteria.workflowstate';

    function WorkflowStateCriterion() {
	$this->sDisplay = _kt('Workflow State');
    }

    function documentDisplay ($oDocument) {
        $oState =& KTWorkflowState::getByDocument($oDocument);
        if ($oState) {
            $oWorkflow = KTWorkflow::get($oState->getWorkflowId());
            return $oWorkflow->getName() . " - " . $oState->getName();
        }
        return "Not in workflow";
    }

    function parameterDisplay($aData) {
	$sId = $aData[$this->getWidgetBase()];

	if($sId == '-1') {
	    $sState = _kt('none');
	} else {
	    $oState =& KTWorkflowState::get((int)$sId);
	    if(!PEAR::isError($oState)) {
		$sState = $oState->getName();
	    } else {
		$sState = _kt('unknown state');
	    }
	}
	return $this->baseParameterDisplay() . $sState;
    }

    function getName() {
        return "state";
    }

    function searchSQL ($aRequest) {
        $p = array();
        $p[0] = "DM.workflow_state_id = ?";
        $p[1] = $aRequest[$this->getWidgetBase()];

        // handle the boolean "not" stuff.
        $want_invert = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');
        if (is_null($want_invert) || ($want_invert == "0")) {
            return $p;
        } else {
            $p[0] = '(NOT (' . $p[0] . '))';
        }
        return $p;
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        $preval = null;
        if ($aPreValue != null) {
            // !#@&)*( (*&!@# *(&@NOT (*&!@#
            $k = array_keys($aPreValue);
            $k = $this->getWidgetBase();
            $preval = $aPreValue[$k];
        }
        $sRet = $this->getNotWidget($aPreValue);
        $sRet .= "<select size=\"1\" name=\"" . $this->getWidgetBase() . "\">\n";
        $aStates = KTWorkflowState::getList();
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

class TagCloudCriterion extends BrowseCriterion {
    var $bString = false;
	var $bContains = false;
    var $bHandleNot = false;
    var $sDocumentField = 'tag';// this is linked to the field
    var $sSortField = 'tag';
    var $sNamespace = 'ktcore.criteria.tagcloud';
    var $sSearchTable = "TWS" ;

    function TagCloudCriterion() {
	$this->sDisplay = _kt('Tag Cloud');
    }

    function documentDisplay ($oDocument) {
        return "Tag Cloud";
    }
    function getName() {
        return "tagcloud";
    }

    function searchSQL ($aRequest) {
         $p = parent::searchSQL($aRequest, false); // handle not ourselves.

        // handle the boolean "not" stuff.
        $want_invert = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');
        if (is_null($want_invert) || ($want_invert == "0")) {
            return $p;
        } else {
            $p[0] = '(NOT (' . $p[0] . '))';
        }

        return $p;
    }

    function searchJoinSQL () {
        global $default;
        return "INNER JOIN document_tags AS DTS ON DTS.document_id = D.id
        		INNER JOIN tag_words AS TWS ON TWS.id = DTS.tag_id";
    }
}

class DateCreatedDeltaCriterion extends DateCreatedCriterion {
    var $sDocumentField = 'created';
    var $sSortField = 'created';
	var $aTypes = array();
    var $sNamespace = 'ktcore.criteria.datecreateddelta';

    function DateCreatedDeltaCriterion() {
		$this->sDisplay = _kt('Date Created Delta');

		$this->aTypes = array('MINUTE'=>_kt('Minutes'),
			'HOUR'=>_kt('Hours'),
			'DAY'=>_kt('Days'),
			'MONTH'=>_kt('Months'),
			'YEAR'=>_kt('Years')
			);
    }

    function parameterDisplay($aData) {
	$sNum = KTUtil::arrayGet($aData, $this->getWidgetBase() . '_num');
	$sType = KTUtil::arrayGet($aData, $this->getWidgetBase() . '_type');
	return sprintf('%s %s %s', $this->baseParameterDisplay(), $sNum, $this->aTypes[$sType]);
    }

    function searchWidget ($aRequest, $aPreValue = null) {
        $sNumWidget = $this->getWidgetBase() . '_num';
        $sTypeWidget = $this->getWidgetBase() . '_type';

	// build type selection widget
	$sSelect = '<select name="'.$sTypeWidget.'">';
	foreach($this->aTypes as $k=>$v) {
	    $sSelect .= '<option value="'.$k.'"';
	    $t = KTUtil::arrayGet($aPreValue, $sTypeWidget, 'DAY');
	    if($k==$t) $sSelect .= ' selected="selected"';
	    $sSelect .= '>'.$v.'</option>';
	}
	$sSelect .= '</select>';

        $sToRender = $this->getNotWidget($aPreValue);

	$sToRender .= '<input name="'.$sNumWidget.'" value="'.$aPreValue[$sNumWidget].'"/>'.$sSelect._kt(' ago');
        return $sToRender;
    }

    function searchSQL ($aRequest) {
	$sNum = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_num');
	$sType = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_type');

	$val = array($this->getSearchTable() . "." . $this->getSearchField() . " > SUBDATE(NOW(), INTERVAL ? {$sType})", array($sNum));

        $want_invert = KTUtil::arrayGet($aRequest, $this->getWidgetBase() . '_not');
        if (is_null($want_invert) || ($want_invert == "0")) {
            return $val;
        } else {
            $val[0] = '(NOT (' . $val[0] . '))';
        }
        return $val;
    }
}


class DateModifiedDeltaCriterion extends DateCreatedDeltaCriterion {
    var $sDocumentField = 'modified';
    var $sSortField = 'modified';
    var $sNamespace = 'ktcore.criteria.datemodifieddelta';

    function DateModifiedDeltaCriterion() {
	$this->sDisplay = _kt('Date Modified Delta');
    }

    function documentDisplay ($oDocument) {
        return $oDocument->getLastModifiedDate();
    }
    function getName() {
        return "datemodified";
    }
}






?>
