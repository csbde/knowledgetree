<form action="index.php?step_name=restore" method="post">
    <p class="title">Confirm Restore</p>

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
if ($dir != '') {
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
if ($dir != '')
{
?>
Press <i>Next</i> to attempt the command(s) above.

<P>
<?php
}
    ?>
    </div>


    <input type="submit" name="Previous" value="Back" class="button_previous">
    <input type="submit" name="Next" value="Next" class="button_next">
    </div>
</form>