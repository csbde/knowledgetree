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
require_once("$default->owl_fs_root/lib/owl.lib.php");
require_once("$default->owl_fs_root/config/html.php");
require_once("$default->owl_fs_root/lib/control.inc");
require_once("$default->owl_fs_root/lib/Session.inc");
require_once("$default->owl_fs_root/lib/SiteMap.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternImage.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");

// -------------------------------
// page start
// -------------------------------

if (checkSession()) {

    // check if this page is authorised, ie. has come from control.php
    if ($_SESSION["authorised"]) {
        // create a page  
        
        // logo
        $img = new PatternImage("$default->owl_root_url/locale/$default->owl_lang/graphics/$default->logo");
        
        // build the top menu of links
        $aTopMenuLinks = array(0=>generateControllerUrl("logout"), 1=>generateControllerUrl("scratchPad"));
        $aTopMenuText = array(0=>"logout", 1=>"scratchPad");
        $oPatternTableLinks = new PatternTableLinks($aTopMenuLinks, $aTopMenuText, 3, 1);
        
        // build the central dashboard
        /*
        $aCentralPageColumns = array(0=>"name",1=>"parent",2=>"security");
        $aColumnTypes = array(0=>1,1=>2,2=>1);
        $oTableSqlQuery = & new PatternTableSqlQuery("Folders", $aCentralPageColumns, $aColumnTypes); 
        ($HTTP_GET_VARS["fStartIndex"]) ? $oTableSqlQuery->setStartIndex($HTTP_GET_VARS["fStartIndex"]) : $oTableSqlQuery->setStartIndex(0);
        $oTableSqlQuery->setLinkType(1);
        */
        
        /* get a page */
        $tmp = new PatternMainPage();
        
        /* put the page together */
        $tmp->setNorthWestPayload($img);
        $tmp->setNorthPayload($oPatternTableLinks);
        //$tmp->setCentralPayload($oTableSqlQuery);
        $tmp->setFormAction("dashboard.php");
        $tmp->render();
        
    } else {
        // FIXME: redirect to no permission page
        print "you do not have access to view this page!  please go away, and come back when you do.<br>";
        echo generateLink("logout") . "logout</a>";    
    }
} else {
    // no session, should have been redirected
    echo "no session<br>";
    print_r($_SESSION);
}
?>

