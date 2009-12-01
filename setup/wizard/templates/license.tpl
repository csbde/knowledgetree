<form id="welcome_license_installtype" action="index.php?step_name=license" method="POST">
<p class="title">License Agreement</p>
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
                echo "<span class='error'>".$e."</span>";
        }
    }?>
<p class="description_1">
Read and accept the license agreement below before continuing with the setup.
</p>

<?php if(!empty($errors)) { ?>
	<div class="license_agreement" tabindex="-1" style="height:270px;">
<?php } else { ?>
	<div class="license_agreement" tabindex="-1">
<?php } ?>
<?php echo file_get_contents($licensePath); ?>
</div>
	<input id="accept" type="hidden" name="license" value=""/>
	<input type="submit" name="Previous" value="Previous" class="button_previous"/>
	<input type="submit" name="Next" value="I Agree" onclick="javascript:{$('#accept').attr('value', '1');}" class="button_next"/>
    <input type="submit" name="Next" value="I Disagree" onclick="javascript:{$('#accept').attr('value', '0');}" class="button_next"/>
</form>
<?php if (AJAX) { echo $html->js('form.js'); } ?>