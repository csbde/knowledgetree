<?php
/**
* webTemplate.inc
*
* Used in conjunction with the navbar to create the north, west and north west payloads
* and in doing so creating the webtemplate 
*
*
* @author Mukhtar Dharsey
* @date 20 January 2003
* @package presentation.lookandfeel
*/

require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternImage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/NavBar.inc");


function getUnitAdminUser() {
    global $default;
    // find out what unit we're in
    $iUnitID = User::getUnitID($_SESSION["userID"]);
    if ($iUnitID) {
        // then find the group that is unit_admin
        $sql = $default->db;
        $sEmail = "";
        if ($sql->query("SELECT group_id FROM $default->owl_groups_units_table GUL " . 
                        "INNER JOIN $default->owl_groups_table GL on GUL.group_id=GL.id " .
                        "WHERE GL.is_unit_admin=1 " .
                        "AND unit_id=$iUnitID")) {
            // get the first record
            if ($sql->next_record()) {
                $iGroupID = $sql->f("group_id");
                // then find the first user in this group that has an email address
                if ($sql->query("SELECT U.id, U.email FROM $default->owl_users_table U " .
                                "INNER JOIN $default->owl_users_groups_table UGL on UGL.user_id=U.id " .
                                "WHERE group_id=$iGroupID")) {
                    while ($sql->next_record()) {
                        if (strlen($sql->f("email")) > 0) {
                            return User::get($sql->f("id"));
                        }
                    }
                }
            }
        }
    }
    return false;
}

// create the navbar and north payload
$navbar = & new NavBar($fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocumentTypeID);

//get the section name if not specified in the url
if ($sectionName == "") {
    $sectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
}

//highlight the respective section
$navbar->setHighlightedSection($sectionName);

// setup nw payload
$colourBar = $navbar->getNWColourbar();
$nwestLoad   = "<table cellspacing=0 cellpadding=0 border=0>\n" . 
               "<tr><td><img valign=\"top\" src=\"$default->graphicsUrl/tree.gif\"/></td></tr>\n" .
               "<tr><td><img valign=\"top\" src=\"$colourBar\"/></td></tr>\n" .
               "</table>";

$nwPayLoad = new PatternCustom;
$nwPayLoad ->setHtml($nwestLoad);

// set up the west payload
//get section links
if ($sectionName != "General") {
    $sectionArray = $default->siteMap->getSectionLinks($sectionName);

    $output1["links"] = $sectionArray["links"];
    $output2["descriptions"] = $sectionArray["descriptions"];

    //create links
    $westLinks = new PatternTableLinks($sectionArray["links"], $sectionArray["descriptions"], 20 , 1 ,1,null);
    $westRender = $westLinks->render();

} else {
    $westLinks = new PatternTableLinks(array(), array(), 20, 1 ,1,null);
    $westRender = $westLinks->render();
}

//set up links
$cAdmin = "$default->graphicsUrl/leftnav/cadmin.gif" ;
$uAdmin = "$default->graphicsUrl/leftnav/uadmin.gif" ;

$oUnitAdmin = getUnitAdminUser();
$sUnitAdminEmail = $oUnitAdmin ? $oUnitAdmin->getEmail() : $default->emailAdmin; 
//setup the westpayload
$westLoad = "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\" height=\"75%\">\n" .
            "\t<tr><td>$westRender</td></tr>\n" .
            "</table>" .
            "<div style=\"position:absolute;top:" . ($default->scroll["height"]-50) . "px;\">" . 
            //"<div style=\"position:relative;top:-5px;\">" .
            "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\">\n" .            
            "\t<tr valign=\"bottom\"><td align=left><a href=\"mailto:$default->emailAdmin\"><img src=\"$cAdmin\"/ border=\"0\"></a></td></tr>\n" .
            "\t<tr valign=\"top\"><td align=left><a href=\"mailto:$sUnitAdminEmail\"><img src=\"$uAdmin\"/ border=\"0\"></a></td></tr>\n" .
            "</table>" .
            "</div>";

$westPayLoad = new PatternCustom;
$westPayLoad->setHtml($westLoad);

$main = new PatternMainPage();
$main->setNorthPayload($navbar);
$main->setNorthWestPayload($nwPayLoad);
$main->setWestPayload($westPayLoad);
$main->setWestPayloadBackGroundColour("#F0F0E4");
?>