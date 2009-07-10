<h1>Database Configuration Confirmation</h1>

<form action="index.php?step_name=<?php echo $step_name; ?>" method="POST">

<div class="dtype">
<?php if($dtypes) {
        foreach($dtypes as $k=>$v) {
            if(!$k) $dtype = $v;
        }
    }
?>

</div>

<table>
    <tr>
        <td><b>Selected database type:</b></td>
        <td>MySQL</td>
    </tr>
    <tr>
        <td><b>Databse Name:</b></td>
        <td><?php echo $dname; ?></td>
    </tr>
    <tr>
        <td><b>Administrative / Root Username:</b></td>
        <td><?php echo $duname; ?></td>
    </tr>
    <tr>
        <td><b>Password:</b></td>
        <td><?php echo $dpassword; ?></td>
    </tr>
    <tr>
        <td><b>Database Server Host:</b></td>
        <td><?php echo $dhost; ?></td>
    </tr>
    <tr>
        <td><b>Port:</b></td>
        <td><?php echo $dport; ?></td>
    </tr>
    <tr>
        <td><b>Table Prefix:</b></td>
        <td><?php echo $tprefix; ?></td>
    </tr>
    <tr>
        <td colspan='2'>You have selected the option to drop the database if it already exists.</td>
    </tr>
</table>


<div class="buttons">
    <input type="submit" name="Edit" value="Edit"/>
    <input type="submit" name="Confirm" value="Confirm"/>
</div>
</form>