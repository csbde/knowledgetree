<?php

/**
* 
* Main page of the document management system.  Consists of a 2 x 2 HTML table.
* Custom defined payloads can be placed in each of the table cells, giving a reasonable
* amount of layout/design flexibility
*
* JavaScript to validate numbers and strings is included by default
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 12 December 2002
* @package lib.visualpatterns
*	
*/

class PatternMainPage {
	
	function PatternMainPage() {
		$this->sFormAction = "$default->owl_root_url/control.php";
	}
	
	/** property holder for $oNorthWestPayload */
	var $oNorthWestPayload;
	/** property holder for $oWestPayload */
	var $oWestPayload;
	/** property holder for $oEastPayload */
	var $oCentralPayload;
	/** property holder for $oNorthPayload */
	var $oNorthPayload;
	/** form action*/
	var $sFormAction;
	
	var $sErrorMessage;
	
	function setNorthWestPayload($oNewVal) {		
		$this->oNorthWestPayload = & $oNewVal;		
	}
	
	function setNorthPayload($oNewVal) {		
		$this->oNorthPayload = & $oNewVal;		
	}
	
	function setCentralPayload($oNewVal) {
		$this->oCentralPayload = & $oNewVal;		
	}
	
	function setWestPayload($oNewVal) {		
		$this->oWestPayload = & $oNewVal;		
	}
	
	function setFormAction($sNewVal) {
		$this->sFormAction = & $sNewVal;
	}	
	
	function setErrorMessage($sNewValue) {
		$this->sErrorMessage = $sNewValue;
	}
	
	
	/**
	* Render the page in HTML
	*/
	function render() {
        global $default;
        
		$sToRender = 	"<html>\n" .
							"<head>\n" .
                            "<meta http-equiv=\"refresh\" content=\"" . ($default->owl_timeout+3) . "\">\n" .
                            "<link rel=\"StyleSheet\" HREF=\"$default->owl_ui_url/stylesheet.css\" type=\"text/css\">\n".
							PatternMainPage::getNumberStringValidationJavaScript() . "\n" .
							"</head>\n" .
							"<body>\n" .
							"<form id=\"MainForm\" action=\"".$this->sFormAction."\" method=\"POST\">\n" .
								"<table border=\"1\" width=\"600\" height=\"600\">\n " .
									"<tr height=\"18%\">\n".
										"<td width=\"5%\">\n";
										if (isset($this->oNorthWestPayload)) {											
											$sToRender = $sToRender . "\n" . $this->oNorthWestPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nNorthwest payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
										<td width = \"90%\">\n";										
										if (isset($this->oNorthPayload)) {
											$sToRender = $sToRender . "\n" . $this->oNorthPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nNorth payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
									</tr>\n
									<tr border = \"1\" height=\"72%\" >\n										
										<td border = \"1\" width=\"5%\">";
										if (isset($this->oWestPayload)) {											
											$sToRender = $sToRender . "\n" . $this->oWestPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nWest payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
										<td width=\"80%\" valign=\"top\">\n";
										if (isset($this->oCentralPayload)) {
											if (isset($this->sErrorMessage)) {
												$sToRender .= "<table border=\"0\"><tr><td><p class=\"errorText\">$this->sErrorMessage</td></tr></table>\n ";
											}
											$sToRender = $sToRender . "\n" . $this->oCentralPayload->render() . "\n";
										} else {											
											$sToRender = $sToRender . "\nCentral payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n" .
									"</tr>\n" .								
								"</table>\n" .
								"</form>\n" .
							"</body>\n" .
						"</html>\n";
		echo $sToRender;
	}

	function getNumberStringValidationJavaScript() {
		return "<script language=\"JavaScript\">\n".
						"<!--\n" .
						"function validateString(field, msg, min, max) {\n" .
							"\tif (!min) { min = 1 }\n" .
							"\tif (!max) { max = 65535 }\n\n" .
			
							"\tif (!field.value || field.value.length < min || field.value.max > max) {\n" .
							"\t\talert(msg);\n" .
							"\t\tfield.focus();\n" .
							"\t\tfield.select();\n" .
							"\t\treturn false;\n" .
							"\t}\n" .
						"\treturn true;\n" .
						"}\n\n" .
						
						"function validateNumber(field, msg, min, max) {\n" .
							"\tif (!min) { min = 0 }\n" .
							"\tif (!max) { max = 255 }\n" .
							
							"\tif ( (parseInt(field.value) != field.value) || field.value.length < min || field.value.length > max) {\n" .
								"\t\talert(msg);\n" .
								"\t\tfield.focus();\n" .
								"\t\tfield.select();\n" .
								"\t\treturn false;\n" .
							"\t}\n" .
						"\treturn true;\n" .
						"}\n" .
						"//-->\n" .
					"</script>\n";
	}
	
}

?>