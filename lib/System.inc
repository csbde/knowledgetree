<?php

/**
 * $Id$
 * 
 * Stores system settings
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */
class System {
    /**
     * Retrieves a system setting
     *
     * @param string setting name
     * @return string the setting
     */
    function get($sSettingName) {
        global $default;
        
        // select the value from the db
        return lookupField($default->owl_system_settings_table, "value", "name", $sSettingName);
    }
    
    /**
     * Sets a setting, if $sSettingName exists then the value is overwritten
     * else a new setting is inserted.
     *
     * @param string the name of the system setting
     * @param string the value of the system setting
     */
    function set($sSettingName, $sSettingValue) {
        global $default, $lang_err_database;
        
        if (lookupField($default->owl_system_settings_table, "name", "name", $sSettingName)) {
            // the setting exists, so overwrite it
            if ($default->db->query("UPDATE $default->owl_system_settings_table SET value='$sSettingValue' where name='$sSettingName'")) {
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
                return false;
            }            
        } else {
            // the setting doesn't exist, lets insert it
            if ($default->db->query("INSERT INTO $default->owl_system_settings_table (name, value) VALUES ('$sSettingName', '$sSettingValue')")) {
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
                return false;
            }
        }
    }
    
    function generateRequiredFieldValidation($aValidationColumnNames,$aValidationDisplayColumnNames) {
    		
		$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
		$sToRender .= "function validateForm(theForm) {\n";			
		for ($i = 0; $i < count($aValidationColumnNames); $i++) {			
			$sToRender .= "\tif (!(validRequired(document.MainForm." . $aValidationColumnNames[$i] . ", '" . $aValidationDisplayColumnNames[$i] . "'))) {\n";
			$sToRender .= "\t\treturn false;\n\t}\n";
				
		}
		$sToRender .= "return true;\n}\n";
		
		$sToRender .= "//-->\n</script>\n\n";
		
		//return $sToRender;
                
	}
}
?>
