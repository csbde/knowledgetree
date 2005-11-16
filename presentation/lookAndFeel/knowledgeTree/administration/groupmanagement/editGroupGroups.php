<?php

require_once("../../../../../config/dmsDefaults.php");

require_once(KT_DIR . "/presentation/Html.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/groups/Group.inc");
require_once(KT_LIB_DIR . "/groups/GroupUtil.php");

require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class EditGroupsGroups extends KTAdminDispatcher {
    function do_main() {
        $oGroup =& Group::get($_REQUEST['fGroupID']);
        global $main;
        $main->setOnLoadJavaScript("optGroup.init(document.forms[0]);");
        $main->setHasRequiredFields(false);
        $main->setAdditionalJavaScript($this->_initialiseOptionTransferJavaScript());
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupID=$fGroupID&fAssign=1");

        $sToRender = "";
        $sToRender .= sprintf('<form method="POST" action="%s">', $_SERVER['PHP_SELF']);
        $sToRender .= '<input type="hidden" name="action" value="update">';
        $sToRender .= sprintf('<input type="hidden" name="fGroupID" value="%s">', $oGroup->getID());
        $sToRender .= '<table width="600">';
        $sToRender .= sprintf('<tr><td valign="left" colspan="2"><b>Group Name: %s</b></td><td valign="right">Back</td></tr>', $oGroup->getName());
        $sToRender .= sprintf('<tr><td><b>Assigned Groups To:</b></td><td>&nbsp;</td><td><b>Available Groups</b></td></tr>');

        $sToRender .= sprintf('<tr><td>');
        $aMemberGroups = $oGroup->getMemberGroups();
        $sToRender .= sprintf('<select name="groupSelect" size="10" multiple>');
        $aMemberIDs = array();
        foreach ($aMemberGroups as $oMemberGroup) {
            $aMemberIDs[] = $oMemberGroup->getID();
            $sJavascript = 'onDblClick="optGroup.transferRight()"';
            $sToRender .= sprintf('<option value="%s" %s>%s</option>', $oMemberGroup->getID(), $sJavascript, $oMemberGroup->getName());
        }
        $sToRender .= sprintf('</select>');
        $sToRender .= sprintf('</td>');
        $sToRender .= sprintf('<td>');
        $sToRender .= '<input TYPE="button" NAME="right" style="width:60px" VALUE="- &gt;&gt;" ONCLICK="optGroup.transferRight()"><BR><input TYPE="button" NAME="left" style="width:60px" VALUE="&lt;&lt; +" ONCLICK="optGroup.transferLeft()">';
        $sToRender .= sprintf('</td>');

        $aGroupArray = GroupUtil::buildGroupArray();
        $aAllowedGroupIDs = GroupUtil::filterCyclicalGroups($oGroup->getID(), $aGroupArray);
        $aAllowedGroupIDs = array_diff($aAllowedGroupIDs, $aMemberIDs);

        $sToRender .= sprintf('<td>');
        $sToRender .= sprintf('<select name="groupAvail" size="10" multiple>');
        foreach ($aAllowedGroupIDs as $iAllowedGroupID) {
            $oAllowedGroup =& Group::get($iAllowedGroupID);
            $sJavascript = 'onDblClick="optGroup.transferLeft()"';
            $sToRender .= sprintf('<option value="%s" %s>%s</option>', $oAllowedGroup->getID(), $sJavascript, $oAllowedGroup->getName());
        }
        $sToRender .= sprintf('</select>');
        $sToRender .= sprintf('</td></tr>');

        $sToRender .= '<tr>';
        $sToRender .= '<td>Filter <BR><input type="text" name="filterUG" onkeyup="optGroup.sortSelectMatch(groupSelect, this.value)\" onchange=\"optGroup.sortSelectMatch(groupSelect, this.value)\"></td>';
        $sToRender .= '<td></td>';
        $sToRender .= '<td>Filter <BR><input type="text" name="filterOG" onkeyup="optGroup.sortSelectMatch(groupAvail, this.value)" onchange="optGroup.sortSelectMatch(groupAvail, this.value)"></td>';
        $sToRender .= '</tr>';

        $sToRender .= '<tr>';
        $sToRender .= '<td colspan="3" align="right">';
        $sToRender .= sprintf('<input type="image" src="%s" border="0" />', KTHtml::getAssignButton());
        $sToRender .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?fGroupID=" . $iGroupID . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/></a>\n";     $sToRender .= "</td></tr>";
        $sToRender .= '</td>';
        $sToRender .= '</tr>';

        $sToRender .= sprintf('</table>');
        $sToRender .= '<input type="hidden" name="groupNewLeft" /><br>';
        $sToRender .= '<input type="hidden" name="groupNewRight" /><br>';
        $sToRender .= '<input type="hidden" name="groupRemovedLeft" /><br>';
        $sToRender .= '<input type="hidden" name="groupRemovedRight" /><br>';
        $sToRender .= '<input type="hidden" name="groupAddedLeft" /><br>';
        $sToRender .= '<input type="hidden" name="groupAddedRight" /><br>';
        $sToRender .= '</form>';

        return $sToRender;
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    function _initialiseOptionTransferJavascript() {
        return "<script LANGUAGE=\"JavaScript\">\n" .
            "var optGroup = new OptionTransfer(\"groupSelect\",\"groupAvail\");\n" .
            "optGroup.setAutoSort(true);\n" .
            "optGroup.setDelimiter(\",\");\n" .
            "optGroup.saveNewLeftOptions(\"groupNewLeft\");\n" .
            "optGroup.saveNewRightOptions(\"groupNewRight\");\n" .
            "optGroup.saveRemovedLeftOptions(\"groupRemovedLeft\");\n" .
            "optGroup.saveRemovedRightOptions(\"groupRemovedRight\");\n" .
            "optGroup.saveAddedLeftOptions(\"groupAddedLeft\");\n" .
            "optGroup.saveAddedRightOptions(\"groupAddedRight\");\n" .
        "</SCRIPT>";
    }

    function do_update() {
        $oGroup =& Group::get($_REQUEST['fGroupID']);
        $aToAddIDs = explode(",", KTUtil::arrayGet($_REQUEST, 'groupAddedLeft'));
        $aToRemoveIDs = explode(",", KTUtil::arrayGet($_REQUEST, 'groupAddedRight'));

        foreach ($aToAddIDs as $iMemberGroupID ) {
            if ($iMemberGroupID > 0) {
                $oMemberGroup = Group::get($iMemberGroupID);
                $res = $oGroup->addMemberGroup($oMemberGroup);
                if (PEAR::isError($res)) {
                    $_SESSION["KTErrorMessage"][] = "Failed to add " . $oMemberGroup->getName() . " to " . $oGroup->getName();
                }
            }
        }

        foreach ($aToRemoveIDs as $iMemberGroupID ) {
            if ($iMemberGroupID > 0) {
                $oMemberGroup = Group::get($iMemberGroupID);
                $res = $oGroup->removeMemberGroup($oMemberGroup);
                if (PEAR::isError($res)) {
                    $_SESSION["KTErrorMessage"][] = "Failed to remove " . $oMemberGroup->getName() . " to " . $oGroup->getName();
                }
            }
        }
        exit($this->redirectToMain('fGroupID=' . $oGroup->getID()));
    }
}

$oDispatcher = new EditGroupsGroups();
$oDispatcher->dispatch();

?>
