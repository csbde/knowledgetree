<?php //global $default; ?>
<form>
	<p class="title">Database Upgrade Completed</p>

	<div id="step_content_complete" class="step">
<!--	Services	-->
	<br/><br/>
	<div>
	    Your database has been upgraded to <?php echo $sysVersion; ?>
	</div>
<br/><br/>
	<?php if($migrateCheck) { ?>
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
				cd <?php echo "\"".SYSTEM_ROOT."\""; ?> <br/>
				dmsctl.bat install<br/>
				dmsctl.bat start
			<?php } else { ?>
				cd /usr/share/knowledgetree-ce<br/>
				sudo ./dmsctl.sh start
			<?php } ?>
			</p>
			<br/>
		<?php } ?>
	</div>
	<a href="../../login.php" class="back button_next" style="width:50px;" onclick="javascript:{w.clearSessions();}">Finish</a>
</form>