<?php
/**
* Implements a cleaner wrapper for webservices  API for KnowledgeTree.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Webservice
* @version Version 0.1
*/


error_reporting(E_ALL);
ob_start("ob_gzhandler");

require_once ("config.php");

if(!extension_loaded("soap"))
	die("Soap extension not loaded!");

session_start();

/** autoload function for PHP5 
* Loads all the classes in the model
*
*/
function __autoload($classname) {
    try{
        if(file_exists("classes/soap/model/$classname.class.php"))
		include("classes/soap/model/$classname.class.php");
	elseif(file_exists("classes/soap/$classname.class.php"))
		include("classes/soap/$classname.class.php");
	elseif(file_exists("classes/soap/$classname.class.php"))
		include("classes/soap/$classname.class.php");
    } catch (Exception $e) {
        echo $e->getMessage();
    }

}

/** Write out  debug file */
function debug($txt,$file="debug.txt"){
	$fp = fopen($file, "a");
	fwrite($fp, str_replace("\n","\r\n","\r\n".$txt));
	fclose($fp);
}

/** Write out object in the debug log */
function debugObject($txt,$obj){
	ob_start();
	print_r($obj);
	$data = ob_get_contents();
	ob_end_clean();
	debug($txt."\n".$data);
}
?>
