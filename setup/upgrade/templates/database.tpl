<form id="dbsettings" action="index.php?step_name=database" method="post">
	<p class="title"><?php echo $title; ?></p>
	<div id="database" class="step1" style="display:block;">
		<div class="description">
		This step performs the necessary Database Upgrades.
		</div>
		<div id="step_content_database" class="step">
            <br/><br/>
            <?php if ($action == 'preview') { ?>
			<p>The table below describes the upgrades that need to occur to
            upgrade your KnowledgeTree installation to <strong><?php echo $systemVersion;?></strong>.
            Click on the button below the table to perform the upgrades.</p>
            <?php echo $upgradeTable; ?>
            <?php }
            else if ($action == 'runUpgrade') { ?>
            <br/>
            <font color="Red">Please ensure that you have made a backup before continuing with the upgrade process.</font>
            <p>
            <br/>
            <?php } ?>
            <p>
            We are about to start the upgrade process.
            <P>
		</div>
	</div>
    <?php if ($action == 'preview') { ?>
    <input type="submit" name="Previous" value="previous" class="button_previous"/>
    <input type="submit" name="RunUpgrade" value="next" class="button_next"/>
    <?php }
    else if ($action == 'runUpgrade') { ?>
    <input type="submit" name="Cancel" value="cancel" class="button_previous"/>
    <input type="submit" name="Confirm" value="next" class="button_next"/>
    <?php } ?>
</form>