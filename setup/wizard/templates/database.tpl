<form action="index.php?step_name=<?php echo $step_name; ?>" method="POST">
<div class="dtype">
<?php if($dtypes) {
        foreach($dtypes as $k=>$v) {
    ?>
        <input type="radio" name="dtype" value="<?php echo $v; ?>" <?php if(!$k)echo 'checked="checked"'; ?>"/><?php echo $v; ?>
<!--        <input type="hidden" name="dtypes" value="<?php echo $v; ?>">-->
        <br/>
<?php }
}
?>
<!--<input type="hidden" name="dtypes" value="postgres">-->
<!--<input type="hidden" name="dtypes" value="oracle">-->
</div>
Name:
<input type="text" value="<?php echo $dname?>" name="dname"/><br/>
Root Username:
<input type="text" value="<?php echo $duname?>" name="duname"/><br/>
Root Password:
<input type="text" value="<?php echo $dpassword?>" name="dpassword"/><br/>
<div id="options" onclick="showAO();" style="cursor:pointer;">Advanced Options</div>
<div class="adv_options" style="display:none;">
    <div class="description">
        These options are only necessary for some sites. If you're not sure what you should enter here, leave the default settings or check with your hosting provider.
    </div>
    Host:
    <input type="text" value="<?php echo $dhost?>" name="dhost"/><br/>
    <div class="description">
        If your database is located on a different server, change this.
    </div>
    Port:
    <input type="text" value="<?php echo $dport?>" name="dport"/><br/>
    <div class="description">
        If your database server is listening to a non-standard port, enter its number.
    </div>
    Table Prefix:
    <input type="text"><br/>
    <div class="description">
        If more than one application will be sharing this database, enter a table prefix such as kt_ for your KnowledgeTree site here.
    </div>

</div>
Drop table if it exists<input type="checkbox" name="ddrop" <?php if($ddrop) echo 'checked="checked"'; ?>>
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