<?php

class MimeTypeField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('mime_id', 'document_content_version', _kt('Mime Type'));
        $this->setAlias('MimeType');
        $this->joinTo('mime_types', 'id');
		$this->matchField('mimetypes');
    }

    public function getInputRequirements()
    {
    	// ideally MIME_TYPES
    	// but we must rework the mime_types table to be prettier!
        return array('value'=>array('type'=>FieldInputType::TEXT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>