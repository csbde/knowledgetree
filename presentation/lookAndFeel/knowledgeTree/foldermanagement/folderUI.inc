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
function displayFolderPathLink($aPathArray, $sLinkPage = "") {
   if (strlen($sLinkPath) == 0) {
       $sLinkPath = $_SERVER["PHP_SELF"];
   }
   // display a separate link to each folder in the path
   for ($i=0; $i<count($aPathArray); $i++) {
       $iFolderID = $aPathArray[$i];
       // retrieve the folder name for this folder       
       $sFolderName = Folder::getFolderName($iFolderID);
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
    
    if (strlen($sLinkPath) == 0) {
       $sLinkPath = $_SERVER["PHP_SELF"];
    }

    $sFolderIconPath = generateImage($default->owl_graphics_url . "/widgets/dfolder.gif");
    return generateLink($sLinkPage,
                        "fBrowseType=folder&fFolderID=" . $oFolder->getID(),
                        $sFolderIconPath .
                        $oFolder->getName());
}

function renderFolderPath($fFolderID, $sLinkURL) {	
	$sFolderPath = displayFolderPathLink(Folder::getFolderPathAsArray($fFolderID, $sLinkURL));
	return "<table border=1 width = 100%><tr><td>$sFolderPath</td></tr></table>\n";	
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
				"WHERE F.parent_id = " . $fFolderID;
	
	$aColumns = array("name");
	$aColumnTypes = array(3);
	$aColumnHeaderNames = array("Folder");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaderNames, "100%", "$default->owl_root_url/$sLinkURL&fFolderID=", "id");
	$oPatternTableSqlQuery->setImageURL("$default->owl_fs_root/graphics/widgets/dfolder.gif");
	$oPatternTableSqlQuery->setEmptyTableMessage("This folder contains no sub folders");
	return $oPatternTableSqlQuery->render();
}



?>
