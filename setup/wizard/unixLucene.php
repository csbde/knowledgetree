<?php
/**
* Lucene Service Controller. 
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
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
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/

define('LUCENE_SOURCE_DIR', SYSTEM_DIR."bin".DS."luceneserver".DS);
define('LUCENE_SOURCE_FILE', SYSTEM_DIR."bin".DS."luceneserver".DS."ktlucene.jar");
require_once("service.php");

class unixLucene extends Service {
	private $name;
//	private $javaBin;
//	private $javaSystem;
//	protected $lucene_pid_file="";
//	protected $lucene_dir="";
//	protected $lucene_message="";
	protected $lucene_options = " -Xms512M -Xmx512M -jar ";
	private $util = null;
	
	public function __construct() {
	}
	
	function load() {
		$this->name = "KTLuceneTest";
		$this->util = new InstallUtil();
//		$this->javaSystem = new Java('java.lang.System');
//		$this->setJavaBin($this->javaSystem->getProperty('java.home').DS."bin");
	}
	
	private function setJavaBin($javaBin) {
		$this->javaBin = $javaBin;
	}
	
	public function getJavaBin() {
		return $this->javaBin;
	}
	
	// Load default settings
//	public function load() {
//		$this->util = new InstallUtil();
// 		$this->lucene_dir = SYSTEM_DIR."bin".DS."luceneserver".DS;
// 		$this->lucene_pid_file = SYSTEM_DIR."bin".DS."luceneserver".DS."lucene.pid";
// 		$this->lucene_pid = '';
//	}
	
	// Stop lucene
  	public function stop() {
    	// TODO:Still need to figure out xmlrpc shutdown()
    	$cmd = "pkill -f lucene<br/>";
    	$response = $util->pexec($cmd);
		echo 'Stop lucene';
    }

    public function install() {
    	
    }
    
    // Start lucene
    public function start() {
    	// TODO:A shot in the dark here
    	$this->util = new InstallUtil();
		$cmd = "nohup java".$this->lucene_options.LUCENE_SOURCE_FILE." &> ".SYS_LOG_DIR."lucene.log";
    	$response = $this->util->pexec($cmd);
    	$this->status = $this->on;
    	echo 'Start lucene';
    }
    
    
    	public function _start_lucene() {
			if($this->is_lucene_running()) { // Is service running
				echo 'Already Running<br/>';
			} else {
				// Get to  
				//echo 'Install service';
				$this->is_lucene_running();
				//nohup $LUCENE  &> $INSTALL_PATH/var/log/dmsctl.log &
				
			}
			
/*
 is_lucene_running
    RUNNING=$?

    if [ $RUNNING -eq 1 ]; then
        echo "$0 $ARG: lucene (pid $LUCENE_PID) already running"
    else
        cd $INSTALL_PATH/knowledgeTree/bin/luceneserver
        nohup $LUCENE  &> $INSTALL_PATH/var/log/dmsctl.log &
        if [ $? -eq 0 ]; then
            echo "$0 $ARG: lucene started"
            ps ax | grep ktlucene.jar | awk {'print $1'} > $LUCENE_PIDFILE
            sleep 2
        else
            echo "$0 $ARG: lucene could not be started"
            ERROR=3
        fi
        cd $INSTALL_PATH
fi
*/
		}
		
		public function is_lucene_running() {
			
			$pid = $this->get_lucene_pid();
			if($this->is_service_running($pid)) {
				echo 'Service is running';
			} else {
				
			}

/*			is_lucene_running() {
    get_lucene_pid
    is_service_running $LUCENE_PID
    RUNNING=$?
    if [ $RUNNING -eq 0 ]; then
        LUCENE_STATUS="lucene not running"
    else
        LUCENE_STATUS="lucene already running"
    fi
    return $RUNNING*/
		}
		
		public function is_service_running($pid) {
			$cmd = "kill -0 $pid 2>/dev/null";
			$response = $this->util->pexec($cmd);
			
			/*
is_service_running() {
    PID=$1
    if [ "x$PID" != "x" ] && kill -0 $PID 2>/dev/null ; then
        RUNNING=1
    else
        RUNNING=0
    fi
    return $RUNNING
}
*/
		}
		
		public function get_lucene_pid() {
			// TODO: PID FILE
/*get_lucene_pid() {
    get_pid $LUCENE_PIDFILE
    if [ ! $PID ]; then
        return 
    fi
    if [ $PID -gt 0 ]; then
        LUCENE_PID=$PID
    fi
}*/
		}
		
		public function get_pid() {
			
/*get_pid() {
    PID=""
    PIDFILE=$1
    # check for pidfile
    if [ -f $PIDFILE ] ; then
        exec 6<&0
        exec < $PIDFILE
        read pid
        PID=$pid
        exec 0<&6 6<&-
    fi
}*/
		}
		
 function shutdown()
	{
		
		$function=new xmlrpcmsg('control.shutdown',array(
				php_xmlrpc_encode((string) $this->ktid),
				php_xmlrpc_encode((string) $this->authToken)));

		$result=&$this->client->send($function);
		if($result->faultCode())
		{
			$this->error($result, 'shutdown');
			return false;
		}
		return true;
	}
}

//$luc = new Lucene();
//$luc->load();
//$luc->start();
//$luc->stop();
?>