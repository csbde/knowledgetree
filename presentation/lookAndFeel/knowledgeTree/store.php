<?php
/**
* Page used by all editable patterns to actually perform the db insert/updates
*
* Expected form variables
*	o fReturnURL
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 27 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*/
require_once("../../../config/dmsDefaults.php");

$aKeys = array_keys($_POST);



for ($i = 0; $i < count($aKeys); $i++) {	
	$sRowStart = $aKeys[$i];		
	$pos = strncasecmp("unique_start", $sRowStart, 12);
	
	if ($pos == 0) {		
		$aColumns;
		$aValues;
		$aTypes;
		settype($aColumns, "array");
		settype($aValues, "array");
		settype($aTypes, "array");
		
		$i++;		
		$iPrimaryKey = $_POST[$aKeys[$i]];		
		
		$i++;		
		$sTableName = $_POST[$aKeys[$i]];
		
		
		$i++;		
		$iColumnCount = 0;
		
		//get all the values for the table
		while (strncasecmp("unique_end", $sRowStart, 10) != 0) {			
			$aColumns[$iColumnCount] = $_POST[$aKeys[$i]];
			$i++;
			$aTypes[$iColumnCount]= $_POST[$aKeys[$i]];
			$i++;
			
			//uncheck checkboxes don't generate any name/value pairs
			//so if the next key doesn't contain the word "value" and it's type
			//is checkbox, then we have an unchecked check box
			/*echo "Type: " . $aTypes[$iColumnCount] . "<br>";
			if ($aTypes[$iColumnCount] == 2) {				
				if (strpos("value", $aKeys[$i]) == 0) {
					//uncheck check box
					$aValues[$iColumnCount] = 0;
				} else {
					$aValues[$iColumnCount] = 1;
					$i++;
				}
			} else {				
				$aValues[$iColumnCount] = $_POST[$aKeys[$i]];
				$i++;
			}*/			
			
			
			$aValues[$iColumnCount] = $_POST[$aKeys[$i]];
			$i++;
			
			$sRowStart = $aKeys[$i];			
			
			$iColumnCount++;
		}
		
		if ($iPrimaryKey < 0) {
			//perform an insert
			$sQuery = "INSERT INTO $sTableName (";
			for ($j = 0; $j < count($aColumns) - 1; $j++) {
				$sQuery .= $aColumns[$j] . ", ";
			}
			$sQuery .= $aColumns[count($aColumns) -1] . ") VALUES (";
			
			for ($j = 0; $j < count($aColumns) - 1; $j++) {
				switch ($aTypes[$j]) {
					case 0 :
						$sQuery .= $aValues[$j] . ", ";
						break;
					case 1:
						//text
						$sQuery .= "'" . addslashes($aValues[$j]) . "', ";
						break;
					case 2:
						//boolean
						break;
					case 3:
						//drop down list
						break;
					default:
						break;
				}
			}
			switch ($aTypes[count($aColumns) - 1]) {
				case 0:
						//id
						$sQuery .= $aValues[count($aColumns) - 1] . ") ";
						break;
				case 1:
						//text
						$sQuery .= "'" . addslashes($aValues[count($aColumns) - 1]) . "') ";
						break;
					case 2:
						//boolean
						break;
					case 3:
						//drop down list
						break;
					default:
						break;
			}
			//execute the query
			$sql = $default->db;
			$sql->query($sQuery);
		} else {
			//perform an update
			$sQuery = "UPDATE $sTableName SET ";
			for ($j = 0; $j < count($aColumns) -1; $j++) {
				$sQuery .= $aColumns[$j] . " = ";
				switch ($aTypes[$j]) {
					case 0:
						//id
						$sQuery .= $aValues[$j] . ", ";
						break;
					case 1:
						$sQuery .= "'" . addslashes($aValues[$j]) . "', ";
						break;
					default:
						break;
				}
			}
			$sQuery .= $aColumns[count($aTypes) -1] . " = ";
			switch ($aTypes[count($aTypes) -1]) {
				case 0:
						//id
						$sQuery .= $aValues[count($aTypes) -1] . " ";
						break;
					case 1:
						$sQuery .= "'" . addslashes($aValues[count($aTypes) -1]) . "' ";
						break;
					default:
						break;
				}			
			$sQuery .= "WHERE id = $iPrimaryKey";
			//execute the query
			$sql = $default->db;
			$sql->query($sQuery);
		}
		
	}
}
redirect(urldecode($fReturnURL));




?>
