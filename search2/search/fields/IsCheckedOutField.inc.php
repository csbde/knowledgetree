<?php

class IsCheckedOutField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('is_checked_out', 'documents','Is Checked Out');
        $this->setAlias('IsCheckedOut');
        $this->isValueQuoted(false);
    }

	public function modifyValue($value)
    {
    	if (is_numeric($value))
    	{
    		$value = ($value+0)?1:0;
    	}
    	else
    	{
    		switch(strtolower($value))
    		{
    			case 'true':
    			case 'yes':
    				$value=1;
    				break;
    			default:
    				$value=0;
    		}
    	}
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