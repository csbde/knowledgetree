<?php

/**
* Removes all documents/folders that are in the database but are not on the file system
* 
* @author Rob Cherry, Jam Warehouse South Africa (Pty) Ltd
* @date 20 February 2003
* @package sync
* 
*/

require_once("../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

$aMissingDocuments = array();
$aMissingFolders = array();

$aDocuments = Document::getList();
$aFolders = Folder::getList();

for ($i = 0; $i < count($aFolders); $i++) {
	$oFolder = $aFolders[$i];
	checkFolder($oFolder, $fDelete);
}

for ($i = 0; $i < count($aDocuments); $i++) {
	$oDocument = $aDocuments[$i];
	checkDoc($oDocument, $fDelete);
}

$sToRender = "<html><head></head><body>\n";
$sToRender .= "The following is a list of documents and folders that are in the database but not on the file system<br>\n";
$sToRender .= "These folders/documents will have to be recreated/recaptured.<br><br>\n";
if (isset($fDelete)) {
	$sToRender .= "<b>These folders/documents HAVE BEEN DELETED.</b><br><br>\n";
} else {
	$sToRender .= "<b>These folders/documents have NOT BEEN DELETED YET.</b><br><br>\n";
	$sToRender .= "Click on the link entitled 'Deleted' below to delete these documents/folders<br><br>\n";
}

$sToRender .= "The following <b>folders</b> must be recreated:<br>\n<ul>\n";
$sToRender .= "<table>";
for ($i = 0; $i < count($aMissingFolders); $i++) {
	$oFolder = $aMissingFolders[$i];
	$sToRender .= "<tr><td nowrap>" . Folder::getFolderPath($oFolder->getID()) . "</td></tr>\n";	
}
$sToRender .= "</table>";

$sToRender .= "<br>The following <b>documents</b> must be recaptured:<br>\n";
$sToRender .= "<table>";
for ($i = 0; $i < count($aMissingDocuments); $i++) {	
	$oDocument = $aMissingDocuments[$i];
	$sToRender .= "<tr><td nowrap>(" . $oDocument->getID() . ", " . $oDocument->getLastModifiedDate() . ") " .  Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName() . "</td></tr>";	
}
$sToRender .= "</table>";

$sToRender .= "<br>\n";
if (!isset($fDelete)) {
	$sToRender .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?fDelete=1\">Delete</a>\n";
}
$sToRender .= "</body></html>";

echo $sToRender;

function checkDoc($oDocument, $bForDelete) {	
	global $aMissingDocuments;	
	$sDocPath = Folder::getFolderPath($oDocument->getFolderID()) . $oDocument->getFileName();	
	if (file_exists($sDocPath) === false) {		
		$aMissingDocuments[count($aMissingDocuments)] = $oDocument;
		if (isset($bForDelete)) {
			$oDocument->delete();
		}
	}
}

function checkFolder($oFolder, $bForDelete) {	
	global $aMissingFolders;
	$sFolderPath = Folder::getFolderPath($oFolder->getID());	
	if (file_exists($sFolderPath)) {
		return;
	} else {
		$aMissingFolders[count($aMissingFolders)] = $oFolder;
		if (isset($bForDelete)) {
			$oFolder->delete();
		}
	}
}

?>
