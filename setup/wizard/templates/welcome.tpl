<form id="welcome_license" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Welcome to the KnowledgeTree Setup Wizard</p>
	<div id="step_content" class="step">
		<p class="empty_space"> This wizard will lead you through all the steps required to install and configure KnowledgeTree on your server.</p> 
		<p class="empty_space">
		Press <b>Next</b> to continue.</p>
		<div class="demo"><?php //echo $html->image('kt_browse.png'); ?> </div>
	</div>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>