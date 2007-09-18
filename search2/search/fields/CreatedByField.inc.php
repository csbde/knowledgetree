<?php

class CreatedByField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('creator_id', 'documents','Created By');
        $this->setAlias('CreatedBy');
		$this->joinTo('users', 'id');
		$this->matchField('name');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::USER_LIST));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>