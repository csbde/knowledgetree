<?php
require_once("../../config/dmsDefaults.php");

/**
 * $Id$
 *
 * Unit tests for the SiteMap
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests.session
 */

if (checkSession()) {

        echo "<pre>";
        echo "SiteMap::getPage(addUser) = " . $default->siteMap->getPage("addUser") . "<br>";
        echo "SiteMap::getSectionName(" . $_SERVER['PHP_SELF'] . ") = " . $default->siteMap->getSectionName($_SERVER['PHP_SELF']) . "<br>";
        echo "SiteMap::getSectionLinks(Administration) = ";
              print_r($default->siteMap->getSectionLinks("Administration"));
        echo "SiteMap::getDefaultAction(Manage Documents) = " . $default->siteMap->getDefaultAction("Manage Documents") . "<br>";
        echo "</pre>";
        
} else {
    // FIXME: redirect to no permission page
    print "you do not have access to view this page!  please go away, and come back when you do.<br>";
    echo generateLink("logout") . "logout</a>";    
}

?>

