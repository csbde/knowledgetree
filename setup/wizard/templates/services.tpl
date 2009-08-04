<h1>Services Dependencies Check</h1>

<p class="description">
This checkup ensures that your environment is ready to support a KnowledgeTree background services. 
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
<?php
if($step_vars){
	if(isset($step_vars['services'])) {
	    foreach ($step_vars['services'] as $msg){
	        echo $msg . "<br />\n";
	    }
	}
}
?>

<form action="index.php?step_name=services" method="post">

<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Next" value="Next"/>
</div>
</form>