<?php

$checkup = true;
error_reporting(E_ALL);
require_once('../config/dmsDefaults.php');

function writablePath($name, $path) {
    $ret = sprintf('<tr><td>%s (%s)</td><td>', $name, $path);
    if (is_writable($path)) {
        $ret .= sprintf('<font color="green"><b>Writeable</b></font>');
    } else {
        $ret .= sprintf('<font color="red"><b>Unwriteable</b></font>');
    }
    return $ret;
}

?>
<html>
  <head>
    <title>KnowledgeTree Post-Configuration Checkup</title>
    <style>
th { text-align: left; }
    </style>
  </head>

  <body>

<h1>KnowledgeTree post-configuration checkup</h1>

<p>This allows you to check that your KnowledgeTree configuration is set
up correctly.  You can run this at any time after configuration to check
that things are still set up correctly.</p>

<h2>Filesystem</h2>

<table width="50%">
  <tbody>
  <?php echo writablePath('Log directory', KT_DIR . '/log')?>
  <?php echo writablePath('Document directory', $default->documentRoot)?>
  <?php echo writablePath('Document Root', $default->documentRoot . '/Root Folder')?>
  </tbody>
</table>

<?php

if (substr($default->documentRoot, 0, strlen(KT_DIR)) == KT_DIR) {
    print '<p><font color="orange">Your document directory seems to be
    accessible from the web.  Change the documentRoot in your
    environment.php configuration file to a place not accessible from
    the web to prevent access outside of KnowledgeTree.</font></p>';
}

?>

<h2>Logging</h2>

<?php
if (PEAR::isError($loggingSupport)) {
    print "<p>Logging support is not currently working.  Error is: " .
        htmlentities($loggingSupport->toString()) . "</p>";
} else {
?>
<p>Logging support is operational.</p>
<?
}
?>

<h2>Database connectivity</h2>

<?
if (PEAR::isError($dbSupport)) {
    print '<p><font color="red">Database support is not currently working.  Error is: ' .
        htmlentities($dbSupport->toString()) . '</font></p>';
} else {
?>
<p>Database connectivity successful.</p>

<h3>Privileges</h3>
<?
$selectPriv = DBUtil::runQuery('SELECT COUNT(id) FROM ' .  $default->documents_table);
if (PEAR::isError($selectPriv)) {
    print '<p><font color="red">Unable to do a basic database query.
    Error is: ' . htmlentities($selectPrive->toString()) .
    '</font></p>';
} else {
    print '<p>Basic database query successful.</p>';
}

?>

<?
}
?>

  </body>
</html>
