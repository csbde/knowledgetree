<?php
/**
 * $Id$
 *
 * Simple wrapper class for working with the pecl zip class
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
 */

/**
 * @author mark
 *
 * this is the base class for Array Transformations - not to be instantiated, only to be extended.
 *
 */
class ktRenderArray{
	protected $options=array(
		'nesting'		=>true
	);
	/**
	 * @var array data		Contains the data array passed
	 */
	protected $data=array();
	
	/**
	 * constructor
	 * 
	 * Construct a new ktRenderArray object
	 * 
	 * @param array $data		An array of data to be rendered 
	 * @param array $options	Rendering options
	 * @return mixed
	 */
	public function __construct($data=NULL,$options=NULL){
		// Merge provided options with default options
		$this->_mergeOptions($options);
		
		//Make sure data is an array
		$this->data=is_array($data)?$data:array();
		if(!$this->options['nesting'])$this->data=$this->flattenArray($this->data);
	}
	
	/**
	 * private _mergeOptions
	 * 
	 * Internal method for merging default options with those passed on constructing the object
	 * 
	 * @param $options
	 * @return unknown_type
	 */
	protected function _mergeOptions($options=NULL){
		//Make the options is an array
		$options=is_array($options)?$options:array();
		$this->options=is_array($this->options)?$this->options:array();
		
		//Merge with default options
		$this->options=array_merge($this->options,$options);
	}
	
	/**
	 * render - the static class method for rendering the array
	 * @param $data		the array data that needs to be rendered
	 * @param $options	the options for the rendering engine
	 * @return mixed	
	 */
	public static function render($data=NULL,$options=NULL){
		//This duplicate function is for scope resolution prior to php5.3	
		// for php 5.3+ this function does not need to be replicated for each rendering engine and the following line
		// should read: $class=static::who();
		$class=self::who();
		$i=new $class($data,$options);
		return $i->doRender();
	}
	
	/**
	 * doRender - the class method for rendering the array
	 * @return unknown_type
	 */
	public function doRender(){
		return $this->data;
	}
	
	/**
	 * who - a static function to get the class of the member.
	 * @return unknown_type
	 */
	protected static function who(){
		return __CLASS__;
	}
	
	/**
	 * flattenArray - Turns a multidimensional array into a one-dimensional array by merging sub-arrays into the primary array
	 * 
	 * @param $array		The array to be flattened
	 * @return array
	 */
	protected function flattenArray($array=NULL){
		if(!is_array($array))$array=array();
		$narray=array();
		foreach($array as $key=>$value){
			if(is_array($value)){
				$narray=array_merge($narray,$this->flattenArray($value));
			}else{
				$narray[$key]=$value;
			}
		}
		return $narray;	
	}
	
}

/**
 * ktRenderArrayJSON
 * 
 * 	Render an array as JSON
 * 
 * @author mark
 *
 */
class ktRenderArrayJSON extends ktRenderArray{
	public function doRender(){
		return json_encode($this->data);
	}
	
	//This duplicate function is for scope resolution prior to php5.3	
	public static function render($data=NULL,$options=NULL){
		$class=self::who();
		$i=new $class($data,$options);
		return $i->doRender();
	}
	
	protected static function who(){
		return __CLASS__;
	}
}

/**
 * ktRenderArrayHTML
 * 
 *	Renders the array into an xml/html nodelist
 *
 * @author mark
 *
 */
class ktRenderArrayHTML extends ktRenderArray{
	protected $options=array(
		'tagName'		=>'div',
		'nesting'		=>true,
		'attributes'	=>array(
			'class'			=>'[key]'
		),
		'value'			=>'[value]'
	);
	
	protected $root=NULL;
	
	//This duplicate function is for scope resolution prior to php5.3	
	public static function render($data=NULL,$options=NULL){
		$class=self::who();
		$i=new $class($data,$options);
		return $i->doRender();
	}
	
	protected static function who(){
		return __CLASS__;
	}
	
	public function doRender(){
		$this->root=new SimpleXMLElement('<root />');
		$this->render_nested();
		return $this->renderXML();
	}
	
	private function renderXML(){
		$result=$this->root->xpath("/root/{$this->options['tagName']}");
		$ret=array();
		while(list(,$node)=each($result)){
			$ret[]=$node->asXML();
		}
		return join('',$ret);
	}
	
	private function render_nested(&$root=NULL,$data=NULL){
		if(!$root)$root=&$this->root;
		if(!$data)$data=$this->data;
		foreach($data as $key=>$value){
			if(is_array($value)){
				$node=$root->addChild($this->options['tagName']);
				foreach($this->options['attributes'] as $attr => $content){
					$node->addAttribute($attr,$this->parseString($content,$key,$attr));
				}
				$this->render_nested($node,$value);
			}else{
				$node=$root->addChild($this->options['tagName'],$this->parseString($this->options['value'],$this->getTransformArray($key,$value,$data)));
				foreach($this->options['attributes'] as $attr => $content){
					$node->addAttribute($attr,$this->parseString($content,$key,$attr));
				}
			}
		}
	}
	
	private function getTransformArray($key,$value,$additional){
		if(!is_array($additional))$additional=array();
		$additional['key']=$key;
		$additional['value']=$value;
		return $additional;
	}
	
	private function parseString($string='',$xform=array()){
		if(!is_array($xform))$xform=array();
		
		$from=array_keys($xform);
		$to=array_values($xform);
		
		$delim=create_function('&$item,$key,$prefix','$item="[".$item."]";');
		array_walk($from,$delim);
		
		return str_replace($from,$to,$string);
	}
	
	private function render_flat(){
		
	}
}

/* TEST FUNCTIONS **************************************************** Remove Space Before Slash to Uncomment ** /
$a=array();
$a['item1']='item1';
$a['item2']='item2';
$a['item3']=array();
$a['item3']['item4']='item4';
$a['item3']['item5']='item5';
$a['item3']['item6']=array();
$a['item3']['item6']['item7']='item7';
$a['item3']['item6']['item8']='item8';
$a['item3']['item9']='item9';
$a['item10']='item10';


$class='ktRenderArrayJSON';echo "<h3>{$class}</h3><pre>".print_r(ktRenderArrayJSON::render($a),true)."</pre>";
$options=array(
	'tagName'=>'div',
	'nesting'=>true,
	'attributes'=>array(
		'rel'=>'something_[key]_something',
		'id'=>'doc_[key]',
		'class'=>'[key]'
	),
	'value'	=>'[key]=[value]'
);
$class='ktRenderArrayHTML';echo "<h3>{$class}</h3><pre>".print_r(htmlentities(ktRenderArrayHTML::render($a,$options)),true)."</pre>";
/* Expected Result:
 * ktRenderArrayJSON {"item1":"item1","item2":"item2","item3":{"item4":"item4","item5":"item5","item6":{"item7":"item7","item8":"item8"},"item9":"item9"},"item10":"item10"}
 * ktRenderArrayHTML <div rel="something_item1_something" id="doc_item1" class="item1">item1=item1</div><div rel="something_item2_something" id="doc_item2" class="item2">item2=item2</div><div rel="something_item3_something" id="doc_item3" class="item3"><div rel="something_item4_something" id="doc_item4" class="item4">item4=item4</div><div rel="something_item5_something" id="doc_item5" class="item5">item5=item5</div><div rel="something_item6_something" id="doc_item6" class="item6"><div rel="something_item7_something" id="doc_item7" class="item7">item7=item7</div><div rel="something_item8_something" id="doc_item8" class="item8">item8=item8</div></div><div rel="something_item9_something" id="doc_item9" class="item9">item9=item9</div></div><div rel="something_item10_something" id="doc_item10" class="item10">item10=item10</div>
 */
//die("Debug Info for ".__FILE__.". To remove test functionality, comment it out.");
/**********************************************************************/
?>