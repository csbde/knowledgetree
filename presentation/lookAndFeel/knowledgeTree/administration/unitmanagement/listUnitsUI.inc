<?php
/**
* Presentation information for adding a unit
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getSubmit() {
	global $default;
	$sToRender = "<input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/>\n";
	return $sToRender;
}

// display the listbox initially ..then just display the text
function getOrgDisplay($fOrgID, $iSelectedValue = 0) {
	global $default;
	    
        // if this is the system administrator, prepend group names with unit name
        $oPatternListBox = & new PatternListBox($default->owl_organisations_table, "name", "id", "fOrgID");
               
        if ($iSelectedValue != 0) {
        	$oPatternListBox->setSelectedValue($iSelectedValue);
        }
                
        return $oPatternListBox->render();	
}


function getFilterOptions($fOrgID = 0){
	global $default;
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<b>Filter by Organisation</b>";
	$sToRender .= "</td>\n";
	$sToRender .= "<td>\n";
	$sToRender .= getOrgDisplay($fOrgID);
	$sToRender .= "</td>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/>\n";
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender ;
}


?>