<form id="database_registration_install" action="index.php?step_name=install" method="post">
	<p class="title">Thank you for registering</p>
	<div id="step_content_<?php echo $step_name; ?>_confirm" class="step">
		<br/>
		<br/>
		<p class="empty_space">
			Thank you for signing up. You'll receive an email from us shortly with download instructions for the KnowledgeTree Drop Box software.
		</p>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Confirm" value="Next" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>