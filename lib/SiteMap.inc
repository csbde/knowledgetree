<?php
/**
 * $Id$
 * 
 * Maintains (page, access) access map, as well as (section, page) map.
 * 
 * @version $Revision$ 
 * @author michael@jamwarehouse.com
 * @package dms
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
     * @param $action        the controller action
     * @param $page          the corresponding page for this action
     * @param $sectionName   the section this page falls under
     * @param $groupName     the user group with access to this page
     */
    function addPage($action, $page, $sectionName, $groupName) {
        // add to map
        $this->siteMapArray[$sectionName][$groupName][$action] = $page;
    }
    
    /**
     * Returns the page mapped to the (action, groupName) pair.
     *
     * @param $action        the action to lookup pages for
     * @param $userID        the user requesting the action
     * @return string the page to redirect to, or false if the user doesn't have access to the page
     */
    function getPage($action, $userID) {
        global $default;
        $default->log->debug("Sitemap::getPage function start; action=$action; userID=$userID");
        // for each section
        foreach ($this->siteMapArray as $section => $valArr) {
            $default->log->debug("Sitemap::getPage section=$section");
            // for each group, page array combination
            foreach ($valArr as $requiredGroupName => $pageArr) {
                // lookup the id of the group with access to this page
                $reqGrpID = lookupID($default->owl_groups_table, "name", "$requiredGroupName");
                $default->log->debug("Sitemap::getPage requiredGroupName=$requiredGroupName; id=$reqGrpID");
                // now loop through pages until we find the right one
                foreach ($pageArr as $ackshin => $page) {
                    if ($ackshin == $action) {
                        // lookup the groups this user is in
                        $groupIDs = lookupGroupIDs($userID);
                        // now check if we have the right group access by
                        // looping through the groupID array and returning the page
                        // if the current groupID <= $reqGrpID
                        
                        // FIXME: this won't work once we have lots of groups will it??
                        // we're assuming that the default groups will be created in the right
                        // order, so that groups with sys and unit access have lower ids than
                        // the required ones!
                        for ($i = 0; $i<count($groupIDs); $i++) {
                            $default->log->debug("Sitemap::getPage current groupid=" . $groupIDs[$i] . "; reqGrpID=$reqGrpID"); 
                            if ($groupIDs[$i] <= $reqGrpID) {
                                return $page;
                            }
                        }
                    }
                }
            }
        }
        // if the function hasn't returned already then the specified
        // userGroup does not have access to the action
        $default->log->info("Sitemap::getPage access denied for ($action, $userID)");
        return false;
    }
    
    /**
     * Returns the pages in the specified section accessible to the 
     * specified userClass.
     *
     * @param $sectionName   the section to retrieve pages for
     * @param $userID        the user class to return pages for
     * @return array the actions for the specified section
     */
    function getSection($sectionName, $userID) {
        // check if the section exists
        if (is_array($this->siteMapArray[$sectionName])) {
            // initialise result array
            $results = array();
            // need to loop through all (groupName, page) arrays in this section
            foreach ($this->siteMapArray[$sectionName] as $requiredGroupName => $pages) {
                // lookup groupID for requiredGroupName
                $reqGrpID = lookupID($default->owl_groups_table, "name", "$requiredGroupName");
                // lookup groupIDs for the user
                $groupIDs = lookupGroupIDs($userID);
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
