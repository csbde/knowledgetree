<?php

/**
* Presentation information for Deleting a Unit 
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// gets the delete stuff
function getPage() {	
	global $default;
	$oSystem = new System;
	
	global $default;
		
	$aRowNames = array ("LDAP Server: ", 
			       "LDAP Root DN: ",
			       "Email Server:", 
			       "Email From: ", 
			       "Email From Name: ", 
			       "File System Root: ", 
			       "Document Root: ",
			       "Language Directory: ",
			       "User Interface Directory: ",
			       "Root URL: ",
			       "Graphics URL: ",
			       "User Interface URL: ",
			       "Use File System: ",
			       "Default Language: ",
			       "Session Timeout: ");
	
	$aNames = array("fldapServer",
			"fldapRootDn",
			"femailServer",
			"femailFrom",
			"femailFromName",
			"ffilesystemRoot",
			"fdocumentRoot",
			"flanguageDirectory",
			"fuiDirectory",
			"frootUrl",
			"fgraphicsUrl",
			"fuiUrl",
			"fuseFs",
			"fdefaultLanguage",
			"fsessionTimeout");
	
	$aValue = $aNames;
	// todo
	
	// create a for loop that'll print out the names and input field with the value in
	// use the system table to get the value into the text field, by passing it the name
	// then loop a database entry to add everything...
	// and then when submit...run a set thingy..
		
			
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><b>Current System Settings: </b></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	for($i = 0; $i < count($aNames); $i++){
	
	$sToRender .= "<td>". ($i+1) .".</td><td>" .$aRowNames[$i] ." </td><td><input size = \"40\" type=\"text\" name=\"". $aNames[$i] ."\" value =\"". $oSystem->get(substr($aNames[$i],1)) ."\"></td>\n";
	$sToRender .= "</tr>\n";
	
	}
	//$oSystem->generateRequiredFieldValidation($aNames,$aRowNames);
	// do some validation
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";			
		
	for ($i = 0; $i < count($aNames); $i++) {			
			$sToRender .= "\tif (!(validRequired(document.MainForm." .$aNames[$i] . ", '" . $aRowNames[$i] . "'))) {\n";
			$sToRender .= "\t\treturn false;\n\t}\n";
				
	}
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td></td><td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/update.gif\" value=\"Submit\" /></td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


	


?>