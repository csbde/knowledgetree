<?php
/**
 * $Id$
 *
 * Stores system settings.
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
 * @package lib
 */ 
class System {
    var $aSettings = array("ldapServer", "ldapRootDn", "ldapServerType",
                           "ldapDomain", "ldapSearchUser", "ldapSearchPassword", 
                       	   "emailServer", "emailFrom", "emailFromName", "emailAdmin", "emailAdminName",
                           "documentRoot", "uiDirectory", 
                           "rootUrl", "graphicsUrl", "uiUrl",
                           "sessionTimeout", "contentPaneScrolling", "folderHidingFlag");
    var $aLabels = array ("LDAP Server", "LDAP Root DN", "LDAP Server Type (iPlanet OR ActiveDirectory)",
                          "LDAP Domain", "LDAP Search User (AD)", "LDAP Search Password",
                          "Email Server", "Default Email Address", "Default Email Name", "Administrator Email Address", "Administrator Name",
                          "Document Root", "User Interface Directory",
                          "Root URL", "Graphics URL", "User Interface URL",
                          "Session Timeout", "Content Pane Scrolling Enabled", "Hide Unreadable Folders?");
                                                         
    /**
     * Returns true if there are entries in the system_settings table
     */
    function initialised() {
        global $default;
        
        if ($default->db->query("SELECT count(*) AS count FROM system_settings WHERE name<>'lastIndexUpdate' AND name<>'filesystemRoot' AND name<>'knowledgeTreeVersion' AND value<>''")) {/*ok*/
            $default->db->next_record();
            return ($default->db->f("count") > 0) ? true : false;
        }
    }
    
    /**
     * Retrieves a system setting
     *
     * @param string setting name
     * @return string the setting
     */
    function get($sSettingName) {
        // select the value from the db
        $value = lookupField("system_settings", "value", "name", $sSettingName);
        // if there are semi-colon delimited values, return as an array
        if (strstr($value, ";")) {
        	return explode(";", $value);
        } else {
        	return $value;
        }
    }
    
    function getInt($sSettingName) {
        // select the value from the db
        return (integer)lookupField("system_settings", "value", "name", $sSettingName);
    }    
    
    /**
     * Sets a setting, if $sSettingName exists then the value is overwritten
     * else a new setting is inserted.
     *
     * @param string the name of the system setting
     * @param string the value of the system setting
     */
    function set($sSettingName, $sSettingValue) {
        global $default;
        if (lookupField($default->system_settings_table, "name", "name", $sSettingName)) {
            $res = &DBUtil::whereUpdate($default->system_settings_table,
                array('value' => $sSettingValue), array('name' => $sSettingName));
            if (PEAR::isError($res)) {
                return false;
            }
            return true;
        } else {
            $aFieldValues = array(
                'name' => $sSettingName,
                'value' => $sSettingValue,
            );
            $res = DBUtil::autoInsert($default->system_settings_table, $aFieldValues);
            if (PEAR::isError($res)) {
                return false;
            }
            return true;
        }
    }    
}
?>
