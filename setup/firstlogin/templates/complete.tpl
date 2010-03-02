<form id="<?php echo $step_name; ?>">
	<p class="title">Preferences Completed</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<p class="description">Your system preferences have been applied</p>
	</div>
	<input type="submit" name="Next" value="Finish" class="button_next"/>
</form>
<script type="text/javascript">
	var fl = new firstlogin('<?php echo WIZARD_ROOTURL; ?>', '<?php echo $ft_dir; ?>');
	jQuery("form").bind("submit", function() {
		fl.closeFirstLogin();
		fl.postComplete();
		return false;
	});
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>