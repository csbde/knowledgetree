<?php
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
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
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
	var $aSectionColours;

    /**
     * Whether to use the database to store the sitemap or not
     */
    var $bUseDB;
    
     /**
     * Whether to the user accessing the page is a Sysadmin
     */
    var $isSysAdmin;
    
     /**
     * * Whether to the user accessing the page is a Unitadmin
     */
    var $isUnitAdmin;

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
		$this->aSectionColours = array();
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
     * @param string  the controller action
     * @param string  the corresponding page for this action
     * @param string  the section this page falls under
     * @param int     the minimum access needed to access this page
     * @param string  description of the page for link presentation
     * @param boolean whether this action is enabled or not
     */
    function addPage($sAction, $sPage, $sSectionName, $sRequiredAccess, $sLinkText, $bEnabled = true, $iOrder = 0) {
        if (!$this->bUseDB) {
            $this->aSiteMap[$sSectionName][$sRequiredAccess][$sAction] = array ("page" => $sPage,
                    "description" => $sLinkText,
                    "enabled" => (($bEnabled) ? "1" : "0"),
                    "default" => "0",
                    "order" => $iOrder);
        }
    }
	
	function addSectionColour($sSectionName, $sHtmlElement, $sColour) {
		$this->aSectionColours[$sSectionName][$sHtmlElement] = $sColour;		
	}
	
	function getSectionColour($sSectionName, $sHtmlElement) {		
		return $this->aSectionColours[$sSectionName][$sHtmlElement];
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
    function addDefaultPage($sAction, $sPage, $sSectionName, $sRequiredAccess, $sLinkText, $bEnabled = true, $iOrder = 0) {
        if (!$this->bUseDB) {
            $this->aSiteMap[$sSectionName][$sRequiredAccess][$sAction] = array ("page" => $sPage,
                    "description" => $sLinkText,
                    "enabled" => (($bEnabled) ? "1" : "0"),
                    "default" => "1",
                    "order" => $iOrder);
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

        // if no access control is required
        if ($requiredAccess == None) {
            return true;
        } else {
            // if you're a system administrator, you've got access to everything
           
           // check whether global var intialised
            if ($this->isSysAdmin) {
            	$default->log->debug("-- SiteMap - checking whether a sys admin - GLOBAL VAR SET ");
            	return true; 
            } else if ($this->isSysAdmin = Permission::userIsSystemAdministrator()) {
            	$default->log->debug("-- SiteMap - checking whether a sys admin ..."); 
                return true;
            } else {
                if ($this->isUnitAdmin || $this->isUnitAdmin = Permission::userIsUnitAdministrator()) {
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
           
           // check whether global var intialised
            if ($isSysAdmin || $isSysAdmin = Permission::userIsSystemAdministrator()) {
            	$default->log->debug("-- SiteMap - checking whether a sys admin ..."); 
                return true;
            } else {
                if ($isUnitAdmin || $isUnitAdmin = Permission::userIsUnitAdministrator()) {
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
        $default->log->error("SiteMap::hasPermission THERE IS A HOLE IN THE PAGE LEVEL ACCESS SECURITY MODEL!!!");
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
    function getSectionLinks($sSectionName, $bSortLinks = true) {
        if ($this->bUseDB) {
            return $this->getSectionLinksDB($sSectionName, $bSortLinks);
        } else {
            return $this->getSectionLinksArray($sSectionName, $bSortLinks);
        }
    }

    /**
     * Returns controller links for a section (uses the db)
     *
     * @param string the section to return links for
     */
    function getSectionLinksDB($sSectionName, $bSortLinks) {
        global $default, $lang_err_database;
        $iFolderID = $_REQUEST['fFolderID'];
        $sql = $default->db;
        // lookup sectionID
        $sectionID = lookupID($default->site_sections_table, "name", $sSectionName);
        if ($sectionID) {
            // initialise result array
            $results = array("descriptions" => array(), "links" => array());

            $sQuery = "SELECT link_text, action, access_id FROM $default->sitemap_table WHERE " ./*ok*/
                "section_id = ? AND is_enabled=1 AND is_default=0";
            if ($bSortLinks) {
                $sQuery .= " ORDER BY link_text ASC";
            }
            $aParams = array($sectionID);

            if ($sql->query(array($sQuery, $aParams))) {
                while ($sql->next_record()) {
                    // check permissions
                  $default->log->info("SiteMap:: getSectionLinksDB calling hasPermission with access:  " . $sql->f("access_id"));
                    if ($this->hasPermission($sql->f("access_id"))) {
                        // add this array to the resultset array if there is link text
                        if (strlen($sql->f("link_text")) > 0) {
                            $results["descriptions"][] = $sql->f("link_text");
                            // if fFolderID is set and fFolderID is in the page string
                            // append folderID to the controller link                            
                            $results["links"][] = isset($iFolderID) ? generateControllerLink($sql->f("action"), "fFolderID=$iFolderID") : generateControllerLink($sql->f("action"), "");
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
    function getSectionLinksArray($sSectionName, $bSortLinks) {
        global $default;
        $iFolderID = $_REQUEST['fFolderID'];

        // check if the section exists
        if (is_array($this->aSiteMap[$sSectionName])) {
            // initialise result array
            $results = array("descriptions" => array(), "links" => array());

            // need to loop through all (access, page) arrays in this section
            foreach ($this->aSiteMap[$sSectionName] as $requiredAccess => $pages) {
            	//$default->log->info("SECTION:$sSectionName; access=" . arrayToString($requiredAccess) . ";pages=" . arrayToString($pages));
            	$default->log->info("SiteMap:: getSectionLinksArray calling hasPermission with requiredAccess:  " . $requiredAccess);
                if ($this->hasPermission($requiredAccess)) {
                	
                    foreach ($pages as $action => $pageDetail) {
                        // add this array to the resultset array if there is link text and it is enabled
                        // but not if it is the default page

                        if ((strlen($pages[$action]["description"]) > 0) &&
                                ($pages[$action]["enabled"]) && (!$pages[$action]["default"])) {
                            if (!$bSortLinks) {
	                            $results["descriptions"][$pages[$action]["order"]] = $pages[$action]["description"];
	                            
	                            $results["links"][$pages[$action]["order"]] = isset($iFolderID) ? generateControllerLink($action, "fFolderID=$iFolderID") : generateControllerLink($action, "");
                            } else {
	                            $results["descriptions"][] = $pages[$action]["description"];
	                            
	                            $results["links"][] = isset($iFolderID) ? generateControllerLink($action, "fFolderID=$iFolderID") : generateControllerLink($action, "");
                            }
                        }
                    }
                }
            }
            // now check if we have anything in the results array before returning it
            if (count($results) > 0) {
            	if ($bSortLinks) {
	            	// alpha sort the results 
	            	array_multisort ($results["descriptions"], SORT_ASC,
	            	 				 $results["links"], SORT_ASC); 
					reset ($results);
            	}
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
     * Returns the link text for a page
     *
     * @param string the action to lookup the link text for
     * @return string the link text
     */
    function getPageLinkText($sAction) {
    	global $default;
    	//$sAction = $this->getActionFromPage($sPage);
    	$default->log->info("getPageLinkText action=$sAction");
        // for each section
        foreach ($this->aSiteMap as $section => $valArr) {
            // for each group, page array combination
            foreach ($valArr as $requiredAccess => $pageArr) {
                // now loop through pages until we find the right one
                foreach ($pageArr as $ackshin => $page) {
                    if ($ackshin == $sAction) {
                    	$default->log->info("loop:$ackshin; test:$sAction");
                    	$default->log->info("SiteMap:: getPageLinkText calling hasPermission with access:  " . $requiredAccess);
                        if ($this->hasPermission($requiredAccess)) {
                            return $page["description"];
                        }
                    }
                }
            }
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
            return $this->getPageDB($action);
        } else {
            return $this->getPageArray($action);
        }
    }

    /**
     * Returns the page mapped to the (action, groupName) pair. (uses the db)
     *
     * @param string the action to lookup pages for
     * @return string the page to redirect to, or false if the user doesn't have access to the page
     */
    function getPageDB($action) {
        global $default, $lang_err_database;
        $sql = $default->db;
        // lookup the page and access_id from the sitemap
        if ($sql->query(array("SELECT page, access_id FROM $default->sitemap_table WHERE action = ?", $action))) {/*ok*/
            if ($sql->next_record()) {
                // check permissions
                $default->log->info("SiteMap:: getPageDB calling hasPermission with access:  " . $sql->f("access_id"));
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
    function getPageArray($action) {
        global $default;

        $default->log->info("SiteMap::getPage: checking ($action, " . (isset($_SESSION["userID"]) ? $_SESSION["userID"] : "") . ")");
        $groupIDs = array();

        // for each section
        foreach ($this->aSiteMap as $section => $valArr) {
            //$default->log->debug("Sitemap::getPage section=$section");
            // for each group, page array combination
            foreach ($valArr as $requiredAccess => $pageArr) {
                // now loop through pages until we find the right one
                foreach ($pageArr as $ackshin => $page) {
                    if ($ackshin == $action) {
                        $default->log->debug("Sitemap::getPageArray calling hasPermission current requiredAccess=$requiredAccess, action=$ackshin");
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
            return $this->getSectionNameDB($sRequiredPage);
        } else {
            return $this->getSectionNameArray($sRequiredPage);
        }
    }

    /**
     * Returns the section name of the supplied page (uses the db)
     *
     * @param string the page to lookup the section for
     */
    function getSectionNameDB($sRequiredPage) {
        global $default, $lang_err_database;
        $sql = $default->db;
        // lookup the page and access_id from the sitemap
        $sQuery = "SELECT SSL.name FROM $default->sitemap_table AS S INNER JOIN $default->site_sections_table AS SSL ON S.section_id=SSL.id WHERE S.page = ?";/*ok*/
        $aParams = array($sRequiredPage);
        if ($sql->query(array($sQuery, $aParams))) {
            if ($sql->next_record()) {
                // return the section name
                return $sql->f("name");
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
     * Returns the section name of the supplied page (uses the array)
     *
     * @param string the page to lookup the section for
     */
    function getSectionNameArray($sRequiredPage) {
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
            return $this->getDefaultActionDB($sSectionName);
        } else {
            return $this->getDefaultActionArray($sSectionName);
        }
    }

    /**
     * Returns the default action for the supplied section (uses the db)
     *
     * @param string the section name to return the default action for
     * @return string the controller action for the default page for this section
     */
    function getDefaultActionDB($sSectionName) {
        global $default, $lang_err_database;
        $sql = $default->db;
        // lookup sectionID
        $sectionID = lookupID($default->site_sections_table, "name", $sSectionName);
        if ($sectionID) {
            // lookup the default action for the specified section
            $sQuery = "SELECT action FROM $default->sitemap_table WHERE section_id = ? AND is_default=1 AND is_enabled=1";/*ok*/
            $aParams = array($sectionID);
            if ($sql->query(array($sQuery, $aParams))) {
                if ($sql->next_record()) {
                    // return the section name
                    return $sql->f("action");
                } else {
                    $_SESSION["errorMessage"] = $lang_err_database;
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
     * Returns the default action for the supplied section (uses the array)
     *
     * @param string the section name to return the default action for
     * @return string the controller action for the default page for this section
     */
    function getDefaultActionArray($sSectionName) {
        global $default;
        // check if the section exists
        if (is_array($this->aSiteMap[$sSectionName])) {
            // initialise result array
            $results = array();
            // need to loop through all (groupName, page) arrays in this section
            foreach ($this->aSiteMap[$sSectionName] as $requiredAccess => $pages) {
            	$default->log->debug("Sitemap::getDefaultActionArray calling hasPermission current requiredAccess=$requiredAccess");
                if ($this->hasPermission($requiredAccess)) {
                    foreach ($pages as $action => $pageArray) {
                        if ($pageArray["default"] && $pageArray["enabled"]) {
                            return $action;
                        }
                    }
                } else {
                    return "";
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
            return $this->getActionFromPageDB($sPage);
        } else {
            return $this->getActionFromPageArray($sPage);
        }
    }

    /**
     * Returns the action for a specific page- to enable redirects (uses the db)
     *
     * @param string the page to perform the reverse lookup for
     * @return string the action for this page, false if there is no mapping
     */
    function getActionFromPageDB($sPage) {
        global $default, $lang_err_database;
        $sql = $default->db;

        // lookup the action for the specified page
        if ($sql->query(array("SELECT action FROM $default->sitemap_table WHERE page = ?", $sPage))) {/*ok*/
            if ($sql->next_record()) {
                // return the section name
                return $sql->f("action");
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
     * Returns the action for a specific page- to enable redirects (uses the array)
     *
     * @param string the page to perform the reverse lookup for
     * @return string the action for this page, false if there is no mapping
     */
    function getActionFromPageArray($sPage) {
        global $default;
       // $default->log->debug("Sitemap::getActionFromPage: page=$sPage");
        // for each section
        foreach ($this->aSiteMap as $section => $valArr) {
            //$default->log->debug("Sitemap::getActionFromPage section=$section");
            // for each group, page array combination
            foreach ($valArr as $requiredAccess => $pageArr) {
                //$default->log->debug("Sitemap::getActionFromPage access=$requiredAccess");
                // now loop through pages until we find the right one
                foreach ($pageArr as $action => $page) {
                    //$default->log->debug("Sitemap::getActionFromPage action=$action, reqPage=$sPage; page=" . $page["page"]);
                    if ($sPage == $page["page"]) {
                        //$default->log->debug("Sitemap::getActionFromPage found action=$action for page=$sPage");
                        return $action;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Prints the current site map
     */
    function printMap() {
        if (!$this->bUseDB) {
            return arrayToString($this->aSiteMap);
        }
    }

    /**
     * Writes the current sitemap from the array to the DB
     */
    function syncWithDB() {
        global $default;
        $sql = $default->db;

        // only if we're using the array
        if (!$this->bUseDB) {
            // clear section table
            if ($sql->query("DELETE from $default->site_sections_table")) {
                $default->log->debug("Sitemap::syncWithDB removed sections");
            } else {
                $default->log->error("Sitemap::syncWithDB remove sections failed");
            }
            // clear sitemap table
            if ($sql->query("DELETE from $default->sitemap_table")) {
                $default->log->debug("Sitemap::syncWithDB removed sitemap");
            } else {
                $default->log->error("Sitemap::syncWithDB remove sitemap failed");
            }
            // for each section
            foreach ($this->aSiteMap as $section => $valArr) {
                // insert into the section
                $aFieldValue = array(
                    'name' => $section,
                );
                $id =& DBUtil::autoInsert($default->site_sections_table, $aFieldValues);

                // $default->log->debug("Sitemap::syncWithDB insert=$sSectionSql");

                if (PEAR::isError($id)) {
                    $default->log->error("Sitemap::syncWithDB add section $section failed");
                } else {
                    $sectionID = $id;
                    $default->log->debug("Sitemap::syncWithDB added section $section; $sectionID");
                }

                // for each group, page array combination
                foreach ($valArr as $requiredAccess => $pageArr) {
                    // now loop through all the pages
                    foreach ($pageArr as $action => $page) {
                        $aFieldValues = array(
                            action => $action,
                            page => $page["page"],
                            section_id => $sectionID,
                            access_id => $requiredAccess,
                            link_text => $page["description"],
                            is_default => $page["default"],
                            is_enabled => $page["enabled"],
                        );
                        $id =& DBUtil::autoInsert($default->sitemap_table, $aFieldValues);
                        if (PEAR::isError($id)) {
                            $default->log->debug("Sitemap::syncWithDB sitemap insert failed ($sSiteMapSql)");
                        } else {
                            $default->log->debug("Sitemap::syncWithDb sitemap insert worked for ($action, " . $page["page"] . ")");
                        }
                    }
                }
            }
        }
    }
}
?>
