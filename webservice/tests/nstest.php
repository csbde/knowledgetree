<?php

class jsonWrapper{
	public function __construct($content=NULL){
		$content=@json_decode($content);
	}
	
}

?>