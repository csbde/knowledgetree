<?php

require_once("$default->owl_fs_root/lib/security/permission.inc");

// define access constants
define("None", 1);
define("Guest", 2);
define("User", 3);
define("UnitAdmin", 4);
define("SysAdmin", 5);

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
    var $aSiteMap;
    
    /**
     * Whether to use the database to store the sitemap or not
     */
    var $bUseDB;
    
    /**
     * Constructs a new SiteMap
     * If the db is not being used, the array is initialised.
     *
     * @param boolean whether to use the database to store the sitemap or not
     */
    function SiteMap($bUseDB) {
        $this->bUseDB = $bUseDB;
        if (!$this->bUseDB) {
            $this->aSiteMap = array();
        }
    }
    
    /**
     * Sets the database flag
     *
     * @param boolean whether to use the database to store the sitemap or not     
     */
    function setUseDB($bUseDB) {
        $this->bUseDB = $bUseDB;
    }
    
    /**
     * Returns the database flag
     */
    function getUseDB() {
        return $this->bUseDB;
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
        if (!$this->bUseDB) {
            $this->aSiteMap[$sectionName][$requiredAccess][$action] = array ("page" => $page, 
                                                                             "description" => $description, 
                                                                             "default" => false);
        }
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
        if (!$this->bUseDB) {
            $this->aSiteMap[$sectionName][$requiredAccess][$action] = array ("page" => $page, 
                                                                             "description" => $description, 
                                                                             "default" => true);
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
        global $default;
        $default->log->debug("SiteMap::hasPermission: reqAcces=$requiredAccess");
        // if no access control is required
        if ($requiredAccess == None) {
            return true;
        } else {
            // if you're a system administrator, you've got access to everything
            if (Permission::userIsSystemAdministrator()) {
                return true;
            } else {              
                if (Permission::userIsUnitAdministrator()) {
                    // if you're a unit administrator, then you have access to everything
                    // including and less than UA
                    return ($requiredAccess <= UnitAdmin) ?  true : false;
                } else if (Permission::userIsGuest()) {
                    return ($requiredAccess == Guest) ? true : false;
                } else {
                    // you're a "normal" unit user
                    return ($requiredAccess <= User) ? true : false;
                }
            }
        }
        // shouldn't ever get here
        $default->log->error("SiteMap::hasPermission THERE IS A WHOLE IN THE PAGE LEVEL ACCESS SECURITY MODEL!!!");
        $default->log->error("SiteMap::hasPermission requiredAccess=$requiredAccess; userID=" . $_SESSION["userID"]);
        // return false anyway
        return false;
    }
    
    /**
     * Returns controller links for a section.
     * Checks whether to use the db or not and calls the appropriate method
     * 
     * @param string the section to return links for
     */    
    function getSectionLinks($sSectionName) {
        if ($this->bUseDB) {
            return $this->getSectionLinksUsingDB($sSectionName);
        } else {
            return $this->getSectionLinksUsingArray($sSectionName);
        }
    }
    
    /**
     * Returns controller links for a section (uses the db)
     *
     * @param string the section to return links for
     */
    function getSectionLinksUsingDB($sSectionName) {
        global $default, $lang_err_database;
        $sql = new Owl_DB();
        // lookup sectionID
        $sectionID = lookupID($default->owl_site_sections_table, "name", $sSectionName);
        if ($sectionID) {
            // initialise result array
            $results = array("descriptions" => array(), "links" => array());
            
            if ($sql->query("SELECT link_text, action, access_id FROM $default->owl_sitemap_table WHERE section_id=$sectionID")) {                
                while ($sql->next_record()) {
                    // check permissions
                    if ($this->hasPermission($sql->f("access_id"))) {
                        // add this array to the resultset array if there is link text
                        if (strlen($sql->f("link_text")) > 0) {
                            $results["descriptions"][] = $sql->f("link_text");
                            $results["links"][] = generateControllerUrl($sql->f("action"));
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
                $_SESSION["errorMessage"] = $lang_err_database;
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = "No such section name ($sSectionName) in the sitemap";
            return false;
        }
    }
    
    /**
     * Returns controller links for a section (uses the array)
     *
     * @param string the section to return links for
     */
    function getSectionLinksUsingArray($sSectionName) {
        global $default;
        // check if the section exists
        if (is_array($this->aSiteMap[$sSectionName])) {
            // initialise result array
            $results = array("descriptions" => array(), "links" => array());
            
            // need to loop through all (access, page) arrays in this section
            foreach ($this->aSiteMap[$sSectionName] as $requiredAccess => $pages) {
                if ($this->hasPermission($requiredAccess)) {
                    foreach ($pages as $action => $pageDetail) {
                        // add this array to the resultset array if there is link text
                        if (strlen($pages[$action]["description"]) > 0) {
                            $results["descriptions"][] = $pages[$action]["description"];
                            $results["links"][] = generateControllerUrl($action);
                        }
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
     * Checks whether to use the db or not and calls the appropriate method     
     *
     * @param string the action to lookup pages for
     * @return string the page to redirect to, or false if the user doesn't have access to the page
     */    
    function getPage($action) {
        if ($this->bUseDB) {
            return $this->getPageUsingDB($action);
        } else {
            return $this->getPageUsingArray($action);
        }
    }
    
    /**
     * Returns the page mapped to the (action, groupName) pair. (uses the db)
     *
     * @param string the action to lookup pages for
     * @return string the page to redirect to, or false if the user doesn't have access to the page
     */
    function getPageUsingDB($action) {
        global $default, $lang_err_database;
        $sql = new Owl_DB();
        // lookup the page and access_id from the sitemap
        if ($sql->query("SELECT page, access_id FROM $default->owl_sitemap_table WHERE action='$action'")) {                
            if ($sql->next_record()) {
                // check permissions
                if ($this->hasPermission($sql->f("access_id"))) {
                    // return the page
                    return $sql->f("page");
                }
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
    }
    
    /**
     * Returns the page mapped to the (action, groupName) pair. (uses the array)
     *
     * @param string the action to lookup pages for
     * @return string the page to redirect to, or false if the user doesn't have access to the page
     */
    function getPageUsingArray($action) {
        global $default;
        
        $default->log->info("SiteMap::getPage: checking ($action, " . $_SESSION["userID"] . ")");
        $groupIDs = array();
        
        // for each section
        foreach ($this->aSiteMap as $section => $valArr) {
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
     * Returns the section name of the supplied page
     * Checks whether to use the db or not and calls the appropriate method     
     *
     * @param string the page to lookup the section for
     */
    function getSectionName($sRequiredPage) {
        if ($this->bUseDB) {
            return $this->getSectionNameUsingDB($sRequiredPage);
        } else {
            return $this->getSectionNameUsingArray($sRequiredPage);
        }
    }

    /**
     * Returns the section name of the supplied page (uses the db)
     *
     * @param string the page to lookup the section for
     */
    function getSectionNameUsingDB($sRequiredPage) {   
    }
    
    /**
     * Returns the section name of the supplied page (uses the array)
     *
     * @param string the page to lookup the section for
     */
    function getSectionNameUsingArray($sRequiredPage) {   
        global $default;
        // for each section
        foreach ($this->aSiteMap as $section => $valArr) {
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
     * Returns the default action for the supplied section
     * Checks whether to use the db or not and calls the appropriate method     
     *
     * @param string the section name to return the default action for
     * @return string the controller action for the default page for this section
     */
    function getDefaultAction($sSectionName) {
        if ($this->bUseDB) {
            return $this->getDefaultActionUsingDB($sSectionName);
        } else {
            return $this->getDefaultActionUsingArray($sSectionName);
        }
    }
    
    /**
     * Returns the default action for the supplied section (uses the db)
     *
     * @param string the section name to return the default action for
     * @return string the controller action for the default page for this section
     */
    function getDefaultActionUsingDB($sSectionName) {
    }
    
    /**
     * Returns the default action for the supplied section (uses the array)
     *
     * @param string the section name to return the default action for
     * @return string the controller action for the default page for this section
     */
    function getDefaultActionUsingArray($sSectionName) {    
        global $default;
        // check if the section exists
        if (is_array($this->aSiteMap[$sSectionName])) {
            // initialise result array
            $results = array();
            // need to loop through all (groupName, page) arrays in this section
            foreach ($this->aSiteMap[$sSectionName] as $requiredAccess => $pages) {
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
            $_SESSION["errorMessage"] = "$sSectionName not in SiteMap!";
            return false;
        }        
    }

    /**
     * Returns the action for a specific page- to enable redirects
     * Checks whether to use the db or not and calls the appropriate method
     *
     * @param string the page to perform the reverse lookup for
     * @return string the action for this page
     */
    function getActionFromPage($sPage) {
        if ($this->bUseDB) {
            return $this->getActionFromPageUsingDB($sPage);
        } else {
            return $this->getActionFromPageUsingArray($sPage);
        }
    }
    
    /**
     * Returns the action for a specific page- to enable redirects (uses the db)
     *
     * @param string the page to perform the reverse lookup for
     * @return string the action for this page
     */
    function getActionFromPageUsingDB($sPage) {
    }
    
    /**
     * Returns the action for a specific page- to enable redirects (uses the array)
     *
     * @param string the page to perform the reverse lookup for
     * @return string the action for this page
     */
    function getActionFromPageUsingArray($sPage) {    
        global $default;
        $default->log->debug("Sitemap::getActionFromPage: page=$sPage");
        // for each section
        foreach ($this->aSiteMap as $section => $valArr) {
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
        return arrayToString($this->aSiteMap);
    }
}
?>
