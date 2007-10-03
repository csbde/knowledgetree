<?php

class CheckedOutField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('checkedout', 'documents', _kt('Checked Out'));
        $this->setAlias('CheckedOut');
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