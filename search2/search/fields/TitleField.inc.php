<?php

class TitleField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('name', 'document_metadata_version', 'Title');
        $this->setAlias('Title');
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