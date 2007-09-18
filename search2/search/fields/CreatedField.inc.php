<?php

class CreatedField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('created', 'documents', 'Created');
        $this->setAlias('Created');
    }

    public function modifyName($sql)
    {
    	return "cast($sql as date)";
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::DATE));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$between);
    }
}

?>