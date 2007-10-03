<?php

class AnyMetadataField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('id', 'document_fields_link', _kt('Any Metadata'));
        $this->setAlias('Metadata');
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