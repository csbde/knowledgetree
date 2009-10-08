<form id="dbsettings" action="index.php?step_name=database" method="post">
	<p class="title"><?php echo $title; ?></p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step performs the necessary Database Upgrades.
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
                if (!$backupStatus) { ?>
                    <p>We are about to start the upgrade process.<P>
                <?php }
                else { ?>
                <br/>
                <font color="Red">Please ensure that you have made a backup before continuing with the upgrade process.</font>
                <p>
                <?php } ?>
            <?php }
            else if ($action == 'runUpgrade') {
                echo $preUpgrade;
                echo '<br/><br/>';
                echo $upgradeTable;
                echo '<br/><br/>';
                echo $postUpgrade;
                echo '<br/><br/>';
                echo $upgradeStatus;
                echo '<br/><br/>';
            }
            ?>
		</div>
	</div>
    <?php if (empty($action) || ($action == 'preview')) { ?>
    <input type="submit" name="Previous" value="previous" class="button_previous"/>
    <input type="submit" name="ConfirmUpgrade" value="next" class="button_next"/>
    <?php }
    else if ($action == 'confirm') { ?>
    <input type="submit" name="Cancel" value="cancel" class="button_previous"/>
    <input type="submit" name="RunUpgrade" value="next" class="button_next"/>
    <?php }
    else if ($action == 'runUpgrade') { ?>
    <script type="text/javascript">
        alert("To complete the upgrade please do the following before continuing:\n\n1. Restart the services as appropriate for your environment.\n\n\nOn first run of your upgraded installaton please do the following:\n\n1. Hard refresh your bowser (CTRL-F5) on first view of the Dashboard.\n2. Enable the new plugins you wish to use.\n\n\nSelect 'next' at the bottom of this page to continue.")
    </script>
    <input type="submit" name="Next" value="next" class="button_next"/>
    <?php } ?>
</form>