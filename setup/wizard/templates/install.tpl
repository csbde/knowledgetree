<form id="registration_install_complete" action="index.php?step_name=install" method="post">
	<p class="title">Finalizing System Installation</p>

	<div id="step_content" class="step">
		<br/>
		<br/>
		<p class="empty_space">
			The wizard will now complete the installation and run a final check on the system. 
		</p>
		<div class="demo"><img src="resources/graphics/kt_browse.png" /></div>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Install" value="Install" class="button_next"/>
</form>
<?php if (AJAX) { ?> <script type="text/javascript" src="resources/form.js"></script> <?php } ?>