<?php

class ZipCR_node{
	public $title='';
	public $id=NULL;
	public $folder=true;
	public $properties=array();
	public $children=array();
	public $revisions=array();
	public $content=null;
}


class ZipCR{
	protected $zipFileName='';
	protected $za=NULL;
	protected $struct=NULL;
	
	public function __construct($zipFileName=NULL){
		$this->zipFileName=$zipFileName;
		$this->za=new ZipArchive();
		if(!$this->isValidArchive())throw new ErrorException('Not a valid archive');
		$this->struct=$this->getRootNode();
	}
	
	public function isValidArchive(){
		$flag=true;
		if(!file_exists($this->zipFileName)){
			$flag=$this->za->open($this->zipFileName,ZipArchive::CREATE)===true;
			$this->za->close();
		}
		if(!file_exists($this->zipFileName)){
			$flag=false;
		}else{
			$flag=$this->za->open($this->zipFileName)===true;
		}
		return $flag;
	}
	
	
	public function createPath($path){
		$path=trim($path,' /');
		$path=split('/',$path);
		$node=&$this->struct;
		foreach ($path as $folder){
			$node=&$this->createFolder($folder,$node);
		}
		$this->save();
	}
	
	
	public function &createFolder($folder,&$node){
		if(!isset($node->children[$folder])){
			$node->children[$folder]=new ZipCR_node();
			$node->children[$folder]->title=$folder;
		}
		return $node->children[$folder];
	}
	
	
	public function save(){
		$this->setMetaNode($this->struct);
	}
	
	
	public function getRootNode(){
		$rNode=$this->getMetaNode('_ROOT');
		if($rNode==false){
			$rNode=new ZipCR_node();
			$rNode->id='_ROOT';
			$rNode->title='/';
			$rNode->folder=true;
		}
		$this->setMetaNode($rNode);
		return $rNode;
	}
	
	
	public function setMetaNode($node){
		if(get_class($node)==='ZipCR_node'){
			$name='m_'.$node->id;
			$content=serialize($node);
			$this->setNode($name,$content);
		}
	}
	
	public function getMetaNode($id){
		$name='m_'.$id;
		$ret=@unserialize($this->getNode($name));
		return $ret;		
	}
	
	public function delMetaNode($id){
		$name='m_'.$id;
		$this->delNode($name);
	}
	
	
	
	public function getNewId(){
		do{
			$id=sha1(time().rand());
			$name_m='m_'.$id;
			$name_o='o_'.$id;
		}while($this->za->locateName($name_m)!==false && $this->za->locateName($name_o)!==false);
		return $id;
	}
	
	
	
	
	public function setNode($name,$content){
		if($this->getNode($name)===false){
			$this->delNode($name);
		}
		$this->za->addFromString($name,$content);
	}
	
	public function getNode($name){
		$ret=$this->za->getFromName($name);
		return $ret;
	}
	
	public function delNode($name){
		$ret=$this->za->deleteName($name);
		return $ret;
	}
	
}

$CR=new ZipCR('test.zip');
//echo '<pre>'.print_r($CR,true).'</pre>';

$folders=array();

function randomString($len=5){
	$rstr='abcdefghijklmnopqrstuvwxyz1234567890';
	$str='';
	$cnt=strlen($rstr);
	for($i=0; $i<$len; $i++){
		$str.=$rstr[rand(0,$cnt-1)];
	}
	return $str;
}

for($i=0; $i<1000; $i++){
	$folder=array();
	for($j=1; $j<5; $j++){
		$folder[]=randomString(5);
	}
	$folders[]=join('/',$folder);
}

foreach($folders as $folder){
	$CR->createPath($folder);
}

//echo '<pre>'.print_r($folders,true).'</pre><hr />';

/*
$za = new ZipArchive();

$za->open('test.zip');
print_r($za);
var_dump($za);
echo "numFiles: " . $za->numFiles . "\n";
echo "status: " . $za->status  . "\n";
echo "statusSys: " . $za->statusSys . "\n";
echo "filename: " . $za->filename . "\n";
echo "comment: " . $za->comment . "\n";

for ($i=0; $i<$za->numFiles;$i++) {
    echo "index: $i\n";
    print_r($za->statIndex($i));
}
echo "numFile:" . $za->numFiles . "\n";
*/
?> 