<?php
/**
* Presentation information when adding a Org is successful
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

require_once("../../../../../config/dmsDefaults.php");

if(checkSession()) {
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	
	$oDocField = DocumentField::get($fDocFieldID);
	if ($oDocField) {
		// if we're setting lookup to be true, then prompt for an initial lookup value??
		if ($oDocField->getHasLookup()) {
			// and there are no metadata values for this lookup
			// there shouldn't be since this has just been added- but lets be paranoid shall we?
			if (DocumentField::getLookupCount($fDocFieldID) == 0) {
				// then redirect to the edit metadata page
				controllerRedirect("addMetaDataForField", "fDocFieldID=$fDocFieldID");
			}
		}
	}

    // include the page template (with navbar)
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	global $default;
    $Center .= renderHeading("Add Document Field");
    $Center .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $Center .= "<tr>\n";
    if ($fDocFieldID == -1) {
        $Center .= "<td><b>Document Field addition Unsuccessful!</b></td>\n";
        $Center .= "</tr>\n";
        $Center .= "<tr></tr>\n";
    } else {
        $Center .= "<td><b>Document Field added Successfully!</b></td>\n";
        $Center .= "</tr>\n";
    }
    $Center .= "<tr></tr>\n";
    $Center .= "<tr></tr>\n";
    $Center .= "<tr></tr>\n";
    $Center .= "<tr></tr>\n";
    $Center .= "<tr>\n";
    $Center .= "<td align = right><a href=\"$default->rootUrl/control.php?action=addDocField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $Center .= "</tr>\n";
    $Center .= "</table>\n";

    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml($Center);
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>