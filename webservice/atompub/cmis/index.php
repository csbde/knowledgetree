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
 * 				Paul Barrett <paul@knowledgetree.com>
 *
 */

require_once('../../../config/dmsDefaults.php');
require_once(KT_DIR . '/ktapi/ktapi.inc.php');

$accessProtocol = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https' : 'http' ;
define('KT_APP_BASE_URI', $accessProtocol . '://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?/');
define('KT_APP_SYSTEM_URI', $accessProtocol . '://'.$_SERVER['HTTP_HOST']);
define('KT_ATOM_LIB_FOLDER', '../../classes/atompub/');
define('CMIS_APP_BASE_URI', trim(KT_APP_BASE_URI, '/'));
define('CMIS_APP_SYSTEM_URI', KT_APP_SYSTEM_URI);
define('CMIS_ATOM_LIB_FOLDER', trim(KT_ATOM_LIB_FOLDER, '/') . '/cmis/');

/**
 * Check Realm Authentication
 */
require_once(KT_ATOM_LIB_FOLDER.'KT_atom_HTTPauth.inc.php');

if(!KT_atom_HTTPauth::isLoggedIn()) {
	KT_atom_HTTPauth::login('KnowledgeTree DMS', 'You must authenticate to enter this realm');
}

/**
 * Includes
 */
include_once(KT_ATOM_LIB_FOLDER.'XMLns2array.inc.php');
include_once(KT_ATOM_LIB_FOLDER.'KT_atom_baseDoc.inc.php');
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_server.inc.php');
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_response.inc.php');				//Containing the response feed class allowing easy atom feed generation
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_responseFeed.inc.php');				//Containing the response feed class allowing easy atom feed generation
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_serviceDoc.inc.php');          //Containing the servicedoc class allowing easy ServiceDocument generation
include_once(CMIS_ATOM_LIB_FOLDER.'KT_cmis_atom_service.inc.php');          //Containing the servicedoc class allowing easy ServiceDocument generation

// services
include_once('KT_cmis_atom_server.services.inc.php');

//Start the AtomPubProtocol Routing Engine
$APP = new KT_cmis_atom_server();

$queryArray = split('/', trim($_SERVER['QUERY_STRING'], '/'));
$workspace = strtolower(trim($queryArray[0]));
if ($workspace == 'servicedocument')
{
    // CMIS service document setup
    $APP->initServiceDocument();
    // User defined title tag
    $APP->addWorkspaceTag('dms','atom:title', $APP->repositoryInfo['repositoryName']);
}

/**
 * Register Services
 *
 * Registered services are classes extended from KT_atom_service
 * The registration process takes the following parameters
 * 		Workspace		:The workspace within which the service collection will be grouped
 * 		ServiceName		:This is the name by which the service/collection is exposed
 * 		ServiceClass	:This is the class name of the class to be instantiated when this service is accessed
 * 		Title			:This is the title given to the service/collection in the servicedocument
 *      http://webservice/atompub/cmis/index.php?/service/param1/param2
 *      http://webservice/atompub/cmis/?/folder/children/whatfoldertolookat
 *      http://webservice/atompub/cmis/{folder/folder2/folder3/}service/param1/param2
*/
// TODO consider a registerServices function which will, dependant on what is requested, register the appropriate services, keep the logic out of the index file
$APP->registerService('dms', 'folder', 'KT_cmis_atom_service_folder', 'Root Folder Children Collection',
                      array(rawurlencode($APP->repositoryInfo['rootFolderId']), 'children'), 'rootchildren');
$APP->registerService('dms', 'folder', 'KT_cmis_atom_service_folder', 'Root Folder Children Collection',
                      array(rawurlencode($APP->repositoryInfo['rootFolderId']), 'descendants'), 'rootdescendants');
$APP->registerService('dms', 'checkedout', 'KT_cmis_atom_service_checkedout', 'Checked Out Document Collection', null, 
                      'checkedout', 'application/atom+xml;type=entry');
$APP->registerService('dms', 'types', 'KT_cmis_atom_service_types', 'Object Type Collection', null, 'typeschildren');
$APP->registerService('dms', 'types', 'KT_cmis_atom_service_types', 'Object Type Collection', null, 'typesdescendants');

if ($workspace != 'servicedocument')
{
    $APP->registerService('dms', 'type', 'KT_cmis_atom_service_type', 'Object Type Entry', null, 'type');
    $APP->registerService('dms', 'document', 'KT_cmis_atom_service_document', 'Document Entry', null, 'document');
    $APP->registerService('dms', 'pwc', 'KT_cmis_atom_service_pwc', 'Private Working Copy', null, 'pwc');
}

//Execute the current url/header request
$APP->execute();

//Render the resulting feed response
$APP->render();

?>
