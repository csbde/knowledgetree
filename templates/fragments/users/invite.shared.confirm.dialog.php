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

    <tr id="showExistingUsers" style="display: none;">
        <td colspan="2" valign="top" style="padding-top: 10px;">
            The following users are already active in KnowledgeTree:<br />
            <ul id="existingUsers" style="padding-left: 30px;"></ul>
        </td>
    </tr>

    <tr id="showFailedUsers" style="display: none;">
        <td colspan="2" valign="top" style="padding-top: 10px;">
            Emails could not be sent to the following addresses: <br />
            <ul id="failedUsers" style="padding-left: 30px;"></ul>
        </td>
    </tr>
    
    <tr id="showNoPerms" style="display: none;">
        <td colspan="2" valign="top" style="padding-top: 10px;">
            There are users that do not have permissions to see the content you have shared:
            <ul id="noPerms" style="padding-left: 30px;"></ul>
        </td>
    </tr>
    
    <tr id="showPermsMessage" style="display: none;">
        <td>
        	<span id="permMessage"></span>
        </td>
	</tr>
        
    <tr>
        <td class="ul_actions" colspan="2" align="right" style="padding-top: 10px;">
            <input id="invite_actions_confirm_btn" class="ul_actions_btns" type="button" value="Close" onClick="kt.app.inviteusers.closeConfirmWindow();" />
        </td>
    </tr>

</table>