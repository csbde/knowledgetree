<?php
require_once("../config/dmsDefaults.php");

/**
 * $Id$
 *
 * Unit tests for lib/System.inc
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests
 */

//if (checkSession()) {
    echo "<pre>";

    echo "System::get(ldapServer) = " . $default->system->get("ldapServer") . "\n";
    echo "System::get(ldapRootDn) = " . $default->system->get("ldapRootDn") . "\n";
    echo "System::get(emailServer) = " . $default->system->get("emailServer") . "\n";
    echo "System::get(emailFrom) = " . $default->system->get("emailFrom") . "\n";
    echo "System::get(emailFromName) = " . $default->system->get("emailFromName") . "\n";
    echo "System::get(filesystemRoot) = " . $default->system->get("filesystemRoot") . "\n";
    echo "System::get(documentRoot) = " . $default->system->get("documentRoot") . "\n";
    echo "System::get(rootUrl) = " . $default->system->get("rootUrl") . "\n";
    echo "System::get(graphicsUrl) = " . $default->system->get("graphicsUrl") . "\n";
    
    echo "System::get(languageDirectory) = " . $default->system->get("languageDirectory") . "\n";
    echo "System::get(uiDirectory) = " . $default->system->get("uiDirectory") . "\n";
    echo "System::get(uiUrl) = " . $default->system->get("uiUrl") . "\n";
    echo "System::get(useFs) = " . $default->system->get("useFs") . "\n";
    echo "System::get(defaultLanguage) = " . $default->system->get("defaultLanguage") . "\n";
    //echo "System::get(notificationLink) = " . $default->system->get("notificationLink") . "\n";
    echo "System::get(sessionTimeout) = " . $default->system->get("sessionTimeout") . "\n";
    echo "</pre>";
//}
?>

