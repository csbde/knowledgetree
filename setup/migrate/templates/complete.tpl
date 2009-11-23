<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Migration Completed</p>

	<p class="description">Your database migration was successfully completed. Please check the section below for details of the migration and final instructions</p>
	<?php
	if($errors || $warnings){
	    echo '<div>'
	       . '<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Complete" target="_blank">'
	       . 'Click Here for help on overcoming post migrate issues</a></div><br/>';
	}
	?>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	
<!--	SQL	-->
		<h3>KnowledgeTree database successfully exported to:</h3>
		<span class='<?php echo $sql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><?php echo $sql['msg']; ?>
		<br /><br />
		<h3>Start new MySQL service</h3>
		In order to complete the upgrade process please start the Zend Server MySQL service and then click <b>Next</b> to continue (<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Complete" target="_blank">Click here for help</a>). 
		<br /><br />
		<?php if(!empty($errors)) { ?>
<!--		<span class='<?php //echo $ktmysql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>-->
		<?php //echo $ktmysql['name']; ?>
		<?php //echo $ktmysql['msg']; ?>
<!--		<br /><br />-->
		<?php //if (WINDOWS_OS) { ?>
		<span class='<?php echo $zmysql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<?php echo $zmysql['name']; ?>
		<?php echo $zmysql['msg']; ?>
		<?php //} ?>
		<?php } ?>
	</div>
<!--    <input class="button_next" type="submit" value="Continue Installation" name="BInstall"/>-->
    <input class="button_next" type="submit" value="Next" name="Next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>