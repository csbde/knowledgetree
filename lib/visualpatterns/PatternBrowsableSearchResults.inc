<?php

class PatternBrowseableSearchResults {
	
	/** query to be executed */
	var $sQuery;
	/** columns in query to be displayed */
	var $aColumns;
	/** column types */
	var $aColumnTypes;
	/** column headings to display */
	var $aColumnHeadings;
	/** link href for column type 3 */
	var $aLinkURLs;
	/** database column values to append to link url if a column type of 3 is specified */
	var $aDBQueryStringColumns;
	/** variables names to give $aDBQueryStringColumns on the query string */
	var $aQueryStringVariableNames;
	/** number of results to display per page */
	var $iResultsToDisplay;
	/* Result  number to start on  */
	var $iStartIndex;
	/** Column to order by */
	var $sOrderByColumn;
	/** direction of ordering, ascending or descending */
	var $sOrderDirection = "ASC";
	
	function PatternBrowseableSearchResults($sTmpQuery, $iTmpResultsToDisplay, $aTmpColumns, $aTmpColumnTypes, $aTmpColumnHeaders, $aTmpLinkURLs = null, $aTmpDBQueryStringColumns = null, $aTmpQueryStringVariableNames = null) {
		$this->sQuery = $sTmpQuery;
		$this->iResultsToDisplay = $iTmpResultsToDisplay;
		$this->aColumns = $aTmpColumns;
		$this->aColumnHeadings = $aTmpColumnHeaders;
		$this->aColumnTypes = $aTmpColumnTypes;
		$this->aLinkURLs = $aTmpLinkURLs;
		$this->aDBQueryStringColumns = $aTmpDBQueryStringColumns;		
		$this->aQueryStringVariableNames = $aTmpQueryStringVariableNames;		
	}
	
	function setStartIndex($iNewValue) {
		$this->iStartIndex = $iNewValue;
	}
	
	function setOrderByColumn($sNewValue) {
		$this->sOrderByColumn = $sNewValue;
	}
	
	function setOrderDirection($sNewValue) {
		$this->sOrderDirection = $sNewValue;
	}
	
	/**
	* Build the HTML string used to render the object
	*
	* @return String of HTML used to render object
	*
	* @todo possibly add in image size restraints for link types 2 and 3
	*/	
	function & render() {
        global $default;
		
		//add the limit and offset stuff for cutting down result set
		$sLimitQuery = $this->sQuery . " LIMIT " . $this->iStartIndex . ", " . $this->iResultsToDisplay;
		$sql = & $default->db;		
		$sql->query($sLimitQuery);
        
		$sToRender = "<table width=\"100%\" height=\"100%\">\n";		
		$sToRender .= "<tr>\n";		
		for ($i = 0; $i < count($this->aColumnHeadings); $i++) {
			if (! (strcmp($this->sOrderByColumn, $this->aColumns[$i]) === false) && (strcmp($this->sOrderByColumn, $this->aColumns[$i]) == 0)) {
				if (!(strcmp($this->sOrderDirection,"ASC") === false) && (strcmp($this->sOrderDirection,"ASC") == 0)) {					
					//$sToRender .= "<th align=\"left\"><a href=\"" . $_SERVER["PHP_SELF"] . "?fOrderBy=" . $this->aColumns[$i] . "&fOrderDirection=DESC&fStartIndex=" . $this->iStartIndex . "\">" . $this->aColumnHeadings[$i]."</a></th>\n";
					$sToRender .= "<th align=\"left\">" . $this->aColumnHeadings[$i]. "</th>\n";
				} else {
					//$sToRender .= "<th align=\"left\"><a href=\"" . $_SERVER["PHP_SELF"] . "?fOrderBy=" . $this->aColumns[$i] . "&fOrderDirection=ASC&fStartIndex=" . $this->iStartIndex . "\">" . $this->aColumnHeadings[$i]."</a></th>\n";
					$sToRender .= "<th align=\"left\">" . $this->aColumnHeadings[$i]. "</th>\n";
				}
			} else {
				//$sToRender .= "<th align=\"left\"><a href=\"" . $_SERVER["PHP_SELF"] . "?fOrderBy=" . $this->aColumns[$i] . "&fOrderDirection=ASC&fStartIndex=" . $this->iStartIndex . "\">" . $this->aColumnHeadings[$i]."</a></th>\n";
				$sToRender .= "<th align=\"left\">" . $this->aColumnHeadings[$i]. "</th>\n";
			}
		}
		$sToRender .= "</tr>\n";		
				
		$iDisplayed = 0;
		//limit the result set displayed		
		while($sql->next_record() && ($iDisplayed < $this->iResultsToDisplay)) {
			$sToRender .= "<tr>";
			
			for ($i = 0; $i < count($this->aColumns); $i++) {
				switch ($this->aColumnTypes[$i]) {
					case 1:							
						//display text
						$sToRender .= "<td>" . $sql->f($this->aColumns[$i]) . "</td>\n";					
					break;
					case 2:
						//diplay a checkbox
						$sToRender .= "<td>" . ($sql->f($this->aColumns[$i]) ? "Yes" : "No") . "</td>\n";						
						break;
					case 3:					
						//display a url
						$sToRender .= "<td><a href=\"" . $this->aLinkURLs[$i];
						for ($j = 0; $j < count($this->aDBQueryStringColumns); $j++) {
							if (strpos($sToRender, "?") === false) {
								$sToRender .= "?" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);										
							} else { 
								$sToRender .= "&" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);
							}									
						}
						$sToRender .= "\">" . $sql->f($this->aColumns[$i]) . "</a></td>\n";
						break;
					case 4:
						//diplay an image URL
						$sToRender .= "<td><a href=\"" . $this->aLinkURLs[$i];
						for ($j = 0; $j < count($this->aDBQueryStringColumns); $j++) {
							if (strpos($sToRender, "?") === false) {
								$sToRender .= "?" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);										
							} else { 
								$sToRender .= "&" . $this->aQueryStringVariableNames[$j] . "=" . $sql->f($this->aDBQueryStringColumns[$j]);
							}									
						}						
						$sToRender .= "\"><img src=\"" . $sql->f($this->aColumns[$i]) . "\" border=\"0\" /></a></td>\n";						
						break;
					default:
						break;
				}
			}
			$sToRender .= "</tr>\n";
			$iDisplayed++;
		}
		
		//if we displayed less results than the number to display
		//simply pad the table
		while ($iDisplayed < $this->iResultsToDisplay) {
			$sToRender .= "<tr><td>&nbsp</td></tr>\n";
			$iDisplayed++;
		}
		
		$sToRender .= "<tr>\n";		
		
		$sToRender .= "<input type=\"hidden\" name=\"fStartIndex\" value=\"" . ($this->iStartIndex +  $this->iResultsToDisplay) . "\" />\n";
		/* Display only the next button */
		if (($this->iStartIndex + $this->iResultsToDisplay) < $this->getResultCount($sql) && $this->iStartIndex == 0) {
			$sToRender .= "<td>";
			//$sToRender .= ("<a href=\"" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay) . "\">Next</a>");
			$sToRender .= ("<input type=\"image\" src=\"$default->graphicsUrl/widgets/next.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay) . "')\" />");
			$sToRender .= "</td>\n";
		}
		/* Display both the next and the previous buttons */		
		else if (($this->iStartIndex + $this->iResultsToDisplay) < $this->getResultCount($sql) && $this->iStartIndex > 0) {
			$sToRender .= "<td>";
			$sToRender .= ("<input type=\"image\" src=\"$default->graphicsUrl/widgets/next.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex +  $this->iResultsToDisplay) . "')\" />");
			$sToRender .= "</td>";
			$sToRender .= "<td>";
			$sToRender .= ("<input type=\"image\" src=\"$default->graphicsUrl/widgets/previous.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex -  $this->iResultsToDisplay) . "')\" />");
			$sToRender .= "</td>\n";
			
		}	
		/* Display only the previous button */
		else if ($this->iStartIndex > 0) {
			$sToRender .= "<td>\n";
			$sToRender .= ("&nbsp");
			$sToRender .= "</td>";
			$sToRender .= "<td>\n";
			$sToRender .= ("<input type=\"image\" src=\"$default->graphicsUrl/widgets/previous.gif\" onClick=\"setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fStartIndex=" . ($this->iStartIndex -  $this->iResultsToDisplay) . "')\" />");
			$sToRender .= "</td>";
		}
		
		$sToRender .= "</tr>\n";
		$sToRender .= "</table>\n";		
		return $sToRender;
	}
	
	function getResultCount($sql) {		
		if ($sql->query($this->sQuery)) {
			return $sql->num_rows();
		}
		return 0;
	}
}

?>
