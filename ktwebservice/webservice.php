<?php

/**
 *
 * $Id:$
 *
 * This implements the KnowledgeTree Web Service in SOAP.
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
require_once(KT_DIR . '/search2/search/search.inc.php');

// TODO: Test getting files/metadata based on versioning works and implementation is consistent.

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
define('KTWS_ERR_PROBLEM',					98);
define('KTWS_ERR_DB_PROBLEM',				99);


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



    function KTWebService()
    {
    	// Caching was giving some problems, so disable it.

    	$config = &KTConfig::getSingleton();
    	$this->mustDebug = $config->get('webservice/debug', false);
    	$cache_enabled = $config->get('cache/cacheEnabled');
    	if ($cache_enabled)
    	{
			$this->error('Cache is enabled. This is likely to cause problems!', 'constructor');
    	}
    	$config->setns('cache','cacheEnabled',false);

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
        	  //  'metadata' => "{urn:$this->namespace}kt_metadata_fieldsets",
        	  // 'owner' => 'string',
         	);

        $this->__typedef["{urn:$this->namespace}kt_search_result_item"] =
         	array(
				'document_id' => 'int',
				'relevance' => 'float',
        		'text' => 'string',
				'title' => 'string',
        		'fullpath' => 'string',
        		'filesize' => 'int',
        		'version' => 'string',
        		'filename' => 'string',
        		'folder_id' => 'int',
        		'workflow' => 'string',
        		'workflow_state' => 'string',
        		'mime_type' => 'string',
        		'owner' => 'string',
        		'created_by' => 'string',
        		'created_date' => 'string',
        		'modified_by' => 'string',
        		'modified_date' => 'string',
        		'checked_out_by' => 'string',
        		'checked_out_date' => 'string',
        		'is_immutable' => 'bool',
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


    	$this->__typedef["{urn:$this->namespace}kt_metadata_field"] =
         	array(
				'name' => 'string',
        		'required' => 'boolean' ,
        		'value' => 'string' ,
        		'description' => 'string' ,
        		'control_type' => 'string' ,
        		'selection' => "{urn:$this->namespace}kt_metadata_selection"
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

    	$this->__typedef["{urn:$this->namespace}kt_document_transaction_history_item"] =
         	array(
         		'transaction_name'=>'string',
         		'username'=>'string',
         		'version' => 'string',
         		'comment' => 'string',
         		'datetime' => 'string',

         		);

    	$this->__typedef["{urn:$this->namespace}kt_linked_document"] =
         	array(
         		'document_id'=>'int',
         		'title'=>'string',
         		'size' => 'int',
         		'workflow' => 'string',
         		'workflow_state' => 'string',
         		'link_type' => 'string',

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
         		'parent_document_id' => 'string',
         		'links' => "{urn:$this->namespace}kt_linked_documents"
         		);

        $this->__typedef["{urn:$this->namespace}kt_document_transaction_history"] =
			array(
            	array(
                        'history' => "{urn:$this->namespace}kt_document_transaction_history_item"
                  )
            );

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

         $this->__dispatch_map['search'] =
            array('in' => array('session_id' => 'string', 'search'=>'string' ,'options'=>'string'),
                  'out' => array('return' => "{urn:$this->namespace}kt_search_response" ),
            );

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

         // get_folder_detail_by_name
         $this->__dispatch_map['get_folder_detail_by_name'] =
            array('in' => array('session_id' => 'string', 'folder_name' => 'string' ),
             'out' => array('return' => "{urn:$this->namespace}kt_folder_detail"),
            );

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

         // move_folder
         $this->__dispatch_map['move_folder'] =
            array('in' => array('session_id'=>'string','source_id'=>'int','target_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

		// get_document_detail
         $this->__dispatch_map['get_document_detail'] =
            array('in' => array('session_id' => 'string', 'document_id' => 'int' ),
             'out' => array('return' => "{urn:$this->namespace}kt_document_detail"),
            );

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


         // get_document_detail_by_name
         $this->__dispatch_map['get_document_detail_by_name'] =
            array('in' => array('session_id' => 'string', 'document_name' => 'string', 'what'=>'string' ),
             'out' => array('return' => "{urn:$this->namespace}kt_document_detail"),
            );

          // checkout_document
           $this->__dispatch_map['checkout_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

          // checkout_small_document
           $this->__dispatch_map['checkout_small_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string','download' => 'boolean'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

          // checkout_base64_document
           $this->__dispatch_map['checkout_base64_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string','download' => 'boolean'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
              'alias' => 'checkout_small_document'
            );

            // undo_document_checkout
            $this->__dispatch_map['undo_document_checkout'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','reason' =>'string'),
             'out' => array('return' => "{urn:$this->namespace}kt_response" ),
            );

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

            // copy_document
			$this->__dispatch_map['copy_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','folder_id'=>'int','reason'=>'string','newtitle'=>'string','newfilename'=>'string'),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // move_document
			$this->__dispatch_map['move_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','folder_id'=>'int','reason'=>'string','newtitle'=>'string','newfilename'=>'string'),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );
			// rename_document_title
            $this->__dispatch_map['rename_document_title'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'newtitle'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // rename_document_filename
            $this->__dispatch_map['rename_document_filename'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'newfilename'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // change_document_type
			$this->__dispatch_map['change_document_type'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'documenttype'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // start_document_workflow
            $this->__dispatch_map['start_document_workflow'] =
            array('in' => array('session_id'=>'string','document_id'=>'int', 'workflow'=>'string' ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );
            // delete_document_workflow
            $this->__dispatch_map['delete_document_workflow'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'  ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // perform_document_workflow_transition
            $this->__dispatch_map['perform_document_workflow_transition'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','transition'=>'string','reason'=>'string'  ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );

            // get_document_metadata
            $this->__dispatch_map['get_document_metadata'] =
            array('in' => array('session_id'=>'string','document_id'=>'int'   ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_metadata_response" ),
            );

            // get_document_type_metadata
            $this->__dispatch_map['get_document_type_metadata'] =
            array('in' => array('session_id'=>'string','document_type'=>'string'   ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_metadata_response" ),
            );
            //update_document_metadata
            $this->__dispatch_map['update_document_metadata'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets"  ),
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );


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


    }

    function debug($msg, $function = null)
    {
    	if ($this->mustDebug)
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


    /**
     * This is used by all exposed functions dependant on the sessionid.
     *
     * @param string $session_id
     * @return KTAPI This could be KTAPI or kt_response array with status_code of KTWS_ERR_INVALID_SESSION.
     */
    function &get_ktapi($session_id)
    {
    	$kt = new KTAPI();

    	$session = $kt->get_active_session($session_id, null);

    	if ( PEAR::isError($session))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_SESSION,
    			'message'=>$session->getMessage()
    		);

            return $response;
    	}
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
    	$response = array(
    		'status_code'=>KTWS_ERR_AUTHENTICATION_ERROR,
    		'message'=>'',
    	);

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
    	$response = array(
    		'status_code'=>KTWS_ERR_AUTHENTICATION_ERROR,
    		'message'=>'',
    	);

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

    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_SESSION,
    		'message'=>''
    	);

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
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
    		$this->debug("get_folder_detail - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $folder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);
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
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
    		$this->debug("get_folder_detail_by_name - cannot get folder $folder_name - "  . $folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $folder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);

    }

    /**
     * Encodes an array as kt_folder_item
     *
     * @param array $item
     * @param string $name
     * @return SOAP_Value of kt_folder_item
     * @access private
     * @static
     */
    function _encode_folder_item($item, $name='item')
    {
    	$item['id'] = (int) $item['id'];

    	if (!empty($item['items']))
    	{
    		$item['items'] = KTWebService::_encode_folder_items($item['items']);
    	}

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_folder_item", $item);
    }

    /**
     * Encodes an array as kt_folder_items
     *
     * @param array $items
     * @param string $name
     * @return SOAP_Value of kt_folder_items
     * @access private
     * @static
     */
    function _encode_folder_items($items, $name='items')
    {
    	$encoded=array();
    	foreach($items as $item)
    	{
    		$encoded[] = KTWebService::_encode_folder_item($item);
    	}

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_folder_items", $encoded);
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
    function _encode_folder_contents($contents, $name='return')
    {
    	$contents['items'] = KTWebService::_encode_folder_items($contents['items']);
    	return new SOAP_Value($name,"{urn:$this->namespace}kt_folder_contents", $contents);
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
    function get_folder_contents($session_id, $folder_id, $depth=1, $what='DF')
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
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
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
    		'items'=>$listing
    	);

    	return KTWebService::_encode_folder_contents($contents);
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
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
    		$this->debug("create_folder - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$newfolder = &$folder->add_folder($folder_name);
    	if (PEAR::isError($newfolder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$newfolder->getMessage()
    		);
    		$this->debug("create_folder - cannot create folder $folder_name - "  . $folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}

    	$detail = $newfolder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $detail);
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

		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	);

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

    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	);

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
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	);

    	$src_folder = &$kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		$this->debug("copy_folder - cannot get source folderid $source_id - "  . $src_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("copy_folder - cannot get target folderid $target_id - "  . $tgt_folder->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result= $src_folder->copy($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("copy_folder - copy to target folder - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code']= KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	);

    	$src_folder = &$kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		$this->debug("move_folder - cannot get source folder $source_id - "  . $src_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("move_folder - cannot get target folder $target_id - "  . $tgt_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $src_folder->move($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    		$this->debug("move_folder - cannot move folder - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code']= KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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

    	$response=array(
    		'status_code'=>KTWS_ERR_PROBLEM,
    		'message'=>''
    	);

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

    	$response=array(
    		'status_code'=>KTWS_ERR_PROBLEM,
    		'message'=>''
    	);

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
    function get_document_detail($session_id, $document_id)
    {
    	$this->debug("get_document_detail('$session_id',$document_id)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$document = $kt->get_document_by_id($document_id);
    	if (PEAR::isError($document))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    			'message'=>$document->getMessage()
    		);
    		$this->debug("get_document_detail - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

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
    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
    }

    /**
     * Returns document detail given a document name which could include a full path.
     *
     * @param string $session_id
     * @param string $document_name
     * @param string @what
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function get_document_detail_by_name($session_id, $document_name, $what='T')
    {
    	$this->debug("get_document_detail_by_name('$session_id','$document_name','$what')");
		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>''
    		);
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

    	$root = &$kt->get_root_folder();
    	if (PEAR::isError($root))
    	{
    		$this->debug("get_document_detail_by_name - cannot get root folder - "  . $root->getMessage(), $session_id);

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

    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $detail->getMessage();

    		$this->debug("get_document_detail_by_name - cannot get document detail - "  . $detail->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
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
    	$tempdir = substr($tempfilename,0,strlen($upload_manager->temp_dir));
    	if ($tempdir != $upload_manager->temp_dir)
    	{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>'Invalid temporary file.'
			);

			$this->debug("add_document - $upload_manager->temp_dir != $tempdir", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>$folder->getMessage()
			);

			$this->debug("add_document - cannot get folder $folder_id - "  . $folder->getMessage(), $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>$document->getMessage()
			);

			$this->debug("add_document - cannot add document - "  . $document->getMessage(), $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

    	$detail = $document->get_detail();
    	$detail['status_code'] = KTWS_SUCCESS;
		$detail['message'] = '';

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $detail);
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

    	// create a temporary file
		$oConfig = KTConfig::getSingleton();
		$tmp_dir = $oConfig->get('webservice/uploadDirectory');

		$tempfilename = tempnam($tmp_dir,'sa_');
		if (!is_writable($tempfilename))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>'Cannot write to temp folder: ' + $tempfilename
			);
			$this->debug("add_small_document - cannot write $tempfilename", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	$tempdir = substr($tempfilename,0,strlen($upload_manager->temp_dir));
		if ( $tempdir  != $upload_manager->temp_dir)
    	{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>'Invalid temporary file.'
			);

			$this->debug("add_small_document - $upload_manager->temp_dir != $tempdir ", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>$folder->getMessage()
			);
			$this->debug("add_small_document - cannot get folderid $folder_id - "  . $folder->getMessage(), $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

		// write to the temporary file
		$fp=fopen($tempfilename, 'wb');
		if ($fp === false)
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>'Cannot write to temp file: ' + $tempfilename
			);
			$this->debug("add_small_document - cannot get folderid $folder_id" , $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
		fwrite($fp, base64_decode($base64));
		fclose($fp);

		// simulate the upload
		$upload_manager->uploaded($filename,$tempfilename, 'A');

		// add the document
    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>$document->getMessage()
			);
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

    	$response=array(
			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
			'message'=>'',
		);

		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	$tempdir = substr($tempfilename,0,strlen($upload_manager->temp_dir));
    	if ($tempdir != $upload_manager->temp_dir)
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

		$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
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
     * @return kt_document_detail. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function checkin_small_document($session_id, $document_id,  $filename, $reason, $base64, $major_update )
    {
    	$this->debug("checkin_small_document('$session_id',$document_id,'$filename','$reason','*** base64 content ***',$major_update)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt);
    	}

    	$response=array(
			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
			'message'=>'',
		);

		// create a temporary file
		$oConfig = KTConfig::getSingleton();
		$tmp_dir = $oConfig->get('webservice/uploadDirectory');

		$tempfilename = tempnam($tmp_dir,'su_');
		if (!is_writable($tempfilename))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>'Cannot write to temp folder: ' + $tempfilename
			);

			$this->debug("checkin_small_document - $tempfilename is not writable", $session_id);

			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}

		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	$tempdir = substr($tempfilename,0,strlen($upload_manager->temp_dir));
    	if ($tempdir != $upload_manager->temp_dir)
    	{
			$response['message'] = 'Invalid temporary file';
			$this->debug("checkin_small_document - $upload_manager->temp_dir != $tempdir", $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}

		// write to the temporary file
		$fp=fopen($tempfilename, 'wb');
		if ($fp === false)
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>'Cannot write to temp file: ' + $tempfilename
			);
			$this->debug("checkin_small_document - cannot write $tempfilename", $session_id);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
		fwrite($fp, base64_decode($base64));
		fclose($fp);

    	// simulate the upload
		$upload_manager->uploaded($filename,$tempfilename, 'C');

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

		$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    }

    /**
     * Does a document checkout.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @return kt_response.  status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function checkout_document($session_id, $document_id, $reason)
    {
    	$this->debug("checkout_document('$session_id',$document_id,'$reason')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
			$this->debug("checkout_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
			$this->debug("checkout_document - cannot checkout - "  . $result->getMessage(), $session_id);
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
     * Does a document checkout.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @param boolean $download
     * @return kt_response  status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_FOLDER or KTWS_SUCCESS
     */
    function checkout_small_document($session_id, $document_id, $reason, $download)
    {
    	$this->debug("checkout_small_document('$session_id',$document_id,'$reason', $download)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("checkout_small_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("checkout_small_document - cannot checkout - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
    			return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    		}
    		$content = fread($fp, filesize($filename));
    		fclose($fp);
    		$content = base64_encode($content);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $content;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Undoes a document checkout.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $reason
     * @return kt_response.  status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function undo_document_checkout($session_id, $document_id, $reason)
    {
    	$this->debug("undo_document_checkout('$session_id',$document_id,'$reason')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("undo_document_checkout - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->undo_checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();

    		$this->debug("undo_document_checkout - cannot undo checkout - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Returns a reference to a file to be downloaded.
     *
     * @param string $session_id
     * @param int $document_id

     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function download_document($session_id, $document_id)
    {
    	$this->debug("download_document('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
    function download_small_document($session_id, $document_id)
    {
    	$this->debug("download_small_document('$session_id',$document_id)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}

		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
     * @return kt_response
     */
    function change_document_type($session_id, $document_id, $documenttype)
    {
    	$this->debug("change_document_type('$session_id',$document_id,'$documenttype')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();

    		$this->debug("change_document_type - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->change_document_type($documenttype);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("change_document_type - cannot change type - "  . $result->getMessage(), $session_id);

    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
     * @return kt_response
     */
 	function copy_document($session_id,$document_id,$folder_id,$reason,$newtitle,$newfilename)
 	{
    	$this->debug("copy_document('$session_id',$document_id,$folder_id,'$reason','$newtitle','$newfilename')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("copy_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("copy_document - cannot get folderid $folder_id - "  . $tgt_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->copy($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("copy_document - cannot copy - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
 	function move_document($session_id,$document_id,$folder_id,$reason,$newtitle,$newfilename)
 	{
    	$this->debug("move_document('$session_id',$document_id,$folder_id,'$reason','$newtitle','$newfilename')");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("move_document - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$tgt_folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		$this->debug("move_document - cannot get folderid $folder_id - "  . $tgt_folder->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->move($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("move_document - cannot move - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("rename_document_title - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->rename($newtitle);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("rename_document_title - cannot rename - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("rename_document_filename - cannot get documetid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->renameFile($newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("rename_document_filename - cannot rename - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
 	}

    /**
     * Changes the owner of a document.
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $username
     * @param string $reason
     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION, KTWS_ERR_INVALID_DOCUMENT or KTWS_SUCCESS
     */
    function change_document_owner($session_id, $document_id, $username, $reason)
    {
    	$this->debug("change_document_owner('$session_id',$document_id,'$username','$reason')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("change_document_owner - cannot get documetid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->change_owner($username,  $reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("change_document_owner - cannot change owner - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Start a workflow on a document
     *
     * @param string $session_id
     * @param int $document_id
     * @param string $workflow
     * @return kt_response
     */
    function start_document_workflow($session_id,$document_id,$workflow)
    {
    	$this->debug("start_document_workflow('$session_id',$document_id,'$workflow')");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("start_document_workflow - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = &$document->start_workflow($workflow);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("start_document_workflow - cannot start workflow - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

	/**
	 * Removes the workflow process on a document.
	 *
	 * @param string $session_id
	 * @param int $document_id
	 * @return kt_response
	 */
    function delete_document_workflow($session_id,$document_id)
    {
    	$this->debug("delete_document_workflow('$session_id',$document_id)");
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("delete_document_workflow - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->delete_workflow();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("delete_document_workflow - cannot stop workflow - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
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
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("perform_document_workflow_transition - cannot get document - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->perform_workflow_transition($transition,$reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("perform_document_workflow_transition - cannot perform transition - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    }

    /**
     * Encodes the array as a kt_metadata_selection_item
     *
     * @param aray $item
     * @param string $name
     * @return SOAP_Value of kt_metadata_selection_item
     * @access private
     * @static
     */
    function _encode_metadata_selection_item($item, $name='item')
    {
    	if (!is_null($item['id']))
    	{
    		$item['id'] = (int) $item['id'];
    	}

    	if (!is_null($item['parent_id']))
    	{
    		$item['parent_id'] = (int) $item['parent_id'];
    	}

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_selection_item", $item);
    }

    /**
     * Encode an array as kt_metadata_selection
     *
     * @param array $selection
     * @param string $name
     * @return SOAP_Value of kt_metadata_selection
     * @access private
     * @static
     */

    function _encode_metadata_selection($selection, $name='selection')
    {
    	$encoded=array();
    	foreach($selection as $field)
    	{
    		$encoded[] = KTWebService::_encode_metadata_selection_item($field);
    	}

    	if (empty($encoded))
    	{
    		$encoded=null;
    	}

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_selection", $encoded);
    }

    /**
     * Encode an array as kt_metadata_field
     *
     * @param arra $field
     * @param string $name
     * @return SOAP_Value of kt_metadata_field
     * @access private
     * @static
     */

    function _encode_metadata_field($field, $name='field')
    {
    	if (!empty($field['selection']))
    	{
    		$field['selection'] = KTWebService::_encode_metadata_selection($field['selection']);
    	}
    	if (!is_null($field['required']))
    	{
    		$field['required'] = (bool) $field['required'];
    	}

    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_field", $field);
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
    function _encode_metadata_fields($fields, $name='fields')
    {
    	$encoded=array();
    	foreach($fields as $field)
    	{
    		$encoded[] = KTWebService::_encode_metadata_field($field);
    	}
    	if (empty($encoded))
    	{
    		$encoded=null;
    	}
    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_fields", $encoded);
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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
	 * @return kt_response
	 */
	function update_document_metadata($session_id,$document_id,$metadata)
	{
    	$this->debug("update_document_metadata('$session_id',$document_id,$metadata)");

    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		$this->debug("update_document_metadata - cannot get documentid $document_id - "  . $document->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$result = $document->update_metadata($metadata);
    	if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		$this->debug("update_document_metadata - cannot update metadata - "  . $result->getMessage(), $session_id);
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);

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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
    	$response['transitions'] = $result;

    	return new SOAP_Value('return',"{urn:$this->namespace}kt_workflow_transitions_response", $response);
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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
	 * Encode an array as kt_document_transaction_history_item
	 *
	 * @param array $item
	 * @param string $name
	 * @return SOAP_Value of kt_document_transaction_history_item
     * @access private
     * @static
	 */
	function _encode_transaction_history_item($item, $name='item')
	{
		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_transaction_history_item", $item);
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
		$encoded=array();
		foreach($history as $item)
		{
			$encoded[] = KTWebService::_encode_transaction_history_item($item);
		}

		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_transaction_history", $encoded);
	}

	/**
	 * Encode an array as kt_document_transaction_history_response
	 *
	 * @param array $response
	 * @param string $name
	 * @return SOAP_Value of kt_document_transaction_history_response
     * @access private
     * @static
	 */
	function _encode_transaction_history_response($response, $name='return')
	{
		$response['history'] = KTWebService::_encode_transaction_history($response['history']);

		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_transaction_history_response", $response);
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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
    	$response['history'] = $result;

    	return KTWebService::_encode_transaction_history_response($response);
	}

	/**
	 * Encode an array as kt_document_version_history_item
	 *
	 * @param array $item
	 * @param string $name
	 * @return SOAP_Value of kt_document_version_history_item
     * @access private
     * @static
	 */
	function _encode_version_history_item($item, $name='item')
	{
		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_version_history_item", $item);
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
		$encoded=array();
		foreach($history as $item)
		{
			$encoded[] = KTWebService::_encode_version_history_item($item);
		}

		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_version_history", $encoded);
	}

	/**
	 * Encode an array as kt_document_version_history_response
	 *
	 * @param array $response
	 * @param string $name
	 * @return SOAP_Value of kt_document_version_history_response
     * @access private
     * @static
	 */
	function _encode_version_history_response($response, $name='return')
	{
		$response['history'] = KTWebService::_encode_version_history($response['history']);

		return new SOAP_Value($name,"{urn:$this->namespace}kt_document_version_history_response", $response);
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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
    	$response['history'] = $result;

    	return KTWebService::_encode_version_history_response($response);
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
    		'parent_document_id' => $document_id,
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
    	$response['links'] = $links;

		return new SOAP_Value('return',"{urn:$this->namespace}kt_linked_document_response", $response);
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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	);

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
    	$encoded=array();
    	foreach($policies as $policy)
    	{
    		$encoded[] = new SOAP_Value('policy',"{urn:$this->namespace}kt_client_policy", $policy);
    	}
    	if (empty($encoded))
    	{
    		$encoded=null;
    	}
    	return new SOAP_Value('policies',"{urn:$this->namespace}kt_client_policies_array", $encoded);
    }

	/**
	 * Retrieves the server policies for this server
	 *
	 * @param string $session_id
	 * @return kt_client_policies_response
	 */
	function get_client_policies($session_id)
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
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>'',
    		'hits'=>array()
    	);

    	$noText = (stripos($options,'notext') !== false);
		$results = array();

    	try
    	{
    		$expr = parseExpression($query);

    		$rs = $expr->evaluate();
    		usort($rs, 'rank_compare');

    		$results = array();
    		foreach($rs as $hit)
    		{
    			 $item = array(
						'document_id' => (int) $hit->DocumentID,
						'title' => (string) $hit->Title,
						'relevance' => (float) $hit->Rank,
        				'text' => (string)  $noText?'':$hit->Text,
        				'filesize' => (int) $hit->Filesize,
        				'fullpath' => (string) $hit->FullPath,
        				'version' => (string) $hit->Version,
        				'filename' => (string) $hit->Filename,
        				'checked_out_by' => (string) $hit->CheckedOutUser,
        				'checked_out_date' => (string) $hit->DateCheckedOut,
        				'is_available' => (bool) $hit->IsAvailable,
        				'workflow' => (string) $hit->Workflow,
        				'workflow_state' => (string) $hit->WorkflowState,
        				'folder_id' => (int) $hit->FolderId,
        				'mime_type' => (string) $hit->MimeType,
						'modified_by' => (string) $hit->ModifiedBy,
						'modified_date' => (string) $hit->DateModified,
						'created_by' => (string) $hit->CreatedBy,
						'created_date' => (string) $hit->DateCreated,
						'owner' => (string) $hit->Owner,
						'is_immutable'=> (bool) $hit->Immutable,
						'status' => (string) $hit->Status
    				);

    				$item = new SOAP_Value('item',"{urn:$this->namespace}kt_search_result_item", $item);
    				$results[] = $item;

    		}

    		$response['message'] = '';
    		$response['status_code'] = KTWS_SUCCESS;
    	}
    	catch(Exception $e)
    	{
    		$this->debug("search - exception " . $e->getMessage(), $session_id);

    		$results = array();
    		$response['message'] = _kt('Could not process query.')  . $e->getMessage();
    	}
    	$response['hits'] = new SOAP_Value('hits',"{urn:$this->namespace}kt_search_results", $results);

		return new SOAP_Value('return',"{urn:$this->namespace}kt_search_response", $response);
	}

    /**
     * This runs the web service
     *
     * @static
     * @access public
     */
    function run()
    {
    	$server = new SOAP_Server();

    	$server->addObjectMap($this, 'http://schemas.xmlsoap.org/soap/envelope/');

    	if (isset($_SERVER['REQUEST_METHOD'])  && $_SERVER['REQUEST_METHOD']=='POST')
    	{
    		$server->service(file_get_contents("php://input"));
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
