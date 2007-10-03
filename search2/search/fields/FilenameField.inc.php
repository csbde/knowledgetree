<?php

class FilenameField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('filename', 'document_content_version', _kt('Filename'));
        $this->setAlias('Filename');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::TEXT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>