<input type="hidden" value="reason" id="type">
<input type="hidden" value="" id="reasondocid">
<input type="hidden" value="" id="reasonaction">
<table class="uploadTable" style="height:100px;">
	<tbody>
		<tr id="error" style='display:none;'>
			<td> <p class="errorMessage"></p> </td>
		</tr>
		<tr>
			<td>
				<p id="esign-info" style='display: none'>
					An electronic signature is required in order to continue, please enter your login details below.
				</p>
				
				<p id="reason-info">
					A reason is required in order to continue, please enter one below.
				</p>
			</td>
		</tr>
		<tr id="user" style='display:none;'>
			<td> 
				<label for="username">Email Address</label>  <br />
				<input name="sign-username" size="30"> 
			</td>
		</tr>
		<tr id="pass" style='display:none;'>
			<td> 
				<label for="password">Password</label> <br />
				<input type="password" name="sign-password" size="30"> 
			</td>
		</tr>
		<tr id=reason>
			<td>
				<label for="reason" name="reason-field" id="reason-label">Note</label> <br />
				<textarea cols="50" rows="5" name="reason"></textarea>
		    </td>
		</tr>
		<tr>
	    	<td align="right" valign="top" class="ul_actions">
				<a href="#" onclick="vActions.closeDisplay('reason'); return false;" class="ul_actions_cancel_link">Cancel</a>
				<input name="reason_submit" type="button" onclick="kt.api.esignatures.saveESignatures();" value="Submit" class="ul_actions_btns" id="ul_actions_upload_btn">
	    	</td>
		</tr>
	</tbody>
</table>