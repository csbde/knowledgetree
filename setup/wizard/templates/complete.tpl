<form id="install_complete_none">
	<p class="title">
		Installation Completed
	</p>
	<p class="description">
		This step allows you to check that your KnowledgeTree configuration is set up correctly.
		Click <b>Finish</b> to browse to the KnowledgeTree Web Interface. Default login credentials are <b>username:</b> <i>admin</i> and <b>password:</b> <i>admin</i>.
	</p>
	<div>
		<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Post_Install" target="_blank">
			Click Here for help on overcoming post install issues
		</a>
	</div>
	<br/>
	<div id="step_content_<?php echo $step_name; ?>" class="step">

	<?php if(!$servicesValidation) { ?>
		<?php if(!$migrate_check) { ?>
			<h3>Services</h3>
			The KnowledgeTree services need to be started to allow for optimal functioning of the search, indexing and pdf generation.
			<?php if(!WINDOWS_OS) { ?>
			To start the services, execute the dmsctl.sh shell script in the KnowledgeTree directory from a terminal.
			<br/>
			<br/>
			<?php } else { ?>
			To start the services, using the instructions below:
<!--			execute the dmsctl.bat batch file in the KnowledgeTree directory from a command prompt, run as administrator.-->
			<br/>
			<br/>
			<?php } ?>
			<p class="disclaimer">
				<?php if(WINDOWS_OS) { ?>
					<b>Using shortcuts:</b>
					<br/>
					[START MENU] => [Programs] => [KnowledgeTree 3.7.x] => [services]
					<br/>
					Right-Click and run [Install Services] as administrator
					<br/>
					[START MENU] => [Programs] => [KnowledgeTree 3.7.x] => [services]
					<br/>
					Right-Click and run [Start Services] as administrator
					<br/>
					<br/>
					<b>Alternatively:</b>
					<br/>
					Open a command prompt and enter the following:
					<br/>
					cd <?php echo "\"".SYSTEM_DIR."\""; ?> <br/>
					dmsctl.bat install<br/>
					dmsctl.bat start
				<?php } else { ?>
				<?php if ($isCE) { ?>
					cd /usr/share/knowledgetree-ce<br/>
				<?php } else { ?>
					cd /usr/share/knowledgetree<br/>
				<?php } ?>
				sudo ./dmsctl.sh start
			<?php } ?>
			</p>
			<br/>
		<?php } ?>
	<?php } ?>
	<!--	Paths and Permissions	-->
	<?php
		$iconFlag = "<span class='{$paths_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
		$checkHeading = "<h3 style='width:35%; float:left;'>".$iconFlag."Paths and Permissions</h3>";
	?>
			<?php if($pathsSection) {
				$details = 'Show Details';
				$display = 'none';
			} else {
				$details = 'Hide Details';
				$display = 'block';
			}?>
			<?php
				$pathsCheck = $checkHeading;
				$pathsCheck .= "<div id='option8' class='onclick' onclick='javascript:{w.toggleClass(\"paths_check\", \"option8\");}'>";
				$pathsCheck .= $details."</div>";
				$pathsCheck .= "<div class='paths_check dependency_details' style='display:".$display.";'>";
				echo $pathsCheck;
			?>
		    <table class="description_complete">
				<tr></tr>
		        <?php if (!empty($varDirectory)) echo '<tr>' . $varDirectory . '</tr>'; ?>
		        <?php if (!empty($documentRoot)) echo '<tr>' . $documentRoot . '</tr>'; ?>
		        <?php if (!empty($logDirectory)) echo '<tr>' . $logDirectory . '</tr>'; ?>
		        <?php if (!empty($tmpDirectory)) echo '<tr>' . $tmpDirectory . '</tr>'; ?>
		        <?php if (!empty($uploadDirectory)) echo '<tr>' . $uploadDirectory . '</tr>'; ?>
		        <?php if (!empty($config)) echo '<tr>' . $config . '</tr>'; ?>
		        <?php if (!empty($docLocation)) echo '<tr>' . $docLocation . '</tr>'; ?>
		    </table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		<br/>
		<br/>
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
		<?php if($silent) { ?>
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
		<?php if($silent) { ?>
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
			<table class="description">
		        <tr>
		        	<td style="width:15px;"> <?php echo "<span class='{$LuceneStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
		        	<td style="width:640px;"> Lucene Service <?php if ($LuceneStatus != 'tick') { ?> Could not be started <?php } else { ?> Started <?php } ?></td>
		        </tr>
		        <tr>
		        	<td> <?php echo "<span class='{$SchedulerStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
		        	<td> Scheduler Service <?php if ($SchedulerStatus != 'tick') { ?> Could not be started <?php } else { ?> Started <?php } ?></td>
		        </tr>
		        <tr>
		        	<td> <?php echo "<span class='{$OpenOfficeStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?> </td>
		        	<td> OpenOffice Service <?php if ($OpenOfficeStatus != 'tick') { ?> Could not be started <?php } else { ?> Started <?php } ?></td>
		        </tr>
		    </table>
		<?php if($silent) { ?>
			</div>
		<?php } ?>
		</div>
		<?php } ?>
		<?php } ?>
	<div class="connect">
		<a class="twitter" href="http://www.twitter.com/knowledgetreesw" style="color:#FFFFFF;" target="_blank">
			<?php echo $html->image('twitterbutton.png'); ?>
		</a>
		<br/>
		<br/>
		<a class="facebook" href="http://www.facebook.com/pages/KnowledgeTree-Document-Management-Made-Simple/59248880725" style="color:#FFFFFF;" target="_blank">
			<?php echo $html->image('facebookbutton.png'); ?>
		</a>
	</div>
	</div>
	<?php
        $script = $_SERVER['SCRIPT_NAME'];
        $file_system_root = $_SERVER['DOCUMENT_ROOT'];
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $pos = strpos($script, '/setup/wizard/');
        $root_url = substr($script, 0, $pos);
        if($port == '')
        	$redirect = "http://".$_SERVER['SERVER_NAME'].$root_url."/admin.php";
        else
        	$redirect = "http://".$_SERVER['SERVER_NAME'].":$port".$root_url."/admin.php";
	?>
	<?php if($migrate_check) { ?>
<!--		<a href="../upgrade/index.php" class="back button_next" style="width:30px;" onclick="javascript:{w.clearSessions();}">Next</a>-->
		<input type="submit" name="Upgrade" value="Next" class="button_next"/>
    <?php } else { ?>
<!--    	<a href="../../login.php" class="back button_next" style="width:50px;" onclick="javascript:{w.clearSessions();}">Finish</a>-->
		<input type="submit" name="Next" value="Finish" class="button_next"/>
    <?php } ?>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>