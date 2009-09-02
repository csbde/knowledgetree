<h1>System Configuration</h1>

<h3>Server Settings</h3>

<table>
    <tr>
        <td>Host: </td>
        <td><?php echo $server['host']['value']; ?></td>
    </tr>
    <tr>
        <td>Port: </td>
        <td><?php echo $server['port']['value']; ?></td>
    </tr>
    <tr>
        <td>Root Url: </td>
        <td><?php echo $server['root_url']['value']; ?></td>
    </tr>
    <tr>
        <td>File System Root: </td>
        <td><?php echo $server['file_system_root']['value']; ?></td>
    </tr>
    <tr>
        <td>SSL Enabled: </td>
        <td><?php echo $server['ssl_enabled']['value']; ?></td>
    </tr>
</table>

<h3>Paths and Permissions</h3>

<table>
<?php
    foreach ($paths as $key => $path){
?>
	<tr>
		<td><div class='<?php echo $path['class']; ?>'></div></td>
		<td><?php echo $path['name']; ?>:</td>
		<td><?php echo $path['path']; ?></td>
	</tr>
<?php
    }
?>
</table>

<form action="index.php?step_name=configuration" method="post">

<div class="buttons">
    <!--<input type="submit" onclick="history.back();" name="Back" value="Back"/>
    <input type="submit"  name="Back" value="Back"/>-->
    <input type="submit"  name="Edit" value="Edit"/>
    <input type="submit" name="Confirm" value="Confirm"/>
</div>
</form>