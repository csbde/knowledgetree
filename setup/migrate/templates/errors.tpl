<p class="title">Welcome to the KnowledgeTree Migration Wizard</p>

<div style="width: 800px;">
<?php if(isset($error)) echo $error; ?>
<?php 
if($errors){
    echo '<div class="error">';
    foreach ($errors as $msg){
        echo $msg . "<br />\n";
    }
    echo '</div>';
}
?>
</div>