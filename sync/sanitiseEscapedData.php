<?php
/**
 * Script to remove escape character from text fields
 */
require_once("../config/dmsDefaults.php");
$aFields = array(	"news" => "synopsis,body",
					"dependant_document_instance" => "document_title", 
					"dependant_document_template" => "document_title",
					"documents" => "name,filename,description,full_path",
					"discussion_comments" => "subject,body",
				 	"document_fields" => "name",
				 	"document_fields_link" => "value",
				 	"document_transactions" => "comment",
				 	"document_types_lookup" => "name",
				 	"metadata_lookup" => "name",
				 	"folders" => "name,description,full_path",
				 	"groups_lookup" => "name",
				 	"organisations_lookup" => "name",
				 	"roles" => "name",
				 	"units_lookup" => "name",
				 	"users" => "name",
				 	"web_sites" => "web_site_name");
echo "<pre>";
foreach ($aFields as $table => $fields) {
	$sql = $default->db;
	$aFields = explode(",", $fields);
	foreach ($aFields as $field) {
		//	  select all escaped fields and ids
		$query = "select id, $field from $table where $field like '%\\\\\\%'";
		echo $query . "<br>";
		$sql->query($query);
		while ($sql->next_record()) {
			//	  strip field
			//$cleanField = stripslashes($sql->f($field));
			echo "found offending field=" . $sql->f($field). "<br>";  
			//	  update it
			updateField($table, $sql->f("id"), $field, $sql->f($field));
		}
	} 
}
echo "</pre>";

function updateField($table, $id, $fieldName, $value) {
	global $default;
	$sql = $default->db;
	$query = "update $table set $fieldName='$value' where id=$id";
	if ($sql->query($query)) {
		echo "successful ";
	} else {
		echo "unsuccessful ";
	}
	echo "update query=$query<br>";
}
?>