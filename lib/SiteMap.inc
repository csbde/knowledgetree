<?php

require_once("$default->owl_fs_root/lib/security/permission.inc");

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
     * @param string the user group with access to this page
     */
    function addPage($action, $page, $sectionName, $groupName) {
        // add to map
        $this->siteMapArray[$sectionName][$groupName][$action] = $page;
    }
    
    /**
     * Adds a site page mapping entry- the default page for the section.
     *
     * @param string the controller action
     * @param string the corresponding page for this action
     * @param string the section this page falls under
     * @param string the user group with access to this page
     */    
    function addDefaultPage($action, $page, $sectionName, $groupName) {
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
            $default->log->debug("Sitemap::getSectionName section=$section; arr=" . arrayToString($valArr));
            // for each group, page array combination
            foreach ($valArr as $requiredGroupName => $pageArr) {
                $default->log->debug("Sitemap::getSectionName ($requiredGroupName, " . arrayToString($pageArr) . ")");
                // now loop through pages until we find the right one
                foreach ($pageArr as $action => $page) {
                    $default->log->debug("Sitemap::getSectionName ($sRequiredPage == $page)");
                    if ($sRequiredPage == $page) {
                        return $section;
                    }
                }
            }
        }        
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
            $results = array();
            // need to loop through all (groupName, page) arrays in this section
            foreach ($this->siteMapArray[$sSectionName] as $requiredGroupName => $pages) {
                $default->log->debug("Sitemap::getSectionLinks: requiredGroup=$requiredGroupName, pages=" . arrayToString($pages));
                // if anonymous
                // or the user is in the required group or the user is the SA
                if ( ($requiredGroupName == "Anonymous") ||
                     ( Permission::userIsInGroupName($requiredGroupName) || 
                       Permission::userIsInGroupName("System Administrators") ) ) {
                    // add this array to the resultset array
                    $default->log->debug("Sitemap::getSectionLinks: about to add " . arrayToString($pages));
                    //$results = array_merge($results, $this->siteMapArray[$sectionName][$requiredGroupName]);
                    $results = array_merge($results, $pages);
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
            foreach ($valArr as $requiredGroupName => $pageArr) {
                // now loop through pages until we find the right one
                foreach ($pageArr as $ackshin => $page) {
                    if ($ackshin == $action) {
                        $default->log->debug("Sitemap::getPage current requiredGroup=$requiredGroupName, action=$ackshin");
                        // if anonymous
                        // or the user is in the required group or the user is the SA
                        if ( ($requiredGroupName == "Anonymous") ||
                             ( Permission::userIsInGroupName($requiredGroupName) || 
                               Permission::userIsInGroupName("System Administrators") ) ) {
                            return $page;
                        }
                    }
                }
            }
        }
        // if the function hasn't returned already then the specified
        // userGroup does not have access to the action
        $default->log->info("Sitemap::getPage: access denied for ($action, " . $_SESSION["userID"] . ")");
        return false;
    }
    
    /**
     * Returns the pages in the specified section accessible to the 
     * specified userClass.
     *
     * @param string the section to retrieve pages for
     * @return array the actions for the specified section
     */
    function getSection($sectionName) {
        // check if the section exists
        if (is_array($this->siteMapArray[$sectionName])) {
            // initialise result array
            $results = array();
            // need to loop through all (groupName, page) arrays in this section
            foreach ($this->siteMapArray[$sectionName] as $requiredGroupName => $pages) {
                // lookup groupID for requiredGroupName
                $reqGrpID = lookupID($default->owl_groups_table, "name", "$requiredGroupName");
                // lookup groupIDs for the user
                $groupIDs = lookupGroupIDs($_SESSION["userID"]);
                // loop through the user's groups and check against the requiredGroup
                for ($i = 0; $i<count($groupIDs); $i++) {
                    $default->log->debug("getPage current groupid=" . $groupIDs[$i] . "; reqGrpID=$reqGrpID"); 
                    if ($groupIDs[$i] <= $reqGrpID) {
                        // add this array to the resultset array
                        $results = array_merge($results, $this->siteMapArray[$sectionName][$uc]);
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
            // supplied section not in sitemap
            // TODO: internal error code?
            $_SESSION["errorMessage"] = "$sectionName not in SiteMap!";
            return false;
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
