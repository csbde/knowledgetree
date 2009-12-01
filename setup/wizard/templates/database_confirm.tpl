<form id="services_database_registration" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
<p class="title">Confirming Database Configurations</p>

	<div class="description">
	Confirm whether KnowledgeTree has correctly determined your database settings before proceeding. Print this page for future use. <a href="javascript:window.print()">Click to Print This Page</a>
	</div>
	<div id="step_content_<?php echo $step_name; ?>_confirm" class="step">
		<h3><b>Database Settings</b></h3>
		<table class="dbconf">
		<?php
		if($dtypes) {
		        $type = '';
		        foreach($dtypes as $k=>$v) {
		            $type = (!$k) ? $v : $type;
		        }
		        ?>
		    <tr>
		        <td width="153px">Database Type: </td>
		        <td width="15px"> <div id="tooltips" title="Type of database being used.">&nbsp;</div> </td>
		        <td><?php echo $type." (Currently the only supported database.)"; ?></td>
		    </tr>
		<?php } ?>
		    <tr>
		        <td>Database Name: </td>
		        <td> <div id="tooltips" title="Name of the database being created.">&nbsp;</div> </td>
		        <td><?php echo $dname; ?></td>
		    </tr>
		    <tr>
		        <td>Root Username: </td>
		        <td> <div id="tooltips" title="Root user name of the database.">&nbsp;</div> </td>
		        <td><?php echo $duname; ?></td>
		    </tr>
		    <tr>
		        <td>Root Password: </td>
		        <td> <div id="tooltips" title="Root user password of the database">&nbsp;</div> </td>
		        <td><?php for($i=0; $i< strlen($dpassword); $i++) echo "*"; //echo $dpassword; ?></td>
		    </tr>
		    <?php if($ddrop) { ?>
		    <tr>
		        <td colspan="2"><b>You are about to drop the database if it exists </b></td>
		    </tr>
		    <?php } ?>
		</table>
		<!--</div>-->
		<h3><b>Advanced Settings</b></h3>
		
		<table class="dbconf">
		    <tr>
		        <td width="153px">Host: </td>
		        <td width="15px"> <div id="tooltips" title="The address of the server where the database is located">&nbsp;</div> </td>
		        <td><?php echo $dhost; ?></td>
		    </tr>
		    <tr>
		        <td>Port: </td>
		        <td> <div id="tooltips" title="The port on which your database server is listening">&nbsp;</div> </td>
		        <td><?php echo $dport; ?></td>
		    </tr>
		    <tr>
		        <td>Mysql Binary: </td>
		        <td> <div id="tooltips" title="The path to the database binary">&nbsp;</div> </td>
		        <td><?php echo $dbbinary; ?></td>
		    </tr>
		    <?php if (!$silent) { ?>
		    <tr>
		        <td><b>Table Prefix: </td>
		        <td> <div id="tooltips" title="If needed, a prefix to the database name.">&nbsp;</div> </td>
		        <td><?php echo $tprefix; ?></td>
		    </tr>
		<?php } ?>
		</table>
		
		<h3><b>Database Users (System Generated)</b></h3>
		
		<table class="dbconf">
		    <?php //if (!$silent) { ?>
		    <tr>
		        <td width="153px">DMS Admin Username: </td>
		        <td width="15px"> <div id="tooltips" title="Database Administrative User for KnowledgeTree System">&nbsp;</div> </td>
		        <td><?php echo $dmsname; ?></td>
		    </tr>
		    <?php //} ?>
		    <tr>
		        <td>DMS Admin Password: </td>
		        <td> <div id="tooltips" title="Database Administrative User password">&nbsp;</div> </td>
		        <td><?php echo $dmspassword; ?></td>
		    </tr>
		    <?php //if (!$silent) { ?>
		    <tr>
		        <td>DMS User Username: </td>
		        <td> <div id="tooltips" title="Database User for KnowledgeTree System">&nbsp;</div> </td>
		        <td><?php echo $dmsusername; ?></td>
		    </tr>
		    <?php //} ?>
		    <tr>
		        <td>DMS User Password: </td>
		        <td> <div id="tooltips" title="Database User password">&nbsp;</div> </td>
		        <td><?php echo $dmsuserpassword; ?></td>
		    </tr>
		</table>
	</div>
    <input type="submit" name="Edit" value="Edit" class="button_previous"/>
    <input type="submit" name="Confirm" value="Confirm" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>