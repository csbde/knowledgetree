<?php

/**
 * $Id$
 *
 * HTML information for folder stuff
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @date 23 January 2003
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

/**
 * Displays the passed path array as a link
 * 
 * @param array containing the path to the folder (folder ids)
 * @param string the page to link to (defaults to the page this is called from)
 */
function displayFolderPathLink($aPathArray, $aPathNameArray, $sLinkPage = "") {
    global $default;
    if (strlen($sLinkPage) == 0) {
        $sLinkPage = $_SERVER["PHP_SELF"];
    }
    $default->log->debug("displayFolderPathLink: slinkPage=$sLinkPage");
    // display a separate link to each folder in the path
    for ($i=0; $i<count($aPathArray); $i++) {
        $iFolderID = $aPathArray[$i];		
        // retrieve the folder name for this folder
        //$sFolderName = Folder::getFolderName($iFolderID);
		$sFolderName = $aPathNameArray[$i];		
        // generate a link back to this page setting fFolderID		
        $sLink = generateLink($sLinkPage,
                              "fBrowseType=folder&fFolderID=$iFolderID",
                              $sFolderName);		
        $sPathLinks = (strlen($sPathLinks) > 0) ? $sPathLinks . " > " . $sLink : $sLink;
    }
    return $sPathLinks;
}


/**
 * Displays the passed folder name as a link
 * 
 * @param object the folder to link to
 * @param string the page to link to (defaults to the page this is called from)
 */
function displayFolderLink($oFolder, $sLinkPage = "") {
    global $default;

    if (strlen($sLinkPage) == 0) {
        $sLinkPage = $_SERVER["PHP_SELF"];
    }

    $sFolderIconPath = generateImage($default->graphicsUrl . "/widgets/dfolder.gif");
    return generateLink($sLinkPage,
                        "fBrowseType=folder&fFolderID=" . $oFolder->getID(),
                        $sFolderIconPath .
                        $oFolder->getName());
}

function renderFolderPath($iFolderID, $sLinkURL) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sFolderPath = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID), $sLinkURL);	
    return "<table border=\"0\" cellpadding=\"5\" width=\"600\"><tr><td bgcolor=\"$sTDBGColour\">$sFolderPath</td></tr></table>\n";
}

/**
* Renders a the list of folders found in $fFolderID as
* and HTML table.  &fFolderID=<folder_id> is automatically
* appended to the query string
*
* @return String HTML table
*/
function renderFolderList($fFolderID, $sLinkURL) {
    global $default;
    $sQuery = "SELECT F.id AS id, F.name AS name " .
              "FROM $default->owl_folders_table AS F " .
              "WHERE F.parent_id = " . $fFolderID . " " .
              "ORDER BY F.name ASC";
     	
    $aColumns = array("name");
    $aColumnTypes = array(3);
    $aColumnHeaderNames = array("Folder");
    $aDBColumns = array("id");
    $aQueryStringVariableNames = array("fFolderID");
    $aLinkURLs = array();
    if (strlen($default->rootUrl) > 0) {    	
    	$aLinkURLs = array("$default->rootUrl/$sLinkURL");
    } else {
    	$aLinkURLs = array("$sLinkURL");
    }

    $oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaderNames, "100%", $aLinkURLs, $aDBColumns, $aQueryStringVariableNames);
    $oPatternTableSqlQuery->setImageURL("$default->graphicsUrl/widgets/dfolder.gif");
    $oPatternTableSqlQuery->setEmptyTableMessage("This folder contains no sub folders");
    return $oPatternTableSqlQuery->render();
}
?>
