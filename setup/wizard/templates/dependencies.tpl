<h1>PHP Dependencies Check</h1>

<p class="description">
This checkup ensures that your environment is ready to support a KnowledgeTree installation. <br/>
Settings marked in green are available, settings marked in orange are optional and settings marked in red are required.
</p>

<?php
if($errors || $warnings){
    echo '<div>'
       . '<a href="http://wiki.knowledgetree.com/Web_Based_Installer#PHP_Dependencies" target="_blank">'
       . 'Click Here for help on overcoming dependency issues</a></div><br/>';
}
?>
<h3><?php echo "<span class='{$php}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>PHP Version Check</h3>
<?php if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('php_details');}">Show Details</div>
	<div class="php_details" style="display:none">
<?php } ?>
<p class="description">
The PHP version must be higher than 5.0 and lower than 6.0. It is not recommended to run a version higher than 5.3.2.
</p>
<?php echo "<span class='{$version['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$version['version']}"; ?>
<?php if($silent) { ?>
	</div>
<?php } ?>
<br />
<h3><?php echo "<span class='{$php_ext}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>PHP Extensions</h3>
<?php
if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('php_ext_details');}">Show Details</div>
	<div class="php_ext_details" style="display:none">
<?php } ?>
<p class="description">
The following determines your PHP installation environment. The extensions are required for KnowledgeTree to perform at an optimal level.
</p>
<table>
<?php
    foreach($extensions as $ext) {
        $row = '<tr>';
        switch($ext['available']){
            case 'yes':
                $class = 'tick';
                break;
            case 'optional':
                $class = 'cross_orange';
                break;
            case 'no':
            default:
                $class = 'cross';
        }
        $row .= "<td><div class='{$class}'></div></td>";
        $row .= "<td>{$ext['name']}</td>";
        $row .= ($ext['available'] != 'yes') ? "<td>{$ext['details']}</td>" : '<td></td>';
        $row .= isset($errors[$ext['extension']]) ? "<td><span class='error'>{$errors[$ext['extension']]}</span></td>" : '<td></td>';
        $row .= '</tr>';
        echo $row."\n";
    }
?>
</table>
<?php if($silent) { ?>
	</div>
<?php } ?>
<br />
<h3><?php echo "<span class='{$php_con}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>"; ?>PHP Configuration</h3>
<?php
if($silent) { ?>
	<div id="options" class="onclick" onclick="javascript:{w.toggleClass('php_con_details');}">Show Details</div>
	<div class="php_con_details" style="display:none">
<?php } ?>
<p class="description">
The following is the recommended PHP configuration for KnowledgeTree to perform at an optimal level.
</p>
<table>
<tr>
    <th>Setting</th>
    <th>Recommended value</th>
    <th>Current value</th>
</tr>
<?php
    foreach($configurations as $config) {
        $row = '<tr>';
        $row .= "<td>{$config['name']}</td>";
        $row .= "<td>{$config['recommended']}</td>";
        $row .= "<td class='{$config['class']}'>{$config['setting']}</td>";
        $row .= '</tr>';
        echo $row."\n";
    }
?>
</table>
<?php if($silent) { ?>
	</div>
<?php } ?>
<form action="index.php?step_name=dependencies" method="post">
<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
    <input type="submit" name="Refresh" value="Refresh"/>
</div>
</form>