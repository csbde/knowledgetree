<?php

class CheckedOutByField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('checked_out_user_id', 'documents', _kt('Checked Out By'));
        $this->setAlias('CheckedOutBy');
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