<?php

/**
 * $Id$
 *
 * Contains database helper functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com">Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
 
/**
 * Performs an id field lookup on the specified table.
 *
 * @param $tableName    the name of table to perform the id lookup.
 * @param $fieldName    the db field to return.
 * @param $fieldValue   the value to perform the lookup for
 * @return the id of the row in the db with $fieldName=$fieldValue
 */
function lookupID($tableName, $fieldName, $fieldValue){
    return lookupField($tableName, "id", $fieldName, $fieldValue);
}

/**
 * Retrieves the groups that the user is a member of
 *
 * @param $userID    the user to lookup groups for
 * @return an array containing the groupsIDs the user is a member of
 */
function lookupGroupIDs($userID) {
    global $default;
    $groupIDs = array();
    
    $sql = new Owl_DB;
    $sql->query("select group_id from $default->owl_groups_users_table where user_id = '$userID'");
    while($sql->next_record()) {
        $groupIDs[] = $sql->f("group_id");
    }
    return $groupIDs;
}

/**
 * Performs a generic one field lookup on a table
 *
 * @param $tableName          the name of the table to perform the lookup on
 * @param $selectFieldName    the field to return
 * @param $whereFieldName     the field to discriminate against(?!)
 * @param $whereFieldValue    the field value to return rows for 
 *                            (NOTE: the caller is responsible for quoting this value)
 */
function lookupField($tableName, $selectFieldName, $whereFieldName, $whereFieldValue) {
    global $default;
    $sql = new Owl_DB();
    $query = "select $selectFieldName from $tableName where $whereFieldName = '". $whereFieldValue . "'" ;
    $default->log->debug("lookup.inc::lookupField query=$query"); 
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
 * @param $array    the array to convert
 * @return a comma separated string of the array values
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

/**
 * Returns the current date time
 *
 * @return String the current date time (Y-m-d H:i:s)
 */
function getCurrentDateTime() {
    return date("Y-m-d H:i:s", time());
}
?>
