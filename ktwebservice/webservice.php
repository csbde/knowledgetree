<?php
//debugger_start_debug();
/**
 *
 * $Id$
 *
 * This implements the KnowledgeTree Web Service in SOAP.
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
 * Contributor( s): ______________________________________
 *
 */
require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');
require_once('SOAP/Server.php');
require_once('SOAP/Disco.php');
require_once('KTDownloadManager.inc.php');
require_once('KTUploadManager.inc.php');
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');

list($major, $minor, $fix) = explode('.', $default->systemVersion);

if ($major == 3 && $minor >= 5)
{
	define('HAS_SEARCH_FUNCTIONALITY',1);
}
unset($major); unset($minor); unset($fix);

if (defined('HAS_SEARCH_FUNCTIONALITY'))
{
	require_once(KT_DIR . '/search2/search/search.inc.php');
}

// TODO: allow downloading of metadata versions
// TODO: allow downloading of document versions
// TODO: chunking search results
// TODO: add basic permissions management - add permissions to folder based on user/groups
// TODO: refactor!!! download manager, split this file into a few smaller ones, etc
// TOdO: define more reason codes!
// TODO: get_folder must have a 'create' option
// TODO: redo metadata encoding
// TODO: unit tests - metadata - test return values in selectin - list/tree
// TODO: ktwsapi/php must be made compatible with v2/v3
// TODO: subscriptions/notifications

// NOTE: some features are not implemented yet. most expected for v3. e.g. oem_document_no, custom_document_no, download($version)., get_metadata($version)

// Status Codes as defined in the specification.

define('KTWS_SUCCESS',						0);
define('KTWS_ERR_INVALID_SESSION',			1);
define('KTWS_ERR_AUTHENTICATION_ERROR',		2);
define('KTWS_ERR_INSUFFICIENT_PERMISSIONS',	3);

define('KTWS_ERR_FILE_NOT_FOUND',			10);
define('KTWS_ERR_INVALID_FILENAME',			20);

define('KTWS_ERR_INVALID_DOCUMENT',			21);
define('KTWS_ERR_INVALID_FOLDER',			22);
define('KTWS_ERR_INVALID_METADATA',			23);
define('KTWS_ERR_INVALID_REASON',			24);
define('KTWS_ERR_INVALID_DEPTH',			25);
define('KTWS_ERR_INVALID_DOCUMENT_TYPE',	26);
define('KTWS_ERR_INVALID_WORKFLOW',			27);

define('KTWS_ERR_PROBLEM',					98);
define('KTWS_ERR_DB_PROBLEM',				99);

if (!defined('LATEST_WEBSERVICE_VERSION'))
{
	define('LATEST_WEBSERVICE_VERSION',2);
}

	function bool2str($bool)
	{
		if (is_bool($bool))
		{
			return $bool?'true':'false';
		}
		if (is_numeric($bool))
		{
			return ($bool+0)?'true':'false';
		}
		// assume str
		return (strtolower($bool) == 'true')?'true':'false';
	}

class KTWebService
{
	/**
	 * Defines functions, parameters, and return values.
	 *
	 * @var array
	 */
    var $__dispatch_map = array();

    /**
     * Defines the structures that are used by web service functions.
     *
     * @var array
     */
    var $__typedef = array();

    /**
     * This is the namespace used by the web service.
     *
     * @var unknown_type
     */

    var $namespace;

    var $mustDebug;
    var $version;
    var $ktapi;

    function KTWebService()
    {
    	// Caching was giving some problems, so disable it.

    	$config = &KTConfig::getSingleton();
    	$this->version = $config->get('webservice/version', LATEST_WEBSERVICE_VERSION);
    	$this->mustDebug = $config->get('webservice/debug', 0);
    	$this->ktapi = null;

    	$this->namespace = 'KnowledgeTree';

    	$this->__typedef["{urn:$this->namespace}kt_response"] =
         	array(
		        'status_code' => 'int',
                'message' => 'string'
         	);

    	$this->__typedef["{urn:$this->namespace}kt_folder_detail"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
				'id' => 'int',
        		'folder_name' => 'string',
        		'parent_id' => 'int',
        		'full_path' => 'string',
         	);

         if ($this->version >= 2)
         {
         	$this->__typedef["{urn:$this->namespace}kt_folder_detail"]['created_by'] = 'string';
         }
         if($this->version >= 3){
         	$this->__typedef["{urn:$this->namespace}kt_folder_detail"]['linked_folder_id'] = 'int';
         }

    	$this->__typedef["{urn:$this->namespace}kt_folder_item"] =
         	array(
		        'id' => 'int',
        		'item_type' => 'string',
                'title' => 'string',
                'creator' => 'string',
                'checked_out_by' => 'string',
                'modified_by' => 'string',
                'filename' => 'string',
                'size' => 'string',
                'major_version' => 'string',
                'minor_version' => 'string',
                'storage_path' => 'string',
                'mime_type' => 'string',
                'mime_icon_path' => 'string',
                'mime_display' => 'string',
                'workflow'=>'string',
                'workflow_state'=>'string',
                'items' =>"{urn:$this->namespace}kt_folder_items"
         	);

        if ($this->version >= 2)
         {
         	$this->__typedef["{urn:$this->namespace}kt_folder_item"] =
         	array(
		        'id' => 'int',
        		'item_type' => 'string',

                'custom_document_no' => 'string',
                'oem_document_no' => 'string',

        		'title' => 'string',
		        'document_type' => 'string',
                'filename' => 'string',
                'filesize' => 'string',

                'created_by' => 'string',
                'created_date' => 'string',

                'checked_out_by' => 'string',
                'checked_out_date' => 'string',

                'modified_by' => 'string',
                'modified_date' => 'string',

                'owned_by' => 'string',

                'version' => 'string',

                'is_immutable'=>'string',
                'permissions' => 'string',

                'workflow'=>'string',
                'workflow_state'=>'string',

                'mime_type' => 'string',
                'mime_icon_path' => 'string',
                'mime_display' => 'string',

                'storage_path' => 'string',


         	);

         	if($this->version>=3){
         		$this->__typedef["{urn:$this->namespace}kt_folder_item"]['linked_folder_id'] = 'int';
         	}

         	$this->__typedef["{urn:$this->namespace}kt_folder_item"]['items'] = "{urn:$this->namespace}kt_folder_items";
         }

        $this->__typedef["{urn:$this->namespace}kt_folder_items"] =
			array(
            	array(
                        'item' => "{urn:$this->namespace}kt_folder_item"
                  )
            );

    	$this->__typedef["{urn:$this->namespace}kt_folder_contents"] =
         	array(
				'status_code' => 'int',
				'message' => 'string',
				'folder_id' => 'int',
        		'folder_name' => 'string' ,
        		'full_path' => 'string' ,
        		'items' => "{urn:$this->namespace}kt_folder_items",
         	);

         $this->__typedef["{urn:$this->namespace}kt_document_detail"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
        		'title' => 'string',
		        'document_type' => 'string',
		        'version' => 'string',
	   		    'filename' => 'string',
	   	        'created_date' => 'string',
        	   	'created_by' => 'string',
        	   	'updated_date' => 'string',
 			   	'updated_by' => 'string',
  			   	'document_id' => 'int',
   			   	'folder_id' => 'int',
        	   	'workflow' => 'string',
        	   	'workflow_state' => 'string',
        	   	'checkout_by' => 'string',
        	   	'full_path' => 'string',
         	);

         if ($this->version >= 2)
         {
         	$this->__typedef["{urn:$this->namespace}kt_document_detail"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',

  			   	'document_id' => 'int',

                'custom_document_no' => 'string',
                'oem_document_no' => 'string',

        		'title' => 'string',
		        'document_type' => 'string',
        	   	'full_path' => 'string',
	   		    'filename' => 'string',
	   		    'filesize' => 'int',
   			   	'folder_id' => 'int',

        	   	'created_by' => 'string',
	   	        'created_date' => 'string',

        	   	'checked_out_by'=>'string',
        	   	'checked_out_date'=>'string',

 			   	'modified_by' => 'string',
        	   	'modified_date' => 'string',

        	   	'owned_by'=>'string',

        	   	'version' => 'float',

        	   	'is_immutable'=>'boolean',
        	   	'permissions' => 'string',

        	   	'workflow' => 'string',
        	   	'workflow_state' => 'string',

				'mime_type' => 'string',
                'mime_icon_path' => 'string',
                'mime_display' => 'string',

                'storage_path' => 'string',


        	   	'metadata' => "{urn:$this->namespace}kt_metadata_fieldsets",
	         	'links' => "{urn:$this->namespace}kt_linked_documents",
    	     	'transitions' => "{urn:$this->namespace}kt_workflow_transitions",
        	 	'version_history' => "{urn:$this->namespace}kt_document_version_history",
         		'transaction_history' => "{urn:$this->namespace}kt_document_transaction_history",
         	);

         	if($this->version>=3){
         		$this->__typedef["{urn:$this->namespace}kt_document_detail"]['linked_document_id'] = 'int';
         	}
         }

        if (defined('HAS_SEARCH_FUNCTIONALITY'))
        {

        $this->__typedef["{urn:$this->namespace}kt_search_result_item"] =
         	array(
				'document_id' => 'int',

				'custom_document_no' => 'string',
                'oem_document_no' => 'string',

				'relevance' => 'float',
        		'text' => 'string',

				'title' => 'string',
				'document_type' => 'string',
        		'fullpath' => 'string',
				'filename' => 'string',
				'filesize' => 'int',
        		'folder_id' => 'int',

        		'created_by' => 'string',
        		'created_date' => 'string',

        		'checked_out_by' => 'string',
        		'checked_out_date' => 'string',

        		'modified_by' => 'string',
        		'modified_date' => 'string',

        		'owned_by' => 'string',

        		'version' => 'float',
        		'is_immutable' => 'boolean',
        		'permissions' => 'string',

        		'workflow' => 'string',
        		'workflow_state' => 'string',

				'mime_type' => 'string',
                'mime_icon_path' => 'string',
                'mime_display' => 'string',

                'storage_path' => 'string',

        		'status' => 'string',
         	);

    	$this->__typedef["{urn:$this->namespace}kt_search_results"] =
         	array(
				array(
                        'item' => "{urn:$this->namespace}kt_search_result_item"
                  )
         	);

    	$this->__typedef["{urn:$this->namespace}kt_search_response"] =
         	array(
				'status_code' => 'int',
				'message' => 'string',
        		'hits' => "{urn:$this->namespace}kt_search_results" ,
         	);
        }

         if ($this->version >= 2)
         {

    	$this->__typedef["{urn:$this->namespace}kt_sysdata_item"] =
         	array(
				'name' => 'string',
				'value' => 'string'
         	);

		$this->__typedef["{urn:$this->namespace}kt_sysdata"] =
         	array(
				array(
                        'item' => "{urn:$this->namespace}kt_sysdata_item"
                  )
         	);
         }

    	$this->__typedef["{urn:$this->namespace}kt_metadata_selection_item"] =
         	array(
				'id' => 'int',
				'name' => 'string',
				'value' => 'string',
        		'parent_id' => 'int'
         	);

    	$this->__typedef["{urn:$this->namespace}kt_metadata_selection"] =
         	array(
				array(
                        'item' => "{urn:$this->namespace}kt_metadata_selection_item"
                  )
         	);
            
    $this->__typedef["{urn:$this->namespace}kt_metadata_options"] =
         	array(
				'ishtml' => 'string',
        		'maxlength' => 'string'
         	);

    	$this->__typedef["{urn:$this->namespace}kt_metadata_field"] =
         	array(
				'name' => 'string',
        		'required' => 'boolean' ,
        		'value' => 'string' ,
        		'description' => 'string' ,
        		'control_type' => 'string' ,
        		'selection' => "{urn:$this->namespace}kt_metadata_selection",
                'options' => "{urn:$this->namespace}kt_metadata_options"
         	);

        $this->__typedef["{urn:$this->namespace}kt_metadata_fields"] =
			array(
            	array(
                        'field' => "{urn:$this->namespace}kt_metadata_field"
                  )
            );

    	$this->__typedef["{urn:$this->namespace}kt_metadata_fieldset"] =
         	array(
				'fieldset' => 'string',
				'description' => 'string',
        		'fields' => "{urn:$this->namespace}kt_metadata_fields" ,
         	);

        $this->__typedef["{urn:$this->namespace}kt_metadata_fieldsets"] =
			array(
            	array(
                        'fieldset' => "{urn:$this->namespace}kt_metadata_fieldset"
                  )
            );

    	$this->__typedef["{urn:$this->namespace}kt_metadata_response"] =
         	array(
				'status_code' => 'int',
				'message' => 'string',
        		'metadata' => "{urn:$this->namespace}kt_metadata_fieldsets" ,
         	);

        $this->__typedef["{urn:$this->namespace}kt_workflow_transitions"] =
			array(
            	array(
                        'transition' => 'string'
                  )
            );

    	$this->__typedef["{urn:$this->namespace}kt_workflow_transitions_response"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'transitions' => "{urn:$this->namespace}kt_workflow_transitions"
         		);

 		$this->__typedef["{urn:$this->namespace}kt_workflows_array"] =
			array(
            	array(
                        'workflow' => 'string'
                  )
            );

		$this->__typedef["{urn:$this->namespace}kt_workflows_response"] =
         	array(
            	'status_code' => 'int',
            	'message' => 'string',
            	'workflows' => "{urn:$this->namespace}kt_workflows_array"
            );

    	$this->__typedef["{urn:$this->namespace}kt_document_transaction_history_item"] =
         	array(
         		'transaction_name'=>'string',
         		'username'=>'string',
         		'version' => 'string',
         		'comment' => 'string',
         		'datetime' => 'string'
         		);

        if ($this->version >= 2)
         {
         	$this->__typedef["{urn:$this->namespace}kt_document_transaction_history_item"] =
         	array(
         		'transaction_name'=>'string',
         		'username'=>'string',
         		'version' => 'float',
         		'comment' => 'string',
         		'datetime' => 'string'
         		);
         }

    	$this->__typedef["{urn:$this->namespace}kt_linked_document"] =
         	array(
         		'document_id'=>'int',
                'custom_document_no' => 'string',
                'oem_document_no' => 'string',
                'title'=>'string',
         		'document_type'=>'string',
         		'filesize' => 'int',
         		'version' => 'float',
         		'workflow' => 'string',
         		'workflow_state' => 'string',
         		'link_type' => 'string'
         		);

        $this->__typedef["{urn:$this->namespace}kt_linked_documents"] =
			array(
            	array(
                        'links' => "{urn:$this->namespace}kt_linked_document"
                  )
            );

    	$this->__typedef["{urn:$this->namespace}kt_linked_document_response"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'parent_document_id' => 'int',
         		'links' => "{urn:$this->namespace}kt_linked_documents"
         		);

        $this->__typedef["{urn:$this->namespace}kt_document_transaction_history"] =
			array(
            	array(
                        'history' => "{urn:$this->namespace}kt_document_transaction_history_item"
                  )
            );
         if($this->version >= 3){
	        $this->__typedef["{urn:$this->namespace}kt_document_shortcut"] =
	         	array(
	         		'id' => 'int',
        	   	'full_path' => 'string',
   			   	'folder_id' => 'int',
        	   	'creator_id' => 'string',
	   	        'created' => 'string',
        	   	'owner_id'=>'string',
        	   	'permission_object_id' => 'string',
	         	'permission_lookup_id' => 'string',
				'linked_document_id' => 'int',
	         		);

	        $this->__typedef["{urn:$this->namespace}kt_document_shortcuts"] =
				array(
	            	array(
	                        'shortcuts' => "{urn:$this->namespace}kt_document_shortcut"
	                  )
	            );

	        $this->__typedef["{urn:$this->namespace}kt_document_shortcut_response"] =
				array(
	            	'status_code'=>'int',
	         		'message'=>'string',
	         		'shortcuts' => "{urn:$this->namespace}kt_document_shortcuts"
	            );

	        $this->__typedef["{urn:$this->namespace}kt_folder_shortcut"] =
	         	array(
	         		'id' => 'int',
	         	'name' => 'string',
	         	'parent_id' => 'string',
        	   	'full_path' => 'string',
   			   	'folder_id' => 'int',
        	   	'creator_id' => 'string',
	   	        'created' => 'string',
        	   	'permission_object_id' => 'string',
	         	'permission_lookup_id' => 'string',
				'linked_folder_id' => 'int',
	         		);

	        $this->__typedef["{urn:$this->namespace}kt_folder_shortcuts"] =
				array(
	            	array(
	                        'shortcuts' => "{urn:$this->namespace}kt_folder_shortcut"
	                  )
	            );

	        $this->__typedef["{urn:$this->namespace}kt_folder_shortcut_response"] =
				array(
	            	'status_code'=>'int',
	         		'message'=>'string',
	         		'shortcuts' => "{urn:$this->namespace}kt_folder_shortcuts"
	            );
         }

    	$this->__typedef["{urn:$this->namespace}kt_document_transaction_history_response"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'history' => "{urn:$this->namespace}kt_document_transaction_history"
         		);

    	$this->__typedef["{urn:$this->namespace}kt_document_version_history_item"] =
         	array(
         		'user'=>'string',
         		'metadata_version'=>'string',
         		'content_version'=>'string',
         		);

         if ($this->version >= 2)
         {
         	$this->__typedef["{urn:$this->namespace}kt_document_version_history_item"] =
         	array(
         		'user'=>'string',
         		'metadata_version'=>'int',
         		'content_version'=>'float',
         		);
         }

        $this->__typedef["{urn:$this->namespace}kt_document_collection"] =
			array(
            	array(
                        'item' =>  "{urn:$this->namespace}kt_document_detail"
                  )
            );

         $this->__typedef["{urn:$this->namespace}kt_document_collection_response"] =
			array(
            	'status_code' => 'int',
            	'message' => 'string',
            	'collection' => "{urn:$this->namespace}kt_document_collection"
            );

        $this->__typedef["{urn:$this->namespace}kt_folder_collection"] =
			array(
            	array(
                        'item' =>  "{urn:$this->namespace}kt_folder_detail"
                  )
            );

         $this->__typedef["{urn:$this->namespace}kt_folder_collection_response"] =
			array(
            	'status_code' => 'int',
            	'message' => 'string',
            	'collection' => "{urn:$this->namespace}kt_folder_collection"
            );

        $this->__typedef["{urn:$this->namespace}kt_document_version_history"] =
			array(
            	array(
                        'history' =>  "{urn:$this->namespace}kt_document_version_history_item"
                  )
            );

    	$this->__typedef["{urn:$this->namespace}kt_document_version_history_response"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'history' => "{urn:$this->namespace}kt_document_version_history"
         		);

           $this->__typedef["{urn:$this->namespace}kt_document_types_array"] =
			array(
            	array(
                        'document_type' => 'string'
                  )
            );

        $this->__typedef["{urn:$this->namespace}kt_document_types_response"] =
			array(
            	'status_code' => 'int',
            	'message' => 'string',
            	'document_types' => "{urn:$this->namespace}kt_document_types_array"
            );

           $this->__typedef["{urn:$this->namespace}kt_client_policy"] =
				array(
					'name' => 'string',
					'value' => 'string',
					'type' => 'string',
			);

           $this->__typedef["{urn:$this->namespace}kt_client_policies_array"] =
			array(
				array(
					 'policies' => "{urn:$this->namespace}kt_client_policy"
				)
			);

	$this->__typedef["{urn:$this->namespace}kt_client_policies_response"] =
			array(
            	'status_code' => 'int',
            	'message' => 'string',
            	'policies' => "{urn:$this->namespace}kt_client_policies_array"
            );

         /* methods */
        if (defined('HAS_SEARCH_FUNCTIONALITY'))
        {
        	$this->__dispatch_map['search'] = array(
        					'in' => array('session_id' => 'string', 'search'=>'string' ,'options'=>'string'),
                  			'out' => array('return' => "{urn:$this->namespace}kt_search_response" ),
            			);
        }

         // login
         $this->__dispatch_map['login'] =
            array('in' => array('username' => 'string', 'password' => 'string', 'ip' => 'string'),
                  'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

         // anonymous_login
         $this->__dispatch_map['anonymous_login'] =
            array('in' => array('ip' => 'string'),
                  'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

         // logout
         $this->__dispatch_map['logout'] =
            array('in' => array('session_id' => 'string' ),
             'out' => array('return' => "{urn:$this->namespace}kt_response"  ),
            );

         // get_folder_detail
         $this->__dispatch_map['get_folder_detail'] =
            array('in' => array('session_id' => 'string', 'folder_id' => 'int' ),
             'out' => array('return' => "{urn:$this->namespace}kt_folder_detail"),
            );

         if ($this->version >=3)
         {
         	 $this->__dispatch_map['get_folder_detail']['in'] = array('session_id' => 'string', 'folder_id' => 'int', 'create'=>'boolean' );
         }

         // get_documents_by_oem_no
         $this->__dispatch_map['get_documents_by_oem_no'] =
            array('in' => array('session_id' => 'string', 'oem_no' => 'string', 'detail' => 'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_document_collection_response"),
            );

         // get_folder_detail_by_name
         $this->__dispatch_map['get_folder_detail_by_name'] =
            array('in' => array('session_id' => 'string', 'folder_name' => 'string' ),
             'out' => array('return' => "{urn:$this->namespace}kt_folder_detail"),
            );

         if ($this->version >=3)
         {
         	 $this->__dispatch_map['get_folder_detail_by_name']['in'] = array('session_id' => 'string', 'folder_id' => 'int', 'create'=>'boolean' );
         }

         // get_folder_contents
         $this->__dispatch_map['get_folder_contents'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','depth'=>'int','what'=>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_folder_contents"),
            );

         // create_folder
         $this->__dispatch_map['create_folder'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','folder_name' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_folder_detail"),
            );

         if ($this->version >=2)
         {
         // create_folder
         $this->__dispatch_map['add_folder'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','folder_name' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_folder_detail"),
             'alias'=>'create_folder'
            );
         }

         // delete_folder
         $this->__dispatch_map['delete_folder'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

         // rename_folder
         $this->__dispatch_map['rename_folder'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','newname' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response"),
            );

 		// get_document_links
         $this->__dispatch_map['get_document_links'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'),
             'out' => array('return' => "{urn:$this->namespace}kt_linked_document_response"  ),
            );

         // link_documents
         $this->__dispatch_map['link_documents'] =
            array('in' => array('session_id'=>'string','parent_document_id'=>'int','child_document_id'=>'int','type'=>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response"  ),
            );

         // unlink_documents
         $this->__dispatch_map['unlink_documents'] =
            array('in' => array('session_id'=>'string','parent_document_id'=>'int','child_document_id'=>'int'),
             'out' => array('return' => "{urn:$this->namespace}kt_response"  ),
            );


            // copy_folder
         $this->__dispatch_map['copy_folder'] =
            array('in' => array('session_id'=>'string','source_id'=>'int','target_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response"  ),
            );

         if ($this->version >= 2)
            {
            	 $this->__dispatch_map['copy_folder']['out'] = array('return' => "{urn:$this->namespace}kt_folder_detail" );
            }

         // move_folder
         $this->__dispatch_map['move_folder'] =
            array('in' => array('session_id'=>'string','source_id'=>'int','target_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

         if ($this->version >= 2)
            {
            	 $this->__dispatch_map['move_folder']['out'] = array('return' => "{urn:$this->namespace}kt_folder_detail" );
            }


		// get_document_detail
         $this->__dispatch_map['get_document_detail'] = array(
         		'in' => array('session_id' => 'string', 'document_id' => 'int' ),
            	'out' => array('return' => "{urn:$this->namespace}kt_document_detail"),
            );

		 if ($this->version >= 2)
            {
            	$this->__dispatch_map['get_document_detail']['in'] = array('session_id' => 'string', 'document_id' => 'int', 'detail'=>'string' );
            }

          //  checkin_document
         $this->__dispatch_map['checkin_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','filename'=>'string','reason' =>'string','tempfilename' =>'string', 'major_update'=>'boolean' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
            );

          //  checkin_small_document
         $this->__dispatch_map['checkin_small_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','filename'=>'string','reason' =>'string','base64' =>'string', 'major_update'=>'boolean' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
            );

          //  checkin_base64_document
         $this->__dispatch_map['checkin_base64_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','filename'=>'string','reason' =>'string','base64' =>'string', 'major_update'=>'boolean' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
              'alias' => 'checkin_small_document'
            );

         if ($this->version >= 2)
         {
 	        $this->__dispatch_map['checkin_base64_document_with_metadata'] =
	    	        array('in' => array('session_id'=>'string','document_id'=>'int','filename'=>'string','reason' =>'string','base64' =>'string', 'major_update'=>'boolean', 'metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets",'sysdata'=>"{urn:$this->namespace}kt_sysdata" ),
	        	     'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
        	     'alias'=>'checkin_small_document_with_metadata'
            	);
 	        $this->__dispatch_map['checkin_document_with_metadata'] =
	    	        array('in' => array('session_id'=>'string','document_id'=>'int','filename'=>'string','reason' =>'string','tempfilename' =>'string', 'major_update'=>'boolean', 'metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets",'sysdata'=>"{urn:$this->namespace}kt_sysdata" ),
	        	     'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" )
            	);
         }


         if($this->version >= 3){
         	//add folder shortcut
         	$this->__dispatch_map['create_folder_shortcut'] = array('in'=>array('session_id'=>'string','target_folder_id'=>'int','source_folder_id'=>'int'),
         	'out'=>array('return' => "{urn:$this->namespace}kt_folder_detail" ));

         	//add document shortcut
         	$this->__dispatch_map['create_document_shortcut'] = array('in'=>array('session_id'=>'string','target_folder_id'=>'int','source_document_id'=>'int'),
         	'out'=>array('return' => "{urn:$this->namespace}kt_document_detail" ));

         	//get document shortcuts
         	$this->__dispatch_map['get_document_shortcuts'] = array('in'=>array('session_id'=>'string','document_id'=>'int'),
         	'out'=>array('return' => "{urn:$this->namespace}kt_document_shortcuts" ));

         	//get folder shortcuts
         	$this->__dispatch_map['get_folder_shortcuts'] = array('in'=>array('session_id'=>'string','folder_id'=>'int'),
         	'out'=>array('return' => "{urn:$this->namespace}kt_folder_shortcuts" ));
         }

         // add_document
         $this->__dispatch_map['add_document'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','title'=>'string','filename'=>'string','documentype' =>'string','tempfilename' =>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
            );

         // add_small_document
         $this->__dispatch_map['add_small_document'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','title'=>'string','filename'=>'string','documentype' =>'string','base64' =>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
            );

         // add_base64_document
         $this->__dispatch_map['add_base64_document'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','title'=>'string','filename'=>'string','documentype' =>'string','base64' =>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
             'alias' => 'add_small_document'

            );

         if ($this->version >= 2)
         {
 	        $this->__dispatch_map['add_base64_document_with_metadata'] =
    	        array('in' => array('session_id'=>'string','folder_id'=>'int','title'=>'string','filename'=>'string','documentype' =>'string','base64' =>'string', 'metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets",'sysdata'=>"{urn:$this->namespace}kt_sysdata" ),
        	     'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
        	     'alias'=>'add_small_document_with_metadata'
            	);

 	        $this->__dispatch_map['add_document_with_metadata'] =
    	        array('in' => array('session_id'=>'string','folder_id'=>'int','title'=>'string','filename'=>'string','documentype' =>'string','tempfilename' =>'string', 'metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets",'sysdata'=>"{urn:$this->namespace}kt_sysdata" ),
        	     'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" )
            	);
         }

         // get_document_detail_by_name
         $this->__dispatch_map['get_document_detail_by_name'] =
            array('in' => array('session_id' => 'string', 'document_name' => 'string', 'what'=>'string' ),
             'out' => array('return' => "{urn:$this->namespace}kt_document_detail"),
            );

            if ($this->version >= 2)
            {
            	$this->__dispatch_map['get_document_detail_by_name']['in'] = array('session_id' => 'string', 'folder_id'=>'int', 'document_name' => 'string', 'what'=>'string', 'detail'=>'string' );

            	$this->__dispatch_map['get_document_detail_by_title'] = array(
            			'in' => array('session_id' => 'string', 'folder_id'=>'int', 'title' => 'string', 'detail'=>'string' ),
            			'out' => array('return' => "{urn:$this->namespace}kt_document_detail"),
            		);

            	$this->__dispatch_map['get_document_detail_by_filename'] = array(
            			'in' => array('session_id' => 'string', 'folder_id'=>'int', 'filename' => 'string', 'detail'=>'string' ),
            			'out' => array('return' => "{urn:$this->namespace}kt_document_detail"),
            		);
            }

          // checkout_document
           $this->__dispatch_map['checkout_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	 $this->__dispatch_map['checkout_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string','download'=>'boolean'),
             'out' => array('return' => "{urn:$this->namespace}kt_document_detail" ),
            );
            }

          // checkout_small_document
           $this->__dispatch_map['checkout_small_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string','download' => 'boolean'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	 $this->__dispatch_map['checkout_small_document']['out'] = array('return' => "{urn:$this->namespace}kt_document_detail" );
            }

          // checkout_base64_document
           $this->__dispatch_map['checkout_base64_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string','download' => 'boolean'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
              'alias' => 'checkout_small_document'
            );

            if ($this->version >= 2)
            {
            	 $this->__dispatch_map['checkout_base64_document']['out'] = array('return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // undo_document_checkout
            $this->__dispatch_map['undo_document_checkout'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	 $this->__dispatch_map['undo_document_checkout']['out'] = array('return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // download_document
            $this->__dispatch_map['download_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int' ),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

            // download_small_document
            $this->__dispatch_map['download_small_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int' ),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

            // download_base64_document
            $this->__dispatch_map['download_base64_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int' ),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
              'alias' => 'download_small_document'
            );

            if ($this->version >= 3)
            {
            	$this->__dispatch_map['download_document']['in'] = array('session_id'=>'string','document_id'=>'int', 'version'=>'string' );
            	$this->__dispatch_map['download_small_document']['in'] = array('session_id'=>'string','document_id'=>'int', 'version'=>'string' );
            	$this->__dispatch_map['download_base64_document']['in'] = array('session_id'=>'string','document_id'=>'int', 'version'=>'string' );
            }

            // delete_document
			$this->__dispatch_map['delete_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason'=>'string'),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // change_document_owner
			$this->__dispatch_map['change_document_owner'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','username'=>'string','reason'=>'string'),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	$this->__dispatch_map['change_document_owner']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // copy_document
			$this->__dispatch_map['copy_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','folder_id'=>'int','reason'=>'string','newtitle'=>'string','newfilename'=>'string'),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );
            if ($this->version >= 2)
            {
            	$this->__dispatch_map['copy_document'] =
            		array('in' => array('session_id'=>'string','document_id'=>'int','folder_id'=>'int','reason'=>'string', 'options'=>'string' ),
             				'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
            			);
            }

            // move_document
			$this->__dispatch_map['move_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','folder_id'=>'int','reason'=>'string','newtitle'=>'string','newfilename'=>'string'),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );
            if ($this->version >= 2)
            {
            	$this->__dispatch_map['move_document'] =
		            array('in' => array('session_id'=>'string','document_id'=>'int','folder_id'=>'int','reason'=>'string', 'options'=>'string'),
        			     'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
            		);
            }

			// rename_document_title
            $this->__dispatch_map['rename_document_title'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'newtitle'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );
			if ($this->version >= 2)
            {
            	$this->__dispatch_map['rename_document_title']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );
            }
            // rename_document_filename
            $this->__dispatch_map['rename_document_filename'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'newfilename'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );
			if ($this->version >= 2)
            {
            	$this->__dispatch_map['rename_document_filename']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // change_document_type
			$this->__dispatch_map['change_document_type'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'documenttype'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	$this->__dispatch_map['change_document_type']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // start_document_workflow
            $this->__dispatch_map['start_document_workflow'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'workflow'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	$this->__dispatch_map['start_document_workflow']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // delete_document_workflow
            $this->__dispatch_map['delete_document_workflow'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'  ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" )
            );

			if ($this->version >= 2)
            {
            	$this->__dispatch_map['delete_document_workflow']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );

            	// stop_document_workflow
	            $this->__dispatch_map['stop_document_workflow'] =
    		        array('in' => array('session_id'=>'string','document_id'=>'int'  ),
       	    	        'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" ),
       	    	        'alias'=>'delete_document_workflow'
            		);
            }

            // perform_document_workflow_transition
            $this->__dispatch_map['perform_document_workflow_transition'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','transition'=>'string','reason'=>'string'  ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            if ($this->version >= 2)
            {
            	$this->__dispatch_map['perform_document_workflow_transition']['out'] = array( 'return' => "{urn:$this->namespace}kt_document_detail" );
            }

            // get_document_metadata
            $this->__dispatch_map['get_document_metadata'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'   ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_metadata_response" ),
            );

            if ($this->version >= 3)
            {
            	 $this->__dispatch_map['get_document_metadata']['in'] = array('session_id'=>'string','document_id'=>'int', 'version'=>'string');
            }

            // get_document_type_metadata
            $this->__dispatch_map['get_document_type_metadata'] =
            array('in' => array('session_id'=>'string','document_type'=>'string'   ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_metadata_response" ),
            );
            //update_document_metadata
            $this->__dispatch_map['update_document_metadata'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets" ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

             if ($this->version >= 2)
            {
            	$this->__dispatch_map['update_document_metadata'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets", 'sysdata'=>"{urn:$this->namespace}kt_sysdata"  ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_detail" )
            );

            }

            //get_document_workflow_transitions
            $this->__dispatch_map['get_document_workflow_transitions'] =
            array('in' => array('session_id'=>'string','document_id'=>'int' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_workflow_transitions_response" ),
            );

            //get_document_workflow_state
            $this->__dispatch_map['get_document_workflow_state'] =
            array('in' => array('session_id'=>'string','document_id'=>'int' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // get_document_transaction_history
            $this->__dispatch_map['get_document_transaction_history'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'   ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_transaction_history_response" ),
            );

            // get_document_version_history
            $this->__dispatch_map['get_document_version_history'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'   ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_version_history_response" ),
            );

         // get_document_types
         $this->__dispatch_map['get_document_types'] =
            array('in' => array('session_id'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_types_response" ),
            );

         // get_workflows
         $this->__dispatch_map['get_workflows'] =
            array('in' => array('session_id'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_workflows_response" ),
            );

         // get_document_link_types
         $this->__dispatch_map['get_document_link_types'] =
            array('in' => array('session_id'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_types_response" ),
            );

         // get_client_policies
         $this->__dispatch_map['get_client_policies'] =
            array('in' => array('session_id'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_client_policies_response" ),
            );

          if ($this->version >= 2)
            {
            	$this->__dispatch_map['get_client_policies']['in'] = array('session_id'=>'string', 'client'=>'string');
            }
    }

    function debug($msg, $function = null, $level=0)
    {
    	if ($this->mustDebug == 0) return;
    	if ($this->mustDebug >= $level)
    	{
    		global $default;
    		if (!is_null($function))
    		{
    			$function = "$function - ";
    		}
    		$default->log->debug('WS - ' . $function . $msg);
    	}
    }

    function error($msg, $function = null)
    {
    	if ($this->mustDebug)
    	{
    		global $default;
    		if (!is_null($function))
    		{
    			$function = "$function - ";
    		}
    		$default->log->error('WS - ' . $function . $msg);
    	}
    }

    function _status($code, $message='')
    {
    	if (PEAR::isError($message))
    	{
    		$message = $message->getMessage();
    	}
		return array('status_code'=>$code, 'message'=>$message);
    }

    /**
     * This is used by all exposed functions dependant on the sessionid.
     *
     * @param string $session_id
     * @return KTAPI This could be KTAPI or kt_response array with status_code of KTWS_ERR_INVALID_SESSION.
     */
    function &get_ktapi($session_id)
    {
    	if (!is_null($this->ktapi))
    	{
    		return $this->ktapi;
    	}

    	$kt = new KTAPI();

    	$session = $kt->get_active_session($session_id, null);

    	if ( PEAR::isError($session))
    	{
            return KTWebService::_status(KTWS_ERR_INVALID_SESSION,$session);
    	}
    	$this->ktapi = $kt;
    	return $kt;
    }

    /**
     * Creates a new anonymous session.
     *
     * @param string $ip
     * @return kt_response
     */
    function anonymous_login($ip=null)
    {
    	$response = KTWebService::_status(KTWS_ERR_AUTHENTICATION_ERROR);

    	$kt = new KTAPI();

    	$session = $kt->start_anonymous_session($ip);

    	if (PEAR::isError($session))
    	{
    		$response['message'] = $session->getMessage();
    		$this->debug($session->getMessage(),'anonymous_login');
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$session= $session->get_session();
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['message'] = $session;

    	$this->debug("anonymous_login('$ip')", $session);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Creates a new session for the user.
     *
     * @param string $username
     * @param string $password
     * @param string $ip
     * @return kt_response
     */
    function login($username, $password, $ip=null)
    {
    	$response = KTWebService::_status(KTWS_ERR_AUTHENTICATION_ERROR);

    	$kt = new KTAPI();

    	$session = $kt->start_session($username,$password, $ip);

    	if (PEAR::isError($session))
    	{
    		$response['message'] = $session->getMessage();
    		$this->debug($session->getMessage(),'login');
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$session= $session->get_session();
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['message'] = $session;

    	$this->debug("login('$username','$password','$ip')", $session);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Closes an active session.
     *
     * @param string $session_id
     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION or KTWS_SUCCESS.
     */
    function logout($session_id)
    {
    	$this->debug("logout('$session_id')");
    	$kt = &$this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_SESSION);

    	$session = &$kt->get_session();
    	if (PEAR::isError($session))
    	{
    		$response['message'] = $session->getMessage();
    		$this->debug("logout()", $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$session->logout();

    	$response['status_code']=KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Returns folder detail given a folder_id.
     *
     * @param string $session_id
     * @param int $folder_id
     * @return kt_folder_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, or KTWS_SUCCESS.
     */
    function get_folder_detail($session_id, $folder_id)
    {
    	$this->debug("get_folder_detail('$session_id',$folder_id)");
    	$kt = &$this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $kt);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);
    		$this->debug("get_folder_detail - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $folder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);
    }

	/** Encode an array as kt_folder_shortcut
	 *
	 * @param array $shortcuts
	 * @param string $name
	 * @return SOAP_Value of kt_folder_shortcuts
     * @access private
     * @static
	 */
	function _encode_folder_shortcuts($shortcuts, $name='shortcuts')
	{
		foreach($shortcuts as $key=>$item)
		{
			$shortcuts[$key] = new SOAP_Value('item',"{urn:$this->namespace}kt_folder_shortcut", $item);
		}
		return new SOAP_Value($name,"{urn:$this->namespace}kt_folder_shortcuts", $shortcuts);
	}

    /**
     * Retrieves all shortcuts linking to a specific document
     *
     * @param string $session_id
     * @param ing $document_id
     *
	 * @return kt_document_shortcuts. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     *
     */
    function get_folder_shortcuts($session_id, $folder_id){
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_shortcuts", $kt);
    	}

    	$folder = $kt->get_folder_by_id($folder_id);
    	if(PEAR::isError($folder)){
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
    		$this->debug("get_folder_shortcuts - cannot get folder - "  . $folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_shortcuts", $response);
    	}

    	$shortcuts = $folder->get_shortcuts();
    	if(PEAR::isError($shortcuts)){
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$shortcuts);
    		$this->debug("get_folder_shortcuts - cannot retrieve shortcuts linking to $folder_id - "  . $shortcuts->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_shortcuts", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] = KTWebService::_encode_folder_shortcuts($shortcuts);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_shortcuts", $response);

    }


    /**
     * Returns folder detail given a folder name which could include a full path.
     *
     * @param string $session_id
     * @param string $folder_name
     * @return kt_folder_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, or KTWS_SUCCESS.
     */
    function get_folder_detail_by_name($session_id, $folder_name)
    {
    	$this->debug("get_folder_detail_by_name('$session_id','$folder_name')");
    	$kt = &$this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $kt);
    	}

    	$folder = &$kt->get_folder_by_name($folder_name);
    	if (PEAR::isError($folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);
    		$this->debug("get_folder_detail_by_name - cannot get folder $folder_name - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $folder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);
    }

    /**
     * Encodes an array as kt_folder_contents
     *
     * @param array $contents
     * @param string $name
     * @return SOAP_Value of kt_folder_contents
     * @access private
     * @static
     */
    function _encode_folder_items($items)
    {
    	foreach($items as $key=>$item)
    	{
    		$item['id'] = (int) $item['id'];
 			$item['items'] = KTWebService::_encode_folder_items($item['items']);

    		$items[$key] = new SOAP_Value('item',"{urn:$this->namespace}kt_folder_item", $item);
    	}
    	return new SOAP_Value('items',"{urn:$this->namespace}kt_folder_items", $items);
    }

    /**
     * Returns the contents of a folder.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param int $depth
     * @param string $what
     * @return kt_folder_contents
     */
    function get_folder_contents($session_id, $folder_id, $depth=1, $what='DFS')
    {
    	$this->debug("get_folder_contents('$session_id',$folder_id,$depth,'$what')");
    	$kt = &$this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_contents", $kt);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
    	if (PEAR::isError($folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);

    		$this->debug("get_folder_contents - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_contents", $response);
    	}

    	$listing = $folder->get_listing($depth, $what);

    	$contents = array(
    		'status_code'=>KTWS_SUCCESS,
    		'message'=>'',
    		'folder_id' => $folder_id+0,
    		'folder_name'=>$folder->get_folder_name(),
    		'full_path'=>$folder->get_full_path(),
    		'items'=>KTWebService::_encode_folder_items($listing)
    	);

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_folder_contents", $contents);
    }

    /**
     * Creates a new folder.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param string $folder_name
     * @return kt_folder_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function create_folder($session_id, $folder_id, $folder_name)
    {
    	$this->debug("create_folder('$session_id',$folder_id,'$folder_name')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $kt);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
    	if (PEAR::isError($folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);

    		$this->debug("create_folder - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$newfolder = &$folder->add_folder($folder_name);
    	if (PEAR::isError($newfolder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$newfolder);
    		$this->debug("create_folder - cannot create folder $folder_name - "  . $newfolder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $newfolder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);
    }

    /**
     * Creates a shortcut to an existing folder
     *
     * @param string $session_id
     * @param int $target_folder_id Folder to place the shortcut in
     * @param int $source_folder_id Folder to create the shortcut to
     * @return kt_folder_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function create_folder_shortcut($session_id, $target_folder_id, $source_folder_id){
    	$this->debug("create_folder_shortcut('$session_id',$target_folder_id,' $source_folder_id')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $kt);
    	}

    	$folder = &$kt->get_folder_by_id($target_folder_id);
    	if (PEAR::isError($folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);

    		$this->debug("create_folder_shortcut - cannot get folderid $target_folder_id - "  . $folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$source_folder = &$kt->get_folder_by_id($source_folder_id);
    	if (PEAR::isError($source_folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$source_folder);

    		$this->debug("create_folder_shortcut - cannot get folderid $source_folder_id - "  . $source_folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$shortcut = &$folder->add_folder_shortcut($source_folder_id);
    	if (PEAR::isError($shortcut))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$shortcut);
    		$this->debug("create_folder_shortcut - cannot create shortcut to $source_folder_id - "  . $shortcut->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $shortcut->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);
    }

	/**
     * Creates a shortcut to an existing document
     *
     * @param string $session_id
     * @param int $target_folder_id Folder to place the shortcut in
     * @param int $source_document_id Document to create the shortcut to
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER,KTWS_ERR_INVALID_DOCUMENT  or KTWS_SUCCESS
     */
    function create_document_shortcut($session_id, $target_folder_id, $source_document_id){
    	$this->debug("create_document_shortcut('$session_id',$target_folder_id,'$source_document_id')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$folder = &$kt->get_folder_by_id($target_folder_id);
    	if (PEAR::isError($folder))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);

    		$this->debug("create_document_shortcut - cannot get folderid $target_folder_id - "  . $folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$source_document = &$kt->get_document_by_id($source_document_id);
    	if (PEAR::isError($source_document))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,$source_document);

    		$this->debug("create_document_shortcut - cannot get docid $source_document_id - "  . $source_document->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$shortcut = &$folder->add_document_shortcut($source_document_id);
    	if (PEAR::isError($shortcut))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,$shortcut);
    		$this->debug("create_document_shortcut - cannot create shortcut to $source_document_id - "  . $shortcut->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}


    	$detail = $shortcut->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
    }

    /**
     * Deletes a folder.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param string $reason
     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function delete_folder($session_id, $folder_id, $reason)
    {
    	$this->debug("delete_folder('$session_id',$folder_id,'$reason')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER);

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    		$response['message'] = $folder->getMessage();
    		$this->debug("delete_folder - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $folder->delete($reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("delete_folder - cannot delete folderid $folder_id - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Renames a folder.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param string $newname
     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function rename_folder($session_id, $folder_id, $newname)
    {
    	$this->debug("rename_folder('$session_id',$folder_id,'$newname')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER);

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
			$response['message']= $folder->getMessage();
			$this->debug("rename_folder - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $folder->rename($newname);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("rename_folder - cannot rename to $newname - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code']= KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Makes a copy of a folder in another location.
     *
     * @param string $session_id
     * @param int $sourceid
     * @param int $targetid
     * @param string $reason
     * @return kt_response
     */
    function copy_folder($session_id, $source_id, $target_id, $reason)
    {
    	$this->debug("copy_folder('$session_id',$source_id,$target_id,'$reason')");
    	$kt = &$this->get_ktapi($session_id );

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_folder_detail';
    	}
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER);

    	$src_folder = &$kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		$this->debug("copy_folder - cannot get source folderid $source_id - "  . $src_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("copy_folder - cannot get target folderid $target_id - "  . $tgt_folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result= $src_folder->copy($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("copy_folder - copy to target folder - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	if ($this->version >=2)
    	{

	    	$sourceName = $src_folder->get_folder_name();
	    	$targetPath = $tgt_folder->get_full_path();

	    	$response = $this->get_folder_detail_by_name($session_id, $targetPath . '/' . $sourceName);

    		return $response;
    	}

    	$response['status_code']= KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Moves a folder to another location.
     *
     * @param string $session_id
     * @param int $sourceid
     * @param int $targetid
     * @param string $reason
     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function move_folder($session_id, $source_id, $target_id, $reason)
    {
    	$this->debug("move_folder('$session_id',$source_id,$target_id,'$reason')");
    	$kt = &$this->get_ktapi($session_id );
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_folder_detail';
    	}
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER);

    	$src_folder = &$kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		$this->debug("move_folder - cannot get source folder $source_id - "  . $src_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("move_folder - cannot get target folder $target_id - "  . $tgt_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $src_folder->move($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("move_folder - cannot move folder - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	if ($this->version >=2)
    	{

	    	$response = $this->get_folder_detail($session_id, $source_id);

    		return $response;
    	}

    	$response['status_code']= KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Returns a list of document types.
     *
     * @param string $session_id
     * @return kt_document_types_response. . status_code can be KTWS_ERR_INVALID_SESSION, KTWS_SUCCESS
     */
    function get_document_types($session_id)
    {
    	$this->debug("get_document_types('$session_id')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_types_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_PROBLEM);

    	$result = $kt->get_documenttypes();
    	if (PEAR::isError($result))
    	{
    	    $response['message']= $result->getMessage();
    		$this->debug("get_document_types - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_types_response", $response);
    	}

   		$response['status_code']= KTWS_SUCCESS;
   		$response['document_types']= $result;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_types_response", $response);

    }

    function get_document_link_types($session_id)
    {
    	$this->debug("get_document_link_types('$session_id')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_types_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_PROBLEM);

    	$result = $kt->get_document_link_types();
    	if (PEAR::isError($result))
    	{
    	    $response['message']= $result->getMessage();
    	    $this->debug("get_document_link_types - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_types_response", $response);
    	}

   		$response['status_code']= KTWS_SUCCESS;
   		$response['document_types']= $result;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_types_response", $response);

    }

    /**
     * Returns document detail given a document_id.
     *
     * @param string $session_id
     * @param int $document_id
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function get_document_detail($session_id, $document_id, $detail='')
    {
    	$this->debug("get_document_detail('$session_id',$document_id,'$detail')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$document = $kt->get_document_by_id($document_id);
    	if (PEAR::isError($document))
    	{
		    $response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,$document);

    		$this->debug("get_document_detail - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$detailstr = $detail;
    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $detail->getMessage();

    		$this->debug("get_document_detail - cannot get detail - "  . $detail->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	if ($this->version >= 2)
    	{
    		$detail['metadata'] = array();
    		$detail['links'] = array();
    		$detail['transitions'] = array();
    		$detail['version_history'] = array();
    		$detail['transaction_history'] = array();

    		if (stripos($detailstr,'M') !== false)
    		{
    			$response = $this->get_document_metadata($session_id, $document_id);
    			$detail['metadata'] = $response->value['metadata'];
    			$detail['metadata']->name = 'metadata';
    		}
    		else
    		{
    			$detail['metadata'] = KTWebService::_encode_metadata_fieldsets($detail['metadata']);
    		}

    		if (stripos($detailstr,'L') !== false)
    		{
    			$response = $this->get_document_links($session_id, $document_id);
    			$detail['links'] = $response->value['links'];
    			$detail['links']->name = 'links';
    		}
    		else
    		{
    			$detail['links'] = KTWebService::_encode_document_links($detail['links']);
    		}

    		if (stripos($detailstr,'T') !== false)
    		{
    			$response = $this->get_document_workflow_transitions($session_id, $document_id);
    			$detail['transitions'] =  $response->value['transitions'] ;
    			$detail['transitions']->name = 'transitions';
    		}
    		else
    		{
    			$detail['transitions'] = KTWebService::_encode_document_workflow_transitions($detail['transitions']);
    		}

    		if (stripos($detailstr,'V') !== false)
    		{
    			$response = $this->get_document_version_history($session_id, $document_id);
    			$detail['version_history'] =  $response->value['history'];
    			$detail['version_history']->name = 'version_history';
    		}
    		else
    		{
    			$detail['version_history'] = KTWebService::_encode_version_history($detail['version_history'],'version_history');
    		}

    		if (stripos($detailstr,'H') !== false)
    		{
    			$response = $this->get_document_transaction_history($session_id, $document_id);
    			$detail['transaction_history'] =  $response->value['history'];
    			$detail['transaction_history']->name = 'transaction_history';
    		}
    		else
    		{
    			$detail['transaction_history'] = KTWebService::_encode_transaction_history($detail['transaction_history'],'transaction_history');
    		}

    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
    }

    function get_document_detail_by_filename($session_id, $folder_id, $filename, $detail='')
    {
    	return $this->get_document_detail_by_name($session_id, $folder_id, $filename, 'F', $detail);
    }

    function get_document_detail_by_title($session_id, $folder_id, $title, $detail='')
    {
    	return $this->get_document_detail_by_name($session_id, $folder_id,  $title, 'T', $detail);
    }


    /**
     * Returns document detail given a document name which could include a full path.
     *
     * @param string $session_id
     * @param string $document_name
     * @param string @what
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function get_document_detail_by_name($session_id, $folder_id, $document_name, $what='T', $detail='')
    {
    	$this->debug("get_document_detail_by_name('$session_id','$document_name','$what','$detail')");

    	$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER);

    	if (empty($document_name))
    	{
    		$response['message'] = 'Document_name is empty.';
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	if (!in_array($what, array('T','F')))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = 'Invalid what code';
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	if ($folder_id < 1) $folder_id = 1;
    	$root = &$kt->get_folder_by_id($folder_id);
    	if (PEAR::isError($root))
    	{
    		$this->debug("get_document_detail_by_name - cannot get root folder - folder_id = $folder_id - "  . $root->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	if ($what == 'T')
    	{
    		$document = &$root->get_document_by_name($document_name);
    	}
    	else
    	{
    		$document = &$root->get_document_by_filename($document_name);
    	}
    	if (PEAR::isError($document))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    			'message'=>$document->getMessage()
    		);
    		$this->debug("get_document_detail_by_name - cannot get document - "  . $document->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	return $this->get_document_detail($session_id, $document->documentid, $detail);
    }

 	/** Encode an array as kt_document_shortcut
	 *
	 * @param array $shortcuts
	 * @param string $name
	 * @return SOAP_Value of kt_document_shortcuts
     * @access private
     * @static
	 */
	function _encode_document_shortcuts($shortcuts, $name='shortcuts')
	{
		foreach($shortcuts as $key=>$item)
		{
			$shortcuts[$key] = new SOAP_Value('item',"{urn:$this->namespace}kt_document_shortcut", $item);
		}
		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_shortcuts", $shortcuts);
	}

    /**
     * Retrieves all shortcuts linking to a specific document
     *
     * @param string $session_id
     * @param ing $document_id
     *
	 * @return kt_document_shortcuts. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     *
     */
    function get_document_shortcuts($session_id, $document_id){
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_shortcuts", $kt);
    	}

    	$document = $kt->get_document_by_id($document_id);
    	if(PEAR::isError($document)){
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    			'message'=>$document->getMessage()
    		);
    		$this->debug("get_document_shortcuts - cannot get document - "  . $document->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_shortcuts", $response);
    	}

    	$shortcuts = $document->get_shortcuts();
    	if(PEAR::isError($shortcuts)){
    		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,$shortcuts);
    		$this->debug("get_document_shortcuts - cannot retrieve shortcuts linking to $document_id - "  . $shortcuts->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_shortcuts", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] = KTWebService::_encode_document_shortcuts($shortcuts);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_shortcuts", $response);

    }

    /**
     * Adds a document to the repository.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param string $title
     * @param string $filename
     * @param string $documenttype
     * @param string $tempfilename
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function add_document($session_id, $folder_id,  $title, $filename, $documenttype, $tempfilename)
    {
    	$this->debug("add_document('$session_id',$folder_id,'$title','$filename','$documenttype','$tempfilename')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,"Invalid temporary file: $tempfilename. Not compatible with $upload_manager->temp_dir.");

			$this->debug("add_document - Invalid temporary file: $tempfilename. Not compatible with $upload_manager->temp_dir.", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
    		$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);

			$this->debug("add_document - cannot get folder $folder_id - "  . $folder->getMessage(), $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$status = is_a($document, 'KTAPI_DocumentTypeError')?KTWS_ERR_INVALID_DOCUMENT_TYPE:KTWS_ERR_INVALID_DOCUMENT;
			$response = KTWebService::_status($status, $document);
			$this->debug("add_document - cannot add document - "  . $document->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

    	$detail = $document->get_detail();
    	$detail['status_code'] = KTWS_SUCCESS;
		$detail['message'] = '';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
    }

    function add_small_document_with_metadata($session_id, $folder_id,  $title, $filename, $documenttype, $base64, $metadata, $sysdata)
    {
		$add_result = $this->add_small_document($session_id, $folder_id, $title, $filename, $documenttype, $base64);

		$status_code = $add_result->value['status_code'];
		if ($status_code != 0)
		{
			return $add_result;
		}
		$document_id = $add_result->value['document_id'];

		$update_result = $this->update_document_metadata($session_id, $document_id, $metadata, $sysdata);
		$status_code = $update_result->value['status_code'];
		if ($status_code != 0)
		{
			$this->delete_document($session_id, $document_id, 'Rollback because metadata could not be added');
			return $update_result;
		}

		$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$document = $kt->get_document_by_id($document_id);
    	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}
		$result = $document->mergeWithLastMetadataVersion();
		if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		return $update_result;
    }

    function add_document_with_metadata($session_id, $folder_id,  $title, $filename, $documenttype, $tempfilename, $metadata, $sysdata)
    {
		$add_result = $this->add_document($session_id, $folder_id, $title, $filename, $documenttype, $tempfilename);

		$status_code = $add_result->value['status_code'];
		if ($status_code != 0)
		{
			return $add_result;
		}
		$document_id = $add_result->value['document_id'];

		$update_result = $this->update_document_metadata($session_id, $document_id, $metadata, $sysdata);
		$status_code = $update_result->value['status_code'];
		if ($status_code != 0)
		{
			$this->delete_document($session_id, $document_id, 'Rollback because metadata could not be added');
			return $update_result;
		}

		$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$document = $kt->get_document_by_id($document_id);
    	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}


		$result = $document->mergeWithLastMetadataVersion();
		if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}

		return $update_result;
    }


    /**
     * Find documents matching the document oem (integration) no
     *
     * @param string $session_id
     * @param string $oem_no
     * @param string $detail
     * @return kt_document_collection_response
     */
	function get_documents_by_oem_no($session_id, $oem_no, $detail)
	{
		$this->debug("get_documents_by_oem_no('$session_id','$oem_no', '$detail')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_collection_response", $kt);
    	}

    	$documents = $kt->get_documents_by_oem_no($oem_no);

    	$collection = array();
    	foreach($documents as $documentId)
    	{
			$detail = $this->get_document_detail($session_id, $documentId, $detail);
			if ($detail->value['status_code'] != 0)
			{
				continue;
			}
			$collection[] = $detail->value;
    	}

    	$response=array();
    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = empty($collection)?_kt('No documents were found matching the specified document no'):'';
    	$response['collection'] = new SOAP_Value('collection',"{urn:$this->namespace}kt_document_collection", $collection);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_collection_response", $response);
	}

    /**
     * Adds a document to the repository.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param string $title
     * @param string $filename
     * @param string $documenttype
     * @param string $base64
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function add_small_document($session_id, $folder_id,  $title, $filename, $documenttype, $base64)
    {
    	$this->debug("add_small_document('$session_id',$folder_id,'$title','$filename','$documenttype','*** base64 content ***')");
		$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response = KTWebService::_status(KTWS_ERR_INVALID_FOLDER,$folder);
			$this->debug("add_small_document - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

		$upload_manager = new KTUploadManager();
    	$tempfilename = $upload_manager->store_base64_file($base64);
    	if (PEAR::isError($tempfilename))
    	{
    		$reason = $tempfilename->getMessage();
    		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,'Cannot write to temp file: ' . $tempfilename . ". Reason: $reason");
			$this->debug("add_small_document - cannot write $tempfilename. Reason: $reason", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

		// simulate the upload
		$tempfilename = $upload_manager->uploaded($filename,$tempfilename, 'A');

		// add the document
    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$status = is_a($document, 'KTAPI_DocumentTypeError')?KTWS_ERR_INVALID_DOCUMENT_TYPE:KTWS_ERR_INVALID_DOCUMENT;
			$response = KTWebService::_status($status,$document);

			$this->debug("add_small_document - cannot add document - "  . $document->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

    	$detail = $document->get_detail();
    	$detail['status_code'] = KTWS_SUCCESS;
		$detail['message'] = '';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
    }

    /**
     * Does a document checkin.
     *
     * @param string $session_id
     * @param int $folder_id
     * @param string $title
     * @param string $filename
     * @param string $documenttype
     * @param string $tempfilename
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function checkin_document($session_id, $document_id,  $filename, $reason, $tempfilename, $major_update )
    {
    	$this->debug("checkin_document('$session_id',$document_id,'$filename','$reason','$tempfilename',$major_update)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (!$upload_manager->is_valid_temporary_file($tempfilename))
    	{
			$response['message'] = 'Invalid temporary file';
			$this->debug("checkin_document - $upload_manager->temp_dir != $tempdir", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
			$response['message'] = $document->getMessage();
			$this->debug("checkin_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

		// checkin
		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
			$response['message'] = $result->getMessage();
			$this->debug("checkin_document - cannot checkin - "  . $result->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

    	// get status after checkin
		return $this->get_document_detail($session_id, $document_id);
    }

    function  checkin_small_document_with_metadata($session_id, $document_id,  $filename, $reason, $base64, $major_update, $metadata, $sysdata)
    {
       	$add_result = $this->checkin_small_document($session_id, $document_id,  $filename, $reason, $base64, $major_update);

       	$status_code = $add_result->value['status_code'];
       	if ($status_code != 0)
       	{
       		return $add_result;
       	}

       	$update_result = $this->update_document_metadata($session_id, $document_id, $metadata, $sysdata);
       	$status_code = $update_result->value['status_code'];
       	if ($status_code != 0)
       	{
       		return $update_result;
       	}

       	$kt = &$this->get_ktapi($session_id );
       	if (is_array($kt))
       	{
       		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
       	}

       	$document = $kt->get_document_by_id($document_id);
       	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}
       	$result = $document->mergeWithLastMetadataVersion();
       	if (PEAR::isError($result))
       	{
       		// not much we can do, maybe just log!
       	}

       	return $update_result;
	}

    function  checkin_document_with_metadata($session_id, $document_id,  $filename, $reason, $tempfilename, $major_update, $metadata, $sysdata)
    {
       	$add_result = $this->checkin_document($session_id, $document_id,  $filename, $reason, $tempfilename, $major_update);

       	$status_code = $add_result->value['status_code'];
       	if ($status_code != 0)
       	{
       		return $add_result;
       	}

       	$update_result = $this->update_document_metadata($session_id, $document_id, $metadata, $sysdata);
       	$status_code = $update_result->value['status_code'];
       	if ($status_code != 0)
       	{
       		return $update_result;
       	}

       	$kt = &$this->get_ktapi($session_id );
       	if (is_array($kt))
       	{
       		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
       	}

       	$document = $kt->get_document_by_id($document_id);
       	$result = $document->removeUpdateNotification();
    	if (PEAR::isError($result))
		{
			// not much we can do, maybe just log!
		}
       	$result = $document->mergeWithLastMetadataVersion();
       	if (PEAR::isError($result))
       	{
       		// not much we can do, maybe just log!
       	}

       	return $update_result;
	}


    /**
     * Does a document checkin.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $filename
     * @param string $reason
     * @param string $base64
     * @param boolean $major_update
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function checkin_small_document($session_id, $document_id,  $filename, $reason, $base64, $major_update )
    {
    	$this->debug("checkin_small_document('$session_id',$document_id,'$filename','$reason','*** base64 content ***',$major_update)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$upload_manager = new KTUploadManager();
    	$tempfilename = $upload_manager->store_base64_file($base64, 'su_');
    	if (PEAR::isError($tempfilename))
    	{
			$reason = $tempfilename->getMessage();
			$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT,'Cannot write to temp file: ' . $tempfilename . ". Reason: $reason");
			$this->debug("checkin_small_document - cannot write $tempfilename. Reason: $reason", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	// simulate the upload
		$tempfilename = $upload_manager->uploaded($filename,$tempfilename, 'C');

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
			$response['message'] = $document->getMessage();
			$this->debug("checkin_small_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
			$response['message'] = $result->getMessage();
			$this->debug("checkin_small_document - cannot checkin document - "  . $result->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
		// get status after checkin
		return $this->get_document_detail($session_id, $document_id);
    }

    /**
     * Does a document checkout.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @return kt_document_detail.  status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function checkout_document($session_id, $document_id, $reason,$download=true)
    {
    	$this->debug("checkout_document('$session_id',$document_id,'$reason')");

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
			$this->debug("checkout_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
			$this->debug("checkout_document - cannot checkout - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$session = &$kt->get_session();

    	$url = '';
    	if ($download)
    	{
	    	$download_manager = new KTDownloadManager();
    		$download_manager->set_session($session->session);
    		$download_manager->cleanup();
    		$url = $download_manager->allow_download($document);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $url;

		if ($this->version >= 2)
		{
			$result = $this->get_document_detail($session_id, $document_id);
			$result->value['message'] = $url;

			return $result;
		}

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Does a document checkout.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @param boolean $download
     * @return kt_document_detail  status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function checkout_small_document($session_id, $document_id, $reason, $download)
    {
    	$this->debug("checkout_small_document('$session_id',$document_id,'$reason', $download)");

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("checkout_small_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("checkout_small_document - cannot checkout - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$content='';
    	if ($download)
    	{
    		$document = $document->document;

    		$oStorage =& KTStorageManagerUtil::getSingleton();
            $filename = $oStorage->temporaryFile($document);

    		$fp=fopen($filename,'rb');
    		if ($fp === false)
    		{
    			$response['message'] = 'The file is not in the storage system. Please contact an administrator!';
	    		$this->debug("checkout_small_document - cannot write $filename ", $session_id);
    			return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    		}
    		$content = fread($fp, filesize($filename));
    		fclose($fp);
    		$content = base64_encode($content);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $content;

		if ($this->version >= 2)
		{
			$result = $this->get_document_detail($session_id, $document_id);
			$result->value['message'] = $content;

			return $result;
		}


    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Undoes a document checkout.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @return kt_document_detail.  status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function undo_document_checkout($session_id, $document_id, $reason)
    {
    	$this->debug("undo_document_checkout('$session_id',$document_id,'$reason')");

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("undo_document_checkout - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->undo_checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();

    		$this->debug("undo_document_checkout - cannot undo checkout - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;

		if ($this->version >= 2)
		{
			return $this->get_document_detail($session_id, $document_id);
		}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Returns a reference to a file to be downloaded.
     *
     * @param string $session_id
     * @param int $document_id

     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function download_document($session_id, $document_id, $version=null)
    {
    	$this->debug("download_document('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("download_document - cannot get $document_id - "  . $document->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("download_document - cannot download - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$session = &$kt->get_session();
    	$download_manager = new KTDownloadManager();
    	$download_manager->set_session($session->session);
    	$download_manager->cleanup();
    	$url = $download_manager->allow_download($document);

    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $url;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Returns a reference to a file to be downloaded.
     *
     * @param string $session_id
     * @param int $document_id

     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function download_small_document($session_id, $document_id, $version=null)
    {
    	$this->debug("download_small_document('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("download_small_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("download_small_document - cannot download - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$content='';

    		$document = $document->document;

    		$oStorage =& KTStorageManagerUtil::getSingleton();
            $filename = $oStorage->temporaryFile($document);

    		$fp=fopen($filename,'rb');
    		if ($fp === false)
    		{
    			$response['message'] = 'The file is not in the storage system. Please contact an administrator!';
    			$this->debug("download_small_document - cannot write $filename", $session_id);
    			return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    		}
    		$content = fread($fp, filesize($filename));
    		fclose($fp);
    		$content = base64_encode($content);


    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $content;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Deletes a document.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @return kt_response
     */
    function delete_document($session_id,$document_id, $reason)
    {
    	$this->debug("delete_document('$session_id',$document_id,'$reason')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
			$this->debug("delete_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->delete($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
			$this->debug("delete_document - cannot delete - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);

    }

    /**
     * Change the document type.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $documenttype
     * @return kt_document_detail
     */
    function change_document_type($session_id, $document_id, $documenttype)
    {
    	$this->debug("change_document_type('$session_id',$document_id,'$documenttype')");

    	$kt = &$this->get_ktapi($session_id );

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();

    		$this->debug("change_document_type - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->change_document_type($documenttype);
		if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_DOCUMENT_TYPE;
    		$response['message'] = $result->getMessage();
    		$this->debug("change_document_type - cannot change type - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Copy a document to another folder.
     *
     * @param string $session_id
     * @param int $document_id
     * @param int $folder_id
     * @param string $reason
     * @param string $newtitle
     * @param string $newfilename
     * @return kt_document_detail
     */
 	function copy_document($session_id,$document_id,$folder_id,$reason,$newtitle=null,$newfilename=null)
 	{
    	$this->debug("copy_document('$session_id',$document_id,$folder_id,'$reason','$newtitle','$newfilename')");

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("copy_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("copy_document - cannot get folderid $folder_id - "  . $tgt_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->copy($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("copy_document - cannot copy - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	if ($this->version >= 2)
    	{
    		$new_document_id = $result->documentid;
    		return $this->get_document_detail($session_id, $new_document_id, '');
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
 	}

 	/**
 	 * Move a folder to another location.
 	 *
 	 * @param string $session_id
 	 * @param int $document_id
 	 * @param int $folder_id
 	 * @param string $reason
 	 * @param string $newtitle
 	 * @param string $newfilename
 	 * @return kt_response
 	 */
 	function move_document($session_id,$document_id,$folder_id,$reason,$newtitle=null,$newfilename=null)
 	{
    	$this->debug("move_document('$session_id',$document_id,$folder_id,'$reason','$newtitle','$newfilename')");
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("move_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	if ($document->ktapi_folder->folderid != $folder_id)
    	{
		// we only have to do something if the source and target folders are different

    	$tgt_folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("move_document - cannot get folderid $folder_id - "  . $tgt_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->move($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("move_document - cannot move - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id, '');
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
 	}

 	/**
 	 * Changes the document title.
 	 *
 	 * @param string $session_id
 	 * @param int $document_id
 	 * @param string $newtitle
 	 * @return kt_response
 	 */
 	function rename_document_title($session_id,$document_id,$newtitle)
 	{
    	$this->debug("rename_document_title('$session_id',$document_id,'$newtitle')");
    	$kt = &$this->get_ktapi($session_id );

    	$responseType = 'kt_response';
    	if ($this->version >=2)
    	{
    		$responseType='kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("rename_document_title - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->rename($newtitle);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("rename_document_title - cannot rename - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
 	}

 	/**
 	 * Renames the document filename.
 	 *
 	 * @param string $session_id
 	 * @param int $document_id
 	 * @param string $newfilename
 	 * @return kt_response
 	 */
 	function rename_document_filename($session_id,$document_id,$newfilename)
 	{
    	$this->debug("rename_document_filename('$session_id',$document_id,'$newfilename')");

    	$kt = &$this->get_ktapi($session_id );
    	$responseType = 'kt_response';
    	if ($this->version >=2)
    	{
    		$responseType='kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("rename_document_filename - cannot get documetid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->renameFile($newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("rename_document_filename - cannot rename - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
 	}

    /**
     * Changes the owner of a document.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $username
     * @param string $reason
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function change_document_owner($session_id, $document_id, $username, $reason)
    {
    	$this->debug("change_document_owner('$session_id',$document_id,'$username','$reason')");

    	$kt = &$this->get_ktapi($session_id );

    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("change_document_owner - cannot get documetid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->change_owner($username,  $reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("change_document_owner - cannot change owner - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Start a workflow on a document
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $workflow
     * @return kt_document_detail
     */
    function start_document_workflow($session_id,$document_id,$workflow)
    {
    	$this->debug("start_document_workflow('$session_id',$document_id,'$workflow')");

    	$kt = &$this->get_ktapi($session_id );
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_WORKFLOW);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("start_document_workflow - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = &$document->start_workflow($workflow);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("start_document_workflow - cannot start workflow - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

		if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

	/**
	 * Removes the workflow process on a document.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_document_detail
	 */
    function delete_document_workflow($session_id,$document_id)
    {
    	$this->debug("delete_document_workflow('$session_id',$document_id)");
    	$kt = &$this->get_ktapi($session_id );
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("delete_document_workflow - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->delete_workflow();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("delete_document_workflow - cannot stop workflow - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Starts a transitions on a document with a workflow.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $transition
     * @param string $reason
     * @return kt_response
     */
    function perform_document_workflow_transition($session_id,$document_id,$transition,$reason)
    {
    	$this->debug("perform_document_workflow_transition('$session_id',$document_id,'$transition','$reason')");

    	$kt = &$this->get_ktapi($session_id );
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("perform_document_workflow_transition - cannot get document - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->perform_workflow_transition($transition,$reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("perform_document_workflow_transition - cannot perform transition - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	if ($this->version >= 2)
    	{
    		return $this->get_document_detail($session_id, $document_id);
    	}

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    }

    /**
     * Encode an array as kt_metadata_fields
     *
     * @param array $fields
     * @param string $name
     * @return SOAP_Value of kt_metadata_fields
     * @access private
     * @static
     */
    function _encode_metadata_fields($fields)
    {
    	foreach($fields as $key=>$field)
    	{
    		$selection = $field['selection'];
    		foreach($selection as $skey=>$sitem)
    		{
    			if (!is_null($item['id']))
    			{
    				$sitem['id'] = (int) $sitem['id'];
    			}

		    	if (!is_null($sitem['parent_id']))
    			{
		    		$sitem['parent_id'] = (int) $sitem['parent_id'];
    			}
    			$selection[$skey] = new SOAP_Value('item',"{urn:$this->namespace}kt_metadata_selection_item", $sitem);
    		}

			$field['selection'] = new SOAP_Value('selection',"{urn:$this->namespace}kt_metadata_selection", $selection);

   			$field['required'] = is_null($field['required'])?false:(bool) $field['required'];

    		$fields[$key] = new SOAP_Value('field',"{urn:$this->namespace}kt_metadata_field", $field);
    	}

    	return new SOAP_Value('fields',"{urn:$this->namespace}kt_metadata_fields", $fields);
    }

    /**
     * Encode an array as kt_metadata_fieldset
     *
     * @param array $fieldset
     * @param string $name
     * @return SOAP_Value of kt_metadata_fieldset
     * @access private
     * @static
     */
    function _encode_metadata_fieldset($fieldset, $name='fieldset')
    {
    	if (!empty($fieldset['fields']))
    	{
    		$fieldset['fields'] = KTWebService::_encode_metadata_fields($fieldset['fields']);
    	}
    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_fieldset", $fieldset);
    }

    /**
     * Encode an array as kt_metadata_fieldsets
     *
     * @param array $fieldsets
     * @param string $name
     * @return SOAP_Value of kt_metadata_fieldsets
     * @access private
     * @static
     */
    function _encode_metadata_fieldsets($fieldsets, $name='metadata')
    {
    	$encoded=array();
    	foreach($fieldsets as $fieldset)
    	{
    		$encoded[] = KTWebService::_encode_metadata_fieldset($fieldset);
    	}
    	if (empty($encoded))
    	{
    		$encoded=null;
    	}
    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_fieldsets", $encoded);
    }

    /**
     * Encodes an array into a kt_metadata_response
     *
     * @param array $response
     * @param string $name
     * @return SOAP_Value of kt_metadata_response
     * @access private
     * @static
     */
    function _encode_metadata_response($response, $name='return')
    {
        if (empty($response['metadata']))
        {
    	    $response['metadata'] = array();
        }
    	else
    	{
            $response['metadata'] = KTWebService::_encode_metadata_fieldsets($response['metadata']);
    	}

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_response", $response);
    }

    /**
     * Returns metadata fields required for a specific document type
     *
     * @param string $session_id
     * @param string $document_type
     * @return kt_metadata_response
     */
    function get_document_type_metadata($session_id, $document_type)
	{
    	$this->debug("get_document_type_metadata('$session_id','$document_type')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT_TYPE);

    	$metadata = $kt->get_document_type_metadata($document_type);

    	if (PEAR::isError($metadata))
    	{
    		$response['message'] = $metadata->getMessage();
    		$this->debug("get_document_type_metadata - cannot get document type metadata - "  . $metadata->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $response);
    	}

		$num_metadata=count($metadata);
		for($i=0;$i<$num_metadata;$i++)
		{
			$num_fields = count($metadata[$i]['fields']);
			for($j=0;$j<$num_fields;$j++)
			{
				$selection=$metadata[$i]['fields'][$j]['selection'];
				$new = array();

				foreach($selection as $item)
				{
					$new[] = array(
						'id'=>null,
						'name'=>$item,
						'value'=>$item,
						'parent_id'=>null
					);
				}
				$metadata[$i]['fields'][$j]['selection'] = $new;
			}
		}

    	$response = array(
    		'status_code' => KTWS_SUCCESS,
    		'message' => '',
    		'metadata' => $metadata);

    	return KTWebService::_encode_metadata_response($response);
	}


    /**
     * Returns the metadata on a document.
     *
     * @param string $session_id
     * @param int $document_id
     * @return kt_metadata_response
     */
	function get_document_metadata($session_id,$document_id)
	{
    	$this->debug("get_document_metadata('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("get_document_metadata - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_metadata_response", $response);
    	}

    	$metadata = $document->get_metadata();

		$num_metadata=count($metadata);
		for($i=0;$i<$num_metadata;$i++)
		{
			$num_fields = count($metadata[$i]['fields']);
			for($j=0;$j<$num_fields;$j++)
			{
				$selection=$metadata[$i]['fields'][$j]['selection'];
				$new = array();

				foreach($selection as $item)
				{
					$new[] = array(
						'id'=>null,
						'name'=>$item,
						'value'=>$item,
						'parent_id'=>null
					);
				}
				$metadata[$i]['fields'][$j]['selection'] = $new;
			}
		}

    	$response = array(
    		'status_code' => KTWS_SUCCESS,
    		'message' => '',
    		'metadata' => $metadata);

    	return KTWebService::_encode_metadata_response($response);
	}

	/**
	 * Updates document metadata.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @param array $metadata
	 * @return kt_document_detail
	 */
	function update_document_metadata($session_id,$document_id,$metadata, $sysdata=null)
	{
    	$this->debug("update_document_metadata('$session_id',$document_id,$metadata, $sysdata)");

    	$kt = &$this->get_ktapi($session_id );
    	$responseType = 'kt_response';
    	if ($this->version >= 2)
    	{
    		$responseType = 'kt_document_detail';
    	}

    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("update_document_metadata - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	$result = $document->update_metadata($metadata);
    	if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("update_document_metadata - cannot update metadata - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    	}

    	if ($this->version >= 2)
    	{
    		$result = $document->update_sysdata($sysdata);
    		if (PEAR::isError($result))
    		{
   	 			$response['message'] = $result->getMessage();
    			$this->debug("update_document_metadata - cannot update sysdata - "  . $result->getMessage(), $session_id);
    			return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
    		}

    		return $this->get_document_detail($session_id, $document_id, 'M');
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}$responseType", $response);
	}

	/**
	 * Returns a list of available workflows
	 *
	 * @param string $session_id
	 * @return kt_response
	 */
	function get_workflows($session_id)
	{
		$this->debug("get_workflows('$session_id')");
    	$kt = &$this->get_ktapi($session_id );
		if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_workflows_response", $kt);
    	}

		$response = KTWebService::_status(KTWS_ERR_PROBLEM);

		$result = $kt->get_workflows();
    	if (PEAR::isError($result))
    	{
    	    $response['message']= $result->getMessage();
    		$this->debug("get_workflows - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_workflows_response", $response);
    	}

   		$response['status_code']= KTWS_SUCCESS;
   		$response['workflows']= $result;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_workflows_response", $response);
	}

	/**
	 * Returns a list of available transitions on a give document with a workflow.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_workflow_transitions_response
	 */
	function get_document_workflow_transitions($session_id, $document_id)
	{
		$this->debug("get_document_workflow_transitions('$session_id',$document_id)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_workflow_transitions_response", $kt);
    	}
		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("get_document_workflow_transitions - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_workflow_transitions_response", $response);
    	}

    	$result = $document->get_workflow_transitions();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("get_document_workflow_transitions - cannot get transitions - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_workflow_transitions_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['transitions'] = KTWebService::_encode_document_workflow_transitions($result);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_workflow_transitions_response", $response);
	}

	function _encode_document_workflow_transitions($transitions, $name='transitions')
	{
		return new SOAP_Value($name,"{urn:$this->namespace}kt_workflow_transitions", $transitions);
	}

	/**
	 * Returns the current state that the document is in.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_response
	 */
	function get_document_workflow_state($session_id, $document_id)
	{
		$this->debug("get_document_workflow_state('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("get_document_workflow_state - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$result = $document->get_workflow_state();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("get_document_workflow_state - cannot get state - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['message'] = $result;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
	}

	/**
	 * Encode an array as kt_document_transaction_history
	 *
	 * @param array $history
	 * @param string $name
	 * @return SOAP_Value of kt_document_transaction_history
     * @access private
     * @static
	 */
	function _encode_transaction_history($history, $name='history')
	{
		foreach($history as $key=>$item)
		{
			$history[$key] = new SOAP_Value('item',"{urn:$this->namespace}kt_document_transaction_history_item", $item);
		}
		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_transaction_history", $history);
	}

	/**
	 * Returns the document transaction history.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_document_transaction_history_response
	 */
	function get_document_transaction_history($session_id, $document_id)
	{
		$this->debug("get_document_transaction_history('$session_id',$document_id)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_transaction_history_response", $kt);
    	}
		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("get_document_transaction_history - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_transaction_history_response", $response);
    	}

    	$result = $document->get_transaction_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("get_document_transaction_history - cannot get history - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_transaction_history_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] = KTWebService::_encode_transaction_history($result);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_transaction_history_response", $response);
	}

	/**
	 * Encode an array as kt_document_version_history
	 *
	 * @param array $history
	 * @param string $name
	 * @return SOAP_Value of kt_document_version_history
     * @access private
     * @static
	 */
	function _encode_version_history($history, $name='history')
	{
		foreach($history as $key=>$item)
		{
			$history[$key] = new SOAP_Value('item',"{urn:$this->namespace}kt_document_version_history_item", $item);
		}
		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_version_history", $history);
	}

	/**
	 * Returns the version history.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_document_version_history_response
	 */
	function get_document_version_history($session_id, $document_id)
	{
		$this->debug("get_document_version_history('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_version_history_response", $kt);
    	}
		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("get_document_version_history - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_version_history_response", $response);
    	}

    	$result = $document->get_version_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("get_document_version_history - cannot get history - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_version_history_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] =KTWebService::_encode_version_history($result);

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_version_history_response", $response);
	}

	/**
	 * Returns a list of linked documents
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_linked_documents_response
	 *
	 * kt_linked_document_response
	 */
	function get_document_links($session_id, $document_id)
	{
		$this->debug("get_document_links('$session_id',$document_id)");

		$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>'',
    		'parent_document_id' => (int) $document_id,
    		'links'=>array()
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("get_document_links - cannot get documentid $document_id  - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$links = $document->get_linked_documents();
   		$response['links'] = KTWebService::_encode_document_links($links);
   		$response['status_code'] = KTWS_SUCCESS;

		return new SOAP_Value('return',"{urn:$this->namespace}kt_linked_document_response", $response);
	}

	function _encode_document_links($links, $name='links')
	{
		foreach($links as $key=>$link)
		{
			$link['document_id'] = (int) $link['document_id'];
			$link['filesize'] = (int) $link['filesize'];

			$links[$key] = new SOAP_Value('links',"{urn:$this->namespace}kt_linked_document", $link);
		}

		return new SOAP_Value($name,"{urn:$this->namespace}kt_linked_documents", $links);
	}

	/**
	 * Removes a link between documents
	 *
	 * @param string $session_id
	 * @param int $parent_document_id
	 * @param int $child_document_id
	 * @return kt_response
	 */
	function unlink_documents($session_id, $parent_document_id, $child_document_id)
	{
		$this->debug("unlink_documents('$session_id',$parent_document_id,$child_document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($parent_document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("unlink_documents - cannot get documentid $parent_document_id  - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$child_document = &$kt->get_document_by_id($child_document_id);
		if (PEAR::isError($child_document))
    	{
    		$response['message'] = $child_document->getMessage();
    		$this->debug("unlink_documents - cannot get documentid $child_document_id  - "  . $child_document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->unlink_document($child_document);
    	if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("unlink_documents - unlink  - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
	}

	/**
	 * Creates a link between documents
	 *
	 * @param string $session_id
	 * @param int $parent_document_id
	 * @param int $child_document_id
	 * @param string $type
	 * @return kt_response
	 */
	function link_documents($session_id, $parent_document_id, $child_document_id, $type)
	{
		$this->debug("link_documents('$session_id',$parent_document_id,$child_document_id,'$type')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response = KTWebService::_status(KTWS_ERR_INVALID_DOCUMENT);

    	$document = &$kt->get_document_by_id($parent_document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("link_documents - cannot get documentid $parent_document_id  - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$child_document = &$kt->get_document_by_id($child_document_id);
		if (PEAR::isError($child_document))
    	{
    		$response['message'] = $child_document->getMessage();
    		$this->debug("link_documents - cannot get documentid $child_document_id  - "  . $child_document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->link_document($child_document, $type);
    	if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("link_documents - cannot link  - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
	}

	function _encode_client_policies($policies)
    {
    	foreach($policies as $key=>$policy)
    	{
    		$policies[$key] = new SOAP_Value('policy',"{urn:$this->namespace}kt_client_policy", $policy);
    	}

    	return new SOAP_Value('policies',"{urn:$this->namespace}kt_client_policies_array", $policies);
    }

	/**
	 * Retrieves the server policies for this server
	 *
	 * @param string $session_id
	 * @return kt_client_policies_response
	 */
	function get_client_policies($session_id, $client=null)
	{
		$this->debug("get_client_policies('$session_id')");
		$config = KTConfig::getSingleton();

		$policies = array(
					array(
						'name' => 'explorer_metadata_capture',
						'value' => bool2str($config->get('clientToolPolicies/explorerMetadataCapture')),
						'type' => 'boolean'
					),
					array(
						'name' => 'office_metadata_capture',
						'value' => bool2str($config->get('clientToolPolicies/officeMetadataCapture')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_delete',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsDelete')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_checkin',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCheckin')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_checkout',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCheckout')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_cancelcheckout',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCancelCheckout')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_copyinkt',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsCopyInKT')),
						'type' => 'boolean'
					),
					array(
						'name' => 'capture_reasons_moveinkt',
						'value' => bool2str($config->get('clientToolPolicies/captureReasonsMoveInKT')),
						'type' => 'boolean'
					),
					array(
						'name' => 'allow_remember_password',
						'value' => bool2str($config->get('clientToolPolicies/allowRememberPassword')),
						'type' => 'boolean'
					),
				);

		$response['policies'] = $this->_encode_client_policies($policies);
		$response['message'] = 'Knowledgetree client policies retrieval succeeded.';
		$response['status_code'] = KTWS_SUCCESS;

		return new SOAP_Value('return',"{urn:$this->namespace}kt_client_policies_response", $response);
	}

	/**
	 * This is the search interface
	 *
	 * @param string $session_id
	 * @param string $query
	 * @param string $options
	 * @return kt_search_response
	 */
	function search($session_id, $query, $options)
	{
		$this->debug("search('$session_id','$query','$options')");

		$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_search_response", $kt);
    	}

    	$response = KTWebService::_status(KTWS_ERR_PROBLEM);
		$response['hits'] = array();

    	if (!defined('HAS_SEARCH_FUNCTIONALITY'))
    	{
    		$response['message'] = _kt('Search has not been implemented for this version of KnowledgeTree');
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_search_response", $response);
    	}

		$results = processSearchExpression($query);
		if (PEAR::isError($results))
		{
			$response['message'] = _kt('Could not process query.')  . $results->getMessage();
			$results = array();
		}
		else
		{
			foreach($results as $key=>$item)
			{
				$results[$key] = new SOAP_Value('item',"{urn:$this->namespace}kt_search_result_item", $item);
			}
			$response['message'] = '';
    		$response['status_code'] = KTWS_SUCCESS;

		}
		$response['hits'] = new SOAP_Value('hits',"{urn:$this->namespace}kt_search_results", $results);

		return new SOAP_Value('return',"{urn:$this->namespace}kt_search_response", $response);
	}

	/**
	 * The main json request processing function.
	 *
	 * The method name of the method to be called must be $_POST['_method'].
	 * All parameters may be set in any order, as long as the names correspond with the wdsl names.
	 *
	 */
	function runJSON()
	{
		// Let us check that all the POST variables that are expected are set
		if ($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			print json_encode(array('msg'=>'Post request must be made!'));
			return;
		}
		$method = '';
		if (array_key_exists('_method',$_POST))
		{
			$method=$_POST['_method'];
		}
		if ($method == '' || !array_key_exists($method,$this->__dispatch_map))
		{
			print json_encode(array('msg'=>'Method must exist!'));
			return;
		}

		// let us check that we have all the variables for the method to be called.
		$dispatcher = $this->__dispatch_map[$method];
		$params = array();
		foreach($dispatcher['in'] as $var=>$type)
		{
			$param = array('var'=>$var,  'set'=>0);

			if (array_key_exists($var, $_POST))
			{
				$param['set'] = 1;

				// if it looks json like we should decode it
				if (substr($_POST[$var],0,1) == '{' && substr($_POST[$var],-1) == '}')
				{
					$original = $_POST[$var];
					$decoded = json_decode($original);

					$_POST[$var] = is_null($decoded)?$original:$decoded;
					unset($original);
					unset($decoded);
				}
			}

			$params[] = $param;
		}

		// prepare the parameters and call the method
		// by passing references to $POST we hopefully save some memory

		$paramstr = '';
		foreach($params as $param)
		{
			$var = $param['var'];
			if ($param['set'] == 0)
			{
				print json_encode(array('msg'=>"'$var' is not set!"));
				return;
			}
			if ($paramstr != '') $paramstr .= ',';

			$paramstr .= "\$_POST['$var']";
		}

		$result = eval("return \$this->$method($paramstr);");

		// return the json encoded result
		print json_encode(KTWebService::decodeSOAPValue($result));
	}

	/**
	 * Returns a decoded soap value structure.
	 *
	 * @param SOAP_Value $value
	 * @return mixed
	 */
	function decodeSOAPValue($value)
	{
		if (is_a($value, 'SOAP_Value'))
		{
			$x = new stdClass();
			$v = & $value->value;
			$vars = array_keys($v);

			foreach($vars as $var)
			{
				$x->$var = KTWebService::decodeSOAPValue($v[$var]);
			}

			return $x;
		}
		else
		{
			return $value;
		}
	}

    /**
     * This runs the web service
     *
     * @static
     * @access public
     */
    function run()
    {
    	if (defined('JSON_WEBSERVICE'))
		{
			$this->runJSON();
			return;
		}

    	ob_start();
    	$server = new SOAP_Server();

    	$server->addObjectMap($this, 'http://schemas.xmlsoap.org/soap/envelope/');
    	$request = 'Not Set';

    	if (isset($_SERVER['REQUEST_METHOD'])  && $_SERVER['REQUEST_METHOD']=='POST')
    	{
    		$request = file_get_contents("php://input");

    		$server->service($request);
    	}
    	else
    	{
    		// Create the DISCO server
    		$disco = new SOAP_DISCO_Server($server, $this->namespace);
    		header("Content-type: text/xml");
    		if (isset($_SERVER['QUERY_STRING']) && strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0)
    		{
    			echo $disco->getWSDL();
    		}
    		else
    		{
    			echo $disco->getDISCO();
    		}
    	}
    	$capture = ob_get_flush();
		$this->debug($request,'request', 5);
    	$this->debug($capture,'response',5);
    	global $_KT_starttime;
    	$time = number_format(KTUtil::getBenchmarkTime() - $_KT_starttime,2);
    	$this->debug($time, 'time from start',4);
    }

    function __dispatch($methodname)
    {
        if (isset($this->__dispatch_map[$methodname]))
        {
        	return $this->__dispatch_map[$methodname];
        }
        return NULL;
    }

}

$webservice = new KTWebService();
$webservice->run();

?>
