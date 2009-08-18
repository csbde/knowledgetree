<h1>Services Dependencies Check</h1>

<p class="description">
This checkup ensures that your environment is ready to support KnowledgeTree's background services. 
</p>

<h3>Java Version Check</h3>
<p class="description">
The Java version must be higher than 1.5.
</p>
<?php echo "<span class='{$step_vars['java']['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$step_vars['java']['found']}"; ?>
<br />
<?php echo "<span class='{$step_vars['version']['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$step_vars['version']['found']}"; ?>
<h3>Java Extensions</h3>
<p class="description">
A PHP Java Bridge is required for KnowledgeTree to perform at an optimal level.
</p>
<?php echo "<span class='{$step_vars['extensions']['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$step_vars['extensions']['found']}"; ?>
<br />
<h3>Services Check</h3>
<p class="description">
Preload Services if posibble.
</p>
<?php
if($step_vars){
	if(isset($step_vars['services'])) {
	    foreach ($step_vars['services'] as $ser){
	    	echo "<span class='{$ser['class']}'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>{$ser['msg']}<br/>";
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