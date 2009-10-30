<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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

// {{{ Format of the descriptor
/**
 * Format of the descriptor
 *
 * type*version*phase*simple description for uniqueness
 *
 * type is: sql, function, subupgrade, upgrade
 * version is: 1.2.4, 2.0.0rc5
 * phase is: 0, 1, 0pre.  Phase is _only_ evaluated by describeUpgrades.
 * description is: anything, unique in terms of version and type.
 */
// }}}

//require_once(KT_LIB_DIR . '/upgrades/UpgradeFunctions.inc.php');
require_once('sqlfile.inc.php');
require_once('datetime.inc.php');

// {{{ Upgrade_Already_Applied
class Upgrade_Already_Applied { //extends PEAR_Error {
    function Upgrade_Already_Applied($oUpgradeItem) {
        $this->oUpgradeItem = $oUpgradeItem;
    }
}
// }}}

class UpgradeItem extends InstallUtil {
    var $type = "";
    var $name;
    var $version;
    var $description;
    var $phase;
    var $priority = 0;
    var $parent;
    var $date;
    var $result;
	 
    function UpgradeItem($name, $version, $description = null, $phase = 0, $priority = 0) {
        $this->name = $name;
        $this->version = $version;
        if (is_null($description)) {
            $description = $this->type . " upgrade to version " .  $version . " phase " . $phase;
        }
        $this->description = $description;
        $this->phase = $phase;
        $this->priority = $priority;
        parent::__construct();
//        print_r($this);
//        die;
    }

    function setParent($parent) {
        $this->parent = $parent;
    }
    function setDate($date) {
        $this->date = $date;
    }

    function getDescriptor() {
        return join("*", array($this->type, $this->version, $this->phase, $this->name));
    }

    function getDescription() {
        return $this->description;
    }

    function getVersion() {
        return $this->version;
    }

    function getPhase() {
        return $this->phase;
    }

    function getPriority() {
        return $this->priority;
    }

    function getType() {
        return $this->type;
    }

    function runDBQuery($query, $checkResult = false, $typeCheck = false) {
		require_once("../wizard/steps/configuration.php"); // configuration to read the ini path
		$wizConfigHandler = new configuration();
		$configPath = $wizConfigHandler->readConfigPathIni();
		if(!is_object($this->iniUtilities)) {
			parent::__construct();
		}
		$this->iniUtilities->load($configPath);
		$dconf = $this->iniUtilities->getSection('db');
		$this->dbUtilities->load($dconf['dbHost'], '', $dconf['dbUser'], $dconf['dbPass'], $dconf['dbName']);
        $result = $this->dbUtilities->query($query);
//        echo "$query<br/>";
//        echo '<pre>';
//        print_r($result);
//        echo '</pre>';
		if($checkResult) {
        	$assArr = $this->dbUtilities->fetchAssoc($result);
//	        echo '<pre>';
//	        print_r($assArr);
//	        echo '</pre>';
//	        if(is_null($assArr)) {
//	        	echo '=== null ===<br/>';
//	        	return false;
//	        } else {
//	        	echo '=== not null ===<br/>';
//	        }
	        if($typeCheck) {
	        	return !is_null($assArr);
	        } else {
	        	return is_null($assArr);
	        }
		}
//        echo '<pre>';
//        print_r($assArr);
//        echo '</pre>';
        return !is_null($result);
    }
    
    function _upgradeTableInstalled() {
        $query = "SELECT COUNT(id) FROM upgrades";
        $res = $this->runDBQuery($query, true, true);
        if($res) {
        	return true;
        }
        return false;
    }

    function isAlreadyApplied() {
        if (!$this->_upgradeTableInstalled()) {
            return false;
        }
        $query = "SELECT id FROM upgrades WHERE descriptor = '".$this->getDescriptor()."' AND result = 1";
        $res = $this->runDBQuery($query, true, false);
        
        if(!$res) {
        	return true;
        }
        return false;
    }

    function performUpgrade($force = false) {
        $res = $this->isAlreadyApplied();
        if ($res === true) {
            if ($force !== true) {
                // PHP5: Exception
                return new Upgrade_Already_Applied($this);
            }
        }
//        if (!$res) {
//			$this->error[] = 'An Error Has Occured';
//        }
//        $oCache =& KTCache::getSingleton();
//        $save = $oCache->bEnabled;
//        $oCache->bEnabled = false;
        $res = $this->_performUpgrade();
//        $oCache->bEnabled = $save;
        if (!$res) {
            $this->_recordUpgrade(false);
            $this->error[] = $this->dbUtilities->getErrors();
            return false;
        }
        $res = $this->_recordUpgrade(true);
        if (!$res) {
        	$this->error[] = 'An Error Has Occured';
			return false;
        }
        return true;
    }

    function _performUpgrade() {
    	$this->error[] = 'Unimplemented';
    	return false;
    }

    function _recordUpgrade($result) {
        if (is_null($this->date)) {
            $this->date = getCurrentDateTime();
        }
        if ($this->parent) {
            $parentid = $this->parent->getDescriptor();
        } else {
            $parentid = null;
        }
		$sql = "INSERT INTO upgrades (`id`, `descriptor`, `description`, `date_performed`, `result`, `parent`) VALUES (NULL, '". $this->getDescriptor()."', '".$this->description."', '".$this->date."', '".$result."', '".$parentid."')";
		$this->dbUtilities->query($sql);
		
		return true;
    }

    // STATIC
    function getAllUpgrades() {
        return array();
    }
    

}

class SQLUpgradeItem extends UpgradeItem {
    function SQLUpgradeItem($path, $version = null, $description = null, $phase = null, $priority = null) {
        $this->type = "sql";
        $this->priority = 0;
        $details = $this->_getDetailsFromFileName($path);
        if (is_null($version)) {
            $version = $details[1];
        }
        if (is_null($description)) {
            $description = $details[2];
        }
        if (is_null($phase)) {
            $phase = $details[3];
        }
        if (is_null($priority)) {
            $priority = isset($details[4]) ? $details[4] : 0;
        }
        $this->UpgradeItem($path, $version, $description, $phase, $priority);
    }

    /**
     * Describe the SQL scripts that will be used to upgrade KnowledgeTree
     *
     * Return an array of arrays with two components: a string identifier
     * that uniquely describes the step to be taken and a string which is an
     * HTML-formatted description of the step to be taken.  These will be
     * returned in any order - describeUpgrade performs the ordering.
     *
     * @param string Original version (e.g., "1.2.4")
     * @param string Current version (e.g., "2.0.2")
     *
     * @return array Array of SQLUpgradeItem describing steps to be taken
     *
     * STATIC
     */
    public static function getUpgrades($origVersion, $currVersion) {
//        global $default;
		
//        $sqlupgradedir = KT_DIR . '/sql/' . $default->dbType . '/upgrade/';
			$dbType = 'mysql';
			$sqlupgradedir = KT_DIR . 'sql/' . $dbType . '/upgrade/';
        $ret = array();

        if (!is_dir($sqlupgradedir)) {
//            return PEAR::raiseError("SQL Upgrade directory ($sqlupgradedir) not accessible");
        }
        if (!($dh = opendir($sqlupgradedir))) {
//            return PEAR::raiseError("SQL Upgrade directory ($sqlupgradedir) not accessible");
        }

        while (($file = readdir($dh)) !== false) {
            // Each entry can be a file or a directory
            //
            // A file is legacy before the upgrade system was created, but
            // will be supported anyway.
            //
            // A directory is the end-result version: so, 2.0.5 contains
            // every script that differentiates it from a previous version,
            // say, 2.0.5rc1 or 2.0.4.
            //
            if (in_array($file, array('.', '..', 'CVS'))) {
                continue;
            }
            $fullpath = $sqlupgradedir . $file;
            if (is_file($fullpath)) {
                // Legacy file support, will be in form of
                // 1.2.4-to-2.0.0.sql.
                $details = SQLUpgradeItem::_getDetailsFromFileName($file);
                if ($details) {
                    if (!gte_version($details[0], $origVersion)) {
                        continue;
                    }
                    if (!lte_version($details[1], $currVersion)) {
                        continue;
                    }
                    //print "Will run $file\n";
//                    print_r($this->util->dbUtilities);
//                    die;
                    $ret[] = new SQLUpgradeItem($file);
                }
            }
            if (is_dir($fullpath)) {
                $subdir = $file;
                if (!($subdh = opendir($fullpath))) {
                    continue;
                }
                while (($file = readdir($subdh)) !== false) {
                    $relpath = $subdir . '/' . $file;
                    $details = SQLUpgradeItem::_getDetailsFromFileName($relpath);
                    if ($details) {
                        if (!gte_version($details[0], $origVersion)) {
                            continue;
                        }
                        if (!lte_version($details[1], $currVersion)) {
                            continue;
                        }
                        //print "Will run $file\n";
//                        print_r(SQLUpgradeItem::);
//                        die;
//						new InstallUtil();
                        $ret[] = new SQLUpgradeItem($relpath);
                    }
                }
            }
       }
       closedir($dh);
       return $ret;
    }

    public static function _getDetailsFromFileName($path) {
        // Old format (pre 2.0.6)
        $matched = preg_match('#^([\d.]*)-to-([\d.]*).sql$#', $path, $matches);
        if ($matched != 0) {
            $fromVersion = $matches[1];
            $toVersion = $matches[2];
            $description = "Database upgrade from version $fromVersion to $toVersion";
            $phase = 0;
            return array($fromVersion, $toVersion, $description, $phase);
        }
        $matched = preg_match('#^([\d.]*)/(?:(\d*)-)?(.*)\.sql$#', $path, $matches);
        //$matched = preg_match('#^([\d.]*)/(?:(\d*)-)?(.*):(?:(\d*))\.sql$#', $path, $matches);
        if ($matched != 0) {
            $fromVersion = $matches[1];
            $toVersion = $matches[1];
            $in = array('_');
            $out = array(' ');
            $phase = (int)$matches[2];

            //$priority = (int)$matches[4];
            $priority = 0;
            $iPriority = preg_match('#^(.*)-(\d*)$#', $matches[3], $priorities);
            if($iPriority != 0){
                $priority = $priorities[2];
                $matches[3] = $priorities[1];
            }

            $description = "Database upgrade to version $toVersion: " . ucfirst(str_replace($in, $out, $matches[3]));
            return array($fromVersion, $toVersion, $description, $phase, $priority);
        }
        // XXX: handle new format
        return null;
    }

    function _performUpgrade() {
		$dbType = 'mysql';
        $sqlupgradedir = KT_DIR . 'sql/' . $dbType . '/upgrade/';
        $queries = SQLFile::sqlFromFile($sqlupgradedir . $this->name);
        return $this->dbUtilities->runQueries($queries);
    }
    

}

class KTRebuildPermissionObserver {
    function start() {
        $this->lastBeat = time();
    }
    function receiveMessage() {
        $now = time();
        if ($this->lastBeat + 15 < $now) {
            print "<!--          -->";
            ob_flush();
            flush();
        }
    }
    function end() {
    }
}

class RecordUpgradeItem extends UpgradeItem {
    function RecordUpgradeItem ($version, $oldversion = null) {
        $this->type = "upgrade";
        if (is_null($oldversion)) {
            $this->description = "Upgrade to version $version";
        } else {
            $this->description = "Upgrade from version $oldversion to $version";
        }
        $this->phase = 99;
        $this->version = $version;
        $this->name = 'upgrade' . $version;
    }

    function _performUpgrade() {
//        $this->_deleteSmartyFiles();
//        $this->_deleteProxyFiles();
//        require_once(KT_LIB_DIR . '/cache/cache.inc.php');
//        $oCache =& KTCache::getSingleton();
//        $oCache->deleteAllCaches();
		// TODO : clear cache folder
//        require_once(KT_LIB_DIR .  '/permissions/permissionutil.inc.php');
		// TODO : What does this do
//        $po =& new KTRebuildPermissionObserver($this);
//        $po->start();
//        $oChannel =& KTPermissionChannel::getSingleton();
//        $oChannel->addObserver($po);

        set_time_limit(0);
        ignore_user_abort(true);

//        KTPermissionUtil::rebuildPermissionLookups(true);
//        $po->end();
		
        $versionFile=KT_DIR . '/docs/VERSION-NAME.txt';
        $fp = fopen($versionFile,'rt');
        $systemVersion = fread($fp, filesize($versionFile));
        fclose($fp);

        $query = "UPDATE system_settings SET value = '$systemVersion' WHERE name = 'knowledgetreeVersion'";
        $this->runDBQuery($query);
        $query = "UPDATE system_settings SET value = '{$this->version}' WHERE name = 'databaseVersion'";
        $result = $this->runDBQuery($query);
		return $result;
    }

    function _deleteSmartyFiles() {
        $oConfig =& KTConfig::getSingleton();
        $dir = sprintf('%s/%s', $oConfig->get('urls/varDirectory'), 'tmp');

        $dh = @opendir($dir);
        if (empty($dh)) {
            return;
        }
        $aFiles = array();
        while (false !== ($sFilename = readdir($dh))) {
            if (substr($sFilename, -10) == "smarty.inc") {
               $aFiles[] = sprintf('%s/%s', $dir, $sFilename);
            }
            if (substr($sFilename, -10) == "smarty.php") {
               $aFiles[] = sprintf('%s/%s', $dir, $sFilename);
            }
        }
        foreach ($aFiles as $sFile) {
            @unlink($sFile);
        }
    }


    function _deleteProxyFiles() {
        $oKTConfig =& KTConfig::getSingleton();


        // from ktentityutil::_proxyCreate
        $sDirectory = $oKTConfig->get('cache/proxyCacheDirectory');

        if (!file_exists($sDirectory)) {
            return;
        }
        $sRunningUser = KTUtil::running_user();
        if ($sRunningUser) {
            $sDirectory = sprintf("%s/%s", $sDirectory, $sRunningUser);
        }
        if (!file_exists($sDirectory)) {
            return ;
        }

        $dh = @opendir($sDirectory);
        if (empty($dh)) {
            return;
        }
        $aFiles = array();
        while (false !== ($sFilename = readdir($dh))) {

            if (substr($sFilename, -8) == ".inc.php") {
               $aFiles[] = sprintf('%s/%s', $sDirectory, $sFilename);
            }
        }

        foreach ($aFiles as $sFile) {
            @unlink($sFile);
        }
    }
}

?>
