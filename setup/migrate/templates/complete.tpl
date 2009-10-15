<form action="index.php?step_name=<?php echo $step_name; ?>" method="post" id="<?php echo $step_name; ?>">
	<p class="title">Migration Completed</p>

	<p class="description">This allows you to check that your KnowledgeTree configuration is set
	up correctly.  You can run this at any time after configuration to check
	that things are still set up correctly.</p>
	
	<?php
	if($errors || $warnings){
	    echo '<div>'
	       . '<a href="http://wiki.knowledgetree.com/Web_Based_Migrater#Post_Migrate" target="_blank">'
	       . 'Click Here for help on overcoming post migrate issues</a></div><br/>';
	}
	?>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
<!--	SQL	-->
		<h3>Exported Database</h3>
		<table>
	    	<tr>
	    		<td> <span class='<?php echo $sql['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
	    		<td style="width:25%;"> <?php echo $sql['name']; ?> </td>
	    		<td style="width:75%;"> <?php echo $sql['msg']; ?> </td>
	    	<tr>
	    </table>
<!--	Services	-->
		<h3>Uninstalled Services</h3>
		<table>
		<?php
			if(isset($step_vars['services'])) {
			    foreach ($step_vars['services'] as $ser){
			    	?>
			    	<tr>
			    		<td> <span class='<?php echo $ser['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
			    		<td style="width:25%;"> <?php echo $ser['name']; ?> </td>
			    		<td style="width:75%;"> <?php echo $ser['msg']; ?> </td>
			    		<?php if ($ser['class'] != 'tick') {
			    			?>
			    			<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
			    			<?php
			    		} ?>
			    	</tr>
			    	<?php
			    }
			}
		?>
		</table>
<!--	Paths		-->
		<h3>System Path</h3>
		<table>
		<?php
		if(isset($step_vars['paths'])) {
			    foreach ($step_vars['paths'] as $path){
			    	?>
			    	<tr>
			    		<td> <span class='<?php echo $path['class']; ?>'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> </td>
			    		<td style="width:25%;"> <?php echo $path['name']; ?> </td>
			    		<td style="width:75%;"> <?php echo $path['msg']; ?> </td>
			    		<?php if ($path['class'] != 'tick') {
			    			?>
<!--			    			<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>-->
			    			<?php
			    		} ?>
			    	</tr>
			    	<?php
			    }
			}
		?>
		</table>
	</div>
    <input class="button_next" type="submit" value="Continue Installion" name="BInstall"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>