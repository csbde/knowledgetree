<h1>Database Configuration Confirmation</h1>

<div class="description">
Please confirm that your database settings have been entered correctly before proceeding with the installation.
</div>
<!--<div id="section">-->

<form action="index.php?step_name=<?php echo $step_name; ?>" method="post">
<h3><b>Database Settings</b></h3>
<table width="20%" class="dbconf">
<?php
if($dtypes) {
        $type = '';
        foreach($dtypes as $k=>$v) {
            $type = (!$k) ? $v : $type;
        }
        ?>
    <tr>
        <td>Database type: </td>
        <td><?php echo $type; ?></td>
    </tr>
<?php } ?>
    <tr>
        <td>Name: </td>
        <td><?php echo $dname; ?></td>
    </tr>
    <tr>
        <td>Root Username: </td>
        <td><?php echo $duname; ?></td>
    </tr>
    <tr>
        <td>Root Username: </td>
        <td><?php echo $duname; ?></td>
    </tr>
    <tr>
        <td>Root Password: </td>
        <td><?php echo $dpassword; ?></td>
    </tr>
    <?php if($ddrop) { ?>
    <tr>
        <td colspan="2"><b>You are about to drop the database if it exists </b></td>
    </tr>
    <?php } ?>
</table>
<!--</div>-->
<h3><b>Advanced Settings</b></h3>

<table width="23%" class="dbconf">
    <tr>
        <td>Host: </td>
        <td><?php echo $dhost; ?></td>
    </tr>
    <tr>
        <td>Port: </td>
        <td><?php echo $dport; ?></td>
    </tr>
    <tr>
        <td>Mysql Binary: </td>
        <td><?php echo $dbbinary; ?></td>
    </tr>
    <?php if (!$silent) { ?>
    <tr>
        <td><b>Table Prefix: </td>
        <td><?php echo $tprefix; ?></td>
    </tr>
<?php } ?>
</table>

<h3><b>Database Users</b></h3>

<table width="23%" class="dbconf">
    <?php //if (!$silent) { ?>
    <tr>
        <td>DMS Admin Username: </td>
        <td><?php echo $dmsname; ?></td>
    </tr>
    <?php //} ?>
    <tr>
        <td>DMS Admin Password: </td>
        <td><?php echo $dmspassword; ?></td>
    </tr>
    <?php //if (!$silent) { ?>
    <tr>
        <td>DMS User Username: </td>
        <td><?php echo $dmsusername; ?></td>
    </tr>
    <?php //} ?>
    <tr>
        <td>DMS User Password: </td>
        <td><?php echo $dmsuserpassword; ?></td>
    </tr>
</table>

<div class="buttons">
    <input type="submit" name="Edit" value="Edit"/>
    <input type="submit" name="Confirm" value="Confirm"/>
</div>
</form>