<?php

/**
* @package lib.visualpatterns
* 
*	Main page of the document management system.  Consists of a 2 x 2 HTML table.
*	Custom defined payloads can be placed in each of the table cells, giving a reasonable
*	amount of layout/design flexibility
*
*	@author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
*	@date 12 December 2002
*	
*/

class PatternMainPage {
	
	function PatternMainPage() {
		
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
	
	function setNorthWestPayload($oNewVal) {		
		$this->oNorthWestPayload = & $oNewVal;		
	}
	
	function setNorthPayload($oNewVal) {		
		$this->oNorthPayload = & $oNewVal;		
	}
	
	function setCentralPayload($oNewVal) {
		$this->oCentralPayload = & $oNewVal;		
	}
	
	function setFormAction($sNewVal) {
		$this->sFormAction = & $sNewVal;
	}
	
	
	/**
	* Render the page in HTML
	*/
	function render() {
        global $default;
        
		$sToRender = 	"<html>
							<head>
                            <meta http-equiv=\"refresh\" content=\"$default->owl_timeout\">
                            <link rel=\"StyleSheet\" HREF=\"$default->owl_root_url\lib\knowledge_tree.css\" type=\"text/css\">
							</head>
							<body>
							<form id=\"MainForm\" action=\"".$this->sFormAction."\" method=\"get\">
								<table border=\"1\" width=\"600\" height=\"800\">\n
									<tr height=\"30%\">\n
										<td width=\"20%\">";
										if (isset($this->oNorthWestPayload)) {											
											$sToRender = $sToRender . $this->oNorthWestPayload->render();
										} else {
											$sToRender = $sToRender . "Northwest payload not set";
										}
										$sToRender = $sToRender . "</td>\n
										<td width = \"80%\">\n";										
										if (isset($this->oNorthPayload)) {
											$sToRender = $sToRender . $this->oNorthPayload->render();
										} else {
											$sToRender = $sToRender . "North payload not set";
										}
										$sToRender = $sToRender . "</td>\n
									</tr>\n
									<tr height=\"70%\" >\n
										<td width=\"20%\">";
										if (isset($this->oWestPayload)) {
											$sToRender = $sToRender . $this->oWestPayload->render();
										} else {
											$sToRender = $sToRender . "West payload not set";
										}
										$sToRender = $sToRender . "</td>\n
										<td width=\"80%\" valign=\"top\">\n";
										if (isset($this->oCentralPayload)) {											
											$sToRender = $sToRender . $this->oCentralPayload->render();
										} else {											
											$sToRender = $sToRender . "Central payload not set";
										}
										$sToRender = $sToRender . "</td>\n
									</tr>\n									
								</table>\n
								</form>
							</body>\n
						</html>\n";
		echo $sToRender;
	}
	
}

?>