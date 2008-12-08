<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

$GLOBALS["checkup"] = true;
session_start();
require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_LIB_DIR . '/upgrades/upgrade.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

function generateUpgradeTable () {
	global $default;
	$query = sprintf('SELECT value FROM %s WHERE name = "databaseVersion"', $default->system_settings_table);
	$lastVersion = DBUtil::getOneResultKey($query, 'value');
	$currentVersion = $default->systemVersion;

	$upgrades = describeUpgrade($lastVersion, $currentVersion);

	$ret = "<table border=1 cellpadding=1 cellspacing=1 width='100%'>\n";
	$ret .= "<tr bgcolor='darkgrey'><th width='10'>Code</th><th width='100%'>Description</th><th width='30'>Applied</th></tr>\n";
	$i=0;
	foreach ($upgrades as $upgrade) {
		$color=((($i++)%2)==0)?'white':'lightgrey';
		$ret .= sprintf("<tr bgcolor='$color'><td>%s</td><td>%s</td><td>%s</td></tr>\n",
		htmlspecialchars($upgrade->getDescriptor()),
		htmlspecialchars($upgrade->getDescription()),
		$upgrade->isAlreadyApplied() ? "Yes" : "No"
		);
	}
	$ret .= '</table>';
	return $ret;
}

function showResult($res) {
	if (PEAR::isError($res)) {
		if (is_a($res, 'Upgrade_Already_Applied')) {
			return '<span style="color: orange">Already applied</span>';
		}
		return sprintf('<span style="color: red">%s</span>', htmlspecialchars($res->toString()));
	}
	if ($res === true) {
		return '<span style="color: green">Success</span>';
	}
	if ($res === false) {
		return '<span style="color: red">Failure</span>';
	}
	return $res;
}

$GLOBALS['row'] = 1;

function performAllUpgrades () {
	global $default;
	$query = sprintf('SELECT value FROM %s WHERE name = "databaseVersion"', $default->system_settings_table);
	$lastVersion = DBUtil::getOneResultKey($query, 'value');
	$currentVersion = $default->systemVersion;

	$upgrades = describeUpgrade($lastVersion, $currentVersion);

	foreach ($upgrades as $upgrade) {
		if (($GLOBALS['row'] % 2) == 1) {
			$class = "odd";
		} else {
			$class = "even";
		}
		printf('<div class="row %s"><div class="foo">%s</div>' . "\n", $class, htmlspecialchars($upgrade->getDescription()));
		$GLOBALS['row']++;
		ob_flush();
		flush();
		$res = $upgrade->performUpgrade();
		printf('<div class="bar">%s</div>', showResult($res));
		print '<br style="clear: both">' . "\n";
		ob_flush();
		flush();
		print "</div>\n";
		if (PEAR::isError($res)) {
			if (!is_a($res, 'Upgrade_Already_Applied')) {
				break;
			} else {
				$res = true;
			}
		}
		if ($res === false) {
			$res = PEAR::raiseError("Upgrade returned false");
			break;
		}
	}

    return $res;
}

function performPreUpgradeActions() {

    // This is just to test and needs to be updated to a more sane and error resistent architrcture if it works.
    // It should idealy work the same as the upgrades.

    global $default;

    // Lock the scheduler
    $lockFile = $default->cacheDirectory . DIRECTORY_SEPARATOR . 'scheduler.lock';
    touch($lockFile);
    return true;

}

function performPostUpgradeActions() {

    // This is just to test and needs to be updated to a more sane and error resistent architrcture if it works.
    // It should idealy work the same as the upgrades.

    global $default;

    // Ensure all plugins are re-registered.
    $sql = "TRUNCATE plugin_helper";
    $res = DBUtil::runQuery($sql);

    // Clear out all caches and proxies - they need to be regenerated with the new code
    $proxyDir = $default->proxyCacheDirectory;
    KTUtil::deleteDirectory($proxyDir);

    $oKTCache = new KTCache();
    $oKTCache->deleteAllCaches();

    // Clear the configuration cache, it'll regenerate on next load
    $oKTConfig = new KTConfig();
    $oKTConfig->clearCache();

    // Unlock the scheduler
    $lockFile = $default->cacheDirectory . DIRECTORY_SEPARATOR . 'scheduler.lock';
    if(file_exists($lockFile)){
        @unlink($lockFile);
    }

    return true;

}

if (PEAR::isError($loggingSupport)) {
	print '<p><font color="red">Logging support is not currently working.  Check post-installation checkup.</font></p>';
	exit(1);
}

if (PEAR::isError($dbSupport)) {
	print '<p><font color="red">Database support is not currently working.  Check post-installation checkup or refresh this page (F5) to try again.</font></p>';
	exit(1);
}



?>
<html>
  <head>
    <title><?php echo APP_NAME;?> Upgrade</title>
    <style>
th { text-align: left; }
td { vertical-align: top; }
.foo { float: left; }
.bar { padding-left: 2em; float: right; }
.odd { background-color: #eeeeee; }
.even { background-color: #dddddd; }
.row { padding: 0.5em 1em; }
    </style>
  </head>

  <body>
  <img src="<?php
  	if($oKTConfig->get('ui/mainLogo')){
  		echo $oKTConfig->get('ui/mainLogo');
  	}else{
	  	echo '../resources/graphics/ktlogo-topbar_base.png';
	}?>">
  <p>
  <img src="upgrade-title.jpg">
  <table width=800 height=500>
<tr><td>
<P>
   <script>
function do_start(action)
{
	document.location='?go=' + action;
}
</script>
<?php

$action = trim($_REQUEST["go"]);
switch ($action)
{
	case 'UpgradeConfirm':
	case 'UpgradePreview':
		UpgradePreview();
		break;
	case 'Upgrade':
		Upgrade();
		break;
	case 'BackupConfirm':
		backupConfirm();
		break;
	case 'Backup':
		backup();
		break;
	case 'BackupDone':
		backupDone();
		break;
	case 'RestoreConfirm':
		restoreConfirm();
		break;
	case 'RestoreSelect':
		restoreSelect();
		break;
	case 'RestoreSelected':
		restoreSelected();
		break;
	case 'Restore':
		restore();
		break;
	case 'RestoreDone':
		restoreDone();
		break;
	case 'Login':
		login();
		break;
	case 'LoginProcess':
		loginProcess();
		break;
	default:
		if (!isset($_SESSION['setup_user']))
			login();
		else
			welcome();
		break;
}

function login()
{
?>
<P>
The database upgrade wizard completes the upgrade process on an existing <?php echo APP_NAME;?> installation. It applies
any upgrades to the database that may be required.
<P>
Only administrator users may access the upgrade wizard.
<P>

<form method=post action="?go=LoginProcess">
<table>
<tr><td>Username<td><input name=username>
<tr><td>Password<td><input name=password type="password">
<tr><td colspan=2 align=center><input type=submit value="login">
</table>
</form>
<?php
}

function loginProcess()
{
	$username=$_REQUEST['username'];
	$password=$_REQUEST['password'];

	$authenticated = checkPassword($username, $password);

	if (!$authenticated)
	{
		session_unset();
		loginFailed(_kt('Could not authenticate administrative user'));
		return;
	}

	$_SESSION['setup_user'] = $username;

	welcome();
}

function checkPassword($username, $password) {
	global $default;

	$sTable = KTUtil::getTableName('users');
	$sQuery = "SELECT count(*) AS match_count FROM $sTable WHERE username = ? AND password = ?";
	$aParams = array($username, md5($password));
	$res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'match_count');
	if (PEAR::isError($res)) { return false; }
	else {
		$sTable = KTUtil::getTableName('users_groups_link');
		$sQuery = "SELECT count(*) AS match_count FROM $sTable WHERE user_id = ? AND group_id = 1";
		$aParams = array($res);
		$res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'match_count');
		if (PEAR::isError($res)) { return false; }
		else {
			return ($res == 1);
		}
	}
}

function loginFailed($message)
{
	print "<font color=red>$message</font>";
	login();
}

function resolveMysqlDir()
{
	// possibly detect existing installations:

	if (OS_UNIX)
	{
		$dirs = array('/opt/mysql/bin','/usr/local/mysql/bin');
		$mysqlname ='mysql';
	}
	else
	{
		$dirs = explode(';', $_SERVER['PATH']);
		$dirs[] ='c:/Program Files/MySQL/MySQL Server 5.0/bin';
		$dirs[] = 'c:/program files/ktdms/mysql/bin';
		$mysqlname ='mysql.exe';
	}

	$oKTConfig =& KTConfig::getSingleton();
	$mysqldir = $oKTConfig->get('backup/mysqlDirectory',$mysqldir);
	$dirs[] = $mysqldir;

	if (strpos(__FILE__,'knowledgeTree') !== false && strpos(__FILE__,'ktdms') != false)
	{
		$dirs [] = realpath(dirname($FILE) . '/../../mysql/bin');
	}

	foreach($dirs as $dir)
	{
		if (is_file($dir . '/' . $mysqlname))
		{
			return $dir;
		}
	}

	return '';
}


function create_backup_stmt($targetfile=null)
{
	$oKTConfig =& KTConfig::getSingleton();

	$adminUser = $oKTConfig->get('db/dbAdminUser');
	$adminPwd = $oKTConfig->get('db/dbAdminPass');
	$dbHost = $oKTConfig->get('db/dbHost');
	$dbName = $oKTConfig->get('db/dbName');

	$dbPort = trim($oKTConfig->get('db/dbPort'));
	if (empty($dbPort) || $dbPort=='default') $dbPort = get_cfg_var('mysql.default_port');
	if (empty($dbPort)) $dbPort='3306';
	$dbSocket = trim($oKTConfig->get('db/dbSocket'));
	if (empty($dbSocket) || $dbSocket=='default') $dbSocket = get_cfg_var('mysql.default_socket');
	if (empty($dbSocket)) $dbSocket='../tmp/mysql.sock';

	$date=date('Y-m-d-H-i-s');

	$dir=resolveMysqlDir();

	$info['dir']=$dir;

	$prefix='';
	if (OS_UNIX)
	{
		$prefix .= "./";
	}

	if (@stat($dbSocket) !== false)
	{
		$mechanism="--socket=\"$dbSocket\"";
	}
	else
	{
		$mechanism="--port=\"$dbPort\"";
	}

	$tmpdir=resolveTempDir();

	if (is_null($targetfile))
	{
		$targetfile="$tmpdir/kt-backup-$date.sql";
	}

	$stmt = $prefix . "mysqldump --user=\"$adminUser\" -p $mechanism \"$dbName\" > \"$targetfile\"";
	$info['display']=$stmt;
	$info['target']=$targetfile;


	$stmt  = $prefix. "mysqldump --user=\"$adminUser\" --password=\"$adminPwd\" $mechanism \"$dbName\" > \"$targetfile\"";
	$info['cmd']=$stmt;
	return $info;
}

function create_restore_stmt($targetfile)
{
	$oKTConfig =& KTConfig::getSingleton();

	$adminUser = $oKTConfig->get('db/dbAdminUser');
	$adminPwd = $oKTConfig->get('db/dbAdminPass');
	$dbHost = $oKTConfig->get('db/dbHost');
	$dbName = $oKTConfig->get('db/dbName');
	$dbPort = trim($oKTConfig->get('db/dbPort'));
	if ($dbPort=='' || $dbPort=='default')$dbPort = get_cfg_var('mysql.default_port');
	if (empty($dbPort)) $dbPort='3306';
	$dbSocket = trim($oKTConfig->get('db/dbSocket'));
	if (empty($dbSocket) || $dbSocket=='default') $dbSocket = get_cfg_var('mysql.default_socket');
	if (empty($dbSocket)) $dbSocket='../tmp/mysql.sock';

	$dir=resolveMysqlDir();

	$info['dir']=$dir;

	$prefix='';
	if (OS_UNIX)
	{
		$prefix .= "./";
	}

	if (@stat($dbSocket) !== false)
	{
		$mechanism="--socket=\"$dbSocket\"";
	}
	else
	{
		$mechanism="--port=\"$dbPort\"";
	}

	$tmpdir=resolveTempDir();

	$stmt = $prefix ."mysqladmin --user=\"$adminUser\" -p $mechanism drop  \"$dbName\"<br>";
	$stmt .= $prefix ."mysqladmin --user=\"$adminUser\" -p $mechanism create  \"$dbName\"<br>";


	$stmt .= $prefix ."mysql --user=\"$adminUser\" -p $mechanism \"$dbName\" < \"$targetfile\"\n";
	$info['display']=$stmt;


	$stmt = $prefix ."mysqladmin --user=\"$adminUser\" --force --password=\"$adminPwd\" $mechanism drop  \"$dbName\"\n";
	$stmt .= $prefix ."mysqladmin --user=\"$adminUser\" --password=\"$adminPwd\" $mechanism create  \"$dbName\"\n";

	$stmt .=  $prefix ."mysql --user=\"$adminUser\" --password=\"$adminPwd\" $mechanism \"$dbName\" < \"$targetfile\"";
	$info['cmd']=$stmt;
	return $info;
}

function title($title)
{
	if (!isset($_SESSION['setup_user']))
	{
		print "<script>document.location='?go=Login'</script>";
	}
	print "<h1>$title</h1>";
}

function resolveTempDir()
{

	if (OS_UNIX)
	{
		$dir='/tmp/kt-db-backup';
	}
	else
	{
		$dir='c:/kt-db-backup';
	}
	$oKTConfig =& KTConfig::getSingleton();
	$dir = $oKTConfig->get('backup/backupDirectory',$dir);

	if (!is_dir($dir))
	{
			mkdir($dir);
	}
	return $dir;
}


function upgradeConfirm()
{
	title('Confirm Upgrade');
	if (!isset($_SESSION['backupStatus']) || $_SESSION['backupStatus'] === false)
	{
?>
<br>
<font color="Red">Please ensure that you have made a backup before continuing with the upgrade process.</font>
<p>
<br>
<?php
	}
?>
<p>
We are about to start the upgrade process.
<P>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradePreview')">

<?php

}


function backupConfirm()
{
	title('Confirm Backup');
	$stmt=create_backup_stmt();
	$_SESSION['backupFile'] = $stmt['target'];

	$dir=$stmt['dir'];
	if ($dir != '')
	{
?>

Are you sure you want to perform the backup?

<P>
Your mysql installation has been resolved. Manually, you would do the following:
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<nobr>cd "<?php echo $dir;?>"</nobr>
<br>
<?php
	}
	else
	{
?>
The mysql backup utility could not be found automatically. Either do a manual backup, or edit the config.ini and update the backup/mysql Directory entry.
<P>
You can continue to do the backup manually using the following process:
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?php

	}
?>
<nobr><?php echo $stmt['display'];?>
</table>
<P>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')"> &nbsp;&nbsp; &nbsp; &nbsp;

<?php
if ($dir != '')
{
?>

<input type=button value="next" onclick="javascript:do_start('Backup')">


<?php
}
}

function restoreSelect()
{
	title('Select Backup to Restore');

	$dir = resolveTempDir();

	$files = array();
	if ($dh = opendir($dir))
	{
        while (($file = readdir($dh)) !== false)
        {
			if (!preg_match('/kt-backup.+\.sql/',$file))
			{
				continue;
			}
        	$files[] = $file;
        }
        closedir($dh);
    }

    if (count($files) == 0)
    {
 ?>
 	There don't seem to be any backups to restore from the <i>"<?php echo $dir;?>"</i> directory.
 <?php
    }
    else
    {
 ?>
 	<P>
 	Select a backup to restore from the list below:
 	<P>
 	<script>
 	function selectRestore(filename)
 	{
 		document.location='?go=RestoreSelected&file=' + filename;
 	}
 	</script>
 	<table border=1 cellpadding=1 cellspacing=1>
 			<tr bgcolor="darkgrey">
			<td>Filename
			<td>File Size
			<td>Action
<?php
	$i=0;
	foreach($files as $file)
	{
		$color=((($i++)%2)==0)?'white':'lightgrey';
?>
		<tr bgcolor="<?php echo $color;?>">
			<td><?php echo $file;?>
			<td><?php echo filesize($dir . '/'.$file);?>
			<td><input type=button value="restore" onclick="javascript:selectRestore('<?php echo $file;?>')">
<?php
	}
?>
 	</table>
 <?php
    }
   ?>

   <p>
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
   <?php

}

function restoreSelected()
{
	$file=$_REQUEST['file'];

	$dir = resolveTempDir();
	$_SESSION['backupFile'] = $dir . '/' . $file;
?>
<script>
document.location='?go=RestoreConfirm';
</script>
<?php

}

function restoreConfirm()
{
	if (!isset($_SESSION['backupFile']) || !is_file($_SESSION['backupFile']) || filesize($_SESSION['backupFile']) == 0)
	{
		restoreSelect();
		exit;
	}

	title('Confirm Restore');
	$status = $_SESSION['backupStatus'];
	$filename=$_SESSION['backupFile'];
	$stmt=create_restore_stmt($filename);

	$dir=$stmt['dir'];
	if ($dir != '')
	{
?>
<P>
<P>
Manually, you would do the following to restore the backup:
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<nobr>cd "<?php echo $dir;?>"</nobr>
<br>
<?php
	}
	else
	{
?>
The mysql backup utility could not be found automatically. Either do a manual restore, or edit the config.ini and update the backup/mysql Directory entry.
<P>
You can continue to do the restore manually using the following command(s):
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?php

	}
?>
<nobr><?php echo $stmt['display'];?>
</table>
<P>
<?php
if ($dir != '')
{
?>
Press <i>continue to restore</i> to attempt the command(s) above.

<P>
<?php
}
?>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="select another backup" onclick="javascript:do_start('RestoreSelect')">

<?php
if ($dir != '')
{
?>
<script>
function restore()
{
	if (confirm('Are you sure you want to restore? This is your last chance if the current data has not been backed up.'))
	{
		do_start('Restore');
	}
}
</script>
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:restore()">


<?php
}
}


function backupDone()
{
	check_state(2);
	set_state(3);
	title('Backup Status');
	$status = $_SESSION['backupStatus'];
	$filename=$_SESSION['backupFile'];

	if ($status)
	{
		$stmt=create_restore_stmt($filename);
?>
		The backup file <nobr><I>"<?php echo $filename;?>"</i></nobr> has been created.
		<P> It appears as though the <font color=green>backup has been successful</font>.
		<P>
		<?php
			if ($stmt['dir'] != '')
			{
		?>
				Manually, you would do the following to restore the backup:
				<P>
				<table bgcolor="lightgrey">
				<tr>
					<td>
						<nobr>cd <?php echo $stmt['dir'];?></nobr>
						<br>
		<?php
			}
			else
			{
		?>
			The mysql backup utility could not be found automatically. Please edit the config.ini and update the backup/mysql Directory entry.
				<P>
				If you need to restore from this backup, you should be able to use the following statements:
				<P>
				<table bgcolor="lightgrey">
				<tr>
					<td>
		<?php
			}
		?>
						<nobr><?php echo $stmt['display'];?>
				</table>

<?php
	}
	else
	{
?>
It appears as though <font color=red>the backup process has failed</font>.<P></P> Unfortunately, it is difficult to diagnose these problems automatically
and would recommend that you try to do the backup process manually.
<P>
We appologise for the inconvenience.
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?php echo $_SESSION['backupOutput'];?>
</table>
<?php

	}
?>
<br>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
<?php
	if ($status)
	{
		?>
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradeConfirm')">

<?php
	}
}
function restoreDone()
{
	check_state(5);
	set_state(6);
	title('Restore Status');
	$status = $_SESSION['restoreStatus'];
	 $filename=$_SESSION['backupFile'];

	if ($status)
	{

?>
		The restore of <nobr><I>"<?php echo $filename;?>"</i></nobr> has been completed.
		<P>
		It appears as though the <font color=green>restore has been successful</font>.
		<P>



<?php
	}
	else
	{
?>
It appears as though <font color=red>the restore process has failed</font>. <P>
Unfortunately, it is difficult to diagnose these problems automatically
and would recommend that you try to do the backup process manually.
<P>
We appologise for the inconvenience.
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?php echo $_SESSION['restoreOutput'];?>
</table>
<?php

	}
?>

<br>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">

<?php

}

function set_state($value)
{
	$_SESSION['state'] = $value;
}
function check_state($value, $state='Home')
{
	if ($_SESSION['state'] != $value)
	{
		?>
			<script>
			document.location="?go=<?php echo $state;?>";
			</script>
			<?php
			exit;
	}
}

function backup()
{
	check_state(1);
	set_state(2);
	title('Backup In Progress');
	$targetfile=$_SESSION['backupFile'];
	$stmt=create_backup_stmt($targetfile);
	$dir=$stmt['dir'];




	if (is_file($dir . '/mysqladmin') || is_file($dir . '/mysqladmin.exe'))
	{
		ob_flush();
		flush();
?>
		The backup is now underway. Please wait till it completes.
<?php

		ob_flush();
		flush();
		$curdir=getcwd();
		chdir($dir);
		ob_flush();
		flush();

		$handle = popen($stmt['cmd'], 'r');
		$read = fread($handle, 10240);
		pclose($handle);
		$_SESSION['backupOutput']=$read;
		$dir=resolveTempDir();
		$_SESSION['backupFile'] =   $stmt['target'];

			if (OS_UNIX)
			{
				chmod($stmt['target'],0600);
			}

		if (is_file($stmt['target']) && filesize($stmt['target']) > 0)
		{
			$_SESSION['backupStatus'] = true;

		}
		else
		{
			$_SESSION['backupStatus'] = false;
		}
?>
			<script>
			document.location="?go=BackupDone";
			</script>
<?php


	}
	else
	{
?>
<P>
	The <i>mysqldump</i> utility was not found in the <?php echo $dir;?> subdirectory.

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
<?php
	}



}


function restore()
{
	check_state(1);
	set_state(5);
	title('Restore In Progress');
	$status = $_SESSION['backupStatus'];
	$filename=$_SESSION['backupFile'];
	$stmt=create_restore_stmt($filename);
	$dir=$stmt['dir'];




	if (is_file($dir . '/mysql') || is_file($dir . '/mysql.exe'))
	{

?>
		The restore is now underway. Please wait till it completes.
<?php
		print "\n";


		$curdir=getcwd();
		chdir($dir);


		$ok=true;
		$stmts=explode("\n",$stmt['cmd']);
		foreach($stmts as $stmt)
		{

			$handle = popen($stmt, 'r');
			if ($handle=='false')
			{
				$ok=false;
				break;
			}
			$read = fread($handle, 10240);
			pclose($handle);
			$_SESSION['restoreOutput']=$read;
		}





			$_SESSION['restoreStatus'] = $ok;


?>
			<script>
			document.location="?go=RestoreDone";
			</script>
<?php


	}
	else
	{
?>
<P>
	The <i>mysql</i> utility was not found in the <?php echo $dir;?> subdirectory.

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
<?php
	}



}


function welcome()
{
	set_state(1);
?>
<br>
Welcome to the <?php echo APP_NAME;?> Database Upgrade Wizard.<P> If you have just updated
your <?php echo APP_NAME;?> code base, you will need to complete the upgrade process in order to ensure your system is fully operational with the new version.
<P>
You will not be able to log into <?php echo APP_NAME;?> until your the database upgrade process is completed.
<P>
<font color=orange>!!NB!! You are advised to backup the database before attempting the upgrade. !!NB!!</font>
<P>
If you have already done this, you may skip this step can continue directly to the upgrade.
<P>


&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="cancel" onclick="document.location='..';">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="backup now" onclick="javascript:do_start('BackupConfirm');">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradeConfirm');">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="restore database" onclick="javascript:do_start('RestoreConfirm');">


<?php


}


function UpgradePreview()
{
	title('Preview Upgrade');
	global $default;
?>
        <p>The table below describes the upgrades that need to occur to
        upgrade your <?php echo APP_NAME;?> installation to <strong><?php echo $default->systemVersion;?></strong>.
        Click on the button below the table to perform the upgrades.</p>
  <?php
        $upgradeTable = generateUpgradeTable();
	print $upgradeTable;
	?>
	<br>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('Upgrade')">
	<?php

}


function Upgrade()
{
	title('Upgrade In Progress');
	global $default;
?>
        <p>The table below describes the upgrades that have occurred to
        upgrade your <?php echo APP_NAME;?> installation to <strong><?php echo $default->systemVersion;?></strong>.

  <?php
    $pre_res = performPreUpgradeActions();
	if (PEAR::isError($pre_res))
	{
?>
<font color="red">Pre-Upgrade actions failed.</font><br>
<?php
	}
	else
	{
?>
<p>
<font color="green">Pre-Upgrade actions succeeded.</font><br>
<?php
	}
?>
<p>
  <?php
	$res = performAllUpgrades();
	if (PEAR::isError($res) || PEAR::isError($pres))
	{
?>
<font color="red">Upgrade failed.</font>
<?php
	}
	else
	{
?>
<p>
<font color="green">Upgrade succeeded.</font>
<?php
	}
?>
<p>
  <?php
    $post_pres = performPostUpgradeActions();
	if (PEAR::isError($post_res))
	{
?>
<font color="red">Post-Upgrade actions failed.</font><br><br>
<?php
	}
	else
	{
?>
<p>
<font color="green">Post-Upgrade actions succeeded.</font><br><br>
<script>
    alert("To complete the upgrade please do the following before continuing:\n\n1. Restart the services as appropriate for your environment.\n\n\nOn first run of your upgraded installaton please do the following:\n\n1. Hard refresh your bowser (CTRL-F5) on first view of the Dashboard.\n2. Enable the new plugins you wish to use.\n\n\nSelect 'next' at the bottom of this page to continue.")
</script>
<?php
	}
?>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')">
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:document.location='..';">
<?php
}

?>
<tr>
<td height=80 <?php
  	if($oKTConfig->get('ui/poweredByDisabled') == '0'){
  		?> align="right"><img src="<?php echo $oKTConfig->get('ui/powerLogo');?>"></td>
  	<?php }else{ ?>
	  	background="../resources/graphics/ktbg.png">&nbsp;</td>
	<?php }?>
</table>
