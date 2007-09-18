<?php

class FolderIDField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('folder_id', 'documents', 'Folder ID');
        $this->setAlias('FolderID');
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