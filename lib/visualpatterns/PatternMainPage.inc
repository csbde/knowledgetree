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
        global $default;
        
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
	/** form enctype */
	var $sFormEncType;
	/** whether the form has fields that require javascript checking */
	var $bHasRequiredFields = false;
	
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
	
	function setFormEncType($sNewValue) {
		$this->sFormEncType = $sNewValue;
	}
	
	function setHasRequiredFields($bNewValue) {
		$this->bHasRequiredFields = $bNewValue;
	}
	
	
	/**
	* Render the page in HTML
	*/
	function render() {
        global $default;
        
		$sToRender = 	"<html>\n" .
							"<head>\n" .
                            "<meta http-equiv=\"refresh\" content=\"" . ($default->owl_timeout+3) . "\">\n" .
                            "<link rel=\"stylesheet\" href=\"$default->owl_ui_url/stylesheet.php\">\n".                            
							PatternMainPage::getJavaScript() . "\n" .							
							"</head>\n" .
							"<body marginleft=\"0\" marginheight=\"0\">\n";
        // hack to circumvent the crapness of ns 4.79
        if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
            $sToRender .=       "<div id=\"divUp\">" .
                                "\t<a href=\"#\" onmouseover=\"scroll(-7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->system->get("graphicsUrl") . "/desc.gif\" width=\"25\" height=\"30\" alt=\"up\" border=\"0\"></a>" .
                                "</div>" .
                                "<div id=\"divDown\">" .
                                "\t<a href=\"#\" onmouseover=\"scroll(7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->system->get("graphicsUrl") . "/asc.gif\" width=\"25\" height=\"30\" alt=\"down\" border=\"0\"></a>" .
                                "</div>";
        }
                            
		$sToRender .= 		    "<form name=\"MainForm\" " . (isset($this->sFormEncType) ? "enctype=\"$this->sFormEncType\" " : " ") . " action=\"".$this->sFormAction."\" method=\"POST\" " . (($this->bHasRequiredFields) ? "onsubmit=\"return validateForm(this)\" " : " ") . ">\n" .
								"<table border=\"1\" width=\"700\" height=\"600\">\n " .

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
                                            // hack to circumvent the crapness of ns 4.79
                                            if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
                                                $sToRender .= "\t<div id=\"divScrollTextCont\">\n
                                                               \t\t<div id=\"divText\">\n";
                                            }                                          
											if (isset($this->sErrorMessage)) {
												$sToRender .= "<table border=\"0\"><tr><td><p class=\"errorText\">$this->sErrorMessage</td></tr></table>\n ";
											}
											$sToRender = $sToRender . "\n" . $this->oCentralPayload->render() . "\n";
										} else {											
											$sToRender = $sToRender . "\nCentral payload not set\n";
										}
                                        // hack to circumvent the crapness of ns 4.79
                                        if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
                                            $sToRender = $sToRender . "</div></div>\n";
                                        }
                                        $sToRender = $sToRender . "</td>\n" .
									"</tr>\n" .								
								"</table>\n" .
								"</form>\n" .
							"</body>\n" .
						"</html>\n";
		echo $sToRender;
	}
	
	/**
	* Returns links to javascript that has 3 functions:
	*	o validateString - validates a string
	*	o validateNumber - validates a number
	*	o setActionAndSubmit - sets the MainForm action to newAction and submits
    * And includes the dhtml text scrolling routine
	*/
	function getJavaScript() {
        global $default;
        $sUrl = $default->system->get("uiUrl");
        // hack to circumvent the crapness of ns 4.79
        if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
            return "<script type=\"text/javascript\" src=\"$sUrl/js/misc.js\"></script>
                    <script type=\"text/javascript\" src=\"$sUrl/js/scroll.js\"></script>";
        } else {
            return "<script type=\"text/javascript\" src=\"$sUrl/js/misc.js\"></script>";        
        }
        
	}
}

?>