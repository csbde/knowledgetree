<form id="registration_install_complete" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Finalizing System Installation</p>

	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<br/>
		<br/>
		<p class="empty_space">
			The wizard will now complete the installation and run a final check on the system.
		</p>
		<div class="demo"><?php echo $html->image('kt_browse.png'); ?></div>
		<br/>
		<br/>
		<p>
            <input type='checkbox' name='call_home' value='enable' checked /> Enable the monitoring system
		</p>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Install" value="Install" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>