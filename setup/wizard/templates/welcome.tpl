<form id="welcome_license" action="index.php?step_name=welcome" method="post">
	<p class="title">Welcome to KnowledgeTree</p>
	<div id="step_content" class="step">
		<br/>
		<br/>
		<p class="empty_space"> This wizard will lead you through the steps needed to install and configure KnowledgeTree on your server. </p>
		<div class="demo"><img src="resources/graphics/kt_browse.png" /></div>
	</div>
	<input type="submit" name="Next" value="Next" class="button_next"/>
<!--	<input type="submit" name="Migrate" value="Migrate" class="button_next"/>-->
</form>
<?php if (AJAX) { ?> <script type="text/javascript" src="resources/form.js"></script> <?php } ?>