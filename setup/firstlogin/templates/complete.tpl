<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Preferences Completed</p>

	<p class="description">Your system preferences have been applied</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	
    <input class="button_next" type="submit" value="Next" name="Next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>