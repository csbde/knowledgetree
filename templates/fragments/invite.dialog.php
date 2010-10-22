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

<table border="0" cellspacing="0" cellpadding="0">

    <tr>
        <td width="40%">
            Invite Users:
        </td>
        <td width="60%">
            <textarea width="100px" height="29px" >
            </textarea>
        </td>
    </tr>

    <tr>
        <td width="40%">
            Select Group:
        </td>
        <td width="60%">
            <select>
                <?php foreach ($aGroupsList as $key => $group) { ?>
                    <option value="<?= $key; ?>"><?= $group; ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>

    <tr>
        <td width="40%"></td>
        <td width="60%" align="right">
            <input id="ul_actions_upload_btn" class="ul_actions_btns" type="button" value="Invite" onClick="kt.app.inviteusers.inviteUsers();" />
            <a class="ul_actions_cancel_link" href="javascript:kt.app.inviteusers.closeWindow();">Cancel</a>
        </td>
    </tr>

</table>
