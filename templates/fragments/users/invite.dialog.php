<?php
include_once('../../ktapi/ktapi.inc.php');

$KT = new KTAPI(3);

//Pick up the session
$session = KTAPI_UserSession::getCurrentBrowserSession($KT);
$KT->start_system_session($session->user->getUserName());

function getGroupsList()
{
    $aGroups = Group::getList('');
    $aGroupList = array();

    foreach($aGroups as $oGroup) {
        $aGroupList[$oGroup->getId()] = $oGroup->getName();
    }

    return $aGroupList;
}

$aGroupsList = getGroupsList();

?>

<table class="uploadTable" style="height:200px;" border="0">

    <tr>
        <td valign="top">
            Enter or copy and paste the email addresses of the user(s) in your organization you want to invite to use KnowledgeTree (separate email addresses with a space or comma).
        </td>
    </tr>

    <tr>
        <td valign="top" style="padding-top: 10px;">
            Invite Users:
        </td>
    </tr>

    <tr>
        <td valign="top">
            <textarea onfocus="kt.app.inviteusers.enableInviteButton();" class="modalField"name="invite.emails" id="invite.emails" rows="5" cols="65" ></textarea>
        </td>
    </tr>

    <tr>
        <td valign="top" style="padding-top: 10px;">
            Select Group:
        </td>
    </tr>

    <tr>
        <td valign="top">
            <select name="invite.grouplist" id="invite.grouplist">
                <option value="">-- Select a group --</option>
                <?php foreach ($aGroupsList as $key => $group) { ?>
                    <option value="<?= $key; ?>"><?= $group; ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td class="ul_actions" align="right" valign="top">
            <a class="ul_actions_cancel_link" href="javascript: kt.app.inviteusers.closeWindow();">Cancel</a>
            <input id="invite_actions_invite_btn" class="ul_actions_btns" type="button" value="Invite" onClick="kt.app.inviteusers.inviteUsers();" />
        </td>
    </tr>

</table>
