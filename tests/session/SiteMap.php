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
    
    // array
    echo "SiteMap:: using the array<br>";
    
    // get section links
    echo "SiteMap::getSectionLinks(Administration) = ";
          print_r($default->siteMap->getSectionLinks("Administration"));
    echo "SiteMap::getSectionLinks(Manage Documents) = ";
          print_r($default->siteMap->getSectionLinks("Manage Documents"));              
    
    // get page
    echo "SiteMap::getPage(viewDocument) = " . $default->siteMap->getPage("viewDocument") . "<br>";
    echo "SiteMap::getPage(unitAdministration) = " . $default->siteMap->getPage("unitAdministration") . "<br>";
    echo "SiteMap::getPage(systemAdministration) = " . $default->siteMap->getPage("systemAdministration") . "<br>";
    
    // get section name
    echo "SiteMap::getSectionName(" . $_SERVER['PHP_SELF'] . ") = " . $default->siteMap->getSectionName($_SERVER['PHP_SELF']) . "<br>";
    echo "SiteMap::getSectionName(/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php) = " . $default->siteMap->getSectionName("/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php") . "<br>";
    
    // get default action
    echo "SiteMap::getDefaultAction(Manage Documents) = " . $default->siteMap->getDefaultAction("Manage Documents") . "<br>";
    echo "SiteMap::getDefaultAction(Administration) = " . $default->siteMap->getDefaultAction("Administration") . "<br>";
    
    // get action from page
    echo "SiteMap::getActionFromPage(/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php) = " . $default->siteMap->getActionFromPage("/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php") . "<br>";
    echo "SiteMap::getActionFromPage(/presentation/documentmanagement/moveFolder.php) = " . $default->siteMap->getActionFromPage("/presentation/documentmanagement/moveFolder.php") . "<br>";    

    // sync array to db
    $default->siteMap->syncWithDB();
    echo "SiteMap::syncWithDB just executed";
    
    // DB
    $default->siteMap->setUseDB(true);
    echo "<br>---------------------------<br>";
    echo "SiteMap:: using the database<br>";
    
    // get section links    
    echo "SiteMap::getSectionLinks(Administration) = ";
          print_r($default->siteMap->getSectionLinks("Administration"));
    echo "SiteMap::getSectionLinks(Manage Documents) = ";
          print_r($default->siteMap->getSectionLinks("Manage Documents"));              

    // get page
    echo "SiteMap::getPage(viewDocument) = " . $default->siteMap->getPage("viewDocument") . "<br>";
    echo "SiteMap::getPage(unitAdministration) = " . $default->siteMap->getPage("unitAdministration") . "<br>";
    echo "SiteMap::getPage(systemAdministration) = " . $default->siteMap->getPage("systemAdministration") . "<br>";
    
    // get section name
    echo "SiteMap::getSectionName(" . $_SERVER['PHP_SELF'] . ") = " . $default->siteMap->getSectionName($_SERVER['PHP_SELF']) . "<br>";
    echo "SiteMap::getSectionName(/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php) = " . $default->siteMap->getSectionName("/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php") . "<br>";

    // get default action
    echo "SiteMap::getDefaultAction(Manage Documents) = " . $default->siteMap->getDefaultAction("Manage Documents") . "<br>";
    echo "SiteMap::getDefaultAction(Administration) = " . $default->siteMap->getDefaultAction("Administration") . "<br>";
    
    // get action from page
    echo "SiteMap::getActionFromPage(/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php) = " . $default->siteMap->getActionFromPage("/presentation/lookAndFeel/knowledgeTree/documentmanagement/browseBL.php") . "<br>";
    echo "SiteMap::getActionFromPage(/presentation/documentmanagement/moveFolder.php) = " . $default->siteMap->getActionFromPage("/presentation/documentmanagement/moveFolder.php") . "<br>";    
    echo "</pre>";
        
} else {
    // FIXME: redirect to no permission page
    print "you do not have access to view this page!  please go away, and come back when you do.<br>";
    echo generateLink("logout") . "logout</a>";    
}

?>
