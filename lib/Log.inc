<?php
/**
 * $Id$
 *
 * File logging class.
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
 *
 */

require_once(KT_LIB_DIR . '/database/datetime.inc');
// define error levels
define("DEBUG", 0);
define("INFO", 1);
define("ERROR", 2);

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

    static function running_user() {
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
