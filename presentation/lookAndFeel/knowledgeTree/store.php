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
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

$aKeys = array_keys($_POST);
$iPrimaryKey;

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
		while ((strncasecmp("unique_end", $sRowStart, 10) != 0) && ($i <= count($aKeys)))  {					
			$aColumns[$iColumnCount] = $_POST[$aKeys[$i]];			
			$i++;
			$aTypes[$iColumnCount]= $_POST[$aKeys[$i]];			

			
			switch ($aTypes[$iColumnCount]) {
				case 0:
					//id's
					$i++;					
					$aValues[$iColumnCount] = $_POST[$aKeys[$i]];					
					break;
				case 1:
					//normal text
					$i++;					
					$aValues[$iColumnCount] = $_POST[$aKeys[$i]];					
					break;
				case 2:					
					//uncheck checkboxes don't generate any name/value pairs
					//so if the next key doesn't contain the word "value" and it's type
					//is checkbox, then we have an unchecked check box					
					if (strpos($aKeys[$i + 1], "value") === false) {						
						$aValues[$iColumnCount] = false;						
					} else {						
						$i++;						
						$aValues[$iColumnCount] = true;						
					}
					//check box
					break;
				case 3:
					//drop down
					$i++;
					$aValues[$iColumnCount] = $_POST[$aKeys[$i]];					
					break;
			}
			
			//$aValues[$iColumnCount] = $_POST[$aKeys[$i]];
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
						$sQuery .= $aValues[$j] . ", ";
						//boolean
						break;
					case 3:
						$sQuery .= $aValues[$j] . ", ";
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
						$sQuery .= ($aValues[count($aColumns) - 1] ? 1 : 0) . ") ";
						break;
					case 3:					
						//drop down list
						$sQuery .= $aValues[count($aColumns) - 1] . ") ";
						break;
					default:
						break;
			}
			//execute the query
			$sql = $default->db;
			$sql->query($sQuery);
			$iPrimaryKey = $sql->insert_id();
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
					case 2:						
						$sQuery .= ($aValues[$j] ? 1 : 0) . ", ";
						break;
					case 3:
						$sQuery .= $aValues[$j] . ", ";
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
					case 2:											
						$sQuery .= ($aValues[count($aTypes) -1] ? 1 : 0) . " ";
						break;
					case 3:						
						$sQuery .= $aValues[count($aTypes) -1] . " ";
						break;
					default:
						break;
				}			
			$sQuery .= "WHERE id = $iPrimaryKey";			
			//execute the query
			$sql = $default->db;
			$sql->query($sQuery);
		}
		
		//need to do some special checks for folders
		if (strcmp($sTableName, "folders") == 0) {			
				$oFolder = Folder::get($iPrimaryKey);
				$oFolder->update(true);				
		}		
	}
}
redirect(urldecode($fReturnURL));




?>
