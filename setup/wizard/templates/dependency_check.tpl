<h1>PHP Dependencies Check</h1>

<p class="description">
This checkup ensures that your environment is ready to support a KnowledgeTree installation. Settings marked in green are available,
settings marked in orange are optional and settings marked in red are required.
</p>

<?php
if($errors){
    echo '<div class="error">';
    foreach ($errors as $msg){
        echo $msg . "<br />\n";
    }
    echo '</div>';
}
?>

<h3>PHP Version Check</h3>

<p class="description">
The PHP version must be higher than 5.0 and lower than 6.0. It is not recommended to run a version higher than 5.3.2.
</p>

<?php echo "<span class='{$version['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$version['version']}"; ?>
<br /><br />
<h3>PHP Extensions</h3>

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

        $row .= '</tr>';

        echo $row."\n";
    }

?>
</table>
<br /><br />
<h3>PHP Configuration</h3>

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
<form action="index.php?step_name=dependency_check" method="post">

<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
</div>
</form>