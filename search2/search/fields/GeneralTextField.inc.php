<?php

class GeneralTextField extends SearchableText
{
    public function __construct()
    {
        parent::__construct('General', _kt('General Text'));
        $this->setAlias('GeneralText');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::TEXT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$contains);
    }


    public function rewrite(&$left, &$op, &$right)
    {
    	// note the grouping of the db queries

    	$left = new OpExpr(new DocumentTextField(), ExprOp::CONTAINS, $right);

    	$op = ExprOp::OP_OR;

    	$right = new OpExpr(
    					new OpExpr(new FilenameField(), ExprOp::CONTAINS, $right),
    					ExprOp::OP_OR,
    					new OpExpr(
    						new OpExpr(
    							new TitleField(), ExprOp::CONTAINS, $right),
    							ExprOp::OP_OR,
			    				new OpExpr(new AnyMetadataField(), ExprOp::CONTAINS, $right)
			    			)
			    		);
    }


}

?>