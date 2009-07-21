<h1>Installation Completed</h1>

<div class="errors">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
                echo $e;
        }
    }?>
</div>

<form>
<div class="buttons">
    <a href="index.php?step_name=complete&step=previous" class="previous">Previous</a>
    <a href="../../">Goto Login</a>
    </div>
</form>