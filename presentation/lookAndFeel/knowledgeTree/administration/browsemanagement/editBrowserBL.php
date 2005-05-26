<?php
/**
 * $Id$
 *
 * Edit visibility and location of browser criteria
 *
 * Copyright (c) 2004 Jam Warehouse http://www.jamwarehouse.com
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
 * @package administration.groupmanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fAssign', 'fGroupID', 'browseNewLeft');

class Verify_Error extends PEAR_Error {
}

function verifyBrowse($aIDs) {
    if (!in_array('-1', $aIDs)) {
        return new Verify_Error("Browser must include Name column");    
    }
    return true;
}

/*
 * Update all Users/Group association
 * Return 1 if success
 *        0 if fail
 */
function updateBrowse($aIDs) {
    $sQuery = "DELETE FROM browse_criteria";
    $res = DBUtil::runQuery($sQuery);
    if (PEAR::isError($res)) {
        return $res;
    }

    while (list($key, $val) = each($aIDs)) {
        $aPost = array(
            'criteria_id' => $val,
            'precedence' => $key,
        );
        $res = DBUtil::autoInsert('browse_criteria', $aPost);
    }

    return true;
}

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("editBrowserUI.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    $oPatternCustom->setHtml(renderBrowsePicker());
    $main->setOnLoadJavaScript("optBrowse.init(document.forms[0]);");
    $main->setHasRequiredFields(false);
    $main->setAdditionalJavaScript(initialiseOptionTransferJavaScript());
    $main->setFormAction($_SERVER["PHP_SELF"] . "?fAssign=1");
    $main->setDHTMLScrolling(false);

    if (isset($fAssign)) {
        $aIDs = explode(",", $browseNewLeft);

        // Verify that the browse list makes at least some sense
        $res = verifyBrowse($aIDs);
        if (PEAR::isError($res)) {
            $main->setErrorMessage($res->getMessage());
        } else {
            // Add/Remove new users to group
            $res = updateBrowse($aIDs);
            if (($res === false) || (PEAR::isError($res))) {
                $main->setErrorMessage("Some problems in updating browse settings.  Please contact your administrator");
            } else {
                redirect($_SERVER["PHP_SELF"]);
            }
        }
    }

    // render page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}



function initialiseOptionTransferJavascript() {
	return "<script LANGUAGE=\"JavaScript\">\n" .
		"var optBrowse = new OptionTransfer(\"browseSelect\",\"chosenCriteria\");\n" .
		"optBrowse.setAutoSort(false);\n" .
		"optBrowse.setDelimiter(\",\");\n" .
		"optBrowse.saveNewLeftOptions(\"browseNewLeft\");\n" .
		"optBrowse.saveNewRightOptions(\"browseNewRight\");\n" .
		"optBrowse.saveRemovedLeftOptions(\"browseRemovedLeft\");\n" .
		"optBrowse.saveRemovedRightOptions(\"browseRemovedRight\");\n" .
		"optBrowse.saveAddedLeftOptions(\"browseAddedLeft\");\n" .
		"optBrowse.saveAddedRightOptions(\"browseAddedRight\");\n" .
	"</SCRIPT>";		
}

?>
