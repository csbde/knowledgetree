<?php
/**
 * $Id$
 *
 * Renders query results in a table.
 *
 * The first column in the table can be rendered as a link 
 * to the document/folder using the $iLinkType variable to specify the link type,
 * the $sLinkPageURL to specify the page URL to link to and $sLinkImageURL to specify
 * the image to display in the case of either a $iLinkType of 2 (image only) or 3 (image + text)
 *
 * If you wish to include images, there are two ways to do this
 *		o set the image url - this means that all rows will use the same image
 *		o set $bUseImageURLFromQuery to true - this will look for a column entitled image_url in
 *		  the sql result set, allowing you to specify different images for each entry
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.visualpatterns
 * @todo $iLinkImageURL is hard coded - change
 * @todo $sLinkPageURL is hard coded - change 
 */

require_once('PatternTableGeneric.inc');

class SqlResultSetAdapter {
    // Has there been an error setting up the result set?
    var $broken = false;

    function SqlResultSetAdapter ($oResultSet) {
        global $default;
        $this->oResultSet = $oResultSet;
        if (PEAR::isError($oResultSet)) {
            $this->broken = true;
        }
    }

    function isEmpty () {
        global $default;
        if ($this->broken) {
            return $this->oResultSet;
        }
        return ($this->oResultSet->numRows() == 0);
    }

    function next () {
        global $default;
        if ($this->broken) {
            return $this->oResultSet;
        }
        $aNextResult = $this->oResultSet->fetchRow(DB_FETCHMODE_ASSOC);
        if ($aNextResult === null) {
            return null;
        }
        return new SqlResultAdapter ($aNextResult);
    }
}

class SqlResultAdapter {
    function SqlResultAdapter ($oResult) {
        $this->oResult = $oResult;
    }
    function get ($sField) {
        global $default;
        return $this->oResult[$sField];
    }
}

class PatternTableSqlQuery extends PatternTableGeneric {	
	function PatternTableSqlQuery($sTmpQuery, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaderNames, $sTmpWidth, $aTmpLinkURLs = null, $aTmpDBQueryStringColumns = null, $aNewQueryStringVariableNames = null) {
            $oResult = DBUtil::runQuery($sTmpQuery);
            $oResultSet =& new SqlResultSetAdapter ($oResult); 
            $this->PatternTableGeneric($oResultSet, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaderNames, $sTmpWidth, $aTmpLinkURLs, $aTmpDBQueryStringColumns, $aNewQueryStringVariableNames);
	}
}

?>
