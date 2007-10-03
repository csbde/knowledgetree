<?php

class DocumentTypeField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('document_type_id', 'document_metadata_version', _kt('Document Type'));
        $this->setAlias('DocumentType');
        $this->joinTo('document_types_lookup', 'id');
		$this->matchField('name');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::DOCUMENT_TYPES));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>