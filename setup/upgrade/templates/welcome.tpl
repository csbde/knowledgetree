<form action="index.php?step_name=welcome" method="post">
	<p class="title">Welcome to the KnowledgeTree Database Upgrade Wizard</p>
	<input type="hidden" value="<?php echo $upgradeOnly?>" name="upgradeOnly">
	<div id="step_content" class="step">
		<br/>
		<br/>
		<p class="empty_space"> The database upgrade wizard completes the upgrade process on an existing KnowledgeTree installation. It applies
        any upgrades to the database that may be required. </p>
        <p class="empty_space"> Only administrator users may access the upgrade wizard. </p>
		<div class="demo">
            <table>
                <tr><td>Username</td><td><input id="username" name=username></td></tr>
                <tr><td>Password</td><td><input name=password type="password"></td></tr>
                <?php if (!empty($errors)) { ?><tr><td></td><td><span class="error">Could Not Authenticate User</span></td></tr> <?php } ?>
            </table>

        </div>
	</div>
	<?php
	if(isset($_GET['action'])) {
		if($_GET['action']) {
			?>
				<input type="submit" name="Return" value="Return To Installation" class="button_previous"/>
			<?php
		}
	}
	?>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$('#username').focus();
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>