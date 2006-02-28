<?php
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.browse
 */

require_once(KT_LIB_DIR . '/security/Permission.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once("Criteria.inc");

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

    var $aOptions = array();
    
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
                $oCriterion =& Criteria::getCriterionByNumber($iID);
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
        foreach (array_keys($this->aSortCriteria) as $sKey) {
            $oCriteria =& $this->aSortCriteria[$sKey];
            $oCriteria->setOptions($aOptions);
        }
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
?>
