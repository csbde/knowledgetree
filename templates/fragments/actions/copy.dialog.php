<table class="uploadTable" style="height:200px;" border="0">

<tr>
	<td>
		<div id="copy-error" class="errorMessage"></div>
	</td>
</tr>

<tr>
	<td valign="top">
		<div id="select-tree" class="select-tree" style="width: 450px; height: 250px; overflow:scroll;"></div>
	</td>
</tr>

<tr>
	<td class="ul_actions" align="right" valign="top">
        <a class="ul_actions_cancel_link" href="javascript: kt.app.copy.closeWindow();">Cancel</a>
        <span id="copy-spinner" class="copy-spinner none">&nbsp;</span>
        <input id="select-btn" class="ul_actions_btns" type="button" value="Save" onClick="kt.app.copy.save();" />
    </td>
</tr>
</table>
