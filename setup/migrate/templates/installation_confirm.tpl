<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Current Installation</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				Installation has been detected. Confirm system settings.
				<br/><br/>
			<?php
		}
	?>
<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>Your system is not quite ready to migrate KnowledgeTree. See the list below to determine which areas you need to address.</span>
		<br/>
	<?php } elseif ($warnings) {
		?>
		<?php
	}?>
	<?php
		if($errors || $warnings) {
			?>
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Current_Installation" target="_blank">Click here for help on overcoming installation detection issues</a>
	<?php } ?>
	<!--Content-->
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<p class="empty_space">
			Verify your current installation settings.
		</p>
		<h3>Installation Settings</h3>
		<table class="conf_paths">
			<tr>
			<?php
				if($versionError) {
					$w = '26%';
					$x = '10%';
				} else {
					$w = '22%';
					$x = '50%';
				}
			?>
				<td width="<?php echo $w; ?>">KnowledgeTree Version: </td>
				<td width="<?php echo $x; ?>"><?php echo $foundVersion; ?></td>
				<?php if($versionError) { ?> <td class="error" width="50%"> KnowledgeTree installation needs to be 3.6.1 or higher </td> <?php } ?>
			</tr>
		</table>
		
		<h3>Database Settings</h3>
		<table class="conf_paths">
			<tr>
				<td width="22%"">Database Name: </td>
				<td width="50%"><?php echo $dbSettings['dbName']; ?></td>
			</tr>
		</table>
		
		<h3>System Paths</h3>
		<table class="conf_paths">
		<?php foreach ($urlPaths as $key => $path) { ?>
			<tr>
				<td width="22%"><?php echo $path['name']; ?>:</td>
				<td width="50%""><?php echo $path['path']; ?></td>
			</tr>
		<?php
		    }
		?>
		</table>
		
	</div>
    <input type="submit" name="Edit" value="Back"  class="button_previous"/>
    <input type="submit" name="Confirm" value="Confirm" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>