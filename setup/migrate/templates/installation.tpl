<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Current Installation</p>
<!--Warning and Error Messages-->
	<?php if($errors) { ?>
		<span class='cross'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class='error_message'>The Setup Wizard will not continue until the issues below have been addressed</span>
		<br/>
	<?php } elseif ($warnings) {
		?>
		<?php
	}?>
	<?php
		if($errors || $warnings) {
			?>
		    	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Current_Installation" target="_blank">Click here for help on overcoming upgrade issues  </a>
	<?php } ?>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<p class="description">
			The Setup Wizard will now prepare your current KnowledgeTree database for migration to the new KnowledgeTree Stack. 
			</p>
			
			<p class="description">
			Enter the full path of the installation you wish to upgrade:
		</p>
		
		<input id="location" name="location" type="text" style="width:430px; float:left" value="<?php if($location) echo $location; ?>">
		<br/><br/>
		<p class="description">
			<small>(The default is <b>'C:\Program Files\ktdms'</b> on Windows and <b>'/opt/ktdms'</b> or <b>'/home/<i>username</i>/ktdms/'</b> on other operating systems.)</small>
		</p>
		<?php
		if($errors) {
			foreach ($errors as $error) {
				?>
				<span class="error"> <?php echo $error; ?> </span>
				<?php
			}
		}
		?>
	</div>
	<input type="submit" name="Install" value="Back To Installer" class="button_previous"/>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$("#location").focus();
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>