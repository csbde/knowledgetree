<?php

require_once("$default->owl_fs_root/lib/security/permission.inc");

// define access constants
define("SA", 0);
define("UA", 1);
define("A", 2);

/**
 * $Id$
 * 
 * Maintains (page, access) access map, as well as (section, page) map.
 * 
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.session
 */
class SiteMap {

    /**
     * The underlying site map storage array
     */
    var $siteMapArray;
    
    /**
     * Constructs a new SiteMap, initialising the array.
     */
    function SiteMap() {
        $this->siteMapArray = array();
    }
    
    /**
     * Adds a site page mapping entry.
     *
     * @param string the controller action
     * @param string the corresponding page for this action
     * @param string the section this page falls under
     * @param int    the minimum access needed to access this page
     * @param string description of the page for link presentation
     */
    function addPage($action, $page, $sectionName, $requiredAccess, $description) {
        $this->siteMapArray[$sectionName][$requiredAccess][$action] = array ("page" => $page, 
                                                                             "description" => $description, 
                                                                             "default" => false);
    }
    
    /**
     * Adds a site page mapping entry- the default page for the section.
     *
     * @param string the controller action
     * @param string the corresponding page for this action
     * @param string the section this page falls under
     * @param int    the minimum access needed to access this page
     * @param string description of the page for link presentation     
     */    
    function addDefaultPage($action, $page, $sectionName, $requiredAccess, $description) {
        $this->siteMapArray[$sectionName][$requiredAccess][$action] = array ("page" => $page, 
                                                                             "description" => $description, 
                                                                             "default" => true);
    }

    /**
     * Returns the section name of the supplied page
     *
     * @param string the page to lookup the section for
     */
    function getSectionName($sRequiredPage) {
        global $default;
        // for each section
        foreach ($this->siteMapArray as $section => $valArr) {
            // for each access, page array combination
            foreach ($valArr as $requiredAccess => $pageArr) {
                // now loop through pages until we find the right one
                foreach ($pageArr as $action => $page) {
                    if ($sRequiredPage == $page["page"]) {
                        return $section;
                    }
                }
            }
        }        
    }
    
    /**
     * Returns true if the user has the necessary rights to access
     * a sitemap entry
     *
     * @param int the required access (defined above class)
     * @return boolean true if the user has the access, else false.
     */
    function hasPermission($requiredAccess) {

        switch ($requiredAccess) {
            case A  : // everyone has access to anonymous pages
                      return true;
                      break;
                      
            case UA : // check that this user is in a group with unit admin access
                      // or is a system adminstrator
                      if (Permission::userIsSystemAdministrator() ||
                          Permission::userIsUnitAdministrator()) {
                          return true;
                      } else {
                          return false;
                      }
                      break;
                      
            case SA : // check that this user is a system administrator
                      if (Permission::userIsSystemAdministrator()) {
                          return true;
                      } else {
                         return false;
                      }
                      break;                     
        }
        // if we haven't returned by here, $requiredAccess is unknown
        
        // TODO: add a check in addPage/addDefaultPage
        return false;
    }
    
    /**
     * Returns controller links for a section
     *
     * @param string the section to return links for
     */
    function getSectionLinks($sSectionName) {
        global $default;
        // check if the section exists
        if (is_array($this->siteMapArray[$sSectionName])) {
            // initialise result array
            $results = array("descriptions" => array(), "links" => array());
            
            // need to loop through all (access, page) arrays in this section
            foreach ($this->siteMapArray[$sSectionName] as $requiredAccess => $pages) {
                if ($this->hasPermission($requiredAccess)) {
                    foreach ($pages as $action => $pageDetail) {
                        // add this array to the resultset array
                        $results["descriptions"][] = $pages[$action]["description"];
                        $results["links"][] = generateControllerUrl($action);
                    }
                }
            }
            // now check if we have anything in the results array before returning it
            if (count($results) > 0) {
                return $results;
            } else {
                return false;
            }            
        } else {
            $_SESSION["errorMessage"] = "No such section name ($sSectionName) in the sitemap";
            return false;
        }
    }
    
    /**
     * Returns the page mapped to the (action, groupName) pair.
     *
     * @param string the action to lookup pages for
     * @return string the page to redirect to, or false if the user doesn't have access to the page
     */
    function getPage($action) {
        global $default;
        
        $default->log->info("SiteMap::getPage: checking ($action, " . $_SESSION["userID"] . ")");
        $groupIDs = array();
        
        // for each section
        foreach ($this->siteMapArray as $section => $valArr) {
            $default->log->debug("Sitemap::getPage section=$section");
            // for each group, page array combination
            foreach ($valArr as $requiredAccess => $pageArr) {
                // now loop through pages until we find the right one
                foreach ($pageArr as $ackshin => $page) {
                    if ($ackshin == $action) {
                        $default->log->debug("Sitemap::getPage current requiredAccess=$requiredAccess, action=$ackshin");
                        if ($this->hasPermission($requiredAccess)) {
                            return $page["page"];
                        }
                    }
                }
            }
        }
        // if the function hasn't returned already then the current
        // user does not have access to the action
        $default->log->info("Sitemap::getPage: access denied for ($action, " . $_SESSION["userID"] . ")");
        return false;
    }
    
    
    /**
     * Returns the default action for the supplied section
     *
     * @param string the section name to return the default action for
     * @return string the controller action for the default page for this section
     */
    function getDefaultAction($sectionName) {
        global $default;
        // check if the section exists
        if (is_array($this->siteMapArray[$sectionName])) {
            // initialise result array
            $results = array();
            // need to loop through all (groupName, page) arrays in this section
            foreach ($this->siteMapArray[$sectionName] as $requiredAccess => $pages) {
                // don't need to check the permissions here, when the controller tries to
                // retrieve the page from the action, the perms will be checked
                //$default->log->debug("Sitemap::getDefaultAction: (section=$sectionName, reqGrp=$requiredGroupName); pages=" . arrayToString($pages));
                foreach ($pages as $action => $pageArray) {
                    //$default->log->debug("Sitemap::getDefaultAction: action=$action; pageArray" . arrayToString($pageArray));
                    if ($pageArray["default"]) {
                        return $action;
                    }
                }
            }
        } else {
            // supplied section not in sitemap
            // TODO: internal error code?
            $_SESSION["errorMessage"] = "$sectionName not in SiteMap!";
            return false;
        }        
    }

    /**
     * Returns the action for a specific page- to enable redirects
     *
     * @param string the page to perform the reverse lookup for
     * @return string the action for this page
     */
    function getActionFromPage($sPage) {
        global $default;
        $default->log->debug("Sitemap::getActionFromPage: page=$sPage");
        // for each section
        foreach ($this->siteMapArray as $section => $valArr) {
            $default->log->debug("Sitemap::getActionFromPage section=$section");
            // for each group, page array combination
            foreach ($valArr as $requiredAccess => $pageArr) {
                $default->log->debug("Sitemap::getActionFromPage access=$requiredAccess");
                // now loop through pages until we find the right one
                foreach ($pageArr as $action => $page) {
                    $default->log->debug("Sitemap::getActionFromPage action=$action, reqPage=$sPage; page=" . $page["page"]);
                    if ($sPage == $page["page"]) {
                        $default->log->debug("Sitemap::getActionFromPage found action=$action for page=$sPage");
                        return $action;
                    }
                }
            }
        }
    }
    
    /**
     * Prints the current site map
     */
    function printMap() {
        return arrayToString($this->siteMapArray);
    }
}
?>
