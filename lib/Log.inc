<?php
/**
 * $Id$
 * 
 * File logging class
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author <a href="mailto:michael@jamwarehouse.com>Michael Joseph</a>, Jam Warehouse (Pty) Ltd, South Africa
 * @package dmslib
 */
define("DEBUG", 0);
define("INFO", 1);
define("ERROR", 2);

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
    
    function Log($logFile) {
        $this->logFile = $logFile;
    }
    
    /**
     * Default constructor
     *
     * @param $logLevel   the desired level to log at
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
     * @param $logEntry  the message to write to the log file
     */
    function debug($logEntry) {
        // prefix entry
        $logEntry = date("Y-m-d H:i:s",time()) . ":DEBUG:" . $logEntry . "\n";
        $this->writeLog($logEntry, DEBUG);
    }
    
    /**
     * Log an info entry
     *
     * @param $logEntry  the message to write to the log file
     */
    function info($logEntry) {
        $logEntry = date("Y-m-d H:i:s",time()) . ":INFO:" . $logEntry . "\n";
        $this->writeLog($logEntry, INFO);
    }
    
    /**
     * Log an error entry
     *
     * @param $logEntry  the message to write to the log file
     */    
    function error($logEntry) {
        $logEntry = date("Y-m-d H:i:s",time()) . ":ERROR:" . $logEntry . "\n";
        $this->writeLog($logEntry, ERROR);
    }
    
    /**
     * Writes to the log file, checking that the log level is within
     * the minimum logging level
     *
     * @param $logEntry  the message to write to the log file
     * @param $logLevel  the error level to log at
     */    
    function writeLog($logEntry, $logLevel) {        
        if ($logLevel >= $this->minimumLoggingLevel) {
            if ($fp = fopen($this->logFile, "a")) {
                fwrite($fp, $logEntry);
            }
            fclose($fp);
        }
    }
}
?>
