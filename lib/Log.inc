<?php

require_once("$default->owl_fs_root/lib/database/datetime.inc");

// define error levels
define("DEBUG", 0);
define("INFO", 1);
define("ERROR", 2);

/**
 * $Id$
 * 
 * File logging class
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */
class Log {
    /**
     * The minimum logging level- log everything with
     * this error level and higher
     */
    var $minimumLoggingLevel = INFO;
    
    /**
     * The file to log the output to
     */
    var $logFile;
    
    /**
     * Default constructor
     *
     * @param string the path to the logfile to write to
     * @param int the desired level to log at
     */
    function Log($logFile, $logLevel = INFO) {
        $this->logFile = $logFile;
        $this->minimumLoggingLevel = $logLevel;
        // initialise log file
        $this->writeLog("\n-------------------\n" . date("Y-m-d H:i:s",time()) . ":logging started\n-------------------\n", $this->minimumLoggingLevel);
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
        $this->writeLog($this->prefixEntry($logEntry, " INFO"), INFO);
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
            if ($fp = fopen($this->logFile, "a")) {
                fwrite($fp, $logEntry);
            }
            fclose($fp);
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
       return getCurrentDateTime() . ":$logLevel: $logEntry\n";
    }      
}
?>
