<?php
/**
 * Script to remove urldecode and stripslashes from all comments
 */
require_once("../config/dmsDefaults.php");
$sql = $default->db;
$query = "select id, body from discussion_comments";
$sql->query($query);
echo "<pre>";
while ($sql->next_record()) {
	echo "found offending field=" . $sql->f("body") . "<br>";  
	//	  update it
	updateField($sql->f("id"), $sql->f("body"));
}
echo "</pre>";

function updateField($id, $value) {
	global $default;
	$sql = $default->db;
	$value = urldecode($value);
	$query = "update discussion_comments set body='$value' where id=$id";
	if ($sql->query($query)) {
		echo "successful ";
	} else {
		echo "unsuccessful ";
	}
	echo "update query=$query<br>";
}
?>