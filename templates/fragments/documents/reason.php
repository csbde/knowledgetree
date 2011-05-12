<table class="uploadTable" style="height:200px;" border="0">


<form method="post">
<tr id="user" style="display: none">
    <td>
        <input name="username" />
    </td>
</tr>
<tr id="pass" style="display: none">
    <td>
        <input name="password" />
    </td>
</tr>

<tr>
<td>
        <label for="data[reason]">Reason</label>
		<textarea name="data[reason]" rows="3" cols="40"></textarea>

    </td></tr>
    <tr>
    <td class="ul_actions" align="right" valign="top">
        <input name="action" type="hidden">
        <input name="fDocumentId" id="fDocumentId" type="hidden">

        <a class="ul_actions_cancel_link" onclick="vActions.closeDisplay('reason'); return false;">Cancel</a>
        <input id="" class="ul_actions_btns" type="button" value="Save" onClick="kt.app.inviteusers.inviteUsers();" />
    </td>
    </tr>

</form>
</table>