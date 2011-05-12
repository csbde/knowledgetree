<form method="post">
        <input name="action" type="hidden">
        <input name="fDocumentId" type="hidden">
        <label for="data[reason]">Reason</label>
		<div class="field ">
			<textarea name="data[reason]" rows="3" cols="40"></textarea>
		</div>
     	<div class="form_actions" id="modal">
    		<input name="submitName" value="Submit" type="submit"> 
            <a class="form_cancel" onclick="vActions.closeDisplay('reason'); return false;" href="#">Cancel</a>
    	</div>
</form>