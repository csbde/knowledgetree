<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
	KTPluginUtil::registerPlugins();
	return $res;
}

function failWritablePath($name, $path) {
	if (!is_writable($path)) {
		sprintf("The path for setting %s, which is set to %s, can not be written to.  Correct this situation before continuing.", $name, $path);
		exit(1);
	}
}

failWritablePath('Log directory', $default->logDirectory);
failWritablePath('Document directory', $default->documentRoot);

if (PEAR::isError($loggingSupport)) {
	print '<p><font color="red">Logging support is not currently working.  Check post-installation checkup.</font></p>';
	exit(1);
}

if (PEAR::isError($dbSupport)) {
	print '<p><font color="red">Database support is not currently working.  Check post-installation checkup.</font></p>';
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
	  	echo '../resources/graphics/ktlogo-topbar-right.png';
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
<?

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
The database upgrade wizard completes the upgrade process on an existing KnowledgeTree installation. It applies
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
<?	
}

function loginProcess()
{
	$username=$_REQUEST['username'];
	$password=$_REQUEST['password'];
	
	$oUser = User::getByUserName($username);
	
	if (PEAR::isError($oUser))
	{
		session_unset();
		loginFailed(_kt('Could not identify user'));
		return;
	}
	
	$is_admin=false;
	$groups = GroupUtil::listGroupsForUser($oUser);
	foreach($groups as $group)
	{
		if ($group->getSysAdmin())
		{
			$is_admin=true;
			break;
		}
	}
	
	if (!$is_admin)
	{
		session_unset();
		loginFailed(_kt('Could not identify administrator'));
		return;
	}
		
	$authenticated = KTAuthenticationUtil::checkPassword($oUser, $password);
	
	if (!$authenticated)
	{
		session_unset();
		loginFailed(_kt('Could not authenticate user'));
		return;
	}
		
	$_SESSION['setup_user'] = $oUser;
		
	welcome();	
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
		$mysqlname ='mysql.exe';
	}
	
	
	
	if (strpos(__FILE__,'knowledgeTree') !== false && strpos(__FILE__,'ktdms'))
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
	if ($dbPort=='' || $dbPort=='default') $dbPort = get_cfg_var('mysql.default_port');

	$date=date('Y-m-d-H-i-s');

	$dir=resolveMysqlDir();
	
	$info['dir']=$dir;

	$prefix='';
	if (OS_UNIX)
	{
		$prefix= "./";
	}
	
	$tmpdir=resolveTempDir();
	
	if (is_null($targetfile))
	{
		$targetfile="$tmpdir/kt-backup-$date.sql";
	}
	
	
	$stmt = $prefix . "mysqldump --user=\"$adminUser\" -p --port=$dbPort \"$dbName\" > \"$targetfile\"";
	$info['display']=$stmt;
	$info['target']=$targetfile;

	 
	$stmt  = $prefix. "mysqldump --user=\"$adminUser\" --password=\"$adminPwd\" --port=$dbPort \"$dbName\" > \"$targetfile\"";
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

	 
	$dir=resolveMysqlDir();
	 
	$info['dir']=$dir;

	$prefix='';
	if (OS_UNIX)
	{
		$prefix .= "./";
	}
	 
	
	$tmpdir=resolveTempDir();
	
	$stmt = $prefix ."mysqladmin --user=\"$adminUser\" -p --port=$dbPort drop  \"$dbName\"<br>";
	$stmt .= $prefix ."mysqladmin --user=\"$adminUser\" -p --port=$dbPort create  \"$dbName\"<br>";
	
	
	$stmt .= $prefix ."mysql --user=\"$adminUser\" -p --port=$dbPort \"$dbName\" < \"$targetfile\"\n";
	$info['display']=$stmt;

	 
	$stmt = $prefix ."mysqladmin --user=\"$adminUser\" --force --password=\"$adminPwd\" --port=$dbPort drop  \"$dbName\"\n";
	$stmt .= $prefix ."mysqladmin --user=\"$adminUser\" --password=\"$adminPwd\" --port=$dbPort create  \"$dbName\"\n";
	 
	$stmt .=  $prefix ."mysql --user=\"$adminUser\" --password=\"$adminPwd\" --port=$dbPort \"$dbName\" < \"$targetfile\"";
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
	
	$dir = $oKTConfig->get('backups/backupDirectory',$dir);
	
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
<?
	}
?>
<p>
We are about to start the upgrade process.  
<P>
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradePreview')"> 

<?
	
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
<nobr>cd "<?=$dir?>"</nobr>
<br>
<?	
	}
	else
	{
?>
It appears as though you are not using the stack installer, or are using a custom install.
<P>
You can continue to do the backup manually using the following process:
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?

	}
?>
<nobr><?=$stmt['display']?>
</table>
<P>
Press <i>continue to backup</i> to attempt the command(s) above.
<P>
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')"> &nbsp;&nbsp; &nbsp; &nbsp; 

<?
if ($dir != '')
{
?>

<input type=button value="next" onclick="javascript:do_start('Backup')"> 


<?
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
 	There don't seem to be any backups to restore from the <i>"<?=$dir?>"</i> directory.
 <?
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
<?
	$i=0;
	foreach($files as $file)
	{
		$color=((($i++)%2)==0)?'white':'lightgrey';		
?>
		<tr bgcolor="<?=$color?>">
			<td><?=$file?>
			<td><?=filesize($dir . '/'.$file)?>
			<td><input type=button value="restore" onclick="javascript:selectRestore('<?=$file?>')">
<?		
	}
?>
 	</table>
 <?   	
    }
   ?>

   <p>
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')"> 
   <?

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
<?

}

function restoreConfirm()
{
	if (!isset($_SESSION['backupFile']) || !is_file($_SESSION['backupFile']))
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
<nobr>cd "<?=$dir?>"</nobr>
<br>
<?	
	}
	else
	{
?>
It appears as though you are not using the stack installer, or are using a custom install.
<P>
You can continue to do the restore manually using the following command(s):
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?

	}
?>
<nobr><?=$stmt['display']?>
</table>
<P>
<?
if ($dir != '')
{
?>
Press <i>continue to restore</i> to attempt the command(s) above.

<P>
<?
}
?>
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="select another backup" onclick="javascript:do_start('RestoreSelect')"> 

<?
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


<?
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
		The backup file <nobr><I>"<?=$filename?>"</i></nobr> has been created.
		<P> It appears as though the <font color=green>backup has been successful</font>.
		<P>
		<?
			if ($stmt['dir'] != '')
			{
		?>
				Manually, you would do the following to restore the backup:
				<P>
				<table bgcolor="lightgrey">
				<tr>
					<td>
						<nobr>cd <?=$stmt['dir']?></nobr>
						<br>
		<?	
			}
			else
			{
		?>
				It appears as though you are not using the stack installer, or are using a custom install.
				<P>
				If you need to restore from this backup, you should be able to use the following statements:
				<P>
				<table bgcolor="lightgrey">
				<tr>
					<td>
		<?
			}
		?>
						<nobr><?=$stmt['display']?>
				</table>
			
<?
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
<?=$_SESSION['backupOutput']?>
</table>
<?
		
	}
?>
<br>				
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')"> 
<?
	if ($status)
	{
		?>
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradeConfirm')"> 
	
<?	
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
		The restore of <nobr><I>"<?=$filename?>"</i></nobr> has been completed. 
		<P>
		It appears as though the <font color=green>restore has been successful</font>.
		<P>


				
<?
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
<?=$_SESSION['restoreOutput']?>
</table>
<?
		
	}
?>

<br>				
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')"> 

<?	
	
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
			document.location="?go=<?=$state?>";
			</script>
			<?
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
<?

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
<?	
		
		 
	}
	else 
	{
?>
<P>
	The <i>mysqldump</i> utility was not found in the <?=$dir?> subdirectory.
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')"> 
<?		
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
<?
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
<?	
		
		 
	}
	else 
	{
?>
<P>
	The <i>mysql</i> utility was not found in the <?=$dir?> subdirectory.
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')"> 
<?		
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
If you have already done this, you may skip this step can continue directly to the upgade.
<P>
 
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="cancel" onclick="document.location='..';"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="backup now" onclick="javascript:do_start('BackupConfirm');"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradeConfirm');"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="restore database" onclick="javascript:do_start('RestoreConfirm');"> 


<?


}


function UpgradePreview()
{
	title('Preview Upgrade');
	global $default;
?>
        <p>The table below describes the upgrades that need to occur to
        upgrade your <?php echo APP_NAME;?> installation to <strong><?=$default->systemVersion?></strong>.
        Click on the button below the table to perform the upgrades.</p>
  <?
        $upgradeTable = generateUpgradeTable();
	print $upgradeTable;
	?>
	<br> 
 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('Upgrade')"> 
	<?

}


function Upgrade()
{
	title('Upgrade In Progress');	
	global $default;
?>
        <p>The table below describes the upgrades that have occurred to
        upgrade your <?php echo APP_NAME;?> installation to <strong><?=$default->systemVersion?></strong>.
 
  <?
	$res = performAllUpgrades();
	if (PEAR::isError($res)) 
	{
?>
<font color="red">Upgrade failed.</font>
<?
	} 
	else 
	{
		 
?>
<p>
<font color="green">Upgrade succeeded.</font>
<?
	}
?>
<p>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('home')"> 
&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:document.location='..';"> 
<?	 
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