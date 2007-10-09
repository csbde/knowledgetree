<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

// Generic error messages used in the API. There may be some others specific to functionality
// directly in the code.
// TODO: Check that they are all relevant.

define('KTAPI_ERROR_SESSION_INVALID', 			'The session could not be resolved.');
define('KTAPI_ERROR_PERMISSION_INVALID', 		'The permission could not be resolved.');
define('KTAPI_ERROR_FOLDER_INVALID', 			'The folder could not be resolved.');
define('KTAPI_ERROR_DOCUMENT_INVALID', 			'The document could not be resolved.');
define('KTAPI_ERROR_USER_INVALID', 				'The user could not be resolved.');
define('KTAPI_ERROR_KTAPI_INVALID', 			'The ktapi could not be resolved.');
define('KTAPI_ERROR_INSUFFICIENT_PERMISSIONS', 	'The user does not have sufficient permissions to access the resource.');
define('KTAPI_ERROR_INTERNAL_ERROR', 			'An internal error occurred. Please review the logs.');
define('KTAPI_ERROR_DOCUMENT_TYPE_INVALID', 	'The document type could not be resolved.');
define('KTAPI_ERROR_DOCUMENT_CHECKED_OUT', 		'The document is checked out.');
define('KTAPI_ERROR_DOCUMENT_NOT_CHECKED_OUT', 	'The document is not checked out.');
define('KTAPI_ERROR_WORKFLOW_INVALID', 			'The workflow could not be resolved.');
define('KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS', 	'The workflow is not in progress.');
define('KTAPI_ERROR_DOCUMENT_LINK_TYPE_INVALID','The link type could not be resolved.');

// Mapping of permissions to actions.
// TODO: Check that they are all correct.
// Note, currently, all core actions have permissions that are defined in the plugins.
// As the permissions are currently associated with actions which are quite closely linked
// to the web interface, it is not the nicest way to do things. They should be associated at
// a lower level, such as in the api. probably, better, would be at some stage to assocate
// the permissions to the action/transaction in the database so administrators can really customise
// as required.

define('KTAPI_PERMISSION_DELETE',			'ktcore.permissions.delete');
define('KTAPI_PERMISSION_READ',				'ktcore.permissions.read');
define('KTAPI_PERMISSION_WRITE',			'ktcore.permissions.write');
define('KTAPI_PERMISSION_ADD_FOLDER',		'ktcore.permissions.addFolder');
define('KTAPI_PERMISSION_RENAME_FOLDER',	'ktcore.permissions.folder_rename');
define('KTAPI_PERMISSION_CHANGE_OWNERSHIP',	'ktcore.permissions.security');
define('KTAPI_PERMISSION_DOCUMENT_MOVE',	'ktcore.permissions.write');
define('KTAPI_PERMISSION_WORKFLOW',			'ktcore.permissions.workflow');
define('KTAPI_PERMISSION_VIEW_FOLDER',		'ktcore.permissions.folder_details');

?>