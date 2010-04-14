<?php 

require_once('Event.class.php');

/**
 * Extended Event object including functionalities needed by the Queue manager
 * @author Mark
 *
 */
class SQSEvent extends Event{
	
	/**
	 * Get the class to be executed
	 * @return String
	 */
	public function get_class(){
		$msg=explode('\.',$this->message);
		return $msg[0];	
	}
	
	/**
	 * Get the method to be executed
	 * @return String
	 */
	public function get_method(){
		$msg=explode('\.',$this->message);
		return $msg[1];	
	}
	
}

?>