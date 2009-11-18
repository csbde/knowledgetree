<?php
/**
 * Framework for an Atom Publication Protocol Service
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
 * Contributor( s):
 * 				Mark Holtzhausen <mark@knowledgetree.com>
 *
 */

/**
 * Includes
 */

class KT_atom_baseDoc{
	const XML_ENCODING='utf-8';
	const XML_VERSION='1.0';

	public $DOM=NULL;

	public function __construct(){
		$this->DOM=new DOMDocument(self::XML_VERSION ,self::XML_ENCODING);
	}

	public function &newAttr($name=NULL){
		$node=$this->DOM->createAttribute($name);
		if(func_num_args()>1){
			$val=func_get_arg(1);
			$node->value=$val;
		}
		return $node;
	}

	public function &newElement($name=NULL){
		if(func_num_args()>1){
			$val=func_get_arg(1);
			$node=$this->DOM->createElement($name,$val);
		}else{
			$node=$this->DOM->createElement($name);
		}
		return $node;
	}

	public function &newCDATA($data=NULL){
		$this->DOM->createCDATASection($data);
	}

	public function &newB64Stream($tagName=NULL,$tagAttrs=array(),$streamString=NULL){
		$e=$this->newElement($tagName,chunk_split(base64_encode($streamString),75));
		foreach($tagAttrs as $attr=>$val){
			$attr=$this->newAttr($attr,$val);
			$e->appendChild($attr);
		}
		return $e;
	}

	public function formatXmlString($xml) {
	  // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
	  $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

	  // now indent the tags
	  $token      = strtok($xml, "\n");
	  $result     = ''; // holds formatted version as it is built
	  $pad        = 0; // initial indent
	  $matches    = array(); // returns from preg_matches()

	  // scan each line and adjust indent based on opening/closing tags
	  while ($token !== false) :

	    // test for the various tag states

	    // 1. open and closing tags on same line - no change
	    if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
	      $indent=0;
	    // 2. closing tag - outdent now
	    elseif (preg_match('/^<\/\w/', $token, $matches)) :
	      $pad--;
	    // 3. opening tag - don't pad this one, only subsequent tags
	    elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
	      $indent=1;
	    // 4. no indentation needed
	    else :
	      $indent = 0;
	    endif;

	    // pad the line with the required number of leading spaces
	    $line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
	    $result .= $line . "\n"; // add to the cumulative result, with linefeed
	    $token   = strtok("\n"); // get the next token
	    $pad    += $indent; // update the pad size for subsequent lines
	  endwhile;

	  return $result;
	}
}

?>