<h1>Installation Completed</h1>

<h2>KnowledgeTree post-configuration checkup</h2>

<p>This allows you to check that your KnowledgeTree configuration is set
up correctly.  You can run this at any time after configuration to check
that things are still set up correctly.</p>

<div class="errors">
    <?php if(isset($errors)) {
        foreach($errors as $k=>$e) {
                echo $e;
        }
    }?>
</div>

<div>
    <h2>Paths and Permissions</h2>
    <table>
        <tr><?php echo $varDirectory; ?></tr>
        <tr><?php echo $documentRoot; ?></tr>
        <tr><?php echo $logDirectory; ?></tr>
        <tr><?php echo $tmpDirectory; ?></tr>
        <tr><?php echo $uploadDirectory; ?></tr>
        <tr><?php echo $config; ?></tr>
        <tr><?php echo $docLocation; ?></tr>
    </table>
</div>
<br/>
<div>
    <h2>Database connectivity</h2>
    <table>
        <tr><?php echo $dbConnectAdmin; ?></tr>
        <tr><?php echo $dbConnectUser; ?></tr>
    </table>
    <h3>Privileges</h3>
    <table>
        <tr><?php echo $dbPrivileges; ?></tr>
        <tr><?php echo $dbTransaction; ?></tr>
    </table>
</div>
<br/>
<div>
    <h2>Services</h2>
    <table>
        <tr><?php echo $luceneServiceStatus; ?></tr> 
        <tr><?php echo $schedulerServiceStatus; ?></tr>
    </table>  
</div>

<form>
<div class="buttons">
    <a href="index.php?step_name=complete&step=previous" class="previous">Previous</a>
    <a href="">Refresh</a>
    <a href="../../">Goto Login</a>
    <?php
        if (INSTALL_TYPE == 'Zend') {
            echo '<a href="http://' . $_SERVER['HTTP_HOST'] . '/ZendServer/Index">Zend Server Configuration</a>';
        }
    ?>
    </div>
</form>