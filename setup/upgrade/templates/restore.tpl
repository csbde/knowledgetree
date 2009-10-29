<form action="index.php?step_name=restore" method="post" name="dbForm" id="dbForm">
    <p class="title"><?php echo $title; ?></p>

    <?php
    if($errors || $warnings){
        echo '<div>'
           . '<a href="http://wiki.knowledgetree.com/Web_Based_Upgrader#Post_Upgrade" target="_blank">'
           . 'Click Here for help on overcoming backup issues</a></div><br/>';
    }
    ?>
    <div id="step_content_complete" class="step">
    <br/><br/>
    <div>
    <?php
    if (!$restore) {
        if (!$availableBackups) {
            ?>There don't seem to be any backups to restore from the <i>"<?php echo $dir;?>"</i> directory.<?php
        }
        else if (!$selected) {
        ?>
        <P>
        Select a backup to restore from the list below:
        <P>
            <form action="index.php?step_name=restore" method="post">
    
        <table border=1 cellpadding=1 cellspacing=1>
                <tr bgcolor="darkgrey">
                <td>Filename
                <td>File Size
                <td>Action
    <?php
        $i=0;
        foreach($files as $file)
        {
            $color=((($i++)%2)==0)?'white':'lightgrey';
    ?>
            <tr bgcolor="<?php echo $color;?>">
                <td><?php echo $file;?>
                <td><?php echo filesize($dir . '/'.$file);?>
                <td><input type="submit" name="RestoreSelect" value="restore">
    <?php
        }
    ?>
        </table>
        <input type="hidden" name="file" value="<?php echo $file; ?>" />
        </form>
        <?php
        }
    else if ($dir != '') {
    ?>
    <P>
    <P>
    Manually, you would do the following to restore the backup:
    <P>
    <table bgcolor="lightgrey">
    <tr>
    <td>
    <nobr>cd "<?php echo $dir;?>"</nobr>
    <br/>
    <?php
        }
        else
        {
        ?>
         The mysql backup utility could not be found automatically. Either do a manual restore, or edit the config.ini and update the backup/mysql Directory entry.
    <P>
    You can continue to do the restore manually using the following command(s):
    <P>
    <table bgcolor="lightgrey">
    <tr>
    <td><?php
        }
    } // end not doing a restore, just preliminary steps
?>
<nobr><?php echo $display;?></nobr>
</td>
</tr>
</table>
<P>
    <?php if($silent) { ?>
        </div>
    <?php } ?>
            <?php
if (($dir != '') && ($selected))
{
?>
Press <i>Next</i> to attempt the command(s) above.

<P>
<?php
}
    ?>
    </div>
    </div>
	<?php include WIZARD_DIR . 'templates/loading.tpl'; ?>
	<div id="buttonBar">
    <input type="submit" name="Previous" value="Previous" class="button_previous">
    <?php if (($dir != '') && ($selected)) { ?>
    <input type="button" name="RunRestore" value="Next" class="button_next" onclick="doSubmit(this);">
    <?php }
    else { ?>
    <input type="submit" name="Next" value="Next" class="button_next">
    <?php } ?>
	</div>
</form>