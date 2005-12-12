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
  <?php echo writablePath('Log directory', KT_DIR . '/var/log')?>
  <?php echo writablePath('Document directory', $default->documentRoot)?>
  <?php echo writablePath('Document Root', $default->documentRoot . '/Root Folder')?>
  </tbody>
</table>

<?php

if (substr($default->documentRoot, 0, strlen(KT_DIR)) == KT_DIR) {
    print '<p><strong><font color="orange">Your document directory is
    set to the default, which is inside the web root.  This may present
    a security problem if your documents can be accessed from the web,
    working around the permission system in
    KnowledgeTree.</font></strong></p>';
}

$linkcheck = generateLink('/Documents/', '');
$handle = @fopen($linkcheck, 'rb');
if ($handle !== false) {
    print '<p><strong><font color="red">Your document directory seems to
    be accessible via the web!</font></strong></p>';
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
    Error is: ' . htmlentities($selectPriv->toString()) .
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
