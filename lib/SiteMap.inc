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
     * Adds a site mapping entry.
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
    function addMap($action, $page, $sectionName, $userClass) {
        global $default;
        
        $realAction = $_ACTIONS[$action];
        // add to map impl.
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
    }
}
?>
