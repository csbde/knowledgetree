<?php
/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */
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


    private static
    function init()
    {
        // this isn't really called, but used to help with translation.
        _kt('is');
        _kt('contains');
        _kt('between');
        _kt('start with');
        _kt('ends with');
        _kt('like');
        _kt('AND');
        _kt('OR');
        _kt('is not');
    }

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
