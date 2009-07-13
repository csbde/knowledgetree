<form action="index.php?step_name=<?php echo $step_name; ?>" method="post">
<h1>Database Configuration</h1>

<h3>Database Settings</h3>

<div class="dtype">
<?php if($dtypes) {
        foreach($dtypes as $k=>$v) {
    ?>
        <input type="radio" name="dtype" value="<?php echo $v; ?>" <?php if(!$k)echo 'checked="checked"'; ?>/><?php echo $v; ?>
        <br/>
<?php }
}
?>
</div>
<table class="settings">
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
        <td><b>You are about to drop the database if it exists </b></td>
    </tr>
    <?php } ?>
</table>

<h3>Database Advanced Settings</h3>

<table class="asettings">
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
    <tr>
        <td><b>Table Prefix: </b></td>
        <td><?php echo $tprefix; ?></td>
    </tr>
</table>

<h3>DMS Users</h3>

<table class="users">
    <tr>
        <td><b>DMS Admin Username: </b></td>
        <td><?php echo $dmsname; ?></td>
    </tr>
    <tr>
        <td><b>DMS Admin Password: </b></td>
        <td><?php echo $dmspassword; ?></td>
    </tr>
    <tr>
        <td><b>DMS User Username: </b></td>
        <td><?php echo $dmsusername; ?></td>
    </tr>
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