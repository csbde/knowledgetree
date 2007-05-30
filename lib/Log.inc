<?php
require_once(KT_LIB_DIR . '/database/datetime.inc');
// define error levels
define("DEBUG", 0);
define("INFO", 1);
define("ERROR", 2);
/**
 * $Id$
 *
 * File logging class.
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
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */ 
class KTLegacyLog {
    /**
     * The minimum logging level- log everything with
     * this error level and higher
     */
    var $minimumLoggingLevel = INFO;

    /**
     * The directory to save logs to
     */
    var $logDirectory;

    /**
     * The file to log the output to
     */
    var $logFile;

    /**
     * Default constructor
     *
     * @param string the path to the logfile to write to
     * @param int the desired level to log at
     * @param string log file name prefix     
     */
    function KTLegacyLog($logDirectory, $logLevel = INFO, $sLogFilePrefix = "log") {
        $this->logDirectory = $logDirectory;
        $this->minimumLoggingLevel = $logLevel;
        $this->sLogFilePrefix = $sLogFilePrefix;
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

    /**
     * Creates the log file from the current datetime
     */
    function initialiseLogFile() {
        global $default;
        $user = $this->running_user();
        if (!file_exists($this->logDirectory)) {
            $res = @mkdir($this->logDirectory, 0755);
            if ($res === false) {
                return new PEAR_Error('Failed to create log directory (check permissions)');
            }
        }
            
        if ($user) {
            $this->logFile = sprintf("%s/%s-%s.%s.txt", $this->logDirectory, $this->sLogFilePrefix, date("Y-m-d"), $user);
        } else {
            $this->logFile = sprintf("%s/%s-%s.txt", $this->logDirectory, $this->sLogFilePrefix, date("Y-m-d"));
        }
        if (!file_exists($this->logFile)) {
            $res = @touch($this->logFile);
            if ($res === false) {
                return new PEAR_Error('Failed to create log file ' . $this->logFile . ' (check permissions)');
            }
        }
        if (!is_writable($this->logFile)) {
            return new PEAR_Error('Cannot write to log file ' . $this->logFile . ' (check permissions)');
        }
    }

    /**
     * Log a debug entry
     *
     * @param string the message to write to the log file
     */
    function debug($logEntry) {
        $this->writeLog($this->prefixEntry($logEntry, "DEBUG"), DEBUG);
    }

    /**
     * Log an info entry
     *
     * @param string the message to write to the log file
     */
    function info($logEntry) {
        $this->writeLog($this->prefixEntry($logEntry, "INFO"), INFO);
    }

    /**
     * Log an error entry
     *
     * @param string the message to write to the log file
     */
    function error($logEntry) {
        $this->writeLog($this->prefixEntry($logEntry, "ERROR"), ERROR);
    }

    /**
     * Writes to the log file, checking that the log level is within
     * the minimum logging level
     *
     * @param string the message to write to the log file
     * @param int the error level to log at
     */
    function writeLog($logEntry, $logLevel) {
        if ($logLevel >= $this->minimumLoggingLevel) {
            if (@$fp = fopen($this->logFile, "a")) {
                fwrite($fp, $logEntry);
            }
            @fclose($fp);
        }
    }

    /**
     * Prefixes the log entry with the current date time, the logging level
     * and the page that called it
     *
     * @param string the message to write to the log file
     * @param int the error level to log at     
     */
    function prefixEntry($logEntry, $logLevel) {
        return getCurrentDateTime() . " (" . getenv("REMOTE_ADDR") . ") $logLevel: $logEntry\n";
    }
}
?>
