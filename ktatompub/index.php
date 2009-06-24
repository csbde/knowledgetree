<?php
/**
 * Framework for an Atom Publication Protocol Service
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 * Contributor( s): 
 * 				Mark Holtzhausen <mark@knowledgetree.com>
 *
 */

ob_start();

/**
 * Constants
 */

/**
 * To sidestep url rewrites but still retain the atomPub URL convention,
 * the entry point is: index.php?/
 * eg. 1. Accessing the servicedocument: http://example.com/ktatompub/index.php?/servicedocument
 *     2. Accessing the folder service: http://example.com/ktatompub/index.php?/folder/1
 *
 * If URL rewrites are used, they should point any reference below
 * this folder to index.php?/
 * 
 * Because index.php is accessed as the default document, the url can be shortened to http://example.com/ktatompub/?/
 */

define('KT_APP_BASE_URI',"http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?/');
define('KT_APP_SYSTEM_URI',"http://".$_SERVER['HTTP_HOST']);

// Define whether to use in debug mode for viewing generated structures
define('KT_APP_WEB_OUTPUT',false); 



/**
 * Includes
 */
include_once('../ktapi/ktapi.inc.php');
include_once('lib/KTAPPHelper.inc.php');						//Containing helper bridge functions to KtAPI
include_once('lib/KTAPDoc.inc.php');							//Containing the parent class allowing easy XML manipulation
include_once('lib/KTAPPServiceDoc.inc.php');					//Containing the servicedoc class allowing easy ServiceDocument generation
include_once('lib/KTAPPFeed.inc.php');							//Containing the response feed class allowing easy atom feed generation
include_once('auth.php');										//Containing the authentication protocols



//Parse the query string
$query=split('/',trim($_SERVER['QUERY_STRING'],'/'));

//Initializing the $output variable. Everything rendered by the engine must be placed in this variable as it is the only thing that will be rendered
$output='';


// Using the querystring to load the appropriate service

switch(strtolower(trim($query[0]))){
	case 'mimetypes':
		include('services/mimetypes.inc.php');
		break;
	case 'fulltree':
		include('services/fulltree.inc.php');
		break;
	case 'folder':
		include('services/folder.inc.php');
		break;
	case 'document':
		include('services/document.inc.php');
		break;
	case 'cmis':
        include('services/cmis/index.php');
        break;
	case 'servicedocument':
	default:
		include('services/servicedocument.inc.php');
		break;
}




/**
 * Writing the Output
 * 
 * To ensure we don't render illegal XML, we clean the output buffer and only use what is in the $ouput variable
 */
ob_end_clean();
if(KT_APP_WEB_OUTPUT){
	echo '<pre>'.htmlentities($output).'</pre>';
}else{
	header('Content-type: text/xml');
	echo $output;
}



?>