<?

/**
 *
 * This implements the KnowledgeTree Web Service in SOAP.
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

require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');
require_once('SOAP/Server.php');
require_once('SOAP/Disco.php');
require_once('KTDownloadManager.inc.php');
require_once('KTUploadManager.inc.php');
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');

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
    
    function KTWebService() 
    {
    	// Caching was giving some problems, so disable it.
    	
    	$config = &KTConfig::getSingleton();
    	$cache_enabled = $config->get('cache/cacheEnabled');
    	if ($cache_enabled)
    	{
    		$config->setns('cache','cacheEnabled',false);
    	}
    	
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
        	  //  'metadata' => "{urn:$this->namespace}kt_metadata_fieldsets" 
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
         	   
        $this->__typedef["{urn:$this->namespace}kt_document_transitions"] =
			array(
            	array(
                        'transition' => 'string'
                  )
            );
            
    	$this->__typedef["{urn:$this->namespace}kt_document_transitions_response"] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'metadata' => "{urn:$this->namespace}kt_document_transitions" 
         		);
         		
    	$this->__typedef["{urn:$this->namespace}kt_document_transaction_history_item"] =
         	array(
         		'transaction_name'=>'string',
         		'username'=>'string',
         		'version' => 'string',
         		'comment' => 'string',
         		'datetime' => 'string',
         		 
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
         		'user'=>'int',
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
         	
         /* methods */         	
         	
         // login
         $this->__dispatch_map['login'] =
            array('in' => array('username' => 'string', 'password' => 'string', 'ip' => 'string'),
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
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );      

          //  checkin_small_document
         $this->__dispatch_map['checkin_small_document'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','filename'=>'string','reason' =>'string','base64' =>'string', 'major_update'=>'boolean' ), 
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
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
            
            //update_document_metadata
            $this->__dispatch_map['update_document_metadata'] =
            array('in' => array('session_id'=>'string','document_id'=>'int','metadata'=>"{urn:$this->namespace}kt_metadata_fieldsets"  ), 
             'out' => array( 'return' => "{urn:$this->namespace}kt_response" ),
            );             
            
              
            //get_document_workflow_transitions
            $this->__dispatch_map['get_document_workflow_transitions'] =
            array('in' => array('session_id'=>'string','document_id'=>'int' ), 
             'out' => array( 'return' => "{urn:$this->namespace}kt_document_transitions_response" ),
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

             
    }
    
    /**
     * This is used by all exposed functions dependant on the sessionid.
     * 
     * @param string $session_id
     * @return KTAPI This could be KTAPI or kt_response array with status_code of KTWS_ERR_INVALID_SESSION.
     */
    function &get_ktapi($session_id)
    {
    	$kt = &new KTAPI();

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
    	
    	$kt = &new KTAPI();

    	$session = $kt->start_session($username,$password, $ip);

    	if (PEAR::isError($session))
    	{
    		$response['message'] = $session->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['message'] = $session->get_session(); 
    	
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $kt);
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
    	$kt = &$this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $kt);
    	}
 
    	$root = &$kt->get_root_folder();
    	if (PEAR::isError($root))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$root->getMessage()
    		); 
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response);
    	}    	

    	$folder = &$root->get_folder_by_name($folder_name);
    	if (PEAR::isError($folder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_folder_detail", $response); 		
    	}

    	$newfolder = &$folder->add_folder($folder_name);
    	if (PEAR::isError($newfolder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$newfolder->getMessage()
    		);   
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	
    	$result = $folder->delete($reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 		
    	}      	
    	    	
    	$result = $folder->rename($newname);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt); 
    	}
    	
    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	); 
    	
    	/* TODO: REMOVE ME*/
    	$response['message'] = 'DEBUG ME PLEASE. WHY AM I NOT WORKING!';
    	return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 
		/* REMOVE TILL HERE */ 
    	    	
    	$src_folder = &$kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 
    	}

    	$tgt_folder = &$kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 
    	}
    	
    	$result= $src_folder->copy($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt); 
    	}
    	
    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	); 

    	/* TODO: REMOVE ME*/
    	$response['message'] = 'DEBUG ME PLEASE. WHY AM I NOT WORKING!';
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 
		/* REMOVE TILL HERE */    	
    	
    	$src_folder = &$kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 
    	}
    	    	
    	$tgt_folder = &$kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 
    	}
    	    	
    	$result = $src_folder->move($tgt_folder, $reason);  
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response); 
    	} 

    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $detail->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response); 
    	} 
    	
    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $detail->getMessage();
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
    	$kt = &$this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $kt); 
    	}
    	
		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (substr($tempfilename,0,strlen($upload_manager->temp_dir)) != $upload_manager->temp_dir)
    	{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>'Invalid temporary file.'
			);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}    	

    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>$folder->getMessage()
			);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
    	    	
    	$document = &$folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>$document->getMessage()
			);
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
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
		    	
		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
		if ( substr($tempfilename,0,strlen($upload_manager->temp_dir))  != $upload_manager->temp_dir)
    	{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>'Invalid temporary file.'
			);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}    	
    	
    	$folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>$folder->getMessage()
			);
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
				
		// write to the temporary file
		$fp=fopen($tempfilename, 'wt');
		if ($fp === false)
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>'Cannot write to temp file: ' + $tempfilename
			);
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
    	if (substr($tempfilename,0,strlen($upload_manager->temp_dir)) != $upload_manager->temp_dir)
    	{
			$response['message'] = 'Invalid temporary file';
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}
			
    	$document = &$kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
			$response['message'] = $document->getMessage();
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
		
		// checkin
		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
			$response['message'] = $result->getMessage();
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
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}		
		
		// we need to add some security to ensure that people don't frig the checkin process to access restricted files.
		// possibly should change 'tempfilename' to be a hash or id of some sort if this is troublesome.
    	$upload_manager = new KTUploadManager();
    	if (substr($tempfilename,0,strlen($upload_manager->temp_dir)) != $upload_manager->temp_dir)
    	{
			$response['message'] = 'Invalid temporary file';
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
    	}		
				
		// write to the temporary file
		$fp=fopen($tempfilename, 'wt');
		if ($fp === false)
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>'Cannot write to temp file: ' + $tempfilename
			);
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
			return new SOAP_Value('return',"{urn:$this->namespace}kt_document_detail", $response);
		}
				
		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
			$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}    	

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}    	

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	
    	$content='';
    	if ($download)
    	{
    		$document = $document->document;
    		
    		$oStorage =& KTStorageManagerUtil::getSingleton();
            $filename = $oStorage->temporaryFile($document);
    		
    		$fp=fopen($filename,'rt');
    		if ($fp === false)
    		{
    			$response['message'] = 'The file is not in the storage system. Please contact an administrator!';
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);		
    	}  
    	
    	$result = $document->undo_checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);	
    	}    	

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);	
    	}    	

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}

    	$content='';
    	 
    		$document = $document->document;
    		
    		$oStorage =& KTStorageManagerUtil::getSingleton();
            $filename = $oStorage->temporaryFile($document);
    		
    		$fp=fopen($filename,'rt');
    		if ($fp === false)
    		{
    			$response['message'] = 'The file is not in the storage system. Please contact an administrator!';
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);	
    	} 

    	$result = $document->delete($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	} 

    	$result = $document->change_document_type($documenttype);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response); 		
    	} 

    	$tgt_folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}     	
    	
    	$result = $document->copy($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	} 

    	$tgt_folder = &$kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);	
    	}     	
    	
    	$result = $document->move($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	
    	$result = $document->rename($newtitle);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);
    	}
    	
    	$result = $document->renameFile($newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);		
    	} 

    	$result = $document->change_owner($username,  $reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);		
    	} 

    	$result = &$document->start_workflow($workflow);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);		
    	} 

    	$result = $document->delete_workflow();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);	
    	} 

    	$result = $document->perform_workflow_transition($transition,$reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    	$response['metadata'] = KTWebService::_encode_metadata_fieldsets($response['metadata']);
    	
    	return new SOAP_Value($name,"{urn:$this->namespace}kt_metadata_response", $response);
    	
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $response);	
    	}
    	
    	$result = $document->update_metadata($metadata); 
    	if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_workflow_transitions_response", $response);	 		
    	}
    	
    	$result = $document->get_workflow_transitions();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_response", $kt);	
    	}
    	
    	$result = $document->get_workflow_state();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_transaction_history_response", $response);
    	}
    	
    	$result = $document->get_transaction_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
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
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_version_history_response", $response);
    	}
    	
    	$result = $document->get_version_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		return new SOAP_Value('return',"{urn:$this->namespace}kt_document_version_history_response", $response);
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] = $result;
    	
    	return KTWebService::_encode_version_history_response($response);
	}	
	
    /**
     * This runs the web service
     *
     * @static
     * @access public
     */
    function run()
    {
    	global $HTTP_RAW_POST_DATA;
     
    	$server = new SOAP_Server();
    	 
    	$server->addObjectMap($this, 'http://schemas.xmlsoap.org/soap/envelope/');

    	if (isset($_SERVER['REQUEST_METHOD'])  && $_SERVER['REQUEST_METHOD']=='POST') 
    	{
    		$server->service($HTTP_RAW_POST_DATA);
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