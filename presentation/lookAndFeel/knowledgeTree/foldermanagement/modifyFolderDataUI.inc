<?php
/**
 * $Id$
 *
 * Presentation information used for folder data editing.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 * 
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */


function getFolderData($iFolderID, $sStatusMessage = "") {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sQuery = "SELECT F.id, F.name, F.description, F.is_public " .
			"FROM $default->owl_folders_table AS F " .
			"WHERE F.id = $iFolderID";
			
	$sql = $default->db;
	$sql->query($sQuery);
	
    $sToRender .= renderHeading("Modify Folder Properties");
    $sToRender .= renderFolderPath($iFolderID, "/control.php?action=browse");    	
	$sToRender .= "<table>";
	$sToRender .= "<tr><td>";
	$sToRender .= "<table cellpadding=\"5\" border=\"0\" width=\"610\">\n";
	$sToRender .= "<caption><strong>Folder Data</strong></caption>";
	if ($sql->next_record()) {
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Name</td>\n";
		if (Folder::folderIsUnitRootFolder($iFolderID)) {
			$sToRender .= "<td>" . $sql->f("name") . "</td>\n";
		} else {
			$sToRender .= "<td><input type=\"text\" name=\"fFolderName\" value=\"" . $sql->f("name") . "\" size=\"50\"></td>\n";
		}
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Description</td>\n";
		$sToRender .= "<td><input type=\"text\" name=\"fDescription\" value=\"" . $sql->f("description") . "\" size=\"50\"></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td bgcolor=\"" . $sTDBGColour . "\">Public</td>\n";
		$sToRender .= "<td><input type=\"checkbox\" name=\"fIsPublic\" " . ($sql->f("is_public") ? "CHECKED" : "") ." ></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "<td></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<input type=\"hidden\" name=\"fFolderID\" value=\"$iFolderID\">";
		$sToRender .= "<input type=\"hidden\" name=\"fForUpdate\" value=\"1\">";
		
	}
	$sToRender .= "</table></td></tr>";
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" border=\"0\" />";
	$sToRender .= generateControllerLink("editFolder", "fFolderID=$iFolderID", "<image src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"/>") . "</td></tr>\n";
	$sToRender .= "</table>\n";

    return $sToRender;
}
?>