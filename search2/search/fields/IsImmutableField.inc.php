<?php

class IsImmutableField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('immutable', 'documents', _kt('Is Immutable'));
        $this->setAlias('IsImmutable');
        $this->isValueQuoted(false);
    }

	public function modifyValue($value)
    {
    	$value = KTUtil::anyToBool($value, false)?1:0;
    	return $value;
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::BOOLEAN));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>