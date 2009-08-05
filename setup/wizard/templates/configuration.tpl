<h1>System Configuration</h1>

<p class="description">
This check ensures that KnowledgeTree is correctly configured to run.
</p>

<form action="index.php?step_name=configuration" method="post">

<h3>Server Settings</h3>

<p class="description">
The settings below have been drawn from the system information. The host and port should reflect the host and port that will be used to access KnowledgeTree. The Root Url is only needed if your installation is in a directory off the main web server root.
</p>

<div class="error">
    <?php
    	if(isset($errors)) {
	        foreach ($errors as $error){
	            echo $error.'<br />';
	        }
    	}
    ?>
</div>

<table>
    <tr>
        <td><label for='host'>Host: </label></td>
        <td><input name='host' id='host' size='60' value='<?php echo $server['host']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='port'>Port: </label></td>
        <td><input name='port' id='port' size='5' value='<?php echo $server['port']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='root_url'>Root Url: </label></td>
        <td><input name='root_url' id='root_url' size='60' value='<?php echo $server['root_url']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='file_system_root'>File System Root: </label></td>
        <td><input name='file_system_root' id='file_system_root' size='60' value='<?php echo $server['file_system_root']['value']; ?>' /></td>
    </tr>
    <tr>
        <td><label for='yes'>SSL Enabled: </label></td>
        <td>
            <label for='yes'>Yes: </label><input type='radio' name='ssl_enabled' id='yes' value='yes' <?php echo $server['ssl_enabled']['value'] == 'yes' ? 'CHECKED' : ''; ?> />&nbsp;&nbsp;
            <label for='no'>No: </label><input type='radio' name='ssl_enabled' id='no' value='no' <?php echo $server['ssl_enabled']['value'] == 'no' ? 'CHECKED' : ''; ?> />
        </td>
    </tr>
</table>

<br />
<h3>Paths and Permissions</h3>

<p class="description">
The following folders must be writable for KnowledgeTree to be able to run. The permissions on the configuration file can be reset to read-only once the installation has completed.
</p>

<table>
<?php
    foreach ($paths as $key => $path){
        $row = '<tr>';

        $row .= "<td><div class='{$path['class']}'></div></td>\n";
        $row .= "<td><label for='{$path['setting']}'>{$path['name']}: </label></td>\n";
        $row .= "<td><input name='{$path['setting']}' id='{$path['setting']}' size='60' value='{$path['path']}' /></td>\n";
        $row .= '<td class="error">';
        $row .= (isset($path['msg'])) ? $path['msg'] : '';
        $row .= "</td>\n";

        $row .= "</tr>\n";

        echo $row;
    }
?>
</table>

<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
</div>
</form>