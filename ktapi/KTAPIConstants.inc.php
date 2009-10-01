<?php
/**
 * Generic error messages used in KTAPI.
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
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version Version 0.9
 */

/**
 * Generic error messages used in the API. There may be some others specific to functionality
 * directly in the code.
 *
 * TODO: Check that they are all relevant.
*/
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

/**
 * Mapping of permissions to actions.
 *
 * Note, currently, all core actions have permissions that are defined in the plugins.
 * As the permissions are currently associated with actions which are quite closely linked
 * to the web interface, it is not the nicest way to do things. They should be associated at
 * a lower level, such as in the api. probably, better, would be at some stage to assocate
 * the permissions to the action/transaction in the database so administrators can really customise
 * as required.
 *
 * TODO: Check that they are all correct.
*/
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
