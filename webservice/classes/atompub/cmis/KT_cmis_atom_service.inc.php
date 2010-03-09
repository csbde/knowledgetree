<?php

include_once(KT_ATOM_LIB_FOLDER.'KT_atom_service.inc.php');

class KT_cmis_atom_service extends KT_atom_service {

	// override and extend as needed
    
    protected $serviceType = null;
    protected $contentDownload = false;
    // status code mapping is for mapping exceptions thrown by the API to their appropriate
    // HTTP error status codes (see section 3.2.4.1)
    static protected $statusCodeMapping = array('InvalidArgumentException' => self::STATUS_BAD_REQUEST,
                                                'ObjectNotFoundException' => self::STATUS_NOT_FOUND,
                                                'PermissionDeniedException' => self::STATUS_PERMISSION_DENIED,
                                                'NotSupportedException' => 405,
                                                'RuntimeException' => self::STATUS_SERVER_ERROR,
                                                'ConstraintViolationException' => 409,
                                                'FilterNotValidException' => self::STATUS_BAD_REQUEST,
                                                'StreamNotSupportedException' => self::STATUS_PERMISSION_DENIED,
                                                'StorageException' => self::STATUS_SERVER_ERROR,
                                                'ContentAlreadyExistsException' => 409,
                                                'VersioningException' => 409,
                                                'UpdateConflictException' => 409,
                                                'NameConstraintViolationException' => 409,
                                                // additional notable exceptions
                                                'TypeNotSupportedException' => 415,
                                                'UnprocessableEntityException' => 422);
    
    public function __construct($method, $params, $content)
    {
        // We are not going to use the parsed xml content, but rather the raw content;
        // This is due to changes in the CMIS spec more than once requiring a change in 
        // the functions which fetch information from the parsed content; using XMLReader
        // now which should be able to handle changes easier
        parent::__construct($method, $params, $content, false);
    }
    
    public function setContentDownload($contentDownload)
    {
        $this->contentDownload = $contentDownload;
    }
    
    public function isContentDownload()
    {
        return $this->contentDownload;
    }
    
    public function notModified()
    {
        return $this->status == self::STATUS_NOT_MODIFIED;
    }
    
    public function setoutput($output)
    {
        $this->output = $output;
    }
    
    public function getOutput()
    {
        return $this->output;
    }
    
    public function getServiceType()
    {
        return $this->serviceType;
    }
    
    public function setHeader($header = null, $value = null)
    {
		if ($header) header($header . ': ' . $value);
	}
	
	public function getStatusCode($exception)
	{
	    return self::$statusCodeMapping[get_class($exception)];
	}

}
?>