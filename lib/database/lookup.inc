<?php

/**
 * $Id$
 *
 * Contains database helper functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.database
 */
 
/**
 * Performs an id field lookup on the specified table.
 *
 * @param string the name of table to perform the id lookup.
 * @param string the db field to return.
 * @param mixed the value to perform the lookup for
 * @return int the id of the row in the db with $fieldName=$fieldValue
 */
function lookupID($tableName, $fieldName, $fieldValue){
    return lookupField($tableName, "id", $fieldName, $fieldValue);
}

/**
 * Performs an name field lookup on the specified table.
 *
 * @param string the name of table to perform the id lookup.
 * @param integer the id value to perform the lookup with
 * @return int the id of the row in the db with $fieldName=$fieldValue
 */
function lookupName($tableName, $idFieldValue){
    return lookupField($tableName, "name", "id", $idFieldValue);
}

function lookupStatusID($statusName) {
	global $default;
	return lookupID($default->owl_status_table, "name", $statusName);
}

/**
 * Retrieves the groups that the user is a member of
 *
 * @param int the user to lookup groups for
 * @return array the groupsIDs the user is a member of
 */
function lookupGroupIDs($userID) {
    global $default;
    $groupIDs = array();
    
    $sql = $default->db;
    $sql->query("select group_id from $default->owl_users_groups_table where user_id = '$userID'");
    while($sql->next_record()) {
        $groupIDs[] = $sql->f("group_id");
    }
    return $groupIDs;
}

/**
 * Performs a generic one field lookup on a table
 *
 * @param string the name of the table to perform the lookup on
 * @param string the field to return
 * @param string the field to discriminate against(?!)
 * @param string the field value to return rows for 
 */
function lookupField($tableName, $selectFieldName, $whereFieldName, $whereFieldValue) {
    global $default;
    $sql = $default->db;
    $query = "select $selectFieldName from $tableName where $whereFieldName = '". $whereFieldValue . "'" ;
 
    if ($sql->query($query)) {
        if ($sql->next_record()) {
            return $sql->f($selectFieldName);
        } else {
            $_SESSION["errorMessage"] = "$selectFieldName field lookup retrieval failed ($query).";
            return false;
        }
    } else {
        $_SESSION["errorMessage"] = "lookup query failed ($query).";
        return false;
    }
}


/**
 * Converts an array to a string
 *
 * @param array the array to convert
 * @return string stringified array
 */
function arrayToString($array) {
    ob_start();
    print_r($array);
    $arrToStr = ob_get_contents();
    ob_end_clean();
    return $arrToStr;
}

/**
 * Converts an array to a comma separated string
 *
 * @param array the array to convert
 * @return string a comma separated string of the array values
 */
function arrayToCss($array) {
    $css = "";
    foreach ($array as $key=>$value) {
        $css = $css . $value . ",";
    }
    // trim the last comma
    $css = substr("$css", 0, -1);
    return $css;
}

?>
