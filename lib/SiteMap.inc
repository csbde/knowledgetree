<?php
/**
 * $Id$
 * 
 * Maintains page-level access map, as well as section, page map.
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
     * @param $action
     *        the controller action
     * @param $page
     *        the corresponding page for this action
     * @param $sectionName
     *        the section this page falls under
     * @param $userClass
     *        the user class with access to this page
     */
    function addPage($action, $page, $sectionName, $userClass) {
        // add to map
        $this->siteMapArray[$sectionName][$userClass][$action] = $page;
    }
    
    /**
     * Returns the page mapped to the action, userClass pair.
     *
     * @param $action
     *        the action to lookup pages for
     * @param $userClass
     *        the userclass to perform page level validation against
     * @return
     *        the page to redirect to, or false if the user class doesn't
     *        have access to the page
     */
    function getPage($action, $userClass) {
        // map incoming userClass to number
        $uc = constant($userClass);

        // iterate through multidim sitemap array
        foreach ($this->siteMapArray as $section => $valArr) {
            foreach ($valArr as $userAccess => $pageArr) {
                if ($uc <= constant($userAccess)) {
                    // now loop through pages until we find the right one
                    foreach ($pageArr as $ackshin => $page) {
                        if ($ackshin == $action) {
                            return $page;
                        }
                    }
                }
            }
        }
        // if the function hasn't returned already then the specified
        // userClass does not have access to the action
        return false;
    }
    
    /**
     * Returns the pages in the specified section accessible to the 
     * specified userClass.
     *
     * @param $sectionName
     *        the section to retrieve pages for
     * @param $userClass
     *        the user class to return pages for
     * @return
     *        an array containing the actions for the specified section
     */
    function getSection($sectionName, $userClass) {
        // check if the section exists
        if (is_array($this->siteMapArray[$sectionName])) {
            // initialise result array
            $results = array();
            // need to loop through all user class arrays in this section
            foreach ($this->siteMapArray[$sectionName] as $uc => $pages) {
                if (constant($userClass) <= constant($uc)) {
                    // add this array to the resultset array
                    $results = array_merge($results, $this->siteMapArray[$sectionName][$uc]);
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
            return false;
        }
    }
    
    /**
     * Prints the current site map
     */
    function printMap() {
        print_r($this->siteMapArray);
    }
}
?>
