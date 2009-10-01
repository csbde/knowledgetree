<form action="index.php?step_name=installation" method="post">
	<p class="title">Current Installation</p>
<!--Continue Message-->
	<?php
		if(!$errors && !$warnings) {
			?>
				<span class='big_ok'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
				Installation has been detected. Please click next to continue.
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
	<div id="step_content" class="step">
		<br/>
		<br/>
		<p class="empty_space">
			Please verify the location of your current installation.
		</p>
		
		<input id="location" name="location" type="text" style="width:430px; float:left" value="<?php echo $location; ?>">
		<br/><br/>
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
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$("#location").focus();
</script>