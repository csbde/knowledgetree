<form id="install_complete_none">
	<p class="title">
		Installation Completed
	</p>
	<p class="description">
		This step allows you to check that your KnowledgeTree configuration is set up correctly.
<!--		<br/>-->
		Click <b>Finish</b> to browse to the KnowledgeTree Web Interface. Default login credentials are <b>username:</b> <i>admin</i> and <b>password:</b> <i>admin</i>.
<!--		You can run this at any time after configuration to check	that things are still set up correctly.-->
	</p>
	<div>
		<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Post_Install" target="_blank">
			Click Here for help on overcoming post install issues
		</a>
	</div>
	<br/>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
	<?php if(!$servicesValidation) { ?>
		<h3>Services</h3>
		The KnowledgeTree services need to be started to allow for optimal functioning of the search, indexing and pdf generation.
		<?php if(!WINDOWS_OS) { ?>
		To start the services, execute the dmsctl.sh shell script in the KnowledgeTree directory from a terminal.
		<br/>
		<br/>
		<?php } else { ?>
		To start the services, execute the dmsctl.bat batch file in the KnowledgeTree directory from a command prompt, run as administrator.
		<br/>
		<br/>
		<?php } ?>
		<p class="disclaimer">
		<?php if(WINDOWS_OS) { ?>
			cd <?php echo "\"".SYSTEM_ROOT."\""; ?> <br/>
			dmsctl.bat install<br/>
			dmsctl.bat start
		<?php } else { ?>
			cd /usr/share/knowledgetree-ce<br/>
			sudo ./dmsctl.sh start
		<?php } ?>
		</p>
		<br/>
	<?php } ?>
	<!--	Paths and Permissions	-->
	<?php
		$iconFlag = "<span class='{$paths_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		$checkHeading = "<h3 style='width:35%; float:left;'>".$iconFlag."Paths and Permissions</h3>";
	?>
<!--		<div>-->
			<?php if($pathsSection) {
				$details = 'Show Details';
				$display = 'none';
			} else {
				$details = 'Hide Details';
				$display = 'block';
			}?>
<!--		    <h3><?php //echo "<span class='{$paths_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Paths and Permissions</h3>-->
		<?php if($silent) { ?>
<!--			<div id="option8" class="onclick" onclick="javascript:{w.toggleClass('paths_check', 'option8');}">Show Details</div>-->
<!--			<div class="paths_check" style="display:none">-->
		<?php } ?>
			<?php
				$pathsCheck = $checkHeading;
				$pathsCheck .= "<div id='option8' class='onclick' onclick='javascript:{w.toggleClass(\"paths_check\", \"option8\");}'>";
				$pathsCheck .= $details."</div>";
				$pathsCheck .= "<div class='paths_check dependency_details' style='display:".$display.";'>";
				echo $pathsCheck;
			?>
		    <table class="description_complete">
<!--			<table class="description">-->
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
<!--		</div>-->
		<br/>
		<br/>
<!--		<div>-->
	<!--	Database connectivity	-->
	<?php
		$iconFlag = "<span class='{$database_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		$checkHeading = "<h3 style='width:35%; float:left;'>".$iconFlag."Database connectivity</h3>";
	?>
			<?php if($databaseSection) {
				$details = 'Show Details';
				$display = 'none';
			} else {
				$details = 'Hide Details';
				$display = 'block';
			}?>
<!--		    <h3><?php //echo "<span class='{$database_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Database connectivity</h3>-->
		<?php if($silent) { ?>
<!--			<div id="option9" class="onclick" onclick="javascript:{w.toggleClass('database_check', 'option9');}">Show Details</div>-->
<!--			<div class="database_check" style="display:none">-->
			<?php
				$databaseCheck = $checkHeading;
				$databaseCheck .= "<div id='option9' class='onclick' onclick='javascript:{w.toggleClass(\"database_check\", \"option9\");}'>";
				$databaseCheck .= $details."</div>";
				$databaseCheck .= "<div class='database_check dependency_details' style='display:".$display.";'>";
				echo $databaseCheck;
			?>
		<?php } ?>
		    <table class="description_complete">
		        <tr><?php echo $dbConnectAdmin; ?></tr>
		        <tr><?php echo $dbConnectUser; ?></tr>
		    </table>
		<?php if($silent) { ?>
		</div>
	<!--	Privileges	-->
		<br/>
		<br/>
		<?php } ?>
	<?php
		$iconFlag = "<span class='{$privileges_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		$checkHeading = "<h3 style='width:35%; float:left;'>".$iconFlag."Privileges</h3>";
	?>
			<?php if($privilegesSection) {
				$details = 'Show Details';
				$display = 'none';
			} else {
				$details = 'Hide Details';
				$display = 'block';
			}?>
<!--		    <h3><?php //echo "<span class='{$privileges_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Privileges</h3>-->
		<?php if($silent) { ?>
<!--			<div id="option1" class="onclick" onclick="javascript:{w.toggleClass('privileges_check', 'option1');}">Show Details</div>-->
<!--			<div class="privileges_check" style="display:none">-->
			<?php
				$privilegesCheck = $checkHeading;
				$privilegesCheck .= "<div id='option1' class='onclick' onclick='javascript:{w.toggleClass(\"privileges_check\", \"option1\");}'>";
				$privilegesCheck .= $details."</div>";
				$privilegesCheck .= "<div class='privileges_check dependency_details' style='display:".$display.";'>";
				echo $privilegesCheck;
			?>
		<?php } ?>
		    <table class="description_complete">
		        <tr><?php echo $dbPrivileges; ?></tr>
		        <tr><?php echo $dbTransaction; ?></tr>
		    </table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
<!--		</div>-->
	<!--	Services	-->
		<br/>
<!--		<br/>-->
<?php if($servicesValidation) { ?>
<?php if(!$migrate_check) { ?>
		<div>
		    <h3><?php echo "<span class='{$services_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services</h3>
		<?php if($silent) { ?>
			<div id="option2" class="onclick" onclick="javascript:{w.toggleClass('services_check', 'option2');}">Show Details</div>
			<div class="services_check" style="display:none">
		<?php } ?>
<!--		    <table class="description_complete">-->
			<table class="description">
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
		<?php } ?>
	</div>
	<?php
        $script = $_SERVER['SCRIPT_NAME'];
        $file_system_root = $_SERVER['DOCUMENT_ROOT'];
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        //$ssl_enabled = isset($_SERVER['HTTPS']) ? (strtolower($_SERVER['HTTPS']) === 'on' ? 'yes' : 'no') : 'no';

        $pos = strpos($script, '/setup/wizard/');
        $root_url = substr($script, 0, $pos);
        if($port == '')
        	$redirect = "http://".$_SERVER['SERVER_NAME'].$root_url."/admin.php";
        else
        	$redirect = "http://".$_SERVER['SERVER_NAME'].":$port".$root_url."/admin.php";
	?>
	<?php if($migrate_check) { ?>
		<a href="../upgrade/index.php" class="back button_next" style="width:30px;" onclick="javascript:{w.clearSessions();}">Next</a>
    <?php } else { ?>
    	<a href="../../login.php" class="back button_next" style="width:50px;" onclick="javascript:{w.clearSessions();}">Finish</a>
    <?php } ?>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>