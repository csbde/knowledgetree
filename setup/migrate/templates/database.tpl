<form id="<?php echo $step_name; ?>" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Migrate Database</p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step configures the connection to the database server and migrates the database.
		</div>
		<div id="step_content_<?php echo $step_name; ?>" class="step">
		<?php if(empty($errors)) { ?>
<p class="description">The Setup Wizard will now export your existing database in preparation for the migration to the new KnowledgeTree Stack.</p>
<br />
<p class="description">
	Ensure that the new Mysql server installed from your operating system's repository is not running
</p>
<br />
<p class="disclaimer">
	<?php if(WINDOWS_OS) { ?>
		[START MENU] => [Programs] => [KnowledgeTree 3.7.x] => [Mysql]
		<br/>
		Right-Click and run [Stop Mysql] as administrator, if applicable, or
		<br/>
		Click [Stop Mysql]
	<?php } else { ?>
	    #Ubuntu
	    <br/>
		sudo /etc/init.d/mysql stop
		<br/>
	    #RedHat
	    <br/>
		sudo /etc/init.d/mysqld stop
	<?php } ?>
</p>
<br/>
<p class="description">
	Ensure that the old KnowledgeTree 3.6.1 Mysql server is running
</p>
<p class="disclaimer">
	<?php if(WINDOWS_OS) { ?>
		[START MENU] => [Programs] => [KnowledgeTree 3.6.x] => [Control]
		<br/>
		Right-Click and run [Start Services] as administrator, if applicable, or
		<br/>
		Click [Start Services]
	<?php } else { ?>
		sudo <?php echo $location.DS; ?>dmsctl.sh start mysql
	<?php } ?>
</p>
<br/>
<span class="error">
	Ensure that your database is backed up before proceeding. If you need assistance with backing up see the &nbsp;
	<a class="description_click" target="_blank" href="http://wiki.knowledgetree.com/Backing_up_and_restoring_KnowledgeTree">'Backing up and restoring KnowledgeTree'</a>&nbsp; wiki entry.
</span>
        <br />
        <br />
        <p class="description">Click <b>Next</b> to continue.</p>
		<?php } ?>
<!--Handle a failed dump-->
<?php if(!empty($errors)) { ?>
	<?php
		foreach ($errors as $error) {
		?>
			<span class="error">
			<?php
				if(isset($error['error'])) {
					echo $error['error'];
					?>
						<br/><br/>
					<?php
				}
			?>
			</span>
			<?php
				//if(isset($error['msg'])) {
					//echo $error['msg'];
					?>
<!--						<br/><br/>-->
					<?php
				//}
			?>
			<?php
				//if(isset($error['cmd'])) {
//					echo $error['cmd'];
	//			}
		}
?>
<p class="description">
	Ensure that the new Mysql server installed from your operating system's repository is not running
</p>
<p class="disclaimer">
	<?php if(WINDOWS_OS) { ?>
		[START MENU] => [Programs] => [KnowledgeTree 3.7.x] => [Mysql]
		<br/>
		Right-Click and run [Stop Mysql] as administrator, if applicable, or
		<br/>
		Click [Stop Mysql]
	<?php } else { ?>
	    #Ubuntu
	    <br/>
		sudo /etc/init.d/mysql stop
		<br/>
	    #RedHat
	    <br/>
		sudo /etc/init.d/mysqld stop
	<?php } ?>
</p>
<br/>
<p class="description">
	Ensure that the old KnowledgeTree 3.6.1 Mysql server is running
</p>
<p class="disclaimer">
	<?php if(WINDOWS_OS) { ?>
		[START MENU] => [Programs] => [KnowledgeTree 3.6.x] => [Control]
		<br/>
		Right-Click and run [Start Services] as administrator, if applicable, or
		<br/>
		Click [Start Services]
	<?php } else { ?>
		sudo <?php echo $location.DS; ?>dmsctl.sh start mysql
	<?php } ?>
</p>
<br/>
<?php
	}
	?>


			<br/>
		</div>
	</div>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$("#duname").focus();
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>