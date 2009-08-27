<h1>Welcome to the KnowledgeTree<sup>&#174;</sup> Setup Wizard</h1>

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