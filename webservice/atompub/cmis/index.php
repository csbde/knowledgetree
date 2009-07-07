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
 * 				Paul Barrett <paul@knowledgetree.com>
 *
 */

require_once('../../../config/dmsDefaults.php');

define('KT_APP_BASE_URI', "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?/');
define('KT_APP_SYSTEM_URI', "http://".$_SERVER['HTTP_HOST']);
define('KT_ATOM_LIB_FOLDER', '../../classes/atompub/');

define('CMIS_APP_BASE_URI', trim(KT_APP_BASE_URI, '/'));
define('CMIS_APP_SYSTEM_URI', KT_APP_SYSTEM_URI);
define('CMIS_ATOM_LIB_FOLDER', trim(KT_ATOM_LIB_FOLDER, '/') . '/cmis/');

// fetch username and password for auth;  note that this apparently only works when PHP is run as an apache module
// TODO method to fetch username and password when running PHP as CGI, if possible
// HTTP Basic Auth:
$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

/**
 * Includes
 */
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_server.inc.php');
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_baseDoc.inc.php');
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_responseFeed.inc.php');				//Containing the response feed class allowing easy atom feed generation
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_serviceDoc.inc.php');          //Containing the servicedoc class allowing easy ServiceDocument generation
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_service.inc.php');          //Containing the servicedoc class allowing easy ServiceDocument generation

include_once('KT_cmis_atom_server.services.inc.php');

//Start the AtomPubProtocol Routing Engine
$APP = new KT_cmis_atom_server();

// FIXME HACK! this should not happen every time, ONLY on a service doc request
// CMIS service document setup
$APP->initServiceDocument();
// FIXME HACK! this should not happen every time, ONLY on a service doc request
// User defined title tag
$APP->addWorkspaceTag('dms','atom:title','KnowledgeTree DMS');

/**
 * Register Services
 *
 * Registered services are classes extended from KT_atom_service
 * The registration process takes the following parameters
 * 		Workspace		:The workspace within which the service collection will be grouped
 * 		ServiceName		:This is the name by which the service/collection is exposed
 * 		ServiceClass	:This is the class name of the class to be instantiated when this service is accessed
 * 		Title			:This is the title given to the service/collection in the servicedocument
 *      http://ktatompub/index.php?/service/param1/param2
 *      http://ktatompub/?/folder/children/whatfoldertolookat
 *      http://ktatompub/{folder/folder2/folder3/}service/param1/param2
*/
// TODO consider a registerServices function which will, dependant on what is requested, register the appropriate services, keep the logic out of the index file
// FIXME HACK! this should not happen every time, ONLY on a service doc request, except for request specific collection links
$APP->registerService('dms', 'folder', 'KT_cmis_atom_service_folder', 'Root Folder Children Collection',
                      array($APP->repositoryInfo['rootFolderId'], 'children'), 'root-children');
$APP->registerService('dms', 'folder', 'KT_cmis_atom_service_folder', 'Root Folder Children Collection',
                      array($APP->repositoryInfo['rootFolderId'], 'descendants'), 'root-descendants');
$APP->registerService('dms', 'checkedout', 'KT_cmis_atom_service_checkedout', 'Checked Out Document Collection', null, 'checkedout');
$APP->registerService('dms', 'types', 'KT_cmis_atom_service_types', 'Object Type Collection', null, 'types-children');
$APP->registerService('dms', 'types', 'KT_cmis_atom_service_types', 'Object Type Collection', null, 'types-descendants');

// FIXME HACK! this should not happen every time, ONLY on a specific request, should NOT appear in service document as this is not definable at that time;
//             SHOULD be appearing in types listing feed
// NOTE $requestParams is meaningless if not actually requesting this service, so not a good way to register the service really
$queryArray=split('/',trim($_SERVER['QUERY_STRING'],'/'));
$requestParams=array_slice($queryArray,2);
$APP->registerService('dms', 'type', 'KT_cmis_atom_service_type', 'Object Type Collection', explode('/', $requestParams), 'types-descendants');
// FIXME HACK! see above, this one for documents
$APP->registerService('dms', 'document', 'KT_cmis_atom_service_document', 'Object Type Collection', explode('/', $requestParams), 'types-descendants');

//Execute the current url/header request
$APP->execute();

//Render the resulting feed response
$APP->render();

?>
