<?php
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
/**
 * $Id$
 *
 * Contains document browsing business logic.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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

    function searchWidget ($aRequest) {
        return "<input type=\"text\" size=\"50\" name=\"bmd" . $this->getID() . "\" />";
    }
}

class NameCriterion extends BrowseCriterion {
    var $bFolderCriterion = true;
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
    function searchWidget ($aRequest) {
        $sRet = "<select size=\"1\" name=\"bmd" . $this->getID() . "\">\n";
        $aUsers = User::getList();
        $sRet .= "<option value=\"-1\">None</option>\n";
        foreach ($aUsers as $oUser) {
            $sRet .= "<option value=\"" . $oUser->getID() . "\">" . $oUser->getName() . "</option>\n";
        }
        $sRet .= "</select>\n";
        return $sRet;
    }
}

class DateCreatedCriterion extends BrowseCriterion {
    var $aLookup = array(
        "table" => "document_transactions",
        "field" => "datetime",
        "joinColumn" => "document_id",
        "whereClause" => "transaction_id=1",
    );

    function documentDisplay ($oDocument) {
        $aDocumentTransaction = DocumentTransaction::getList("transaction_id=1 AND document_id=" . $oDocument->getID());
        return $aDocumentTransaction[0]->dDateTime;
    }
    function getName() {
        return "datecreated";
    }
}

class DocumentTypeCriterion extends BrowseCriterion {
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
    function searchWidget ($aRequest) {
        $sRet = "<select size=\"1\" name=\"bmd" . $this->getID() . "\">\n";
        $aUsers = DocumentType::getList();
        $sRet .= "<option value=\"-1\">None</option>\n";
        foreach ($aUsers as $oUser) {
            $sRet .= "<option value=\"" . $oUser->getID() . "\">" . $oUser->getName() . "</option>\n";
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
    var $iFieldID;

    function GenericMetadataCriterion ($sDisplay, $sDocumentField, $sSortField, $iFieldID) {
        $this->iFieldID = $iFieldID;
        $this->BrowseCriterion($sDisplay, $sDocumentField, $sSortField, $iFieldID);
        $this->aLookup['whereClause'] = 'document_field_id = ' .  $iFieldID;
    }

    function documentDisplay ($oDocument) {
        global $default;
        $sQuery = "SELECT DFL.value as value " .
              "FROM $default->document_fields_link_table AS DFL " .
              "WHERE DFL.document_id = ? " .
              "AND DFL.document_field_id = ?";
        $aParams = array($oDocument->getID(), $this->iFieldID);

        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'value');
        if (PEAR::isError($res)) {
            // WARN: Add log warning
            return "&nbsp;";
        }
        return $res;
    }

    function getName() {
        global $default;
        $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($this->iFieldID)); /*ok*/
        return "gmd_" . DBUtil::getOneResultKey($aQuery, 'name');
    }
}

class Criteria {
    function &_getBaseCriterionByNumber($iID) {
        global $default;
        switch ($iID) {
            case -1:
                $oCriterion =& new NameCriterion(_("Name"), 'filename', 'filename', -1);
                break;
            case -2:
                $oCriterion =& new TitleCriterion(_("Title"), 'name', 'name', -2);
                break;
            case -3:
                $oCriterion =& new CreatorCriterion(_("Creator"), 'creator_id', 'creator_id', -3);
                break;
            case -4:
                $oCriterion =& new DateCreatedCriterion(_("Date Created"), 'id', 'id', -4);
                break;
            case -5:
                $oCriterion =& new DocumentTypeCriterion(_("Document Type"), 'document_type_id', 'document_type_id', -5);
                break;
            case -6:
                $oCriterion =& new IDCriterion(_("ID"), 'id', 'id', -6);
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
        $aIDs = range(-1, -6); 
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
