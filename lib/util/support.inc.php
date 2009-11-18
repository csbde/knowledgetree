<?php

/**
 * $Id:$
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


/**
 * TODO: refactor into seperate comparison object
 *
 */
class MD5SourceTree
{
	private $rootDir;
	private $logFilename;
	private $logFile;
	private $numDirectories;
	private $numFiles;
	private $comparisonFailure;
	private $exclusions;

	public function __construct($exclusions = array())
	{
		$this->numDirectories = 0;
		$this->numFiles = 0;
		$this->exclusions = $exclusions;
	}

	/**
	 * Helper function to traverse the directories. Called initially by scan()
	 *
	 * @param string $dir
	 */
	private function _scan($dir)
	{
		if (in_array($dir, $this->exclusions))
		{
			return;
		}
		if (is_dir($dir))
		{
    		if ($dh = opendir($dir))
    		{
        		while (($filename = readdir($dh)) !== false)
        		{
        			if (substr($filename,0,1) == '.')
        			{
        				continue;
        			}

        			$path = $dir . '/' . $filename;
        			if (is_dir($path))
        			{
        				$this->numDirectories++;
        				$this->_scan($path);
        			}
        			else
        			{
        				$this->numFiles++;
        				if (is_readable($path))
        				{
							$md5 = md5_file($path);
							$path = substr($path, strlen($this->rootDir) + 1);
							fwrite($this->logFile, "$md5:$path\n");
        				}
        			}
        		}
        		closedir($dh);
    		}
		}
	}

	/**
	 * This does the scan of the directory.
	 *
	 * @param string $rootDir
	 * @param string $reportFile
	 */
	public function scan($rootDir, $reportFile)
	{
		$this->rootDir = $rootDir;
		$this->logFilename = $reportFile;
		$this->logFile = fopen($reportFile,'wt');
		$this->_scan($rootDir);
		fclose($this->logFile);
	}


	/**
	 * Used by the compare function, to load a md5 file
	 *
	 * @param string $path
	 * @return array
	 */
	private function _loadDirectory($path)
	{
		$dirs = array();
		$numFiles = 0;
		$numDirectories = 0;
		$fp = fopen($path, 'rt');
		while (!feof($fp))
		{
			$line = fgets($fp, 10240);
			list($md5, $path) = explode(':',$line);
			$dirname = dirname($path);
			$filename = basename($path);
			$numFiles++;
			$dirs[$dirname][$filename] = $md5;
		}
		fclose($fp);
		return array('numFiles'=>$numFiles, 'numDirectories'=>$numDirectories, 'dirs'=>$dirs);
	}

	/**
	 * Internal function used to compare two md5 directory structures.
	 *
	 * @param array $prev
	 * @param array $cur
	 * @param string $msg
	 */
	private function _compare($prev, $cur, $msg)
	{
		foreach($prev['dirs'] as $prevDir=>$prevDirFiles)
		{
			if (!array_key_exists($prevDir, $cur['dirs']))
			{
				print "$msg: $prevDir does not exist in target.\n";
			}
			else
			{
				foreach($prevDirFiles as $prevFilename=>$prevMD5)
				{
					if (!array_key_exists($prevFilename, $cur['dirs'][$prevDir]))
					{
						$prevFilename = substr($prevFilename,0,-1);
						print "$msg: $prevFilename does not exist in $prevDir.\n";
					}
					else
					{
						if (in_array($prevDir . '/' . $prevFilename, $this->comparisonFailure))
						{
							continue;
						}

						$newMD5 = $cur['dirs'][$prevDir][$prevFilename];
						if ($prevMD5 != $newMD5)
						{
							$this->comparisonFailure[] = $prevDir . '/' . $prevFilename;
							$prevFilename = substr($prevFilename,0,-1);
							print "$msg: $prevFilename does not match md5; $prevMD5 != $newMD5.\n";
						}
					}
				}
			}
		}
	}

	/**
	 * Compare to md5 report files
	 *
	 * @param string $reportA
	 * @param string $reportB
	 */
	public function compare($reportA, $reportB)
	{
		if (is_null($reportB))
		{
			$reportB = $this->logFilename;
		}
		$this->comparisonFailure = array();
		$prev = $this->_loadDirectory($reportA);
		$cur = $this->_loadDirectory($reportB);

		if ($prev['numDirectories'] != $cur['numDirectories'])
		{
			print "Folder count mismatch!\n";
		}

		if ($prev['numFiles'] != $cur['numFiles'])
		{
			print "File count mismatch!\n";
		}

		$this->_compare($prev, $cur,'>');
		$this->_compare($cur,$prev,'<');
	}
}

class SupportUtil
{
	private $path;
	private $innodb;
	private $noninnodb;

	/**
	 * Constructor for SupportUtil. Creates a folder with format support-YYYY-MM-DD_HH-mm-ss
	 *
	 */
	function __construct()
	{
		$config = KTConfig::getSingleton();
		$tempdir = $config->get('urls/tmpDirectory');

		$this->path = $tempdir . "/support-" . date('Y-m-d_H-i-s');

		mkdir($this->path);
	}

	/**
	 * Main function to capture as much info that is reasonable.
	 *
	 */
	public function capture()
	{
		// get php info
		$this->capture_phpinfo($this->path . '/phpinfo.htm');

		// get db schema
		$tables = $this->capture_db_schema($this->path);

		// get zseq counters from taables
		$this->capture_zseqs($tables, $this->path . '/zseqreport.htm');

		// get md5 on table
		$exclusions = array(
			KT_DIR . '/var',
			realpath(KT_DIR . '/../var')
		);
		$tree = new MD5SourceTree($exclusions);
		$config = KTConfig::getSingleton();
		$sourcePath = $config->get('KnowledgeTree/fileSystemRoot');
		$tree->scan($sourcePath, $this->path . '/md5report.txt');

		// get plugins
		$this->capture_plugins($this->path . '/plugins.htm');

		// get logs
		$this->capture_logs($this->path);

		// get sys info
		$this->get_sysinfo($this->path);

		// get storage engine list
		$this->create_storage_engine($this->path);

		// get disk space listing
		$this->capture_df($this->path);

		// get process listing
		$this->capture_ps($this->path);

		// get version files
		$this->capture_version_files($this->path);

		// get system settings
		$this->capture_system_settings($this->path);

		// create out index file
		$this->create_index($this->path);

	}

	/**
	 * Main helper function to cleanup after creating zip file
	 *
	 * @param stirng $path
	 */
	private function _cleanup($path)
	{
		$dh = opendir($path);
		while (($filename = readdir($dh)) !== false)
		{
			if (substr($filename,0,1) == '.') continue;

			$fullname = $path . '/' . $filename;
			if (is_dir($fullname))
			{
				$this->_cleanup($fullname);
			}
			else
			{
				unlink($fullname);
			}
        }
		closedir($dh);
		rmdir($path);
	}

	/**
	 * Main cleanup function
	 *
	 */
	public function cleanup()
	{
		$this->_cleanup($this->path);
	}

	/**
	 * Creates an archive file
	 *
	 * @return string
	 */
	public function archive()
	{
		$zip = KTUtil::findCommand('export/zip', 'zip');

		chdir(dirname($this->path));
		$subdir = basename($this->path);
		$archivename = $this->path . '.zip';
		$cmd = "\"$zip\" -r \"$archivename\" \"$subdir\"";

		KTUtil::pexec($cmd);

		return $archivename;
	}

	/**
	 * Tries to get list of running processes
	 *
	 * @param string $path
	 */
	private function capture_ps($path)
	{
		$ps = KTUtil::findCommand('externalBinary/ps', 'ps');
		if (!file_exists($ps) || !is_executable($ps))
		{
			return;
		}

		$cmd = "'$ps' waux";
		// TODO: refactor to use KTUtil::pexec

		$ps = popen($cmd, 'r');
		$content = fread($ps , 10240);
		pclose($ps);

		file_put_contents($path . '/ps.txt', $content);
	}

	/**
	 * Get list of KnowledgeTree version files
	 *
	 * @param string $path
	 */
	private function capture_version_files($path)
	{
		$path = $path . '/versions';
		mkdir($path);

		$ver_path = KT_DIR . '/docs';
		$dh = opendir($ver_path);
		while (($filename = readdir($dh)) !== false)
		{
			if (substr($filename, 0, 7) == 'VERSION')
			{
				copy($ver_path . '/' . $filename, $path . '/' . $filename);
			}
        }
		closedir($dh);
	}

	/**
	 * Dump the system_settings table, except for dashboard-state entries.
	 *
	 * @param string $path
	 */
	private function capture_system_settings($path)
	{
		$sql = "SELECT id, name, value FROM system_settings";
		$rs = DBUtil::getResultArray($sql);
		$html = "<h1>System Settings</h1>";
		$html .= '<br><table border=1 cellpadding=0 cellspacing=0>';
		foreach($rs as $rec)
		{
			$id = $rec['id'];
			$name = $rec['name'];
			$value = $rec['value'];
			if (substr($name, 0, 15) == 'dashboard-state') continue;
			$html .= "<tr><td>$id<td>$name<td>$value\r\n";
		}

		$html .= '</table>';
		file_put_contents($path . '/systemsettings.htm', $html);
	}

	/**
	 * Get disk usage
	 *
	 * @param string $path
	 */
	private function capture_df($path)
	{
		$df = KTUtil::findCommand('externalBinary/df', 'df');
		if (!file_exists($df) || !is_executable($df))
		{
			return;
		}

		$df = popen($df, 'r');
		$content = fread($df, 10240);
		pclose($df);

		file_put_contents($path . '/df.txt', $content);
	}

	/**
	 * Get php info
	 *
	 * @param string $filename
	 */
	private function capture_phpinfo($filename)
	{
		ob_start();
		phpinfo();
		$phpinfo = ob_get_clean();
		file_put_contents($filename, $phpinfo);
	}

	/**
	 * Helper table to get schema
	 *
	 * @param string $folder
	 * @return string
	 */
	private function capture_table_schema($folder)
	{
		$tables = array();
		$sql = 'show tables';
		$results = DBUtil::getResultArray($sql);

		foreach($results as $rec)
		{
			$rec = array_values($rec);
			$tablename = $rec[0];
			$sql = "show create table $tablename";
			$sql = DBUtil::getOneResultKey($sql,'Create Table');

			file_put_contents($folder . '/' . $tablename . '.sql.txt', $sql);

			$sql = strtolower($sql);
			if (strpos($sql, 'innodb') === false)
				$this->noninnodb[] = $tablename;
			else
				$this->innodb[] = $tablename;


			$tables[] = $tablename;
		}

		return $tables;
	}

	/**
	 * Get database schema
	 *
	 * @param string $folder
	 * @param string $suffix
	 * @return array
	 */
	private function capture_db_schema($folder, $suffix='')
	{
		$schema_folder = $folder . '/' . $suffix . 'schema';
		mkdir($schema_folder);

		return $this->capture_table_schema($schema_folder);
	}

	/**
	 * Get list of plugins
	 *
	 * @param string $filename
	 */
	private function capture_plugins($filename)
	{
		$sql = 'select namespace,path, disabled, unavailable,friendly_name from plugins';
		$result = DBUtil::getResultArray($sql);
		$plugins = "<h1>Plugin Status Report</h1>";

		$plugins .= '<table border=1 cellpadding=0 cellspacing=0u	>';
		$plugins .= '<tr><th>Display Name<th>Availability<th>Namespace<th>Path';
		foreach($result as $rec)
		{
			$fileexists = file_exists(KT_DIR . '/' . $rec['path'])?'':'<font color="red">';
			$status = ($rec['disabled'] == 0)?'<font color="green">':'<font color="orange">';
			$unavailable = ($rec['unavailable'] == 0)?'available':'<font color="orange">unavailable';

			$plugins .= '<tr>';
			$plugins .= '<td>' . $status . $rec['friendly_name'];
			$plugins .= '<td>' . $unavailable;
			$plugins .= '<td>' . $rec['namespace'];
			$plugins .= '<td>'  . $fileexists . $rec['path'] . "\r\n";
		}
		$plugins .= '</table>';
		$plugins .= '<br>Plugin name is <font color=green>green</font> if  enabled  and <font color=orange>orange</font> if disabled .';
		$plugins .= '<br>Availability indicates that KnowledgeTree has detected the plugin not to be available.';
		$plugins .= '<br>Path is coloured <font color=red>red</font> if the plugin file cannot be resolved. If the path is not resolved, it should be flagged unavailable.';
		file_put_contents($filename, $plugins);
	}

	/**
	 * Make a zseq report
	 *
	 * @param string $tables
	 * @param string $filename
	 */
	private function capture_zseqs($tables, $filename)
	{
		$zseqs = '<h1>Table Counter Report</h1>';

		$zseqs .= '<table border=1 cellpadding=0 cellspacing=0>';
		$zseqs .= '<tr><td>Table<td>Max ID<td>ZSEQ<td>Status';

		foreach($tables as $ztablename)
		{
			if (substr($ztablename, 0, 5) != 'zseq_')
			{
				continue;
			}

			$tablename = substr($ztablename, 5);
			$sql = "SELECT max(id) as maxid FROM $tablename";
			$maxid = DBUtil::getOneResultKey($sql, 'maxid');

			$sql = "SELECT id FROM $ztablename";
			$zseqid = DBUtil::getOneResultKey($sql, 'id');

			$note = (is_null($maxid) || $maxid <= $zseqid)?'OK':'FAIL';
			if ($note == 'FAIL' && $maxid > $zseqid)
			{
				$note = 'COUNTER PROBLEM! maxid should be less than or equal to zseq';
			}
			if (PEAR::isError($maxid))
			{
				$maxid = '??';
				$note = "STRANGE - DB ERROR ON $tablename";
			}
			if (PEAR::isError($zseqid))
			{
				$zseqid = '??';
				$note = "STRANGE - DB ERROR ON $ztablename";
			}
			if (is_null($maxid))
			{
				$maxid='empty';
			}
			if (is_null($zseqid))
			{
				$zseqid='empty';
				$note = "STRANGE - ZSEQ SHOULD NOT BE EMPTY ON $ztablename";
			}
			$zseqs .= "<tr><td>$tablename<td>$maxid<td>$zseqid<td>$note\r\n";
		}
		$zseqs .= "</table>";
		file_put_contents($filename, $zseqs);
	}

	/**
	 * Get log files
	 *
	 * @param string $path
	 */
	private function capture_logs($path)
	{
		$path = $path . '/logs';
		mkdir($path);

		$this->capture_kt_log($path);
		$this->capture_apache_log($path);
		$this->capture_php_log($path);
		$this->capture_mysql_log($path);

	}

	/**
	 * Get Php log file. KT makes a php_error_log when tweak setting is enabled.
	 *
	 * @param string $path
	 */
	private function capture_php_log($path)
	{
		$config = KTConfig::getSingleton();
		$logdir = $config->get('urls/logDirectory');
		$logfile = $logdir . '/php_error_log';
		if (file_exists($logfile))
		{
			copy($logfile, $path . '/php-error_log.txt');
		}
	}

	/**
	 * Get mysql log from stack. It is difficult to resolve otherwise.
	 *
	 * @param string $path
	 */
	private function capture_mysql_log($path)
	{
		$stack_path = realpath(KT_DIR . '/../mysql/data');
		if ($stack_path === false || !is_dir($stack_path))
		{
			return;
		}

		$dh = opendir($stack_path);
		while (($filename = readdir($dh)) !== false)
		{
			if (substr($filename, -4) == '.log' && strpos($filename, 'err') !== false)
			{
				copy($stack_path . '/' . $filename, $path . '/mysql-' . $filename);
			}
        }
		closedir($dh);
	}

	/**
	 * Get Apache log file from stack. It is difficult to resolve otherwise.
	 *
	 * @param string $path
	 */
	private function capture_apache_log($path)
	{
		$stack_path = realpath(KT_DIR . '/../apache2/logs');
		if ($stack_path === false || !is_dir($stack_path))
		{
			return;
		}

		$dh = opendir($stack_path);
		while (($filename = readdir($dh)) !== false)
		{
			if (substr($filename, -4) == '.log' && strpos($filename, 'err') !== false)
			{
				copy($stack_path . '/' . $filename, $path . '/apache-' . $filename);
			}
        }
		closedir($dh);
	}

	/**
	 * Get KT log file.
	 *
	 * @param string $path
	 */
	private function capture_kt_log($path)
	{
		$date = date('Y-m-d');
		$config = KTConfig::getSingleton();
		$logdir = $config->get('urls/logDirectory');
		$dh = opendir($logdir);
		while (($filename = readdir($dh)) !== false)
		{
			if (substr($filename,0,14) != 'log-' . $date)
			{
				continue;
			}
            copy($logdir . '/' . $filename, $path . '/kt-' . $filename);
        }
		closedir($dh);
	}

	/**
	 * Get some basic info on Linux if possible. Get cpuinfo, loadavg, meminfo
	 *
	 * @param string $path
	 */
	private function get_sysinfo($path)
	{
		if (!OS_UNIX && !is_dir('/proc'))
		{
			return;
		}

		$path .= '/sysinfo';
		mkdir($path);

		$this->get_sysinfo_file('cpuinfo', $path);
		$this->get_sysinfo_file('loadavg', $path);
		$this->get_sysinfo_file('meminfo', $path);

	}

	/**
	 * Helper to get linux sysinfo
	 *
	 * @param string $filename
	 * @param string $path
	 */
	private function get_sysinfo_file($filename, $path)
	{
		if (!is_readable('/proc/' . $filename))
		{
			return;
		}
		$content = file_get_contents('/proc/' . $filename);
		file_put_contents($path . '/' . $filename . '.txt', $content);
	}

	/**
	 * Helper to create the index file for the support archive.
	 *
	 * @param string $title
	 * @param string $path
	 * @param boolean $relative
	 * @return string
	 */

	private function get_index_contents($title, $path, $relative = true)
	{
		if (!is_dir($path))
		{
			return '';
		}
		$contents = array();
		$dh = opendir($path);
		while (($filename = readdir($dh)) !== false)
		{
			if (substr($filename,0,1) == '.') continue;

			$fullname = $path . '/' . $filename;

			if (!file_exists($fullname) || is_dir($fullname))
			{
				continue;
			}

            $contents[] = $fullname;
        }
		closedir($dh);
		sort($contents);

		$html = $title;

		if (empty($contents))
		{
			$html .= 'There is no content for this section.';
			return $html;
		}

		$dir = '';
		if ($relative) $dir = basename($path) . '/';
		foreach($contents as $filename)
		{
			$corename = basename($filename);
			$ext = pathinfo($corename, PATHINFO_EXTENSION);
			$basename = substr($corename, 0, -strlen($ext)-1);
			$html .= "<a href=\"$dir$corename\">$basename</a><br>";
		}
		return $html;
	}

	/**
	 * Create the support archvie index.htm
	 *
	 * @param string $path
	 */
	private function create_index($path)
	{
		$contents = $this->get_index_contents('<h1>Support Info</h1><br>', $path, false);

		$contents .= $this->get_index_contents('<h2>System Info</h2>', $path . '/sysinfo');
		$contents .= $this->get_index_contents('<h2>Logs</h2>', $path . '/logs');
		$contents .= $this->get_index_contents('<h2>Schema</h2>', $path . '/schema');
		file_put_contents($path . '/index.htm', $contents);

	}

	/**
	 * Get list of tables based on InnoDB
	 *
	 * @param string $path
	 */

	private function create_storage_engine($path)
	{
		$html = '<h1>Table Storage Engines<h1>';
		$html .= '<table>';
		$html .= '<tr><td valign=top>';

		$html .= '<h2>InnoDB</h2>';
		foreach($this->innodb as $tablename)
		{
			$html .= "$tablename<br>";
		}


		$html .= '<td valign=top>';

		$html .= '<h2>Non-InnoDB</h2>';
		foreach($this->noninnodb as $tablename)
		{
			$html .= "$tablename<br>";
		}

		$html .= '</table>';

		file_put_contents($path . '/tablestorage.htm', $html);
	}

}




?>
