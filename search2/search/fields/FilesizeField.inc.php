<?php

class FilesizeField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('size', 'document_content_version', _kt('Filesize'));
        $this->setAlias('Filesize');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::FILESIZE));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>