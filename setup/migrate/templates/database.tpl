<form id="dbsettings" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Migrate Database</p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step configures the connection to the database server and migrates the database. The details for an administrative <br/>
		user on the database server are required in order to be able to configure and migrate the migrateation database.
		</div>
		<div id="step_content_database" class="step">
		
		</div>
	</div>
    <input type="button" name="Previous" value="previous" class="button_previous"/>
	<input type="submit" name="Next" value="next" class="button_next"/>
</form>