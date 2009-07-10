<h1>System Configuration</h1>

<h3>Server Settings</h3>

<table>
    <tr>
        <td><b>Host: </b></td>
        <td><?php echo $server['host']; ?></td>
    </tr>
    <tr>
        <td><b>Port: </b></td>
        <td><?php echo $server['port']; ?></td>
    </tr>
    <tr>
        <td><b>Root Url: </b></td>
        <td><?php echo $server['root_url']; ?></td>
    </tr>
    <tr>
        <td><b>File System Root: </b></td>
        <td><?php echo $server['file_system_root']; ?></td>
    </tr>
    <tr>
        <td><b>SSL Enabled: </b></td>
        <td><?php echo $server['ssl_enabled']; ?></td>
    </tr>
</table>

<br />
<h3>Paths and Permissions</h3>

<table>
<?php
    foreach ($paths as $key => $path){
        $row = '<tr>';

        $row .= "<td><div class='{$path['class']}'></div></td>\n";
        $row .= "<td><b>{$path['name']}:</b></td>\n";
        $row .= "<td>{$path['path']}</td>\n";

        $row .= "</tr>\n";

        echo $row;
    }
?>
</table>

<form action="index.php?step_name=configuration" method="POST">

<div class="buttons">
    <!--<input type="submit" onclick="history.back();" name="Back" value="Back"/>
    <input type="submit"  name="Back" value="Back"/>-->
    <input type="submit"  name="Edit" value="Edit"/>
    <input type="submit" name="Confirm" value="Confirm"/>
</div>
</form>