<h1>Installation Completed</h1>

<h2>KnowledgeTree<sup>&#174;</sup> post-configuration checkup</h2>

<p>This allows you to check that your KnowledgeTree<sup>&#174;</sup> configuration is set
up correctly.  You can run this at any time after configuration to check
that things are still set up correctly.</p>

<?php
if($errors || $warnings){
    echo '<div>'
       . '<a href="http://wiki.knowledgetree.com/Web_Based_Installer#Post_Install" target="_blank">'
       . 'Click Here for help on overcoming post install issues</a></div><br/>';
}
?>

<div>
    <h3><?php echo "<span class='{$paths_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Paths and Permissions</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('paths_check');}">Show Details</div>
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
<div>
    <h3><?php echo "<span class='{$database_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Database connectivity</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('database_check');}">Show Details</div>
	<div class="database_check" style="display:none">
<?php } ?>
    <table>
        <tr><?php echo $dbConnectAdmin; ?></tr>
        <tr><?php echo $dbConnectUser; ?></tr>
    </table>
<?php if($silent) { ?>
	</div>
<?php } ?>
    <h3><?php echo "<span class='{$privileges_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Privileges</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('privileges_check');}">Show Details</div>
	<div class="privileges_check" style="display:none">
<?php } ?>
    <table>
        <tr><?php echo $dbPrivileges; ?></tr>
        <tr><?php echo $dbTransaction; ?></tr>
    </table>
<?php if($silent) { ?>
	</div>
<?php } ?>
</div>
<div>
    <h3><?php echo "<span class='{$services_check}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Services</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('services_check');}">Show Details</div>
	<div class="services_check" style="display:none">
<?php } ?>
    <table>
        <tr>
        	<td><?php echo "<span class='{$LuceneStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Lucene Service</td>
        </tr> 
        <tr>
        	<td><?php echo "<span class='{$SchedulerStatus}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>Scheduler Service</td>
        </tr>
    </table>
<?php if($silent) { ?>
	</div>
<?php } ?>
</div>
<form>
	<div class="buttons">
<!--	    <a href="index.php?step_name=complete&step=previous" class="previous">Previous</a>-->
	    <a href="">Refresh</a>
	    <a href="../../">Goto Login</a>
	    <?php
	        if (INSTALL_TYPE == 'Zend') {
	            echo '<a href="http://' . $_SERVER['HTTP_HOST'] . '/ZendServer/Index">Zend Server Configuration</a>';
	        }
	    ?>
</div>
</form>