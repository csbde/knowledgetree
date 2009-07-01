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
//define('KT_APP_WEB_OUTPUT',false);  //defunct



/**
 * Includes
 */
include_once('../ktapi/ktapi.inc.php');
include_once('lib/ktAPP.inc.php');
include_once('lib/KTAPPHelper.inc.php');						//Containing helper bridge functions to KtAPI
include_once('lib/KTAPDoc.inc.php');							//Containing the parent class allowing easy XML manipulation
include_once('lib/KTAPPServiceDoc.inc.php');					//Containing the servicedoc class allowing easy ServiceDocument generation
include_once('lib/KTAPPFeed.inc.php');							//Containing the response feed class allowing easy atom feed generation
include_once('lib/ktAPP_Service.inc.php');
include_once('lib/ktApp.default_dms_services.inc.php');
include_once('auth.php');										//Containing the authentication protocols


//Start the AtomPubProtocol Routing Engine
$APP=new KTAPP();

//Register New Services (in the DMS workspace)
$APP->registerService('DMS','fulltree','ktAPP_Service_fullTree','Full Document Tree');
$APP->registerService('DMS','folder','ktAPP_Service_folder','Folder Detail');
$APP->registerService('DMS','document','ktAPP_Service_document','Document Detail');

//Execute the current url/header request
$APP->execute();

//Render the resulting feed response
$APP->render();

?>