<?php

/**
* HTML information for folder stuff
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 23 January 2003
* @package presentation.lookAndFeel.knowledgeTree.foldermanagement
*/

function displayFolderPathLink($aPathArray) {
   // display a separate link to each folder in the path
   for ($i=0; $i<count($aPathArray); $i++) {
       // retrieve the folder id for this folder name
       $iFolderID = Folder::getFolderID($aPathArray[$i]);
       // generate a link back to this page setting fFolderID
       $sLink = generateLink($_SERVER["PHP_SELF"],
                            "fBrowseType=folder&fFolderID=$iFolderID", 
                             $aPathArray[$i]);
       $sPathLinks = (strlen($sPathLinks) > 0) ? $sPathLinks . " > " . $sLink : $sLink; 
   }
   return $sPathLinks;
}

function displayFolderLink($oFolder) {
    global $default;
    $sFolderIconPath = generateImage($default->owl_graphics_url . "/widgets/dfolder.gif");
    return generateLink($_SERVER["PHP_SELF"],
                        "fBrowseType=folder&fFolderID=" . $oFolder->getID(),
                        $sFolderIconPath .
                        $oFolder->getName());
}


?>
