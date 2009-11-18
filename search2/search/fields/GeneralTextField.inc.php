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

/**
 * Class to combine multiple search fields under the heading of GeneralText search
 *
 * Fields are added to the GeneralText search by declaring and initialising the
 * ${fieldclass}->general_op variable within the field class you would like to
 * include in the GeneralText search.  To do this, add the following class variable
 * declaration:
 * 
 * public $general_op = ExprOp::CONTAINS;
 *
 * At the time of writing (2009/05/15) the following field classes are included:
 * 
 * AnyMetadataField
 * DiscussionTextField
 * DocumentIdField
 * FilenameField
 * FullPathField
 * TitleField
 * 
 */
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
    	$value = $right;
    	$right=null;

    	$registry = ExprFieldRegistry::getRegistry();
    	$classes = $registry->getGeneralTextClasses();

    	while (!empty($classes))
    	{
    		$classname = array_pop($classes);
    		$obj = new $classname();
    		$exprop = $obj->general_op;
    		$newexpr = new OpExpr($obj, $exprop, $value);
    		if (empty($right))
    		{
    			$right = $newexpr;
    		}
    		else
    		{
				$right = new OpExpr($right, ExprOp::OP_OR, $newexpr);
    		}
    	}
    }
}

?>
