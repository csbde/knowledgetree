<?php

/**
 * $Id$
 *
 * Contains database helper functions
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
 
/**
 * Performs an id field lookup on the specified table.
 *
 * @param $tableName
 *        the name of table to perform the id lookup.
 * @param $fieldName
 *        the db field to return.
 * @param $fieldValue
 *        the value to perform the lookup for
 * @return the id of the row in the db with $fieldName=$fieldValue
 */
 
function lookupID($tableName, $fieldName, $fieldValue){
    $sql = new Owl_DB();
    $query = "select id from $tableName where $fieldName = '$fieldValue'";
    //echo "lookup.inc::lookupID: about to execute $query<br>";
    if ($sql->query($query)) {
        if ($sql->next_record()) {
            return $sql->f("id");
        } else {
            $_SESSION["errorMessage"] = "id retrieval failed.";
            return false;
        }
    } else {
        $_SESSION["errorMessage"] = "lookup query ($query) failed.";
        return false;
    }
}

function lookupGroupID($userid) {
    global $default;
    $sql = new Owl_DB;
    $sql->query("select group_id from $default->owl_groups_users_table where user_id = '$userid'");
    while($sql->next_record()) 
    {
        $groupid = $sql->f("group_id");
        return $groupid;
    }
}
?>
