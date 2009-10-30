<?php //global $default; ?>
<form>
	<p class="title">Database Upgrade Completed</p>

	<div id="step_content_complete" class="step">
<!--	Services	-->
	<br/><br/>
	<div>
	    Your database has been upgraded to <?php echo $sysVersion; ?>
	</div>
	</div>
	<a href="../../login.php" class="back button_next" style="width:90px;" onclick="javascript:{w.clearSessions();}">Goto Login</a>
</form>