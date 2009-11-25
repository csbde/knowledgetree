<form action="index.php?step_name=database" method="post" name="dbForm" id="dbForm">
	<p class="title"><?php echo $title; ?></p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step performs the necessary Database Upgrades.
		<?php
			echo "<br/>";
			foreach ($errors as $error) {
				if($error != '')
					echo "<span class = 'error'>$error</span><br/>";
			}
		?>
		</div>
		<div id="step_content_database" class="step">
            <br/><br/>
            <?php if (empty($action) || ($action == 'preview')) { ?>
			<p>The table below describes the upgrades that need to occur to
            upgrade your KnowledgeTree installation to <strong><?php echo $systemVersion;?></strong>.
            Click on the button below the table to perform the upgrades.</p>
            <?php echo $upgradeTable; ?>
            <?php }
            else if ($action == 'confirm') {
                if ($backupStatus) { ?>
                    <p>We are about to start the upgrade process. Are you sure you want to proceed with the upgrade process?<P>
                <?php }
                else { ?>
                    <p><font color="Red">Please ensure that you have made a backup before continuing with the upgrade process.</font><p>
                <?php } ?>
            <?php }
            else if ($action == 'runUpgrade') {
                // hiding pre-upgrade notification
                echo $upgradeTable;
                echo '<br/><br/>';
                // hiding pre-upgrade notification
                echo $upgradeStatus;
                echo '<br/><br/>';
            }
            ?>
		</div>
	</div>
	<?php include 'templates/loading.tpl'; ?>
	<div id="buttonBar">
    <?php if (empty($action) || ($action == 'preview')) { ?>
    <input type="submit" name="Previous" value="Previous" class="button_previous"/>
    <input type="submit" name="ConfirmUpgrade" value="Next" class="button_next"/>
    <?php }
    else if ($action == 'confirm') { ?>
    <input type="submit" name="Cancel" value="Cancel" class="button_previous"/>
    <input type="button" name="RunUpgrade" value="Next" class="button_next" onclick="doSubmit(this);"/>
    <?php }
    else if ($action == 'runUpgrade') { ?>
		<?php if ($backupSuccessful) { ?>
		<?php if($migrateCheck) { ?>
		<script type="text/javascript">
		alert("To complete the upgrade please do the following before continuing:\n\n1. Hard refresh your browser (CTRL-F5) on first view of the Dashboard.\n\n\nSelect 'Finish' at the bottom of this page to continue.");
		</script>
		<?php } else { ?>
		<script type="text/javascript">
        alert("To complete the upgrade please do the following before continuing:\n\n1. Restart the services as appropriate for your environment.\n\n\nOn first run of your upgraded installaton please do the following:\n\n1. Hard refresh your bowser (CTRL-F5) on first view of the Dashboard.\n2. Enable the new plugins you wish to use.\n\n\nSelect 'Finish' at the bottom of this page to continue.")
    </script>
    	<?php } ?>
				<input type="submit" name="Next" value="Finish" class="button_next"/>
		<?php }
			  else { ?><input type="submit" name="Previous" value="Restore" class="button_previous"/><?php } ?>
    <?php } ?>
	</div>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>