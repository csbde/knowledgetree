<?php
/**
* @package tests.web
*
* Unit tests for WebSite class in /lib/web/WebSite.inc
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
*/

require_once("../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->owl_fs_root/lib/web/WebSite.inc");
	
	$oWebSite = & new WebSite("test web !@43&!@*& site", "http://www.google.com", 1);
	echo "Create ? " . ($oWebSite->create() ? "Yes" : "No") . "<br>";
	$oWebSite = & new WebSite("test web !@43&!@*& site", "http://www.google.com", 1);
	$oWebSite->create();
	$oWebSite = & new WebSite("test web !@43&!@*& site", "http://www.google.com", 1);
	$oWebSite->create();
	$oWebSite = & new WebSite("test web !@43&!@*& site", "http://www.google.com", 1);
	$oWebSite->create();
	$oWebSite = & new WebSite("test web !@43&!@*& site", "http://www.google.com", 1);
	$oWebSite->create();
	echo "Update ? " . ($oWebSite->update() ? "Yes" : "No") . "<br>";
	echo "Delete ? " . ($oWebSite->delete() ? "Yes" : "No") . "<br>";
	$oNewWebSite = WebSite::get(1);
	echo "Get ? <pre>" . print_r($oNewWebSite) . "</pre>";
	$aWebSiteList = WebSite::getList();
	echo "List ? <pre>" . print_r($aWebSiteList) . "</pre>";
	
}

?>
