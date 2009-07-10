<form action="index.php?step_name=<?php echo $step_name; ?>" method="POST">
DB Configuration Confirmation:
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
Name:<?php echo $dname; ?><br/>
Root Username:<?php echo $duname; ?><br/>
Root Password:<?php echo $dpassword; ?><br/>
Host:<?php echo $dhost; ?><br/>
Port:<?php echo $dport; ?><br/>
Table Prefix:<?php echo $tprefix; ?><br/>
<?php if($ddrop) { ?> You are about to drop the table if it exists <?php } ?>
<div class="buttons">
    <input type="submit" name="Edit" value="Edit"/>
    <input type="submit" name="Confirm" value="Confirm"/>
</div>
</form>