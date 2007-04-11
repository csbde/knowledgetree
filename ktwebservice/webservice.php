<?

/**
 *
 * This implements the KnowledgeTree Web Service in SOAP.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');
require_once('SOAP/Server.php');
require_once('SOAP/Disco.php');
require_once('KTDownloadManager.inc.php');

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
    	
    	$config = KTConfig::getSingleton();
    	$cache_enabled = $config->get('cache/cacheEnabled');
    	if ($cache_enabled)
    	{
    		$config->setns('cache','cacheEnabled',false);
    	}
    	
    	$this->namespace = 'KTWebService';

    	$this->__typedef['kt_response'] =
         	array(
		        'status_code' => 'int',
                'message' => 'string'  
         	);  
         	
    	$this->__typedef['kt_folder_detail'] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
				'id' => 'int',
        		'folder_name' => 'string',
        		'parent_id' => 'int',
        		'full_path' => 'string',
         	);         	    	
    	
       	
         	
    	$this->__typedef['kt_folder_item'] =
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

        $this->__typedef['kt_folder_items'] =
			array(
            	array(
                        'item' => "{urn:$this->namespace}kt_folder_item"
                  )
            );          

            
    	$this->__typedef['kt_folder_contents'] =
         	array(
				'status_code' => 'int',      
				'message' => 'string',   		
				'folder_id' => 'int',
        		'folder_name' => 'string' ,
        		'full_path' => 'string' ,
        		'items' => "{urn:$this->namespace}kt_folder_items",       	
         	);             	
         	
         $this->__typedef['kt_document_detail'] =
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
         	
    	$this->__typedef['kt_metadata_selection_item'] =
         	array(
				'id' => 'int',      
				'name' => 'string',   		
				'value' => 'string',
        		'parent_id' => 'int'      	
         	);         	
         	        

    	$this->__typedef['kt_metadata_selection'] =
         	array(
				array(
                        'item' => "{urn:$this->namespace}kt_metadata_selection_item"
                  )   	
         	);          	
         	
         	
    	$this->__typedef['kt_metadata_field'] =
         	array(
				'name' => 'string',
        		'required' => 'boolean' ,
        		'value' => 'string' ,
        		'description' => 'string' ,
        		'control_type' => 'string' ,
        		'selection' => "{urn:$this->namespace}kt_metadata_selection" ,
         	);

        $this->__typedef['kt_metadata_fields'] =
			array(
            	array(
                        'field' => "{urn:$this->namespace}kt_metadata_field"
                  )
            );
    	$this->__typedef['kt_metadata_fieldset'] =
         	array(
				'fieldset' => 'string',
				'description' => 'string',
        		'fields' => "{urn:$this->namespace}kt_metadata_fields" ,
         	);
         	
        $this->__typedef['kt_metadata_fieldsets'] =
			array(
            	array(
                        'fieldset' => "{urn:$this->namespace}kt_metadata_fieldset"
                  )
            );         	
         	
    	$this->__typedef['kt_metadata_response'] =
         	array(
				'status_code' => 'int',
				'message' => 'string',
        		'metadata' => "{urn:$this->namespace}kt_metadata_fieldsets" ,
         	);  
         	   
        $this->__typedef['kt_document_transitions'] =
			array(
            	array(
                        'transition' => 'string'
                  )
            );     
            
    	$this->__typedef['kt_document_transitions_response'] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'metadata' => "{urn:$this->namespace}kt_document_transitions" 
         		);  	
         		
    	$this->__typedef['kt_document_transaction_history_item'] =
         	array(
         		'transaction_name'=>'string',
         		'username'=>'string',
         		'version' => 'string',
         		'comment' => 'string',
         		'datetime' => 'string',
         		 
         		);           		
         		
         	
        $this->__typedef['kt_document_transaction_history'] =
			array(
            	array(
                        'history' => "{urn:$this->namespace}kt_document_transaction_history_item"
                  )
            );           		
         		
         		
    	$this->__typedef['kt_document_transaction_history_response'] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'history' => "{urn:$this->namespace}kt_document_transaction_history" 
         		); 

    	$this->__typedef['kt_document_version_history_item'] =
         	array(
         		'user'=>'int',
         		'metadata_version'=>'string',
         		'content_version'=>'string', 
         		);          		
         		
         		
        $this->__typedef['kt_document_version_history'] =
			array(
            	array(
                        'history' =>  "{urn:$this->namespace}kt_document_version_history_item" 
                  )
            );           		
         		

    	$this->__typedef['kt_document_version_history_response'] =
         	array(
         		'status_code'=>'int',
         		'message'=>'string',
         		'history' => "{urn:$this->namespace}kt_document_version_history" 
         		); 
         		
         		
           $this->__typedef['kt_document_types_array'] =
			array(
            	array(
                        'document_type' => 'string'
                  )
            );           	
         	
        $this->__typedef['kt_document_types_response'] =
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
            
                 // add_document
         $this->__dispatch_map['add_document'] =
            array('in' => array('session_id'=>'string','folder_id'=>'int','title'=>'string','filename'=>'string','documentype' =>'string','tempfilename' =>'string' ), 
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
            array('in' => array('session_id'=>'string','document_id'=>'int','metadata'=>"{urn:$this->webservice}kt_metadata_fieldsets"  ), 
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
    function get_ktapi($session_id)
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
    		return $response;
    	}

    	$response['status_code'] = KTWS_SUCCESS;
    	$response['message'] = $session->get_session(); 
    	
    	return $response;
    }

    /**
     * Closes an active session.
     *
     * @param string $session_id
     * @return kt_response. status_code can be KTWS_ERR_INVALID_SESSION or KTWS_SUCCESS.
     */
    function logout($session_id)
    {    	
    	$kt = $this->get_ktapi($session_id); 
    	if (is_array($kt))
    	{
    		return $kt;
    	}

    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_SESSION,
    		'message'=>''
    	); 
    	
    	$session = $kt->get_session();
    	if (PEAR::isError($session))
    	{
    		$response['message'] = $session->getMessage();
    		return $response;
    	} 
    	$session->logout();
    	
    	$response['status_code']=KTWS_SUCCESS;
    	
    	return $response;
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
    	$kt = $this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	    	
    	$folder = $kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
    		return $response;
    	}	
    	
    	$detail = $folder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';
    	
    	return $detail;
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
    	$kt = $this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return $kt;
    	}
 
    	$root = $kt->get_root_folder();
    	if (PEAR::isError($root))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$root->getMessage()
    		); 
    		return $response;
    	}    	

    	$folder = $root->get_folder_by_name($folder_name);
    	if (PEAR::isError($folder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);
    		return $response;
    	}	
    
    	$detail = $folder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';

    	return $detail;
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
    	$kt = $this->get_ktapi($session_id);
    	if (is_array($kt))
    	{
    		return $kt;
    	} 

    	$folder = $kt->get_folder_by_id($folder_id);
    	if (PEAR::isError($folder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);   
    		return $response;
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

    	return $contents;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	} 	
    	
    	$folder = $kt->get_folder_by_id($folder_id);
    	if (PEAR::isError($folder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$folder->getMessage()
    		);   
    		return $response;    		
    	}

    	$newfolder = $folder->add_folder($folder_name);
    	if (PEAR::isError($newfolder))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_FOLDER,
    			'message'=>$newfolder->getMessage()
    		);   
    		return $response;    		
    	}
    	
    	$detail = $newfolder->get_detail();
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';
    	    	
    	return $detail;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	} 
    	
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	); 
    	   	
    	$folder = $kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
    		$response['message'] = $folder->getMessage();
    		return $response;    		
    	}
    	
    	$result = $folder->delete($reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	return $response;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		
    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	);     	
    	
    	$folder = $kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
    	{
			$response['message']= $folder->getMessage();
			
    		return $response;    		
    	}      	
    	    	
    	$result = $folder->rename($newname);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    	}
    	    	
    	$response['status_code']= KTWS_SUCCESS;
    	
    	return $response;    	
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	
    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	); 
    	
    	/* TODO: REMOVE ME*/
    	$response['message'] = 'DEBUG ME PLEASE. WHY AM I NOT WORKING!';
    	return $response;
		/* REMOVE TILL HERE */ 
    	    	
    	$src_folder = $kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		return $response;
    	}

    	$tgt_folder = $kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		return $response;
    	}
    	
    	$result= $src_folder->copy($tgt_folder, $reason);
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    	}    	
    	
    	
    	$response['status_code']= KTWS_SUCCESS;
    	
    	return $response;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	
    	$response=array(
    		'status_code'=>KTWS_ERR_INVALID_FOLDER,
    		'message'=>''
    	); 

    	/* TODO: REMOVE ME*/
    	$response['message'] = 'DEBUG ME PLEASE. WHY AM I NOT WORKING!';
    	return $response;
		/* REMOVE TILL HERE */    	
    	
    	$src_folder = $kt->get_folder_by_id($source_id);
    	if (PEAR::isError($src_folder))
    	{
    		$response['message'] = $src_folder->getMessage();
    		return $response;
    	}
    	    	
    	$tgt_folder = $kt->get_folder_by_id($target_id);
    	if (PEAR::isError($tgt_folder))
    	{
    		$response['message'] = $tgt_folder->getMessage();
    		return $response;
    	}
    	    	
    	$result = $src_folder->move($tgt_folder, $reason);  
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $result->getMessage();
    	}     	
    	    	  
    	$response['status_code']= KTWS_SUCCESS;
    	
    	return $response;    		
    }
    
    /**
     * Returns a list of document types.
     *
     * @param string $session_id
     * @return kt_document_types_response. . status_code can be KTWS_ERR_INVALID_SESSION, KTWS_SUCCESS
     */
    function get_document_types($session_id)
    {
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	
    	$response=array(
    		'status_code'=>KTWS_ERR_PROBLEM,
    		'message'=>''
    	);     	
    	
    	$result = $kt->get_documenttypes();    	
    	if (PEAR::isError($result))
    	{
    	    $response['message']= $result->getMessage();
    		return $response;
    	}
    	
   		$response['status_code']= KTWS_SUCCESS;
   		$response['document_types']= $result;
   		
   		return $response;
    	
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}   	  	
    	
    	$document = $kt->get_document_by_id($document_id);
    	if (PEAR::isError($document))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    			'message'=>$document->getMessage()
    		);  
    		return $response;
    	} 

    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $detail->getMessage();
    	}     	
		
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';
    	return $detail;
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
    		return $response;
    	}
    	
    	if (!in_array($what, array('T','F')))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = 'Invalid what code';
    	}
    	
    	
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}   	
    	
    	$root = $kt->get_root_folder();
    	if (PEAR::isError($root))
    	{
    		 		 
    		return $response;
    	}    	

    	if ($what == 'T')
    	{ 
    		$document = $root->get_document_by_name($document_name);
    	}
    	else
    	{
    		$document = $root->get_document_by_filename($document_name);
    	}
    	if (PEAR::isError($document))
    	{
    		$response=array(
    			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    			'message'=>$document->getMessage()
    		);
    		return $response;
    	} 
    	
    	$detail = $document->get_detail();
    	if (PEAR::isError($detail))
    	{
    		$response['status_code'] = KTWS_ERR_PROBLEM;
    		$response['message'] = $detail->getMessage();
    		return $response;
    	}     	
    	
    	$detail['status_code']=KTWS_SUCCESS;
    	$detail['message']='';
    	
    	return $detail;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}

    	$folder = $kt->get_folder_by_id($folder_id);
		if (PEAR::isError($folder))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_FOLDER,
				'message'=>$folder->getMessage()
			);
			return $response;
		}
    	    	
    	$document = $folder->add_document($title, $filename, $documenttype, $tempfilename);
		if (PEAR::isError($document))
		{
			$response=array(
				'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
				'message'=>$document->getMessage()
			);
			return $response;
		}
		    	
    	$detail = $document->get_detail();
    	$detail['status_code'] = KTWS_SUCCESS;
		$detail['message'] = '';  
		    	
    	return $detail;    	   	
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		
    	$response=array(
			'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
			'message'=>'',
		);
			    	   	    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
		{
			$response['message'] = $document->getMessage();
			return $response;
		}
		
		$result = $document->checkin($filename, $reason, $tempfilename, $major_update);
		if (PEAR::isError($result))
		{
			$response['message'] = $result->getMessage();
			return $response;
		}		
				
		$response['status_code'] = KTWS_SUCCESS;
		

    	return $response;    	   	
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}    	

    	$result = $document->checkout($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}

    	$download_manager = new KTDownloadManager();
    	$download_manager->set_session($kt->get_session()->session);
    	$download_manager->cleanup(); 
    	$url = $download_manager->allow_download($document);
    	
    	
    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $url;    	 
		   	
    	return $response;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 

    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}  
    	
    	$document->undo_checkout($reason);
    	 
    	$response['status_code'] = KTWS_SUCCESS;
    	
    	return $response;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
    	
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}    	

    	$result = $document->download();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	    	
    	$user = $kt->get_user();
    	
    	$download_manager = new KTDownloadManager();
    	$download_manager->set_session($kt->get_session()->session);
    	$download_manager->cleanup(); 
    	$url = $download_manager->allow_download($document);
    	
    	
    	$response['status_code'] = KTWS_SUCCESS;
		$response['message'] = $url;    	 
		   	
    	return $response;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$result = $document->delete($reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;    	   	
    	
    }
    
    function change_document_type($session_id, $document_id, $documenttype)
    {
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$result = $document->change_document_type($documenttype);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;  
    }
    
 	function copy_document($session_id,$document_id,$folder_id,$reason,$newtitle,$newfilename)
 	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$tgt_folder = $kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		return $response;    		
    	}     	
    	
    	$result = $document->copy($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
 	}

 	function move_document($session_id,$document_id,$folder_id,$reason,$newtitle,$newfilename)
 	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$tgt_folder = $kt->get_folder_by_id($folder_id);
		if (PEAR::isError($tgt_folder))
    	{
    		$response['status_code'] = KTWS_ERR_INVALID_FOLDER;
    		$response['message'] = $tgt_folder->getMessage();
    		return $response;    		
    	}     	
    	
    	$result = $document->move($tgt_folder, $reason, $newtitle, $newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
 	} 	
 	 
 	function rename_document_title($session_id,$document_id,$newtitle)
 	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->rename($newtitle);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
 	}
 	
 	function rename_document_filename($session_id,$document_id,$newfilename)
 	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->renameFile($newfilename);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
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
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$result = $document->change_owner($username,  $reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;  
    }

    function start_document_workflow($session_id,$document_id,$workflow)
    {
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$result = $document->start_workflow($workflow);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
    }
 

    function delete_document_workflow($session_id,$document_id)
    {
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$result = $document->delete_workflow();
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
    }    

    function perform_document_workflow_transition($session_id,$document_id,$transition,$reason)
    {
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	} 

    	$result = $document->perform_workflow_transition($transition,$reason);
		if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	$response['status_code'] = KTWS_SUCCESS;
    	    	    	
    	return $response;
    }
    
	function get_document_metadata($session_id,$document_id)
	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
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
		/*foreach($metadata as & $fieldset)
		{
			foreach($fieldset['fields'] as &$fields)
			{
				$selection = array();
				foreach($fields['selection'] as $item)
				{
					$selection[] = array(
						'id'=>null,
						'name'=>$item,
						'value'=>$item,
						'parent_id'=>null
					);
				}
				$fieldset['fields']['selection'] = $selection;
			}
		}*/
    	
    	$response = array(
    		'status_code' => KTWS_SUCCESS,
    		'message' => '',
    		'metadata' => $metadata);
    		
    	return $response;
    	    		
	}
    

	function update_document_metadata($session_id,$document_id,$metadata)
	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->update_metadata($metadata); 
    	if (PEAR::isError($result))
    	{
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	
    	return $response;
    	
	}

	function get_document_workflow_transitions($session_id, $document_id)
	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->get_workflow_transitions();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['transitions'] = $result;
    	
    	return $response;    	
	}
    
	function get_document_workflow_state($session_id, $document_id)
	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->get_workflow_state();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['message'] = $result;
    	
    	return $response;    	
	}	
	
	function get_document_transaction_history($session_id, $document_id)
	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->get_transaction_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] = $result;
    	
    	return $response;  
	}
	
	function get_document_version_history($session_id, $document_id)
	{
    	$kt = $this->get_ktapi($session_id );
    	if (is_array($kt))
    	{
    		return $kt;
    	}
		$response=array(
    		'status_code'=>KTWS_ERR_INVALID_DOCUMENT,
    		'message'=>''
    	); 
    	
    	$document = $kt->get_document_by_id($document_id);
		if (PEAR::isError($document))
    	{
    		$response['message'] = $document->getMessage();
    		return $response;    		
    	}
    	
    	$result = $document->get_version_history();
    	if (PEAR::isError($result))
    	{
    		$response['status_code'] = KTWS_PROBLEM;
    		$response['message'] = $result->getMessage();
    		return $response;    		
    	}
    	
    	$response['status_code'] = KTWS_SUCCESS;
    	$response['history'] = $result;
    	
    	return $response;  
	}	
	
    /**
     * This runs the web service
     *
     * @access static
     */
    function run()
    {
    	global $HTTP_RAW_POST_DATA;
     
    	$server = new SOAP_Server();
    	 
    	$server->addObjectMap($this, 'http://schemas.xmlsoap.org/soap/envelope/');

    	if (isset($_SERVER['REQUEST_METHOD'])  && $_SERVER['REQUEST_METHOD']=='POST') {
    		$server->service($HTTP_RAW_POST_DATA);
    	} else {
    		// Create the DISCO server
    		$disco = new SOAP_DISCO_Server($server, $this->namespace);
    		header("Content-type: text/xml");
    		if (isset($_SERVER['QUERY_STRING']) &&
    		strcasecmp($_SERVER['QUERY_STRING'],'wsdl') == 0) {
    			echo $disco->getWSDL();
    		} else {
    			echo $disco->getDISCO();
    		}
    	}
    }
    	
    function __dispatch($methodname) 
    {
        if (isset($this->__dispatch_map[$methodname]))
            return $this->__dispatch_map[$methodname];
        return NULL;
    }
    
}



$webservice = new KTWebService();
$webservice->run();


?>