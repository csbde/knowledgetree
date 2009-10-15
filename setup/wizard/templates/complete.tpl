<form id="install_complete_none">
	<p class="title">Installation Completed</p>

	<p class="description">This allows you to check that your KnowledgeTree configuration is set
	up correctly.  You can run this at any time after configuration to check
	that things are still set up correctly.</p>
	
	<?php
	if($errors || $warnings){
	    echo '<div>'
	       . '<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Post_Install" target="_blank">'
	       . 'Click Here for help on overcoming post install issues</a></div><br/>';
	}
	?>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	<!--	Paths and Permissions	-->
		<div>
		    <h3><?php echo "<span class='{$paths_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Paths and Permissions</h3>
		<?php if($silent) { ?>
			<div id="option8" class="onclick" onclick="javascript:{w.toggleClass('paths_check', 'option8');}">Show Details</div>
			<div class="paths_check" style="display:none">
		<?php } ?>
		    <table>
		        <tr><?php echo $varDirectory; ?></tr>
		        <tr><?php echo $documentRoot; ?></tr>
		        <tr><?php echo $logDirectory; ?></tr>
		        <tr><?php echo $tmpDirectory; ?></tr>
		        <tr><?php echo $uploadDirectory; ?></tr>
		        <tr><?php echo $config; ?></tr>
		        <tr><?php echo $docLocation; ?></tr>
		    </table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		</div>
		<br/><br/>
		<div>
	<!--	Database connectivity	-->
		    <h3><?php echo "<span class='{$database_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Database connectivity</h3>
		<?php if($silent) { ?>
			<div id="option9" class="onclick" onclick="javascript:{w.toggleClass('database_check', 'option9');}">Show Details</div>
			<div class="database_check" style="display:none">
		<?php } ?>
		    <table>
		        <tr><?php echo $dbConnectAdmin; ?></tr>
		        <tr><?php echo $dbConnectUser; ?></tr>
		    </table>
		<?php if($silent) { ?>
		</div>
	<!--	Privileges	-->
		<br/><br/>
		<?php } ?>
		    <h3><?php echo "<span class='{$privileges_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Privileges</h3>
		<?php if($silent) { ?>
			<div id="option1" class="onclick" onclick="javascript:{w.toggleClass('privileges_check', 'option1');}">Show Details</div>
			<div class="privileges_check" style="display:none">
		<?php } ?>
		    <table style="width:265px;">
		        <tr><?php echo $dbPrivileges; ?></tr>
		        <tr><?php echo $dbTransaction; ?></tr>
		    </table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		</div>
	<!--	Services	-->
		<br/><br/>
<?php if(!$migrate_check) { ?>
		<div>
		    <h3><?php echo "<span class='{$services_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services</h3>
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
							<a href="#" class="refresh" onclick="w.refresh('complete')">Refresh</a>
						<?php } else { ?>
							<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
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
							<a href="#" class="refresh" onclick="w.refresh('complete')">Refresh</a>
						<?php } else { ?>
							<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
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
							<a href="#" class="refresh" onclick="w.refresh('complete')">Refresh</a>
						<?php } else { ?>
							<a href="javascript:this.location.reload();" class="refresh">Refresh</a>
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
	
	<?php if($migrate_check) { ?>
		<a href="../../control.php" class="buttons back upgrade" style="width:80px;" onclick="javascript:{w.clearSessions();}">Goto Login</a>
    <?php } else { ?>
    	<a href="../../control.php" class="buttons back upgrade" style="width:80px;" onclick="javascript:{w.clearSessions();}">Goto Login</a>
    <?php } ?>
    <?php
        if (INSTALL_TYPE == 'Zend') {
        	?>
            <a href="<?php echo "http://".$_SERVER['HTTP_HOST'].":10081/ZendServer/Index"; ?>" class="back" target="_blank" onclick="javascript:{w.clearSessions();}">Zend Server Configuration</a>
            <?php
        }
    ?>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>