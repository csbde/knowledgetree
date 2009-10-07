<form>
	<p class="title">Upgrade Completed</p>

	<p class="description">This allows you to check that your KnowledgeTree configuration is set
	up correctly.  You can run this at any time after configuration to check
	that things are still set up correctly.</p>
	
	<?php
	if($errors || $warnings){
	    echo '<div>'
	       . '<a href="http://wiki.knowledgetree.com/Web_Based_Upgrader#Post_Upgrade" target="_blank">'
	       . 'Click Here for help on overcoming post Upgrade issues</a></div><br/>';
	}
	?>
	<div id="step_content_complete" class="step">
<!--	Services	-->
	<br/><br/>
	<div>
	    <h3><?php echo "<span class='{$services_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services</h3>
	<?php if($silent) { ?>
		<div id="option2" class="onclick" onclick="javascript:{w.toggleClass('services_check', 'option2');}">Show Details</div>
		<div class="services_check" style="display:none">
	<?php } ?>
	    <table style="width:755px;">
	        <tr>
	        	<td style="width:15px;"> <?php echo "<span class='{$LuceneStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
	        	<td style="width:640px;"> Lucene Service <?php if ($LuceneStatus != 'tick') { ?> Could not be stopped <?php } else { ?> Stopped <?php } ?></td>
	        	<?php if ($LuceneStatus != 'tick') { ?>
	        		<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
	        	<?php } ?>
	        </tr> 
	        <tr>
	        	<td> <?php echo "<span class='{$SchedulerStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
	        	<td> Scheduler Service <?php if ($LuceneStatus != 'tick') { ?> Could not be stopped <?php } else { ?> Stopped <?php } ?></td>
	        	<?php if ($SchedulerStatus != 'tick') { ?>
	        		<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
	        	<?php } ?>
	        </tr>
	        <tr>
	        	<td> <?php echo "<span class='{$OpenOfficeStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
	        	<td> OpenOffice Service <?php if ($OpenOfficeStatus != 'tick') { ?> Could not be stopped <?php } else { ?> Stopped <?php } ?></td>
	        	<?php if ($OpenOfficeStatus != 'tick') { ?>
	        		<td><a href="javascript:this.location.reload();" class="refresh">Refresh</a></td>
	        	<?php } ?>
	        </tr>
	    </table>
	<?php if($silent) { ?>
		</div>
	<?php } ?>
	</div>
	</div>
    <a href="../wizard" class="buttons back" style="width:100px;">Goto Installer</a>
</form>