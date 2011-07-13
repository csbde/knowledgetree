<?php
include_once('../../ktapi/ktapi.inc.php');

global $default;
$username = _kt('Username');
if($default->useEmailLogin) {
	$username = _kt('Email Address');
}
?>
<table class="uploadTable" style="height:100px;">
	<tbody>
		<tr id="error" style='display:none;'>
			<td> <p class="errorMessage"></p> </td>
		</tr>
		<tr>
			<td>
				<p id="esign-info" style='display: none'>
					<b>This action requires authentication</b><br />
                    Please provide your user credentials as confirmation of this action.
				</p>
				
				<p id="reason-info">
					Please provide a reason for performing this action.
				</p>
			</td>
		</tr>
		<tr id="user" style='display:none;'>
			<td> 
				<label for="username"><?php echo $username; ?><span class="required" title="<?php echo _kt('Required'); ?>">&nbsp;</span></label>  <br />
				<input class="modalField" name="sign-username" size="30"> 
			</td>
		</tr>
		<tr id="pass" style='display:none;'>
			<td> 
				<label for="password">Password<span class="required" title="<?php echo _kt('Required'); ?>">&nbsp;</span></label> <br />
				<input class="modalField" type="password" name="sign-password" size="30"> 
			</td>
		</tr>
		<tr id="reason">
			<td>
				<label for="reason" name="reason-label" id="reason-label">Note<span class="required" title="<?php echo _kt('Required'); ?>">&nbsp;</span></label> <br />
				<textarea class="modalField" cols="50" rows="5" name="reason" id="reason-field"></textarea>
		    </td>
		</tr>
	</tbody>
</table>
<div class="action-footer">
	<div class="form_actions">
	    <input type="hidden" value="reason" id="type">
		<input type="hidden" value="" id="reason-doc-id">
		<input type="hidden" value="" id="reason-action">
		<a href="#" onclick="vActions.closeDisplay('reason'); return false;" class="ul_actions_cancel_link">Cancel</a>
		<input name="reason_submit" type="button" onclick="kt.api.esignatures.saveESignatures();" value="Continue" id="reason-btn">
		<span class="reason-spinner none" style="visibility:hidden;">&nbsp;</span>
	</div>
</div>
