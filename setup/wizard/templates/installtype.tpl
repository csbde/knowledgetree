<form id="registration_install_complete" action="index.php?step_name=installtype" method="post">
	<p class="title">Installation Type</p>

	<div id="step_content" class="step">
		<br/>
		<br/>
		<p class="empty_space">
			The wizard will require you choose between an upgrade of an existing sytem or a clean install. 
		</p>
		<div class="demo"><?php echo $html->image('kt_browse.png'); ?></div>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="Clean Install" class="button_next"/>
	<input type="submit" name="Migrate" value="Upgrade Install" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>