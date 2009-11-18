<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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

error_reporting(E_ALL);

//require_once('../config/dmsDefaults.php');

function get_php_setting($val) {
    $r =  (ini_get($val) == '1' ? 1 : 0);
    return $r ? 'ON' : 'OFF';
}

function boolSetting($name, $setting, $preferred, $red = true, $message = "") {
    $current = get_php_setting($setting);
    $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, $preferred);
    if ($current == $preferred) {
        $ret .= sprintf('<font color="green"><b>%s</b></font>', $current);
    } else {
        if ($red === true) {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', $current);
        } else {
            $ret .= sprintf('<font color="orange"><b>%s</b></font>', $current);
        }
        if ($message) {
            $ret .= ' (' . $message . ')';
        }
    }
    $ret .= "</td></tr>\n";
    return $ret;
}

function stringSetting($name, $setting, $preferred, $red = true, $message = "") {
    $current = ini_get($setting);
    $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, $preferred);
    if ($current == $preferred) {
        $ret .= sprintf('<font color="green"><b>%s</b></font>', $current);
    } else {
        if ($red === true) {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', $current);
        } else {
            $ret .= sprintf('<font color="orange"><b>%s</b></font>', $current);
        }
        if ($message) {
            $ret .= ' (' . $message . ')';
        }
    }
    $ret .= "</td></tr>\n";
    return $ret;
}

function emptySetting($name, $setting) {
    $current = ini_get($setting);
    $ret = sprintf('<tr><td>%s (%s)</td><td>unset</td><td>', $name, $setting);
    if (($current === false) or ($current === "")) {
        $ret .= sprintf('<font color="green"><b>unset</b></font>');
    } else {
        $ret .= sprintf('<font color="red"><b>Set: %s</b></font>', $current);
    }
    $ret .= "</td></tr>\n";
    return $ret;
}

function writablePath($name, $path) {
    $ret = sprintf('<tr><td>%s (%s)</td><td>', $name, $path);
    if (is_writable('../' . $path)) {
        $ret .= sprintf('<font color="green"><b>Writeable</b></font>');
    } else {
        $ret .= sprintf('<font color="red"><b>Unwriteable</b></font>');
    }
    return $ret;
}

function prettySizeToActualSize($pretty) {
    if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'G') {
        return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024 * 1024;
    }
    if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'M') {
        return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024;
    }
    if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'K') {
        return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024;
    }
    return (int)$pretty;
}

function prettySize($v) {
    $v = (float)$v;
    foreach (array('B', 'K', 'M', 'G') as $unit) {
        if ($v < 1024) {
            return $v . $unit;
        }
        $v = $v / 1024;
    }
}

function get_php_int_setting($val) {
    $r = ini_get($val);
    if ($r === false) {
        return $r;
    }
    return prettySizeToActualSize($r);
}

function bigEnough($name, $setting, $preferred, $bytes = false, $red = true, $zero_ok = false, $minusone_ok = false) {
    $current = get_php_int_setting($setting);
    if ($bytes === true) {
        $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, prettySize($preferred));
    } else {
        $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, $preferred);
    }

    if ($current === false) {
        $ret .= '<font color="green"><b>unset</b></font>';
    } else if ($current >= $preferred) {
        if ($bytes === true) {
            $ret .= sprintf('<font color="green"><b>%s</b></font>', prettySize($current));
        } else {
            $ret .= sprintf('<font color="green"><b>%s</b></font>', $current);
        }
    } else if (($current == 0) && ($zero_ok)) {
        $ret .= sprintf('<font color="green"><b>unlimited (%s)</b></font>', $current);
    } else if (($current == -1) && ($minusone_ok)) {
        $ret .= sprintf('<font color="green"><b>unlimited (%s)</b></font>', $current);
    } else {
        if ($bytes === true) {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', prettySize($current));
        } else {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', $current);
        }
    }

    $ret .= "</td></tr>\n";
    return $ret;
}

function haveExtension($ext) {
    if (extension_loaded($ext)) {
        return true;
    }

    // According to PEAR.php:
    // if either returns true dl() will produce a FATAL error, stop that
    if ((ini_get('enable_dl') != 1) || (ini_get('safe_mode') == 1)) {
        return false;
    }

    $libfileext = '.so';
    $libraryprefix = '';
    if (substr(PHP_OS, 0, 3) == "WIN") {
        $libfileext = '.dll';
        $libraryprefix = 'php_';
    }
    @dl(sprintf("%s%s%s", $libraryprefix, $ext, $libfileext));
    return extension_loaded($ext);
}


function must_extension_loaded($ext, $message = "") {
    if (haveExtension($ext)) {
        return '<b><font color="green">Available</font></b>';
    }
    if ($message) {
        return '<b><font color="red">Unavailable</font></b> (' .  $message . ')';
    }
    return '<b><font color="red">Unavailable</font></b>';
}
function can_extension_loaded($ext, $message = "") {
    if (haveExtension($ext)) {
        return '<b><font color="green">Available</font></b>';
    }
    if ($message) {
    return '<b><font color="orange">Unavailable</font></b> (' . $message . ')';
    }
    return '<b><font color="orange">Unavailable</font></b>';
}

$phpversion = phpversion();
//$phpversion = '5.1'; // for debug

$phpversion5 = version_compare($phpversion, '5.0.0', '>=');
$phpversion522 = version_compare($phpversion, '5.2.2', '>=');
$phpversion6 = version_compare($phpversion, '6.0.0', '<');

if($phpversion5 == 1){
    $phpversion5text = '<b><font color="green">Yes</font></b>';
} else {
    $phpversion5text = '<b><font color="red">No</font></b> <small>(You have PHP version '. $phpversion .' - '.APP_NAME.' does not work with versions less than PHP5 anymore)</small>';
}
if($phpversion522 == 1){
    $phpversion522text = '<b><font color="green">Yes</font></b>';
} else {
    $phpversion522text = '<b><font color="red">No</font></b> <small>(You have PHP version '. $phpversion .' - PHP 5.2.2 or above is recommended)</small>';
}
if($phpversion6 == 1){
    $phpversion6text = '<b><font color="green">Yes</font></b>';
} else {
    $phpversion6text = '<b><font color="red">No</font></b> <small>(You have PHP version '. $phpversion .' - '.APP_NAME.' does not work with versions greater than PHP5 yet)</small>';
}

function running_user() {
    if (substr(PHP_OS, 0, 3) == "WIN") {
        return null;
    }
    if (extension_loaded("posix")) {
        $uid = posix_getuid();
        $userdetails = posix_getpwuid($uid);
        return $userdetails['name'];
    }
    if (file_exists('/usr/bin/whoami')) {
        return exec('/usr/bin/whoami');
    }
    if (file_exists('/usr/bin/id')) {
        return exec('/usr/bin/id -nu');
    }
    return null;
}

function htaccess() {
    if (array_key_exists('kt_htaccess_worked', $_SERVER)) {
        return '<p><strong><font color="green">Your web server is set up to use the .htaccess files.</font></strong></p>';
    }
    return '<p><strong><font color="orange">Your web server is NOT set up to use the .htaccess files.</font></strong></p>';
}

?>
<html>
  <head>
    <title><?php echo APP_NAME;?> Checkup</title>
    <style>
th { text-align: left; }
td { vertical-align: top; }
    </style>
  </head>

  <body>

<h1><?php echo APP_NAME;?> Checkup</h1>

<p>This checkup allows you to check that your environment is ready to
support a <?php echo APP_NAME;?> installation, and that you can proceed to
configure your system.  Red items are things to fix.  Orange items means
you may not be having the ultimate experience unless the support is
added.  Green items means you're ready to go in this area.  You can
check back here to see if anything has changed in your environment if
you have any problems.</p>

<h2>.htaccess file</h2>

<p>You can let <?php echo APP_NAME;?> manage the PHP settings that apply to the
<?php echo APP_NAME;?> application (it won't affect your other applications) by
configuring your web server to use the .htaccess files that come with
<?php echo APP_NAME;?>.  This will ensure that the settings for <?php echo APP_NAME;?>
(detailed below) are set up for optimal, reliable performance.</p>

<?php echo htaccess()?>

<?php

$kturl = KTUtil::kt_url();

?>

<h2>General KnowledgeTree</h2>

<table>
<tbody>
<tr>
<th width=400>KnowledgeTree Server Directory</td>
<td><?php echo KT_DIR;?></td>
</tr>
<tr>
<th>KnowledgeTree URL</td>
<td><?php echo $kturl;?></td>
</tr>
</tbody>
</table>


<h2>PHP version and extensions</h2>

<p>This relates to your PHP installation environment - which version of
PHP you are running, and which modules are available.</p>

<?php
$extensions = array(
	'session'=>'Session',
	'mysql'=>'MySQL',
	'mbstring'=>'Multi Byte String',
	'curl'=>'cURL',
	'exif'=>'Exif',
	'sockets'=>'Sockets',
);
$optional_extensions = array(
	'gettext'=>array('Gettext','Only needed for using non-English languages'),
	'fileinfo'=>array('Fileinfo','Provides better file identification support - not necessary if you use file extensions'),
	'win32service'=>array('Win32 Service','Allows Microsoft Windows services to be controlled. '),
	'openssl'=>array('OpenSSL','Provides encryption support')
);


?>

<table width="100%">
  <tbody>
    <tr>
      <th>PHP version 5 or above</th>
      <td><?php echo $phpversion5text?></td>
    </tr>
    <tr>
      <th>PHP version 5.2.2 or above</th>
      <td><?php echo $phpversion522text?></td>
    </tr>
    <tr>
      <th>PHP version below 6</th>
      <td><?php echo $phpversion6text?></td>
    </tr>
<?php
	foreach($extensions as $ext=>$name)
	{
?>
    <tr>
      <th><?php echo $name?> support</th>
      <td><?php echo must_extension_loaded($ext);?></td>
    </tr>
<?php
	}
	foreach($optional_extensions as $ext=>$detail)
	{
		list($name, $desc) = $detail;
?>
    <tr>
      <th><?php echo $name?> support</th>
      <td><?php echo can_extension_loaded($ext, $desc);?></td>
    </tr>
<?php
	}
?>
  </tbody>
</table>

<h2>PHP configuration</h2>

<p>This relates to the configuration of PHP on your system.</p>

<h3>Recommended settings</h3>

<table width="50%">
  <thead>
    <tr>
      <th>Configuration option</th>
      <th>Recommended value</th>
      <th>Current value</th>
    </tr>
  </thead>
  <tbody>
<?php echo boolSetting('Safe Mode','safe_mode','OFF')?>
<?php echo boolSetting('Display Errors','display_errors','ON', false, "Will be set correctly anyway.")?>
<?php echo boolSetting('Display Startup Errors','display_startup_errors','ON', false, "Will be set correctly anyway.")?>
<?php echo boolSetting('File Uploads','file_uploads','ON')?>
<?php echo boolSetting('Magic Quotes GPC','magic_quotes_gpc','OFF', false, "Quotes will be removed; not optimal")?>
<?php echo boolSetting('Magic Quotes Runtime','magic_quotes_runtime','OFF')?>
<?php echo boolSetting('Register Globals','register_globals','OFF', false, "Globals will be removed; not optimal, may be a security risk")?>
<?php echo boolSetting('Output Buffering','output_buffering','OFF')?>
<?php echo boolSetting('Session auto start','session.auto_start','OFF')?>
<?php echo emptySetting('Automatic prepend file','auto_prepend_file')?>
<?php echo emptySetting('Automatic append file','auto_append_file')?>
<?php echo emptySetting('Open base directory','open_basedir')?>
<?php echo stringSetting('Default MIME type', 'default_mimetype', 'text/html')?>
  </tbody>
</table>

<h3>Limits</h3>

<table width="50%">
  <thead>
    <tr>
      <th>Configuration option</th>
      <th>Recommended value</th>
      <th>Current value</th>
    </tr>
  </thead>
  <tbody>
<?php echo bigEnough('Maximum POST size', 'post_max_size', 32 * 1024 * 1024, true)?>
<?php echo bigEnough('Maximum upload size', 'upload_max_filesize', 32 * 1024 * 1024, true, true, false, true)?>
<?php echo bigEnough('Memory limit', 'memory_limit', 32 * 1024 * 1024, true, true, false, true)?>
<?php echo ""; # bigEnough('Maximum execution time', 'max_execution_time', 30)?>
<?php echo ""; # bigEnough('Maximum input time', 'max_input_time', 60)?>
  <tbody>
</table>


<h3>Paths</h3>
<table width="50%">
  <tbody>
    <tr>
      <td class="item">
      Session save path
      </td>
      <td align="left">
      <b><?php echo (($sp=ini_get('session.save_path'))?$sp:'Not set'); ?></b>,
      <?php echo is_writable( $sp ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>';?>
      </td>
    </tr>
    <tr>
      <td class="item">
      Upload temporary path
      </td>
      <td align="left">
      <b><?php echo (($sp=ini_get('upload_tmp_dir'))?$sp:'Not set'); ?></b>
      <?php if ($sp) { echo ', ' . is_writable( $sp ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>';} ?>
      </td>
    </tr>
  </tbody>
</table>

<h2>Filesystem</h2>

<table width="50%">
  <tbody>
<?php
$username = running_user();
if (is_null($username)) {
    $message = "You are on a system that does not make user details available, and so no advice is possible on the correct ownership of the <b>log</b> and <b>Documents</b> directories.";
} else {
    $message = APP_NAME.' will be run as the <b><font color="orange">' . $username . '</font></b> system user, and must be able to write to the <b>log</b> and <b>Documents</b> directories.';
}
?>
<tr>
<td width="33%">General</td>
<td><?php echo $message?></td>
</tr>
  </tbody>
</table>

<h2>Post-installation checkup</h2>

<p>Once you have installed, check the <a href="postcheckup.php">Post-installation checkup</a>.</p>

  </body>
</html>
