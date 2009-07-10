<h1>Database Configuration</h1>

<form action="index.php?step_name=<?php echo $step_name; ?>" method='POST'>

Select your database type:
<div class='dtype'>
<?php if($dtypes) {
        foreach($dtypes as $k=>$v) {
    ?>
        <input type='radio' name='dtype' value="<?php echo $v; ?>" <?php echo (!$k) ? 'CHECKED' : ''; ?>"/>&nbsp;&nbsp;<?php echo ucwords($v); ?>
<!--        <input type='hidden' name='dtypes' value='<?php echo $v; ?>'>-->
        <br/>
<?php }
}
?>
<!--<input type='hidden' name='dtypes' value='postgres'>-->
<!--<input type='hidden' name='dtypes' value='oracle'>-->
<br />
</div>

<table>
    <tr>
        <td><label for='dname'>Enter a name for the database: </label></td>
        <td><input type='text' value="<?php echo $dname?>" id='dname' name='dname' size='40' /></td>
    </tr>
    <tr>
        <td><label for='duname'>Enter the username for the Root or Administrative User: </label></td>
        <td><input type='text' value="<?php echo $duname?>" id='duname' name='duname' size='40' /></td>
    </tr>
    <tr>
        <td><label for='dpassword'>Enter the password for the user: </label></td>
        <td><input type='text' value="<?php echo $dpassword?>" id='dpassword' name='dpassword' size='40' /></td>
    </tr>
    <tr>
        <td><label for='ddrop'>Drop database if it exists:  </label></td>
        <td><input type="checkbox" id='ddrop' name="ddrop" <?php echo ($ddrop) ? 'CHECKED' : ''; ?>></td>
</table>

<br />

<div id='options' onclick='showAO();' class='onclick'>Advanced Options</div>
<div class='description'>
        These options are only necessary for some sites. If you're not sure what you should enter here, leave the default settings.
    </div>
<div class='adv_options'>

    <p>
        <label for='dhost'>Host:</label><br />
        <span class='description'>If your database is located on a different server, change this.</span><br />
        <input type='text' value='<?php echo $dhost?>' name='dhost' id='dhost' size='40' />
    </p>

    <p>
        <label for='dport'>Port:</label><br />
        <span class='description'>If your database server is listening to a non-standard port, enter its number.</span><br />
        <input type='text' value='<?php echo $dport?>' name='dport' id='dport' size='10' />
    </p>

    <p>
        <label for='prefix'>Table Prefix: </label><br />
        <span class='description'>If more than one application will be sharing this database, enter a table prefix such as kt_ for your KnowledgeTree site here.</span><br />
        <input type='text' value='' name='prefix' id='prefix' size='40' />
    </p>

</div>
<div class="buttons">
    <input type="submit" name="Previous" value="previous"/>
    <input type="submit" name="Next" value="next"/>
</div>
<div class="errors">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
          echo $e;
        }
    }?>
</div>
</form>
<script>
    function showAO() {
        var v = document.getElementsByClassName("adv_options");
        if(v[0].style.display == 'none')
            v[0].style.display = 'block';
        else
            v[0].style.display = 'none';
    }
</script>