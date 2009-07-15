<?php
class KT_atom_response extends KT_atom_baseDoc {

	protected $baseURI=NULL;
	protected $feed=NULL;

	public function __construct($baseURI=NULL){
		parent::__construct();
		$this->baseURI = $baseURI;
		$this->feed =&$this->DOM;
	}

	public function &newEntry(){
		$entry=$this->newElement('entry');
		$this->feed->appendChild($entry);
		return $entry;
	}

	public function &newField($name=NULL,$value=NULL,&$attachToNode=NULL){
		$field=$this->newElement($name,$value);
		if(isset($attachToNode))$attachToNode->appendChild($field);
		return $field;
	}

	public function render(){
		return $this->formatXmlString(trim($this->DOM->saveXML()));
	}


}

class KT_atom_Response_GET extends KT_atom_response{}
class KT_atom_Response_PUT extends KT_atom_response{}
class KT_atom_Response_POST extends KT_atom_response{}
class KT_atom_Response_DELETE extends KT_atom_response{}

?>