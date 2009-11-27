<form action="index.php?step_name=installation" method="post">
	<p class="title">Current Installation</p>
	<div id="step_content" class="step">
		<p class="empty_space">
        <p class="empty_space"> If you have just updated
        your KnowledgeTree code base, you will need to complete the upgrade process in order to ensure your system is fully operational with the new version.
        <p class="empty_space">
        You will not be able to log into KnowledgeTree until your the database upgrade process is completed.
        </p>
        <p class="empty_space">
        <font color="red">You are advised to backup the database before attempting the upgrade.</font>
        </p>
        <p class="empty_space"></p>
        If you have already done this, you may skip this step and can continue directly to the upgrade.
	</div>
    <input type="submit" name="Previous" value="Cancel" class="button_previous"/>
    <!-- NOTE class button_next requires reverse order to the desired display order, go figure :) -->
    <input type="submit" name="Upgrade" value="Upgrade" class="button_next"/>
    <input type="submit" name="Restore" value="Restore Database" class="button_next"/>
    <input type="submit" name="Next" value="Backup Now" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>