<?php
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.browse
 */

class BrowseCriterion {
    var $sDisplay;
    var $sDocumentField;
    var $sSortField;
    var $aLookup = null;
    var $oBrowser;
    var $bFolderCriterion = false;

    function BrowseCriterion ($sDisplay, $sDocumentField, $sSortField, &$oBrowser) {
        $this->sDisplay =& $sDisplay;
        $this->sDocumentField =& $sDocumentField;
        $this->sSortField =& $sSortField;
        $this->oBrowser =& $oBrowser;
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

    function folderQuery ($iParentID) {
        global $default;
        $sFolderQuery = "SELECT f.id FROM $default->folders_table AS f ";/*ok*/
        if (!$this->bFolderCriterion) {
            $sFolderQuery .= "WHERE parent_id = ? ORDER BY f.name asc";
            $aParams = array($iParentID);
            return array($sFolderQuery, $aParams);
        }

        if (!is_null($this->aLookup)) {
            $sFolderQuery .= "INNER JOIN " . $this->aLookup["table"] . " lt ON f.$this->sDocumentField = lt.id WHERE parent_id = ?";
            $sFolderQuery .= " ORDER BY lt." . $this->aLookup["field"] . " " . $this->oBrowser->sSortDirection;
            $aParams = array($iParentID);
            return array($sFolderQuery, $aParams);
        }

        $sFolderQuery .= "WHERE parent_id = ? ORDER BY " . $this->getFolderSortField() . " " . $this->oBrowser->sSortDirection;
        $aParams = array($iParentID);
        return array($sFolderQuery, $aParams);
    }

    function documentQuery ($iFolderID) {
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

            $documentQuery .= "ORDER BY lt." . $this->aLookup["field"] . " " . $this->oBrowser->sSortDirection;
        } else {
            $sDocumentJoinField = $this->getDocumentField();
            // $sSortField = $this->getSortField();
            $documentQuery .= "ORDER BY " . $this->getSortField() . " " . $this->oBrowser->sSortDirection;
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
}

class NameCriterion extends BrowseCriterion {
    var $bFolderCriterion = true;
    function documentDisplay ($oDocument) {
        $aOptions =& $this->oBrowser->getOptions();
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
}

class GenericMetadataCriterion extends BrowseCriterion {
    var $aLookup = array(
        "table" => "document_fields_link",
        "field" => "value",
        "joinColumn" => "document_id",
    );
    var $iFieldID;

    function GenericMetadataCriterion ($sDisplay, $sDocumentField, $sSortField, &$oBrowser, $iFieldID) {
        $this->iFieldID = $iFieldID;
        $this->BrowseCriterion($sDisplay, $sDocumentField, $sSortField, $oBrowser);
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

function &getCriterionByNumber(&$aSortCriteria, &$oBrowser, $iID) {
    global $default;
    switch ($iID) {
        case -1:
            $oCriterion =& new NameCriterion(_("Name"), 'filename', 'filename', $oBrowser);
            break;
        case -2:
            $oCriterion =& new TitleCriterion(_("Title"), 'name', 'name', $oBrowser);
            break;
        case -3:
            $oCriterion =& new CreatorCriterion(_("Creator"), 'creator_id', 'creator_id', $oBrowser);
            break;
        case -4:
            $oCriterion =& new DateCreatedCriterion(_("Date Created"), 'id', 'id', $oBrowser);
            break;
        case -5:
            $oCriterion =& new DocumentTypeCriterion(_("Document Type"), 'document_type_id', 'document_type_id', $oBrowser);
            break;
        case -6:
            $oCriterion =& new IDCriterion(_("ID"), 'id', 'id', $oBrowser);
            break;
        default:
            $aQuery = array("SELECT name FROM $default->document_fields_table WHERE id = ?", array($iID)); /*ok*/
            $sName = DBUtil::getOneResultKey($aQuery, 'name');
            $oCriterion =& new GenericMetadataCriterion($sName, 'id', 'id', $oBrowser, $iID);
            break;

    }
    return $oCriterion;
}
 
class Browser {

    /**
     * The point we're browsing from
     */
    var $sBrowseStart;
    /**
     * The criteria to sort the browse results by- maps the database field to display name and lookup table
     */
    var $aSortCriteria;
    
    /**
     * The field to sort the browse results by
     */
    var $sSortField;
    
    /**
     * The direction to sort the results in
     */
    var $sSortDirection;

    var $aOptions;
    
    /**
     * Constructs a new Browser instance
     *
     * @param string the field to sort by
     * @param string the direction to sort in
     * @param array the sort criteria
     */
    function Browser($sNewSortField, $sNewSortDirection, $aNewSortCriteria = array()) {
        // default sort criteria
        if (count($aNewSortCriteria) == 0) {
            $aNewSortCriteria = array();
            $aQuery = array("SELECT criteria_id FROM browse_criteria ORDER BY precedence", array()); /*ok*/
            $aSelectedCriteriaIDs = DBUtil::getResultArrayKey($aQuery, 'criteria_id');
            foreach ($aSelectedCriteriaIDs as $iID) {
                $oCriterion =& getCriterionByNumber($aNewSortCriteria, $this, $iID);
                $aNewSortCriteria[$oCriterion->getName()] =& $oCriterion;
            }
    	}
							 	 										   		      	
    	$this->aSortCriteria =& $aNewSortCriteria;
    	$this->sSortField = $sNewSortField;
    	$this->sSortDirection = $sNewSortDirection;
    }
    
	/**
	 * Returns the browse start point
	 */					
	function getBrowseStart() {
		return $this->$sBrowseStart;
	}

	/**
	 * Set the browse start point
	 */
	function setBrowseStart($aNewBrowseStart) {
		$this->$sBrowseStart = $aNewBrowseStart;
	}
      
	/**
	 * Returns the document sort criteria
	 */					
	function getSortCriteria() {
		return $this->aSortCriteria;
	}

	/**
	 * Set the document sort criteria
	 */
	function setSortCriteria($aNewSortCriteria) {
		$this->aSortCriteria = $aNewSortCriteria;
	}
	    
    /**
     * Return the current sort field
     */
    function getSortField() {
    	return $this->sSortField;
    }
     
    /**
     * Set the current sort field
     */
    function setSortField($sNewSortField) {
    	$this->sSortField = $sNewSortField;
    }
          
    /**
     * Return the current sort direction
     */
    function getSortDirection() {
    	return $this->sSortDirection;
    }      	 						
     
    /**
     * Set the current sort direction
     */     				   		 
	function setSortDirection($sNewSortDirection) {
		$this->sSortDirection = $sNewSortDirection;
	}
	 

    function setOptions($aOptions) {
        $this->aOptions = array_merge($this->aOptions, $aOptions);
    }

    function getOptions() {
        return $this->aOptions;
    }

    function columns() {
        return count($this->aSortCriteria);
    }

    /**
     * [Abstract] Browse the documents
     *
     * @return array documentCollections and documents
     */
    function browse() {
    }


}
