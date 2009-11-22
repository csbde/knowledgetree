<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Migration Completed</p>

	<p class="description">Your database migration was successfully completed. Please check the section below for details of the migration and final instructions</p>
	<?php
	if($errors || $warnings){
	    echo '<div>'
	       . '<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Post_Migrate" target="_blank">'
	       . 'Click Here for help on overcoming post migrate issues</a></div><br/>';
	}
	?>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	
<!--	SQL	-->
		<h3>KnowledgeTree database successfully exported</h3>
		<span class='<?php echo $sql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><?php echo $sql['msg']; ?>
		<br /><br />
		<h3>Start new MySQL service</h3>
		In order to complete the upgrade process please start the Zend Server MySQL process (<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Post_Migrate" target="_blank">Click here for help</a>).
		
	</div>
    <input class="button_next" type="submit" value="Continue Installation" name="BInstall"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>