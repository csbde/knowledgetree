<?php
include_once('../../ktapi/ktapi.inc.php');

$KT = new KTAPI();
// Set it to Use Web Version 3
$KT->get(3);
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

<table border="0" cellspacing="5" cellpadding="5px">

    <tr>
        <td colspan="2" valign="top">
            Enter or copy and paste the email addresses of the user(s) in your organization you want to invite to use KnowledgeTree. Each user invited will be assigned a KnowledgeTree license.
        </td>
    </tr>

    <tr>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td width="30%" valign="top">
            Invite Users:
        </td>
        <td width="70%">
            <textarea name="invite.emails" id="invite.emails" rows="3" cols="46" ></textarea>
        </td>
    </tr>

    <tr>
        <td width="30%" valign="top">
            Select Group:
        </td>
        <td width="70%">
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
        <td width="70%" align="right">
            <input id="ul_actions_upload_btn" class="ul_actions_btns" type="button" value="Invite" onClick="kt.app.inviteusers.inviteUsers();" />
            &nbsp;&nbsp;
            <a class="ul_actions_cancel_link" href="javascript: kt.app.inviteusers.closeWindow();">Cancel</a>
        </td>
    </tr>

</table>
