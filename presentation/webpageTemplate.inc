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

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternImage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/NavBar.inc");

// create the navbar and north payload
$navbar = new NavBar($fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocTypeID);


if ($sectionName == "")
    //get the section name if not specified in the url
{
    $sectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
}


//highlight the respective section
$navbar->setHighlightedSection($sectionName);

// setup nw payload
$nwestLoad   = "<table width=\"100%\" height=\"100%\">\n" . 
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
    $westLinks = new PatternTableLinks($sectionArray["links"], $sectionArray["descriptions"], 40 , 1 ,1,null);
    $westRender = $westLinks->render();

} else {
    $westLinks = new PatternTableLinks(array(), array(), 40 , 1 ,1,null);
    $westRender = $westLinks->render();

}

//set up links
$cAdmin = "$default->graphicsUrl/leftnav/cadmin.gif" ;
$link = "$default->graphicsUrl/leftnav/links.gif" ;


//setup the westpayload
// TODO -> change to links the images as well
$westLoad = "<table cellspacing=\"5\"  cellpadding=\"5\"  border=\"0\" width=\"100%\" height=\"75%\">\n" .
            "\t<tr><td>$westRender</td></tr>\n" .
            "\t<tr><td><a href=\"mailto:$default->emailAdmin\"><img src=\"$cAdmin\"/ border=\"0\"></a>\n" .
            "\t<br><img src=\"$link\"/>\n</td></tr>" .
            "</table>";

$westPayLoad = new PatternCustom;
$westPayLoad->setHtml($westLoad);

$main = new PatternMainPage();
$main->setNorthPayload($navbar);
$main->setNorthWestPayload($nwPayLoad);
$main->setWestPayload($westPayLoad);

?>

