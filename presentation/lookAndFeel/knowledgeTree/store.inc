<?php
/**
 * $Id$
 *
 * Contains the logic for constructing queries from Pattern generated forms.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 */
 
function constructQuery($aKeys, $aSuppliedValues = null) {
	global $default;
	
	$aQuery = array();
	for ($i = 0; $i < count($aKeys); $i++) {	
		$sRowStart = $aKeys[$i];		
		$pos = strncasecmp("unique_start", $sRowStart, 12);
		
		if ($pos == 0) {		
            $sRandomString = substr($sRowStart, 13);
            if (!array_key_exists("pelfq_" . $sRandomString . "_tn", $_SESSION)) {
                print "Hack attempt!  Session data not set up for store.\n";
                return false;
            }
            if (!array_key_exists("pelfq_" . $sRandomString . "_id", $_SESSION)) {
                print "Hack attempt!  Session data not set up for store.\n";
                return false;
            }
            if (!array_key_exists("pelfq_" . $sRandomString . "_columns", $_SESSION)) {
                print "Hack attempt!  Session data not set up for store.\n";
                return false;
            }
			
            $aColumns = array();
			$aValues = array();
			$aTypes = array();
			
			// $iPrimaryKey = $_POST[$aKeys[++$i]];		
			// $sTableName = $_POST[$aKeys[++$i]];
            $iPrimaryKey = $_SESSION["pelfq_" . $sRandomString . "_id"];
            $sTableName = $_SESSION["pelfq_" . $sRandomString . "_tn"];
            $aColumnNames = $_SESSION["pelfq_" . $sRandomString . "_columns"];
			
			$i++;
			$iColumnCount = 0;
			
			//get all the values for the table
			while ((strncasecmp("unique_end", $sRowStart, 10) != 0) && ($i <= count($aKeys)))  {					
				$aColumns[$iColumnCount] = $_POST[$aKeys[$i]];			
                if (!in_array($aColumns[$iColumnCount], $aColumnNames)) {
                    print "Hack attempt! $aColumns[$iColumnCount] is not in "; var_dump($aColumnNames);
                    return false;
                }
				$aTypes[$iColumnCount]= $_POST[$aKeys[++$i]];
				
				switch ($aTypes[$iColumnCount]) {
					case 0:
						//id's
						$aValues[$iColumnCount] = $_POST[$aKeys[++$i]];					
						break;
					case 1:
						//normal text
						$aValues[$iColumnCount] = $_POST[$aKeys[++$i]];					
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
					case 4:
						// user supplied values
						$aValues[$iColumnCount] = $_POST[$aKeys[++$i]];					
						break;
				}
				
				$sRowStart = $aKeys[++$i];			
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
							$sQuery .= "'" . $aValues[$j] . "', ";
							break;
						case 2:
							//boolean												
							$sQuery .= $aValues[$j] . ", ";
							break;
						case 3:
							//drop down list						
							$sQuery .= $aValues[$j] . ", ";
							break;
						case 4:
							// user supplied values
						    $sQuery .= $aSuppliedValues[$aColumns[$j]] . ", ";
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
							$sQuery .= "'" . $aValues[count($aColumns) - 1] . "') ";
							break;
						case 2:						
							//boolean
							$sQuery .= ($aValues[count($aColumns) - 1] ? 1 : 0) . ") ";
							break;
						case 3:					
							//drop down list
							$sQuery .= $aValues[count($aColumns) - 1] . ") ";
							break;
						case 4:
							// user supplied values
						    $sQuery .= $aSuppliedValues[$aColumns[count($aColumns) - 1]] . ", ";
						    break;							
						default:
							break;
				}
				// add query to array
				$aQuery[] = $sQuery;
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
							$sQuery .= "'" . $aValues[$j] . "', ";
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
							$sQuery .= "'" . $aValues[count($aTypes) -1] . "' ";
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
				// add query to array
				$aQuery[] = $sQuery;
			}
		}
	}
	return $aQuery;
}
?>
