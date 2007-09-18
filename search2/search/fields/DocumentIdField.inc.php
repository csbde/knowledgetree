<?php

class DocumentIdField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('id', 'documents', 'Document ID');
        $this->setAlias('DocumentId');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::INT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>