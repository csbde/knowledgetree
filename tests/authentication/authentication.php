<?php

require_once("../../config/dmsDefaults.php");

/**
 * $Id$
 *
 * Contains unit test code for authentication classes: lib/authentication
 *
 * Tests are:
 * - creation of document subscription object
 * - setting/getting of values
 * - storing of object
 * - updating of object
 * - deletion of object
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package tests.authentication
 */
if (checkSession()) {
    require_once("$default->owl_fs_root/lib/authentication/DBAuthenticator.inc");
    echo "<b>Testing DB searching</b>";
    // user attributes to search for
    $aAttributes = array ("username", "name", "email", "mobile", "email_notification", "sms_notification");
    $oDbAuth = new DBAuthenticator();
    $sSearch = "user";
    echo "<ul><li>searching for $sSearch with attributes=<pre>" . arrayToString($aAttributes) . "</pre></li>";
    $aResults = $oDbAuth->searchUsers($sSearch, $aAttributes);
    echo "<li><pre>" . arrayToString($aResults) . "</pre></li></ul>";

    require_once("$default->owl_fs_root/lib/authentication/LDAPAuthenticator.inc");
    echo "<b>Testing LDAP searching</b>";
    // user attributes to search for
    $aAttributes = array ("dn", "uid", "givenname", "sn", "mail", "mobile");
    $oLdapAuth = new LDAPAuthenticator();
    $sSearch = "michael";
    echo "<ul><li>searching for $sSearch with attributes=<pre>" . arrayToSTring($aAttributes) . "</pre></li>";
    $aResults = $oLdapAuth->searchUsers($sSearch, $aAttributes);
    echo "<li><pre>" . arrayToString($aResults) . "</pre></li></ul>";

}
?>
