<?php

class IsCheckedOutField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('is_checked_out', 'documents', _kt('Is Checked Out'));
        $this->setAlias('IsCheckedOut');
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