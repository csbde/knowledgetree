<?php
/**
 * dashboard.php -- Main dashboard page.
 *  
 * This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out 
 * document, pending approval routing documents, etc. 
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Id$
 * @Copyright (c) 1999-2003 The Owl Project Team
 * @author michael@jamwarehouse.com
 * @package dms
 */

// main library routines and defaults

require_once("./config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/SiteMap.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternImage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");

// -------------------------------
// page start
// -------------------------------

if (checkSession()) {

    // check if this page is authorised, ie. has come from control.php
    if ($_SESSION["authorised"]) {
        // create a page  

        // logo
        $img = new PatternImage("$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo");
        $img->setImgSize(238, 178);
        
        // build the top menu of links
        // TODO: this is a function of the sitemap
        // get list of sections
        $aTopMenuLinks = array(generateControllerUrl("dashboard"), generateControllerUrl("browse"), generateControllerUrl("subscriptions"),
                               generateControllerUrl("search"), generateControllerUrl("administration"), generateControllerUrl("preferences"), generateControllerUrl("help"));
        $aTopMenuText = array("Dashboard", "Browse Documents", "Subscriptions", "Advanced Search", "Administration", "Preferences", "Help", "Logout");
        $aTopMenuImages = array("$default->owl_graphics_url/dashboard.jpg", "$default->owl_graphics_url/browse.jpg",
                                "$default->owl_graphics_url/subscriptions.jpg", "$default->owl_graphics_url/search.jpg",
                                "$default->owl_graphics_url/administration.jpg", "$default->owl_graphics_url/preferences.jpg", 
                                "$default->owl_graphics_url/help.jpg", "$default->owl_graphics_url/logout.jpg");
        
        $oPatternTableLinks = new PatternTableLinks($aTopMenuLinks, null, 1, 8, 2, $aTopMenuImages);
       
        

        // build the central dashboard
          //- pending documents (document approval)
          //- checked out documents
          //- subscriptions        
        $sHtml = "<table border=\"1\" width=\"100%\">
                            <tr><td>
                                <table>
                                    <tr>
                                        <td colspan=\"3\">
                                        Pending Documents
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width=\"33%\">
                                        Title
                                        </td>
                                        <td width=\"33%\">
                                        Status
                                        </td>
                                        <td width=\"33%\">
                                        Days
                                        </td>
                                    </tr>";
                                    /*
                                    $aPendingDocumentList = getPendingDocuments($_SESSION["userID"]);
                                    for ($i = 0; $i < count($aPendingDocumentList); $i++) {
                                        $sHtml = $sHtml . "<tr><td>" . $aPendingDocumentList[$i] . "</td></tr>";
                                    }
                                    */
       $sHtml = $sHtml . "
                                </table>
                            </td></tr>

                            <tr><td  width=80%>
                               <table border=2>
                                    <tr align=\"center\">
                                        <td colspan=2>
                                        Checked Out Documents
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width=\"33%\">
                                        Title
                                        </td>
                                        <td width=\"66%\">
                                        Days
                                        </td>
                                    </tr>";
                                    /*
                                    $aCheckedOutDocumentList = getCheckedOutDocuments($_SESSION["userID"]);
                                    for ($i = 0; $i < count($aCheckedOutDocumentList); $i++) {
                                        $sHtml = $sHtml . "<tr><td>" . $aCheckedOutDocumentList[$i] . "</td></tr>";
                                    }
                                    */
         $sHtml = $sHtml . "
                                </table>
                            </td></tr>

                            <tr><td>
                               <table>
                                    <tr>
                                        <td>
                                        Subscriptions Alerts
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width=\"33%\">
                                        Title
                                        </td>
                                        <td width=\"33%\">
                                        Status
                                        </td>
                                        <td width=\"33%\">
                                        Days
                                        </td>
                                    </tr>";
                                    /*
                                    $aSubscriptionList = getSubscriptionAlerts($_SESSION["userID"]);
                                    for ($i = 0; $i < count($aSubscriptionList); $i++) {
                                        $sHtml = $sHtml . "<tr><td>" . $aSubscriptionList[$i] . "</td></tr>";
                                    }
                                    */
        $sHtml = $sHtml . "                                    
                                </table>                            
                            </td></tr>                            
                            </table>";

        
        $oContent = new PatternCustom();
        $oContent->setHtml($html);
        
        /* get a page */
        $tmp = new PatternMainPage();
        
        /* put the page together */
        $tmp->setNorthWestPayload($img);
        $tmp->setNorthPayload($oPatternTableLinks);
        $tmp->setCentralPayload($oContent);
        $tmp->setFormAction("dashboard.php");
        $tmp->render();
        
    } else {
        // FIXME: redirect to no permission page
        print "you do not have access to view this page!  please go away, and come back when you do.<br>";
        echo generateLink("logout") . "logout</a>";    
    }
}
?>

