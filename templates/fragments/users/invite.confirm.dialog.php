<?php
include_once('../../ktapi/ktapi.inc.php');

$url = KTUtil::kt_url();

$users_url = $url. '/admin.php?kt_path_info=userSetup/users';
$groups_url = $url. '/admin.php?kt_path_info=userSetup/groups';
$contact_url = 'http://www.knowledgetree.com/contact-us';
?>

<table class="uploadTable" style="height:100%;" border="0">

    <tr>
        <td colspan="2" valign="top">
            Invitations to join KnowledgeTree were sent to: <a href="<?= $users_url; ?>"><span id="invitedUsers"></span> users</a>
        </td>
    </tr>

    <tr>
        <td colspan="2" id="showInvitedGroup">
            As part of the: <a href="<?= $groups_url; ?>"><span id="invitedGroup"></span> group</a>
        </td>
    </tr>

    <tr id="inviteLicenses" style="display: none;">
        <td width="15%" valign="top">Warning: </td>
        <td width="85%" valign="top">
            The number of users invited now exceeds your available KnowledgeTree licenses.<br />
            Please <a href="<?= $contact_url; ?>">contact us</a> to purchase additional licenses.
        </td>
    </tr>

    <tr>
        <td class="ul_actions" colspan="2" align="right">
            <input id="ul_actions_upload_btn" class="ul_actions_btns" type="button" value="Close" onClick="kt.app.inviteusers.closeConfirmWindow();" />
        </td>
    </tr>

</table>