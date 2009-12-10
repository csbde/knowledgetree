<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Migration Completed</p>

	<p class="description">Your database migration was successfully completed. Check the section below for details of the migration and final instructions</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	
<!--	SQL	-->
		<h3>KnowledgeTree database successfully exported to:</h3>
		<span class='<?php echo $sql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><?php echo $sql['msg']; ?>
		<br /><br />
		<h3>Start new MySQL service</h3>
		In order to complete the upgrade process, start the Zend Server MySQL service and then click <b>Next</b> to continue (<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Complete" target="_blank">Click here for help</a>). 
		<br />
		<br />
		<?php if(!empty($errors)) { ?>
			<span class='<?php echo $zmysql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
			<?php echo $zmysql['name']; ?>
			<?php echo $zmysql['msg']; ?>
			<br />
			<br />
		<?php } ?>

<p class="disclaimer">
	<?php if(WINDOWS_OS) { ?>
		[START MENU] => [Programs] => [KnowledgeTree 3.7.x] => [Mysql]
		<br/>
		Right-Click and run [Start Mysql] as administrator, if applicable, or
		<br/>
		Click [Start Mysql]
	<?php } else { ?>
	    #Ubuntu
	    <br/>
		sudo /etc/init.d/mysql start
		<br/>
	    #RedHat
	    <br/>
		sudo /etc/init.d/mysqld start
	<?php } ?>
</p>
<br />
	</div>
<!--    <input class="button_next" type="submit" value="Continue Installation" name="BInstall"/>-->
    <input class="button_next" type="submit" value="Next" name="Next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>