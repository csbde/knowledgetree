<h1>PHP Dependencies Check</h1>

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

<?php echo "<span class='{$version['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$version['version']}"; ?>
<br /><br />
<h3>PHP Extensions</h3>

<table>
<?php

    foreach($extensions as $ext) {
        $row = '<tr>';

        switch($ext['available']){
            case 'yes':
                $class = 'tick';
                break;
            case 'optional':
                $class = 'cross';
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
<form action="index.php?step_name=dependency_check" method="POST">

<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
</div>
</form>