<?php

class FolderField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('full_path', 'documents', _kt('Folder'));
        $this->setAlias('Folder');
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