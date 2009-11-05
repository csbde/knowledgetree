<?php //global $default; ?>
<form>
	<p class="title">Database Upgrade Completed</p>

	<div id="step_content_complete" class="step">
<!--	Services	-->
	<br/><br/>
	<div>
	    Your database has been upgraded to <?php echo $sysVersion; ?>
	</div>
<br/><br/>
	<?php if($migrateCheck) { ?>
		<div>
		    <h3><?php echo "<span class='{$servicesCheck}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services</h3>
		<?php if($silent) { ?>
			<div id="option2" class="onclick" onclick="javascript:{w.toggleClass('services_check', 'option2');}">Show Details</div>
			<div class="services_check" style="display:none">
		<?php } ?>
		    <table style="width:755px;">
		        <tr>
		        	<td style="width:15px;"> <?php echo "<span class='{$LuceneStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
		        	<td style="width:640px;"> Lucene Service <?php if ($LuceneStatus != 'tick') { ?> Could not be started <?php } else { ?> Started <?php } ?></td>
		        	<?php if ($LuceneStatus != 'tick') { ?>
		        		<td>
						<?php if (AJAX) { ?>
<!--							<a href="#" class="refresh" onclick="w.refresh('complete')">Refresh</a>-->
						<?php } else { ?>
<!--							<a href="javascript:this.location.reload();" class="refresh">Refresh</a>-->
						<?php } ?>
		        		</td>
		        	<?php } ?>
		        </tr> 
		        <tr>
		        	<td> <?php echo "<span class='{$SchedulerStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
		        	<td> Scheduler Service <?php if ($SchedulerStatus != 'tick') { ?> Could not be started <?php } else { ?> Started <?php } ?></td>
		        	<?php if ($SchedulerStatus != 'tick') { ?>
		        		<td>
						<?php if (AJAX) { ?>
<!--							<a href="#" class="refresh" onclick="w.refresh('complete')">Refresh</a>-->
						<?php } else { ?>
<!--							<a href="javascript:this.location.reload();" class="refresh">Refresh</a>-->
						<?php } ?>
		        		</td>
		        	<?php } ?>
		        </tr>
		        <tr>
		        	<td> <?php echo "<span class='{$OpenOfficeStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
		        	<td> OpenOffice Service <?php if ($OpenOfficeStatus != 'tick') { ?> Could not be started <?php } else { ?> Started <?php } ?></td>
		        	<?php if ($OpenOfficeStatus != 'tick') { ?>
		        		<td>
						<?php if (AJAX) { ?>
<!--							<a href="#" class="refresh" onclick="w.refresh('complete')">Refresh</a>-->
						<?php } else { ?>
<!--							<a href="javascript:this.location.reload();" class="refresh">Refresh</a>-->
						<?php } ?>
		        		</td>
		        	<?php } ?>
		        </tr>
		    </table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		</div>
		<?php } ?>
	</div>
	<a href="../../login.php" class="back button_next" style="width:50px;" onclick="javascript:{w.clearSessions();}">Finish</a>
</form>