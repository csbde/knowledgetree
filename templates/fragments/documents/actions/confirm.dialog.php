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
</table>

<div class="action-footer">
	<div class="form_actions">
	    <a class="ul_actions_cancel_link" href="javascript: kt.app.copy.closeConfirmWindow();">Cancel</a>
        <input id="select-btn" class="ul_actions_btns" type="button" value="Continue" onClick="kt.app.copy.save();" />
        <span id="action-spinner" class="action-spinner spin" style="visibility: hidden; width: 16px; height: 16px;">&nbsp;</span>
	</div>
</div>