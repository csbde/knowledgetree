<?php
/**
 * $Id: 
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

/**
* Class to keep a script running in the background.
*
* Example:
* $bg = new background();
* $bg->checkConnection();
* $bg->keepConnectionAlive();
* $bg->setCallbackParams('admin@knowledgetree.com', 'BG task completed', 'The requested background task has completed');
* $bg->setCallbackFunction($bg, 'callBackFunc');
*
*/
class background
{
    var $connection = FALSE;
    var $address = '';
    var $subject = 'Task completed';
    var $message = '';
    
    function background() {
    }
    
    /**
    * Check the status of the users connection 
    */
    function checkConnection() {
        $status = connection_status();
        
        if($status != 0){
            $this->connection = FALSE;
            return FALSE;
        }else{
            $this->connection = TRUE;
            return TRUE;
        }
    }
    
    /**
    * Keep the script alive
    */
    function keepConnectionAlive() {
        // prevent the script timing out
        set_time_limit(0);
        // prevent the user from aborting the script
        ignore_user_abort(TRUE);
        while($this->connection == FALSE) {
            print "\n";
            flush(); 
            sleep(1);
        }
    }

    /**
    * Set the parameters of the callback function
    */
    function setCallbackParams($address, $subject, $message)
	{
		//set the properties
		$this->address = $address;
		$this->subject = $subject;
		$this->message = $message;
		
		//register the callback method
		//register_shutdown_function(array(&$this, "callBackFunc"));
	}
    
    /**
    * Set the callback function to be run on completion of the script.
    */
    function setCallbackFunction(&$class, $function){
        register_shutdown_function(array(&$class, "$function"));
    }
    
    /**
    * Call back function to be called on completion of the background task
    */
    function callBackFunc() {
		@mail($this->address, $this->subject ,$this->message);

	}
}
?>