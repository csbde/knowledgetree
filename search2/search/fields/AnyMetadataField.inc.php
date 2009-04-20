<?php

/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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

class AnyMetadataField extends DBFieldExpr
{
	public $general_op = ExprOp::CONTAINS;
    public $references = 0;

    public function __construct()
    {
        parent::__construct('value', 'document_fields_link', _kt('Any Metadata'));
        $this->setAlias('Metadata');
    }

    /*
     * Overridden function to adjust table alias in cases of
     * the document_fields_link table being included more than once
     *
     * NOTE this only works in conjunction with code in expr.inc.php which adds the left joins to the db query.
     *      I don't like this and think we should look for a way to make the table joining more generic
     *      such that it can be controlled via these classes and thereby contained as a unit.
     */
    public function modifyName($name)
    {
        if ($this->references > 0)
        {
            static $count = 0;
            if ($count >= $this->references)
            {
                $count = 0;
            }

            if ((($pos = strpos($name, '.')) !== false) && ($count != 0))
            {
                $name = substr($name, 0, $pos) . $count . substr($name, $pos);
            }
            ++$count;
        }

    	return $name;
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::TEXT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>
