<?php
/**
 * $Id$
 * $BasedOnId: PatternTableSqlQuery.inc,v 1.39 2003/09/09 09:20:36 michael Exp $
 *
 * Renders an iterable result set in a table.
 *
 * The first column in the table can be rendered as a link
 * to the document/folder using the $iLinkType variable to specify the link type,
 * the $sLinkPageURL to specify the page URL to link to and $sLinkImageURL to specify
 * the image to display in the case of either a $iLinkType of 2 (image only) or 3 (image + text)
 *
 * If you wish to include images, there are two ways to do this
 *        o set the image url - this means that all rows will use the same image
 *        o set $bUseImageURLFromQuery to true - this will look for a column entitled image_url in
 *          the sql result set, allowing you to specify different images for each entry
 *
 * Copyright (c) 2004 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.visualpatterns
 * @todo $iLinkImageURL is hard coded - change
 * @todo $sLinkPageURL is hard coded - change
 */
class PatternTableGeneric {
    /** query to execute*/
    var $oResultSet;
    /* Columns in table to query (ID is included by default) */
    var $aColumns;
    /* Column types.  Possibles are 1 = text, 2 = boolean, 3 = hyperlink url */
    var $aColumnTypes;
    /** header names to display */
    var $aColumnHeaderNames;
    /** table width, either in pixels or as a percentage e.g. 600 or 100%*/
    var $sWidth;
    /** link url used if a column type of 3 is specified */
    var $aLinkURLs;
    /** database column values to append to link url if a column type of 3 is specified */
    var $aDBQueryStringColumns;
    /** variables names to give $aDBQueryStringColumns on the query string */
    var $aQueryStringVariableNames;
    /** display the column headings or not */
    var $bDisplayColumnHeadings;
    /** table heading */
    var $sTableHeading;
    /** specify an image at the start of each row in the table */
    var $sImageURL;
    /** specify whether to use $sImageURL or to get the image url from the sql result set */
    var $bUseImageURLFromQuery = false;
    /** message that will be displayed if the table is empty*/
    var $sEmptyTableMessage;
    /** Picture paths */
    var $sChkPicPath = "widgets/checked.gif";
    var $sUnChkPicPath = "widgets/unchecked.gif";
    /** whether to force entries to wrap */
    var $bWordWrap = false;
    /** whether the table cannot be edited */
    var $bDisabled = false;

    function PatternTableGeneric($oResultSet, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaderNames, $sTmpWidth, $aTmpLinkURLs = null, $aTmpDBQueryStringColumns = null, $aNewQueryStringVariableNames = null) {
        $this->oResultSet =& $oResultSet;
        $this->aColumns = & $aTmpColumns;
        $this->aColumnTypes = $aTmpColumnTypes;
        $this->aColumnHeaderNames = $aTmpColumnHeaderNames;
        $this->sWidth = $sTmpWidth;
        $this->bDisplayColumnHeadings = $bTmpDisplayColumnHeadings;
        $this->aLinkURLs = $aTmpLinkURLs;
        $this->aDBQueryStringColumns = $aTmpDBQueryStringColumns;
        $this->aQueryStringVariableNames = $aNewQueryStringVariableNames;
    }

    function setEmptyTableMessage($sNewValue) { $this->sEmptyTableMessage = $sNewValue; }
    function setTableHeading($sNewValue) { $this->sTableHeading = $sNewValue; }
    function setImageURL($sNewValue) { $this->sImageURL = $sNewValue; }
    function setUseImageURLFromQuery($bNewValue) { $this->bUseImageURLFromQuery = $bNewValue; }
    function setDisplayColumnHeadings($bNewValue) { $this->bDisplayColumnHeadings = $bNewValue; }
    function setIncludeBorder($bNewValue) { $this->bIncludeBorder = $bNewValue; }
    function setChkPicPath($sNewChkPicPath) { $this->sChkPicPath = $sNewChkPicPath; }
    function getChkPicPath() { return $this->sChkPicPath ; }
    function setWordWrap($bNewValue) { $this->bWordWrap = $bNewValue; }
    function setDisabled($bNewValue) { $this->bDisabled = $bNewValue; }

    /**
    * Build the HTML string used to render the object
    *
    * @return String of HTML used to render object
    *
    * @todo possibly add in image size restraints for link types 2 and 3
    */
    function & render() {
        global $default;

        $sToRender = "<table cellpadding=\"5\" border=\"" . ($this->bIncludeBorder ? "1" : "0") . "\" width=\"" . $this->sWidth . "\">\n";
        if (isset($this->sTableHeading)) {
            $sToRender .= "<caption align=\"top\" colspan=\"" . count($this->aColumns) . "\" align=\"left\"><b>$this->sTableHeading</b></caption>\n";
        }
        if ($this->bDisplayColumnHeadings) {
            for ($i = 0; $i < count($this->aColumnHeaderNames); $i++) {
                $sToRender .= "<th align=left>" . $this->aColumnHeaderNames[$i] . "</th>\n";
            }
        }
        # $sql->query($this->sQuery);
        $oResultSet =& $this->oResultSet;
        if ($oResultSet->isEmpty()) {
            $sToRender .= "<tr>\n";
            if (isset($this->sEmptyTableMessage)) {
                $sToRender .= "<td colspan=" . count($this->aColumns) . ">$this->sEmptyTableMessage</td>\n";
            } else {
                $sToRender .= "<td colspan=" . count($this->aColumns) . ">No " . (isset($this->sTableHeading) ? $this->sTableHeading : "") . " data</td>\n";
            }
            $sToRender .= "</tr>\n";
        } else {
            $iColour = 0;
            while ($oResult = $oResultSet->next()) {
                $sToRender .= "<tr bgcolor=\"" . getColour($iColour) . "\">\n";
                $iColour++;
                for ($i = 0; $i < count($this->aColumns); $i++) {
                    switch ($this->aColumnTypes[$i]) {
                        case 1:
                                //text
                                $sToRender .= "<td>";
                                if (isset($this->sImageURL)) {
                                    $sToRender .= $this->generateImageURL($this->sImageURL);
                                } else if ($this->bUseImageURLFromQuery) {
                                    $sToRender .= $this->generateImageURL($oResult->get("image_url"));
                                }
                                if ($oResult->get($this->aColumns[$i]) != null) {
                                    if ($this->bWordWrap) {
                                        $sToRender .= wordwrap($oResult->get($this->aColumns[$i]), 25, " ", 1) . "</td>";
                                    } else {
                                        $sToRender .= $oResult->get($this->aColumns[$i]) . "</td>";
                                    }
                                } else {
                                    $sToRender .= "&nbsp;</td>";
                                }
                                break;
                        case 2:
                                //boolean
                                $sToRender .= "<td>";
                                if ($oResult->get($this->aColumns[$i]) != null) {
                                    $value = $oResult->get($this->aColumns[$i]);
                                    if ($value) {
                                        $sToRender .= "<img src=\"$default->graphicsUrl/" . $this->sChkPicPath . "\">";
                                    } else {
                                        $sToRender .= "<img src=\"$default->graphicsUrl/" . $this->sUnChkPicPath . "\">";
                                    }
                                    $sToRender .= "&nbsp;</td>";
                                } else {
                                    $sToRender .= "&nbsp;</td>";
                                }

                                break;
                        case 3:
                                if ($this->bDisabled === true) {
                                    $sToRender .= '<td><span style="background-color: #CCCCCC; color: #222222;">';
                                    $sToRender .= $oResult->get($this->aColumns[$i]);
                                    $sToRender .= '</span></td>';
                                    $sToRender .= "\n";
                                    break;
                                }
                                //hyperlink
                                $sToRender .= "<td><a href=\"";
                                $sLink = $this->aLinkURLs[$i];
                                for ($j = 0; $j < count($this->aDBQueryStringColumns); $j++) {
                                    if (strpos($sLink, "?") === false) {
                                        $sLink .= "?" . $this->aQueryStringVariableNames[$j] . "=" . $oResult->get($this->aDBQueryStringColumns[$j]);
                                    } else {
                                        $sLink .= "&" . $this->aQueryStringVariableNames[$j] . "=" . $oResult->get($this->aDBQueryStringColumns[$j]);
                                    }
                                }
                                $sToRender .= $sLink . "\">";

                                if (isset($this->sImageURL)) {
                                    $sToRender .= $this->generateImageURL($this->sImageURL);
                                } else if ($this->bUseImageURLFromQuery) {
                                    $sToRender .= $this->generateImageURL($oResult->get("image_url"));
                                }
                                $sToRender .= $oResult->get($this->aColumns[$i]) . "</a></td>\n";
                                break;
                        default:
                                break;
                    }
                }
                $sToRender .= "</tr>\n";
            }
        }
        $sToRender .= "</table>";
        return $sToRender;
    }

    function generateImageURL($sURL) {
        return "<img src=\"" . $sURL . "\" border=\"0\"/>";
    }
}
?>
