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
        
		$this->sFormAction = "$default->rootUrl/control.php";
	}
	
	/** property holder for $oNorthWestPayload */
	var $oNorthWestPayload;
    /** bg colour **/
    var $sNorthWestPayloadBackGroundColour;
    
	/** property holder for $oWestPayload */
	var $oWestPayload;
    /** bg colour **/
    var $sWestPayloadBackGroundColour;
    
	/** property holder for $oEastPayload */
	var $oCentralPayload;
    /** bg colour **/
    var $sCentralPayloadBackGroundColour;
    
	/** property holder for $oNorthPayload */
	var $oNorthPayload;
    /** bg colour **/
    var $sNorthPayloadBackGroundColour;
    
	/** form action*/
	var $sFormAction;
	/** form enctype */
	var $sFormEncType;
	/** whether the form has fields that require javascript checking */
	var $bHasRequiredFields = false;
	
	var $sErrorMessage;
	
	var $sHelpContext = "browse";
	
	function setNorthWestPayload($oNewVal) {		
		$this->oNorthWestPayload = & $oNewVal;		
	}
	
    function setNorthWestPayloadBackGroundColour($sColour) {
        $this->sNorthWestPayloadBackGroundColour = $sColour;
    }
    
	function setNorthPayload($oNewVal) {		
		$this->oNorthPayload = & $oNewVal;		
	}
	
    function setNorthPayloadBackGroundColour($sColour) {
        $this->sNorthPayloadBackGroundColour = $sColour;
    }
    
	function setCentralPayload($oNewVal) {
		$this->oCentralPayload = & $oNewVal;		
	}
	
    function setCentralPayloadBackGroundColour($sColour) {
        $this->sCentralPayloadBackGroundColour = $sColour;
    }
    
	function setWestPayload($oNewVal) {
		$this->oWestPayload = & $oNewVal;
	}
    
    function setWestPayloadBackGroundColour($sColour) {
        $this->sWestPayloadBackGroundColour = $sColour;
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
	
	function setHelpContext($sNewValue) {
		$this->sHelpContext = $sNewValue;
	}
	
	
	/**
	* Render the page in HTML
	*/
	function render() {
        global $default;
        
		$sToRender = 	"<html>\n" .
							"<head>\n" .
                            "<meta http-equiv=\"refresh\" content=\"" . ($default->sessionTimeout+3) . "\">\n" .
                            "<link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">\n".                            
							PatternMainPage::getJavaScript() . "\n" .							
							"</head>\n" .
							"<body marginleft=\"0\" marginheight=\"0\">\n";							
        // hack to circumvent the crapness of ns 4.79
        if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
            $sToRender .=       "<div id=\"divUp\">" .
                                "\t<a href=\"#\" onmouseover=\"scroll(-7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->graphicsUrl . "/desc.gif\" width=\"18\" height=\"9\" alt=\"up\" border=\"0\"></a>" .
                                "</div>" .
                                "<div id=\"divDown\">" .
                                "\t<a href=\"#\" onmouseover=\"scroll(7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->graphicsUrl . "/asc.gif\" width=\"18\" height=\"9\" alt=\"down\" border=\"0\"></a>" .
                                "</div>";
        }

		$sToRender .= 		    "<form name=\"MainForm\" " . (isset($this->sFormEncType) ? "enctype=\"$this->sFormEncType\" " : " ") . " action=\"".$this->sFormAction."\" method=POST " . (($this->bHasRequiredFields) ? "onsubmit=\"return validateForm(this)\" " : " ") . ">\n" .		
								"<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"700\" height=\"600\">\n " .
									"<tr>\n".
										"<td height=\"0\" valign=\"top\" " . (isset($this->sNorthWestPayloadBackGroundColour) ? " bgcolor=\"" . $this->sNorthWestPayloadBackGroundColour . "\"" : "") . ">\n";
										if (isset($this->oNorthWestPayload)) {											
											$sToRender = $sToRender . "\n" . $this->oNorthWestPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nNorthwest payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
										<td valign=\"top\" align=\"left\" " . (isset($this->sNorthPayloadBackGroundColour) ? " bgcolor=\"" . $this->sNorthPayloadBackGroundColour . "\"" : "") . ">\n";										
										if (isset($this->oNorthPayload)) {
											$sToRender = $sToRender . "\n" . $this->oNorthPayload->render() . "\n";
										} else {
											$sToRender = $sToRender . "\nNorth payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
									</tr>\n
									<tr>\n										
										<td height=\"100%\" valign=\"top\"" . (isset($this->sWestPayloadBackGroundColour) ? " bgcolor=\"" . $this->sWestPayloadBackGroundColour . "\"" : "") . ">";
										if (isset($this->oWestPayload)) {											
											$sToRender = $sToRender . "\n" . $this->oWestPayload->render() . "\n";											
										} else {
											$sToRender = $sToRender . "\nWest payload not set\n";
										}
										$sToRender = $sToRender . "</td>\n
										<td valign=\"top\"" . (isset($this->sCentralPayloadBackGroundColour) ? " bgcolor=\"" . $this->sCentralPayloadBackGroundColour . "\"" : "") . ">\n";
                                        
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
        $sUrl = $default->uiUrl;
        // hack to circumvent the crapness of ns 4.79
        if (! (($default->phpSniff->property("browser") == "moz") && ($default->phpSniff->property("version") <= "4.79")) ) {
            return "<script type=\"text/javascript\" src=\"$sUrl/js/misc.js\"></script>
                    <script type=\"text/javascript\" src=\"$sUrl/js/scroll.js\"></script>" .
					$this->getHelpJavaScript();
        } else {
            return "<script type=\"text/javascript\" src=\"$sUrl/js/misc.js\"></script>" . $this->getHelpJavaScript();        
        }
        
	}
	
	function getHelpJavaScript() {
		global $default;
		return "\n\n<SCRIPT LANGUAGE=\"javascript\">\n " .
		"<!--\n" .		
		"function helpWindow() {\n" .			
			"window.open ('" . generateControllerUrl("help", "fAction=" . urlencode($default->siteMap->getActionFromPage((substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"]))))) . "&fSection=" . urlencode($default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"]))))) . "', 'newwindow', config='height=400,width=600,left=400,top=300, toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=no, directories=no, status=no');\n" .
			"return false;\n" .
		"}\n" .
		"//-->\n" .
		"</SCRIPT>\n\n";
	}
}
?>