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
class Background
{
    var $connection = FALSE;
    var $address = '';
    var $subject = 'Task completed';
    var $message = '';
    
    function Background() {
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
