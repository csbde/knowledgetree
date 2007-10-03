<?php

class ModifiedByField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('modified_user_id', 'documents', _kt('Modified By'));
        $this->setAlias('ModifiedBy');
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