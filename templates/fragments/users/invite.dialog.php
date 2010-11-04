<?php
include_once('../../ktapi/ktapi.inc.php');

$KT = new KTAPI();

$KT->get(3);// Set it to Use Web Version 3

//Pick up the session
$session=KTAPI_UserSession::getCurrentBrowserSession($KT);
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

<table class="uploadTable" style="height:220px;" border="0">

    <tr>
        <td colspan="2" valign="top">
            Enter or copy and paste the email addresses of the user in your organization you want to invite to use KnowledgeTree. Each user invited will be assigned a KnowledgeTree license.
        </td>
    </tr>

    <tr>
        <td width="30%" valign="top">
            Invite Users:
        </td>
        <td width="70%" valign="top">
            <textarea name="invite.emails" id="invite.emails" rows="3" cols="46" ></textarea>
        </td>
    </tr>

    <tr>
        <td width="30%" valign="top">
            Select Group:
        </td>
        <td width="70%" valign="top">
            <select name="invite.grouplist" id="invite.grouplist">
                <option value="">-- Select a group --</option>
                <?php foreach ($aGroupsList as $key => $group) { ?>
                    <option value="<?= $key; ?>"><?= $group; ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td width="30%"></td>
        <td class="ul_actions" width="70%" align="right" valign="top">
            <a class="ul_actions_cancel_link" href="javascript: kt.app.inviteusers.closeWindow();">Cancel</a>
            <input id="ul_actions_upload_btn" class="ul_actions_btns" type="button" value="Invite" onClick="kt.app.inviteusers.inviteUsers();" />
        </td>
    </tr>

</table>
