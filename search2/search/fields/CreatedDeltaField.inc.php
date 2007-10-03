<?php

class CreatedDeltaField extends DBFieldExpr
{
	private $modifiedName;

    public function __construct()
    {
        parent::__construct('created', 'documents', _kt('Created Delta'));
        $this->setAlias('CreatedDelta');
        $this->isValueQuoted(false);
    }

    public function modifyName($sql)
    {
    	$this->modifiedName = $sql;
    	$now = date('Y-m-d');


    	return "cast('$now' as date)";
    }


    public function modifyValue($value)
    {

    	return "cast($this->modifiedName + $value as date)";
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::DATEDIFF));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$between);
    }
}

?>