<form>
	<p class="title">Database Upgrade Completed</p>
	<div id="step_content_complete" class="step">
<!--	Services	-->
	<div>
	    Your database has been upgraded to <?php echo $sysVersion; ?>
	</div>
	<?php if($migrateCheck) { ?>
		<br/>
		<h3>Services</h3>
		The KnowledgeTree services need to be started to allow for optimal functioning of the search, indexing and pdf generation.
		<?php if(!WINDOWS_OS) { ?>
		To start the services, execute the dmsctl.sh shell script in the KnowledgeTree directory from a terminal.
		<br/>
		<br/>
		<?php } else { ?>
		To start the services, execute the dmsctl.bat batch file in the KnowledgeTree directory from a command prompt, run as administrator.
		<br/>
		<br/>
		<?php } ?>
		<p class="disclaimer">
		<?php if(WINDOWS_OS) { ?>
			[START MENU] => [Programs] => [KnowledgeTree]
			<br/>
			Click [Start Services] 
			<br/>
			<br/>
			Alternatively:
			<br/>
			Open a command prompt and enter the following:
			<br/>
			cd <?php echo "\"".SYSTEM_DIR."\""; ?> <br/>
			dmsctl.bat install<br/>
			dmsctl.bat start
		<?php } else { ?>
			<?php if ($isCE) { ?>
				cd /usr/share/knowledgetree-ce<br/>
			<?php } else { ?>
				cd /usr/share/knowledgetree<br/>
			<?php } ?>
			sudo ./dmsctl.sh start
		<?php } ?>
		</p>
		<br/>
	<?php } else { ?>
	<br>
	<?php } ?>
	<div>
		<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Post_Install" target="_blank">
			Click Here for help on overcoming post install issues
		</a>
	</div>
	</div>
	<a href="../../login.php" class="back button_next" style="width:50px;" onclick="javascript:{w.clearSessions();}">Finish</a>
</form>