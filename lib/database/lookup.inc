<?php
/**
 * $Id$
 *
 * Contains database helper functions.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
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
	return lookupID($default->status_table, "name", $statusName);
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
    $sql->query("select group_id from $default->users_groups_table where user_id = '$userID'");
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
    $query = "select $selectFieldName from $tableName where $whereFieldName = ?" ;

    $aParams = array($whereFieldValue);
 
    if ($sql->query(array($query, $aParams))) {
        if ($sql->next_record()) {
            return $sql->f($selectFieldName);
        } else {
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
