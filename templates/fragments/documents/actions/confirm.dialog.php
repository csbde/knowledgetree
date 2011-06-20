<div id="action-error"></div>

<table id='action-modal' class="uploadTable" style="height: 0px;">

<tr>
	<td valign="top">
		<div id='action-single'>
			<?php echo _kt('Are you sure you want to [action] the document') . ': '; ?>
			<br />
			<span id='confirm-doc-name'>&nbsp;</span>
		</div>
		<div id='action-bulk' style="visibility: none">
			<?php echo _kt('Are you sure you want to [action] the selected items') . '?'; ?>
		</div>
		<br />
	</td>
</tr>

<tr>
	<td class="ul_actions" align="right" valign="bottom">
        <input id="select-btn" class="ul_actions_btns" type="button" value="Continue" onClick="kt.app.copy.save();" />
        <a class="ul_actions_cancel_link" href="javascript: kt.app.copy.closeConfirmWindow();">Cancel</a>
        <span id="action-spinner" class="action-spinner none">&nbsp;</span>
    </td>
</tr>
</table>
