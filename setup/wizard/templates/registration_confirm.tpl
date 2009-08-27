<h1>Thank you for registering</h1>

<div class="error">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
                echo $e;
        }
    }?>
</div>

<p>
Thank you for signing up. You'll receive an email from us shortly with download instructions for the KnowledgeTree<sup>&#174;</sup> Drop Box software.
</p>

<form action="index.php?step_name=registration" method="post">

<div class="buttons">
    <input type="submit" name="Previous" value="Previous"/>
    <input type="submit" name="Confirm" value="Next"/>
</div>
</form>