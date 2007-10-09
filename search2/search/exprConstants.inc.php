<?php

class ExprOp
{
    const IS                 = 'is';
    const CONTAINS           = 'contains';
    const BETWEEN            = 'between';
    const STARTS_WITH            = 'start with';
    const ENDS_WITH			= 'ends with';
    const LIKE               = 'like';
    const LESS_THAN          = '<';
    const GREATER_THAN       = '>';
    const LESS_THAN_EQUAL    = '<=';
    const GREATER_THAN_EQUAL = '>=';
    const OP_AND                = 'AND';
    const OP_OR                = 'OR';
    const IS_NOT				= 'is not';

}



/**
 * This is a collection of various operators that may be used
 */
class DefaultOpCollection
{
    public static $is = array(ExprOp::IS);
    public static $contains = array(ExprOp::CONTAINS, ExprOp::STARTS_WITH , ExprOp::ENDS_WITH );
    public static $between = array(ExprOp::BETWEEN);
    public static $boolean = array(ExprOp::OP_OR , ExprOp::OP_AND );

    /**
     * Validates if the operator on the expression's parent is allowed
     *
     * @param Expr $expr
     * @param array $collection
     * @return boolean
     */
    public static function validateParent(&$expr, &$collection)
    {
        $parent = $expr->getParent();
        if ($parent instanceof OpExpr)
        {
            return in_array($parent->op(), $collection);
        }
        return false;
    }

    public static function validate(&$expr, &$collection)
    {
        if ($expr instanceof OpExpr)
        {
            return in_array($expr->op(), $collection);
        }
        return false;
    }

    public static function isBoolean(&$expr)
    {
    	 if ($expr instanceof OpExpr)
    	 {
    	 	return in_array($expr->op(), DefaultOpCollection::$boolean);
    	 }
    	 elseif(is_string($expr))
    	 {
    	 	return in_array($expr, DefaultOpCollection::$boolean);
    	 }
    	 return false;
    }
}

class FieldInputType
{
    const TEXT      = 'STRING';
    const INT      = 'INT';
    const REAL      = 'FLOAT';
    const BOOLEAN      = 'BOOL';
    const USER_LIST = 'USERLIST';
    const DATE = 'DATE';
    const MIME_TYPES = 'MIMETYPES';
    const DOCUMENT_TYPES = 'DOCTYPES';
    const DATEDIFF = 'DATEDIFF';
    const FULLTEXT = 'FULLTEXT';
    const FILESIZE = 'FILESIZE';
}

?>