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
*/

require_once("$default->owl_fs_root/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternImage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/NavBar.inc");


// create the navbar and north payload
$navbar = new NavBar();

//get the section name
$sectionName = $default->siteMap->getSectionName($_SERVER["PHP_SELF"]);

//highlight the respective section
$navbar->setHighlightedSection($sectionName);

// setup nw payload
$colourBar = $navbar->getNWColourbar();
$image = new PatternImage("$default->owl_graphics_url/tree.gif");
$image->setImgSize(114, 96);
$img = $image->render();


$nwestLoad   = "
        <html>
        <table width = \"100%\" height = \"100%\">
        <td>". $img ."</td></tr>
        <tr><td><img src = " . $colourBar . "></img></td></tr>
        </table>
        </html>
         ";

$nwPayLoad = new PatternCustom;
$nwPayLoad ->setHtml($nwestLoad);	     
         
// set up the west payload
//get section links
$sectionArray =$default->siteMap->getSectionLinks($sectionName);

//format the array..by taking first element off
$output1["links"] = array_shift ($sectionArray["links"]);      
$output2["descriptions"] = array_shift($sectionArray["descriptions"]);

//create links
$westLinks = new PatternTableLinks($sectionArray["links"], $sectionArray["descriptions"], 50 , 1 ,1,null);
$westRender = $westLinks->render();

//set up links
$cAdmin = "$default->owl_graphics_url/leftnav/cadmin.gif" ;
$link = "$default->owl_graphics_url/leftnav/links.gif" ;


//setup the westpayload 
// TODO -> change to links the images as well
$westLoad = "
        <html>
         <table border  = \"1\" width = \"100%\" height = \"100%\">
         <tr><td>". $westRender ."</td></tr>
         <tr><td><img src = " . $cAdmin . "></img></td></tr>
         <tr><td><img src = ". $link ."></img></td></tr>
         </table>
         </html>
         ";
         
$westPayLoad = new PatternCustom;
$westPayLoad->setHtml($westLoad);	     

$main = new PatternMainPage();
$main->setNorthPayload($navbar);
$main->setNorthWestPayload($nwPayLoad);
$main->setWestPayload($westPayLoad);

?>

