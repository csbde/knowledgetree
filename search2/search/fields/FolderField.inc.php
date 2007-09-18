<?php

class FolderField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('folder_id', 'documents', 'Folder');
        $this->setAlias('Folder');
        $this->joinTo('folders', 'id');
		$this->matchField('full_path');
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