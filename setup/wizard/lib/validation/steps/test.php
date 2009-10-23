<?php
class Test {
	private $ignore = array('action', 'step', 'type');
	
	function __construct() {

	}
	
//	function dispatch() {
		/*
		if(isset($_GET['action'])) {
			$class = $_GET['step']."Test";
			$func = $_GET['action'];
			if($func != '') {
				foreach ($_GET as $k=>$v) {
					if(in_array($k, $this->ignore)) {
						$temp[] = $_GET[$k];
						unset($_GET[$k]);
					}
				}
				$funcargs = array_slice($_GET,0);
				require_once("$class.php");
				$Test = new $class();
				$func_call = strtoupper(substr($func,0,1)).substr($func,1);
				$method = "do$func_call";
				call_user_func_array(array($Test, $method), $funcargs);
			}
		}
	*/
//	}
}

?>