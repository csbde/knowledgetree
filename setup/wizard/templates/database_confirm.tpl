<h1>Database Configuration Confirmation</h1>

<div class="description">
Please confirm that your database settings have been entered correctly before proceeding with the installation.
</div>
<div id="section">
<h3>Database Settings</h3>

<form action="index.php?step_name=<?php echo $step_name; ?>" method="post">

<table width="20%" class="dbconf">
<?php
if($dtypes) {
        $type = '';
        foreach($dtypes as $k=>$v) {
            $type = (!$k) ? $v : $type;
        }
        ?>
    <tr>
        <td><b>Database type: </b></td>
        <td><?php echo $type; ?></td>
    </tr>
<?php } ?>
    <tr>
        <td><b>Name: </b></td>
        <td><?php echo $dname; ?></td>
    </tr>
    <tr>
        <td><b>Root Username: </b></td>
        <td><?php echo $duname; ?></td>
    </tr>
    <tr>
        <td><b>Root Username: </b></td>
        <td><?php echo $duname; ?></td>
    </tr>
    <tr>
        <td><b>Root Password: </b></td>
        <td><?php echo $dpassword; ?></td>
    </tr>
    <?php if($ddrop) { ?>
    <tr>
        <td colspan="2"><b>You are about to drop the database if it exists </b></td>
    </tr>
    <?php } ?>
</table>
</div>
<h3>Advanced Settings</h3>

<table width="46%" class="dbconf">
    <tr>
        <td><b>Host: </b></td>
        <td><?php echo $dhost; ?></td>
    </tr>
    <tr>
        <td><b>Port: </b></td>
        <td><?php echo $dport; ?></td>
    </tr>
    <tr>
        <td><b>Mysql Binary: </b></td>
        <td><?php echo $dbbinary; ?></td>
    </tr>
    <?php if (!$silent) { ?>
    <tr>
        <td><b>Table Prefix: </b></td>
        <td><?php echo $tprefix; ?></td>
    </tr>
<?php } ?>
</table>

<h3>Database Users</h3>

<table width="46%" class="dbconf">
    <?php //if (!$silent) { ?>
    <tr>
        <td><b>DMS Admin Username: </b></td>
        <td><?php echo $dmsname; ?></td>
    </tr>
    <?php //} ?>
    <tr>
        <td><b>DMS Admin Password: </b></td>
        <td><?php echo $dmspassword; ?></td>
    </tr>
    <?php //if (!$silent) { ?>
    <tr>
        <td><b>DMS User Username: </b></td>
        <td><?php echo $dmsusername; ?></td>
    </tr>
    <?php //} ?>
    <tr>
        <td><b>DMS User Password: </b></td>
        <td><?php echo $dmsuserpassword; ?></td>
    </tr>
</table>

<div class="buttons">
    <input type="submit" name="Edit" value="Edit"/>
    <input type="submit" name="Confirm" value="Confirm"/>
</div>
</form>