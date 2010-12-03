<?php

include_once('../../ktapi/ktapi.inc.php');

$url = KTUtil::kt_url();

$users_url = $url. '/admin.php?kt_path_info=userSetup/users&show_all=1';
$groups_url = $url. '/admin.php?kt_path_info=userSetup/groups&show_all=1';
$contact_url = 'http://www.knowledgetree.com/contact-us';

?>

<table class="uploadTable" style="height:120%;" border="0">

    <tr>
        <td colspan="2" valign="top">
            Content was shared with: <a href="<?= $users_url; ?>"><span id="invitedUsers"></span> users </a>
        </td>
    </tr>
    <tr>
        <td class="ul_actions" colspan="2" align="right" style="padding-top: 10px;">
            <input id="invite_actions_confirm_btn" class="ul_actions_btns" type="button" value="Close" onClick="kt.app.inviteusers.closeConfirmWindow();" />
        </td>
    </tr>

</table>