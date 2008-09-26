<?php
/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
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

//require_once('../../config/dmsDefaults.php');

/**
 * This is the ideal case, but more complex
 *
 */

// TODO: search expression evaluation needs some optimisation

require_once('indexing/indexerCore.inc.php');
require_once('search/fieldRegistry.inc.php');
require_once('search/exprConstants.inc.php');

class RankManager
{
	/**
	 * This array contains the rankings of fields on database tables.
	 *
	 * @var array
	 */
	private $db;
	/**
	 * Contains the rankings of metadata fields on fieldset/field combinations.
	 *
	 * @var array
	 */
	private $metadata;
	/**
	 * Contains ranking factor for discussion matching
	 *
	 * @var float
	 */
	private $discussion;
	/**
	 * Contains the ranking factor for text matching
	 *
	 * @var float
	 */
	private $text;

	private function __construct()
	{
		$this->dbfields=array();
		$sql = "SELECT groupname, itemname, ranking, type FROM search_ranking";
		$rs = DBUtil::getResultArray($sql);
		foreach($rs as $item)
		{
			switch ($item['type'])
			{
				case 'T':
					$this->db[$item['groupname']][$item['itemname']] = $item['ranking']+0;
					break;
				case 'M':
					$this->metadata[$item['groupname']][$item['itemname']] = $item['ranking']+0;
					break;
				case 'S':
					switch($item['groupname'])
					{
						case 'Discussion':
							$this->discussion = $item['ranking']+0;
							break;
						case 'DocumentText':
							$this->text = $item['ranking']+0;
							break;
					}
					break;
			}
		}
	}

	/**
	 * Enter description here...
	 *
	 * @return RankManager
	 */
	public static function get()
	{
		static $singleton = null;
		if (is_null($singleton))
		{
			$singleton = new RankManager();
		}
		return $singleton;
	}

	public function scoreField($groupname, $type='T', $itemname='')
	{
		switch($type)
		{
			case 'T':
				return $this->db[$groupname][$itemname];
			case 'M':
				return $this->metadata[$groupname][$itemname];
			case 'S':
				switch($groupname)
				{
					case 'Discussion':
						return $this->discussion;
					case 'DocumentText':
						return $this->text;
					default:
						return 0;
				}
			default:
				return 0;
		}
	}
}


class Expr
{
    /**
     * The parent expression
     *
     * @var Expr
     */
    protected $parent;

    protected static $node_id = 0;

    protected $expr_id;

    protected $context;

    public function __construct()
    {
        $this->expr_id = Expr::$node_id++;
    }

    public function appliesToContext()
    {
        return ExprContext::DOCUMENT;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getExprId()
    {
        return $this->expr_id;
    }

    /**
     * Coverts the expression to a string
     *
     * @return string
     */
    public function __toString()
    {
        throw new Exception(sprintf(_kt('Not yet implemented in %s'), get_class($this)));
    }

    /**
     * Reference to the parent expression
     *
     * @return Expr
     */
    public function &getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the parent expiression
     *
     * @param Expr $parent
     */
    public function setParent(&$parent)
    {
        $this->parent = &$parent;
    }

    /**
     * Is the expression valid
     *
     * @return boolean
     */
    public function is_valid()
    {
        return true;
    }

    public function isExpr()
    {
    	return $this instanceof OpExpr;
    }

    public function isOpExpr()
    {
    	return $this instanceof OpExpr;
    }
    public function isValueExpr()
    {
    	return $this instanceof ValueExpr;
    }
    public function isValueListExpr()
    {
    	return $this instanceof ValueListExpr;
    }

    public function isDbExpr()
    {
    	return $this instanceof DBFieldExpr;
    }

    public function isFieldExpr()
    {
    	return $this instanceof FieldExpr;
    }

    public function isSearchableText()
    {
    	return $this instanceof SearchableText ;
    }

    public function isMetadataField()
    {
    	return $this instanceof MetadataField;
    }





    public function toViz(&$str, $phase)
    {
        throw new Exception('To be implemented' . get_class($this));
    }

    public function toVizGraph($options=array())
    {
        $str = "digraph tree {\n";
        if (isset($options['left-to-right']) && $options['left-to-right'])
        {
            $str .= "rankdir=LR\n";
        }

        $this->toViz($str, 0);
        $this->toViz($str, 1);

        $str .= "}\n";

        if (isset($options['tofile']))
        {
            $path=dirname($options['tofile']);
            $filename=basename($options['tofile']);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $base = substr($filename, 0, -strlen($ext)-1);

            $curdir = getcwd();
            chdir($_ENV['PWD']);
            $dotfile="$base.$ext";
            $jpgfile="$base.jpg";
            $fp = fopen($dotfile,'wt');
            fwrite($fp, $str);
            fclose($fp);

            system("dot -Tjpg -o$jpgfile $dotfile 2>1 >/dev/null ");

            if (isset($options['view']) && $options['view'])
            {
                system("eog $jpgfile");
            }
            chdir($curdir);
        }

        return $str;
    }
}

class FieldExpr extends Expr
{
    /**
     * Name of the field
     *
     * @var string
     */
    protected $field;

    protected $alias;

    protected $display;


    /**
     * Constructor for the field expression
     *
     * @param string $field
     */
    public function __construct($field, $display=null)
    {
        parent::__construct();
        $this->field=$field;
        if (is_null($display))
        {
        	$display=get_class($this);
        }
        $this->display = $display;
        $this->setAlias(get_class($this));
    }

    public function setAlias($alias)
    {
        $this->alias=$alias;
    }

    public function getDisplay()
    {
    	return $this->display;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getFullName()
    {
        return $this->alias . '.' . $this->field;
    }

    /**
     * Returns the field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Coverts the expression to a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->display;
    }

    public function toViz(&$str, $phase)
    {
        if ($phase == 0)
        {
            $expr_id = $this->getExprId();
            $str .= "struct$expr_id [style=rounded, label=\"$expr_id: FIELD[$this->alias]\"]\n";
        }
    }

    public function rewrite(&$left, &$op, &$right, $not=false)
    {
    	$input = $left->getInputRequirements();

		if ($input['value']['type'] != FieldInputType::FULLTEXT)
		{
			return;
		}


    	if ($right->isValueExpr())
		{
			$value = $right->getValue();
		}
		else
		{
			$value = $right;
		}

		if ((substr($value,0,1) == '\'' && substr($value,-1) == '\'') || (substr($value,0,1) == '"' && substr($value,-1) == '"'))
		{
			$value =  trim( substr($value,1,-1) );
			$right = new ValueExpr($value);
		}
		else
		{
			OpExpr::rewriteString($left, $op, $right, $not);
		}
    }
}

class ExprContext
{
    const DOCUMENT = 1;
    const FOLDER = 2;
    const DOCUMENT_AND_FOLDER = 3;
}


class DBFieldExpr extends FieldExpr
{
    /**
     * The table the field is associated with
     *
     * @var string
     */
    protected $table;

    protected $jointable;
    protected $joinfield;
    protected $matchfield;
    protected $quotedvalue;


    /**
     * Constructor for the database field
     *
     * @param string $field
     * @param string $table
     */
    public function __construct($field, $table, $display=null)
    {
    	if (is_null($display))
    	{
    		$display = get_class($this);
    	}

        parent::__construct($field, $display);

        $this->table=$table;
        $this->jointable = null;
        $this->joinfield = null;
        $this->matchfield = null;
        $this->quotedvalue=true;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    public function joinTo($table, $field)
    {
    	$this->jointable=$table;
    	$this->joinfield=$field;
    }
    public function matchField($field)
    {
    	$this->matchfield = $field;
    }

	public function modifyName($name)
    {
    	return $name;
    }

	public function modifyValue($value)
    {
    	return $value;
    }


    public function getJoinTable() { return $this->jointable; }
    public function getJoinField() { return $this->joinfield; }
    public function getMatchingField() { return $this->matchfield; }
    public function isValueQuoted($quotedvalue = null)
    {
    	if (isset($quotedvalue))
    	{
    		$this->quotedvalue = $quotedvalue;
    	}
    	return $this->quotedvalue;
    }
}

class MetadataField extends DBFieldExpr
{
    protected $fieldset;
    protected $fieldid;
    protected $fieldsetid;

    public function __construct($fieldset, $field, $fieldsetid, $fieldid)
    {
        parent::__construct($field, 'document_fields_link');
        $this->fieldset=$fieldset;
        $this->fieldid=$fieldid;
        $this->fieldsetid=$fieldsetid;
    }

    public function getFieldSet()
    {
        return $this->fieldset;
    }

    public function getFieldId()
    {
        return $this->fieldid;
    }

    public function getFieldSetId()
    {
        return $this->fieldsetid;
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::TEXT));
    }

    /**
     * Coverts the expression to a string
     *
     * @return string
     */
    public function __toString()
    {
        return "METADATA[$this->fieldset][$this->field]";
    }

}

class SearchableText extends FieldExpr
{
}

class ValueExpr extends Expr
{
    /**
     * The value
     *
     * @var mixed
     */
    protected $value;

    protected $fuzzy;

    protected $proximity;

    /**
     * Constructor for the value expression
     *
     * @param mixed $value
     */
    public function __construct($value, $fuzzy=null, $proximity=null)
    {
        parent::__construct();

        // some keywords are used by lucene, and may conflict. for some reason, if it is lowercase, the problem does not occur.
        if (in_array($value, array('AND','OR','NOT')))
        {
            $value = strtolower($value);
        }
        $this->value= $value;
        $this->fuzzy = $fuzzy;
        $this->proximity = $proximity;
    }

    public function getValue()
    {

        return $this->value;
    }

    /**
     * Converts the value to a string
     *
     * @return unknown
     */
    public function __toString()
    {
        return (string) "\"$this->value\"";
    }

    public function toViz(&$str, $phase)
    {
        if ($phase == 0)
        {
            $expr_id = $this->getExprId();
            $value = addslashes($this->value);
            $str .= "struct$expr_id [style=ellipse, label=\"$expr_id: \\\"$value\\\"\"]\n";
        }
    }




	public function getSQL($field, $fieldname, $op, $not=false)
    {
    	$val = $this->getValue();
    	if (strpos($val, '*') !== false || strpos($val, '?') !== false)
    	{
			$val = str_replace(array('?','*'), array('%','%'), $val);
    	}

    	switch($op)
    	{
    		case ExprOp::LIKE:
    			break;
    		case ExprOp::CONTAINS:
    			$val = "%$val%";
    			break;
    		case ExprOp::STARTS_WITH:
    			$val = "$val%";
    			break;
    		case ExprOp::ENDS_WITH:
    			$val = "%$val";
    			break;
    	}

    	$val = $field->modifyValue($val);
    	$quote = '';
    	if ($field->isValueQuoted())
    	{
    		$val = addslashes($val);
    		$quote = '\'';
    	}

        switch($op)
        {
            case ExprOp::LIKE:
                $sql = "$fieldname LIKE $quote$val$quote";
                if ($not) $sql = "not ($sql)";
                break;
            case ExprOp::CONTAINS:
                $sql = "$fieldname LIKE $quote$val$quote";
                if ($not) $sql = "not ($sql)";
                break;
            case ExprOp::STARTS_WITH:
                $sql = "$fieldname LIKE $quote$val$quote";
                if ($not) $sql = "not ($sql)";
                break;
            case ExprOp::ENDS_WITH:
                $sql = "$fieldname LIKE $quote$val$quote";
                if ($not) $sql = "not ($sql)";
                break;
            case ExprOp::IS:
            	if ($not)
                	$sql = "$fieldname != $quote$val$quote";
                else
                	$sql = "$fieldname = $quote$val$quote";
                break;
            case ExprOp::GREATER_THAN :
            	if ($not)
                	$sql = "$fieldname <= $quote$val$quote";
                else
                	$sql = "$fieldname > $quote$val$quote";
                break;
            case ExprOp::GREATER_THAN_EQUAL  :
            	if ($not)
                	$sql = "$fieldname < $quote$val$quote";
                else
                	$sql = "$fieldname >= $quote$val$quote";
                break;
            case ExprOp::LESS_THAN  :
            	if ($not)
                	$sql = "$fieldname >= $quote$val$quote";
                else
                	$sql = "$fieldname < $quote$val$quote";
                break;
            case ExprOp::LESS_THAN_EQUAL :
            	if ($not)
	                $sql = "$fieldname > $quote$val$quote";
	            else
                	$sql = "$fieldname <= $quote$val$quote";
                break;
            default:
                throw new Exception(sprintf(_kt('Unknown op: %s'), $op));
        }

        return $sql;
    }

}

class ValueListExpr extends Expr
{
    /**
     * The value
     *
     * @var mixed
     */
    protected $values;

    /**
     * Constructor for the value expression
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        parent::__construct($value);
        $this->values=array($value);
    }

    public function addValue($value)
    {
    	$this->values[] = $value;
    }


    public function getValue($param=null)
    {
    	if (!empty($param))
    	{
    		return $this->values[$param];
    	}
        $str = '';

        foreach($this->values as $value)
        {
        	if ($str != '') $str .= ',';
        	$str .= "\"$value\"";
        }

        return $str;
    }

    /**
     * Converts the value to a string
     *
     * @return unknown
     */
    public function __toString()
    {
        return $this->getValue();
    }

    public function toViz(&$str, $phase)
    {
        if ($phase == 0)
        {
            $expr_id = $this->getExprId();

            $str .= "struct$expr_id [style=ellipse, label=\"$expr_id: ";
            $i=0;
            foreach($this->values as $value)
            {
            	if ($i++>0) $str .= ',';
            	$value = addslashes($value);
            	$str .= "\\\"$value\\\"";
            }
            $str .= "\"]\n";
        }
    }



    public function rewrite(&$left, &$op, &$right, &$not)
    {
    	if (count($this->values) == 1)
		{
			$right = new ValueExpr($this->values[0]);
			return;
		}
		$newops = array();
		foreach($this->values as $value)
		{
			$classname = get_class($left);
			$class = new $classname;
			$newop = new OpExpr($class, $op, $value);
			$newops[] = $newop;
		}

		$result = $newops[0];
		for($i=1;$i<count($newops);$i++)
		{
			$result = new OpExpr($result, ExprOp::OP_OR, $newops[$i]);
		}

		$left = $result->left();
		$op = $result->op();
		$right = $result->right();
    }

}


class BetweenValueExpr extends ValueExpr
{
    protected $endvalue;

    public function __construct($start, $end)
    {
        parent::__construct($start);
        $this->endvalue = $end;
    }

    public function getStart()
    {
        return $this->getValue();
    }

    public function getEnd()
    {
        return $this->endvalue;
    }

    /**
     * Converts the value to a string
     *
     * @return unknown
     */
    public function __toString()
    {
        return (string) $this->value  . ' AND ' . $this->endvalue;
    }

    public function toViz(&$str, $phase)
    {
        if ($phase == 0)
        {
            $value = addslashes($this->value);
            $value2 = addslashes($this->endvalue);

            $expr_id = $this->getExprId();
            $str .= "struct$expr_id [style=rounded, label=\"$expr_id: $value AND $value2\"]\n";
        }
    }

    public function getSQL($field, $fieldname, $op, $not=false)
    {
        if ($op != ExprOp::BETWEEN)
        {
            throw new Exception(sprintf(_kt('Unexpected operator: %s'), $op));
        }

		$quote = '';

		$start = $field->modifyValue($this->getStart());
		$end = $field->modifyValue($this->getEnd());

		if ($field->isValueQuoted())
    	{
    		$start = addslashes($start);
    		$end = addslashes($end);
    		$quote = '\'';
    	}


        $not = $not?' NOT ':'';
        return "$not ($fieldname $op $quote$start$quote AND $quote$end$quote) ";
    }
}

interface QueryBuilder
{
	function buildComplexQuery($expr);

	function buildSimpleQuery($op, $group);

	function getRanking($result);

	function getResultText($result);

}

class TextQueryBuilder implements QueryBuilder
{
	private $text;
	private $query;

	public function buildComplexQuery($expr)
	{
		$left = $expr->left();
        $right = $expr->right();
		if (DefaultOpCollection::isBoolean($expr))
		{
			$query = '(' . $this->buildComplexQuery($left) . ' ' . $expr->op() . ' ' . $this->buildComplexQuery($right)  . ')';

			if ($expr->not())
			{
				$query = "NOT $query";
			}
		}
		else
		{
			$fieldname = $left->getField();
            $value = addslashes($right->getValue());

            $not = $expr->not()?' NOT ':'';
            if (empty($value))
            {
                // minor hack to prevent the case of 'field:'. results are no 'field:""'
                $value = ' ';
            }

            if (strpos($value, ' ') === false)
            {
            	$query = "$not$fieldname:$value";
            }
            else
            {
            	$query = "$not$fieldname:\"$value\"";
            }
		}

		return $query;
	}

	public function buildSimpleQuery($op, $group)
	{
		$query = '';
		foreach($group as $expr)
		{
			if (!empty($query))
			{
				$query .= " $op ";
			}

			$left = $expr->left();
            $right = $expr->right();

			$fieldname = $left->getField();
            $value = addslashes($right->getValue());

            $not = $expr->not()?' NOT ':'';

            if (strpos($value, ' ') !== false)
				$query .= "$not$fieldname:\"$value\"";
			else
				$query .= "$not$fieldname:$value";
		}

		return $query;
	}

	public function getRanking($result)
	{
		$init = $result->Rank;
		$score=0;
		$ranker = RankManager::get();
		$score += $init *$ranker->scoreField('DocumentText', 'S');
		return $score;
	}

	public function setQuery($query)
	{
		$this->query = $query;
	}

	function getResultText($result)
	{
		// not require!
		return '';
	}
}

class SQLQueryBuilder implements QueryBuilder
{
	private $used_tables;
	private $aliases;
	private $sql;
	private $db;
	private $metadata;
	private $context;

	public function __construct($context)
	{
	    $this->context = $context;

	    switch ($context)
	    {
	        case ExprContext::DOCUMENT:
	            $this->used_tables = array(
	               'documents'=>1,
	               'document_metadata_version'=>1,
	               'document_content_version'=>0,
	               'tag_words'=>0,
	               'document_fields_link'=>0
	            );

	            $this->aliases = array(
	               'documents'=>'d',
	               'document_metadata_version'=>'dmv',
	               'document_content_version'=>'dcv',
	               'tag_words'=>'tw',
	               'document_fields_link'=>'pdfl'
	            );
	            break;
	        case ExprContext::FOLDER:
	            $this->used_tables = array(
	               'folders'=>1,
	            );

	            $this->aliases = array(
	               'folders'=>'f',
	            );
	            break;
	        default:
	            throw new Exception('This was not expected - Context = ' . $context);
	    }

		$this->sql = '';
		$this->db = array();
		$this->metadata = array();
	}

	/**
	 * This looks up a table name to find the appropriate alias.
	 *
	 * @param string $tablename
	 * @return string
	 */
	private function resolveTableToAlias($tablename)
	{
		if (array_key_exists($tablename, $this->aliases))
		{
			return $this->aliases[$tablename];
		}
		throw new Exception("Unknown tablename '$tablename'");
	}

	private function exploreExprs($expr, $parent=null)
	{
		if ($expr->isMetadataField())
		{
			$this->metadata[] = & $parent;
		}
		elseif ($expr->isDBExpr())
		{
		    if (($this->context & $expr->appliesToContext()) == $this->context)
		    {
		        $this->db[]  = & $parent;
		        $tablename = $expr->getTable();
		        if (array_key_exists($tablename, $this->used_tables))
		        {
		            $this->used_tables[$tablename]++;
		        }
		    }
		}
		elseif ($expr->isOpExpr())
		{
			$left = & $expr->left();
			$right = & $expr->right();
			if (DefaultOpCollection::isBoolean($expr))
			{
				$this->exploreExprs($left, $expr);
				$this->exploreExprs($right, $expr);
			}
			else
			{
				// if it is not a boolean, we only need to explore left as it is the one where the main field is defined.
				$this->exploreExprs($left, $expr);
			}
		}
	}

	private function exploreGroup($group)
	{
		// split up metadata and determine table usage
        foreach($group as $expr)
        {
            $field = $expr->left();

            if ($field->isMetadataField())
            {
                $this->metadata[] = $expr->getParent();
            }
            elseif ($field->isDBExpr())
            {
                $this->db[]  = $expr->getParent();
                $tablename = $field->getTable();
                if (array_key_exists($tablename, $this->used_tables))
                {
                    $this->used_tables[$tablename]++;
                }
            }
        }
	}

	private function getFieldnameFromExpr($expr)
	{
		$field = $expr->left();
		if (is_null($field->getJoinTable()))
		{
			$alias      = $this->resolveTableToAlias($field->getTable());
			$fieldname  = $alias . '.' . $field->getField();
		}
		else
		{
			$offset = $this->resolveJoinOffset($expr);
			$matching = $field->getMatchingField();
			$tablename = $field->getJoinTable();
			$fieldname = "$tablename$offset.$matching";
		}

		return $fieldname;
	}

	private function getSQLEvalExpr($expr)
	{
		$left = $expr->left();
		$right = $expr->right();
		$isNot = $expr->not();
		if ($left->isMetadataField() )
		{
		    if ($this->context == ExprContext::DOCUMENT)
		    {
		        $offset = $this->resolveMetadataOffset($expr) + 1;

		        $fieldset = $left->getField();
		        $query = '(';

		        if ($isNot)
		        {
		            $query .= "df$offset.name IS NULL OR ";
		        }

		        $query .= '(' . "df$offset.name='$fieldset' AND " .  $right->getSQL($left, "dfl$offset.value", $expr->op(), $isNot) . ')';

		        $query .= ')';
		    }
		    else
		    {
		        $query = 'false';
		    }
		}
		else
		{
		    if ($this->context == ExprContext::FOLDER && $left->getContext() != ExprContext::FOLDER)
		    {
		        $query = 'false';
		    }
		    else
		    {
			    $fieldname = $this->getFieldnameFromExpr($expr);
			    $query = $right->getSQL($left, $left->modifyName($fieldname), $expr->op(), $isNot);
		    }
		}
		return $query;
	}

	private function buildCoreSQL()
	{
		if (count($this->metadata) + count($this->db) == 0)
        {
            // return empty result set
            return 'select 1 from documents where false';
        }

		$sql =
            'SELECT ' . "\n";

        if ($this->context == ExprContext::DOCUMENT)
        {
            // we are doing this because content table is dependant on metadata table
            if ($this->used_tables['document_content_version'] > 0)
            {
                $this->used_tables['document_metadata_version']++;
            }

            $sql .=
            ' DISTINCT d.id, dmv.name as title';
        }
        else
        {
            $sql .=
            ' DISTINCT f.id, f.name as title';
        }


        $offset=0;
        foreach($this->db as $expr)
        {
            $offset++;
            $sql .= ", ifnull(" . $this->getSQLEvalExpr($expr) . ",0) as expr$offset ";
        }

        foreach($this->metadata as $expr)
        {
        	$offset++;
        	$sql .= ", ifnull(" . $this->getSQLEvalExpr($expr) . ",0) as expr$offset ";
        }

        $sql .=
            "\n" . 'FROM ' ."\n";

        if ($this->context == ExprContext::DOCUMENT)
        {
            $primaryAlias = 'd';
            $sql .= ' documents d ' ."\n";

            if ($this->used_tables['document_metadata_version'] > 0)
            {
                $sql .= ' INNER JOIN document_metadata_version dmv ON d.metadata_version_id=dmv.id' . "\n";
            }
            if ($this->used_tables['document_content_version'] > 0)
            {
                $sql .= ' INNER JOIN document_content_version dcv ON dmv.content_version_id=dcv.id ' . "\n";
            }
            if ($this->used_tables['document_fields_link'] > 0)
            {
                $sql .= ' LEFT JOIN document_fields_link pdfl ON dmv.id=pdfl.metadata_version_id ' . "\n";
            }

            if ($this->used_tables['tag_words'] > 0)
            {
                $sql .= ' LEFT OUTER JOIN document_tags dt  ON dt.document_id=d.id ' . "\n" .
                ' LEFT OUTER JOIN tag_words tw  ON dt.tag_id = tw.id ' . "\n";
            }
        }
        else
        {
            $primaryAlias = 'f';
            $sql .= ' folders f ' ."\n";
        }

		$offset = 0;
        foreach($this->db as $expr)
        {
        	$field       = $expr->left();
        	$jointable=$field->getJoinTable();
        	if (!is_null($jointable))
        	{
				$fieldname = $this->resolveTableToAlias($field->getTable()) . '.' . $field->getField();

    	        $joinalias = "$jointable$offset";
    	        $joinfield = $field->getJoinField();
				$sql .= " LEFT OUTER JOIN $jointable $joinalias ON $fieldname=$joinalias.$joinfield\n";
        	}
        	$offset++;
        }

        if ($this->context == ExprContext::DOCUMENT)
        {
            $offset=0;
            foreach($this->metadata as $expr)
            {
                $offset++;
                $field = $expr->left();

                $fieldid = $field->getFieldId();
                $sql .= " LEFT JOIN document_fields_link dfl$offset ON dfl$offset.metadata_version_id=d.metadata_version_id AND dfl$offset.document_field_id=$fieldid" . "\n";
                $sql .= " LEFT JOIN document_fields df$offset ON df$offset.id=dfl$offset.document_field_id" . "\n";
            }
        }

        // Add permissions sql for read access
        $oPermission =& KTPermission::getByName('ktcore.permissions.read');
        $permId = $oPermission->getID();
        $oUser = User::get($_SESSION['userID']);
        $aPermissionDescriptors = KTPermissionUtil::getPermissionDescriptorsForUser($oUser);
        $sPermissionDescriptors = empty($aPermissionDescriptors)? -1: implode(',', $aPermissionDescriptors);

        $sql .= "INNER JOIN permission_lookups AS PL ON $primaryAlias.permission_lookup_id = PL.id\n";
        $sql .= 'INNER JOIN permission_lookup_assignments AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = '.$permId. " \n";
        $sql .= "WHERE PLA.permission_descriptor_id IN ($sPermissionDescriptors) AND ";

        if ($this->context == ExprContext::DOCUMENT)
        {
             $sql .= "dmv.status_id=1 AND d.status_id=1 AND  \n ";
        }
       	return $sql;
	}

	private function resolveMetadataOffset($expr)
	{
		if (!$expr->left()->isMetadataField())
		{
			throw new Exception(_kt('Metadata field expected'));
		}

		$offset=0;
		foreach($this->metadata as $item)
		{
			if ($item->getExprId() == $expr->getExprId())
			{
				return $offset;
			}
			$offset++;
		}
		throw new Exception('metadata field not found');
	}

	private function resolveJoinOffset($expr)
	{
		$offset=0;
		foreach($this->db as $item)
		{
			if ($item->getExprId() == $expr->getExprId())
			{
				return $offset;
			}
			$offset++;
		}
		throw new Exception('join field not found');
	}

	private function buildCoreSQLExpr($expr)
	{
		$left = $expr->left();
        $right = $expr->right();
		if (DefaultOpCollection::isBoolean($expr))
		{
		  $query = '(' . $this->buildCoreSQLExpr($left) . ' ' . $expr->op() . ' ' . $this->buildCoreSQLExpr($right)  . ')';
		}
		else
		{
		    if (($this->context & $expr->appliesToContext()) == $this->context)
		    {
		        $query = $this->getSQLEvalExpr($expr);
		    }
		    else
		    {
		        $query = 'false';
		    }
		}

		return $query;
	}

	public function buildComplexQuery($expr)
	{
//		print "building complex \n\n";
		$this->exploreExprs($expr);

		$sql = $this->buildCoreSQL();

		$expr = $this->buildCoreSQLExpr($expr);
		if ($expr != 'false')
		{
		    $sql .= $expr;
		}

		return $sql;
	}

	public function buildSimpleQuery($op, $group)
	{
//		print "building simple \n\n";
		$this->exploreGroup($group);

        $sql = $this->buildCoreSQL();

        $offset=0;
        foreach($this->db as $expr)
        {
            if ($offset++)
            {
                $sql .= " $op\n " ;
            }

			$field       = $expr->left();

			if (is_null($field->getJoinTable()))
				{
	        	    $alias      = $this->resolveTableToAlias($field->getTable());
    	        	$fieldname  = $alias . '.' . $field->getField();
				}
				else
				{
					$offset = $this->resolveJoinOffset($expr);
					$matching = $field->getMatchingField();
					$tablename = $field->getJoinTable();
					$fieldname = "$tablename$offset.$matching";
				}


            $value      = $expr->right();
            $sql .= $value->getSQL($field, $left->modifyName($fieldname), $expr->op(), $expr->not());
        }

        if ($this->context == ExprContext::DOCUMENT)
        {
            $moffset=0;
            foreach($this->metadata as $expr)
            {
                $moffset++;
                if ($offset++)
                {
                    $sql .= " $op\n " ;
                }

                $field = $expr->left();
                $value = $expr->right();

                $sql .= $value->getSQL($field, "dfl$moffset.value", $expr->getOp());
            }
        }

        return $sql;
	}

	public function getRanking($result)
	{
		$ranker = RankManager::get();
		$score = 0;
		foreach($result as $col=>$val)
		{
			if ($val + 0 == 0)
			{
				// we are not interested if the expression failed
				continue;
			}

			if (substr($col, 0, 4) == 'expr' && is_numeric(substr($col, 4)))
			{
				$exprno = substr($col, 4);
				if ($exprno <= count($this->db))
				{
					$expr = $this->db[$exprno-1];
					$left=$expr->left();
					$score += $ranker->scoreField($left->getTable(), 'T', $left->getField());
				}
				else
				{
					$exprno -= count($this->db);
					$expr = $this->metadata[$exprno-1];
					$left=$expr->left();
					$score += $ranker->scoreField($left->getTable(), 'M', $left->getField());
				}
			}
		}

		return $score;
	}

	public function getResultText($result)
	{
		$text = array();
		foreach($result as $col=>$val)
		{
			if (substr($col, 0, 4) == 'expr' && is_numeric(substr($col, 4)))
			{
				if ($val + 0 == 0)
				{
					// we are not interested if the expression failed
					continue;
				}
				$exprno = substr($col, 4);
				if ($exprno <= count($this->db))
				{
					$expr = $this->db[$exprno-1];
				}
				else
				{
					$exprno -= count($this->db);
					$expr = $this->metadata[$exprno-1];
				}
				$text[] = (string) $expr;
			}
		}
		return '(' . implode(') AND (', $text) . ')';
	}
}



class OpExpr extends Expr
{
    /**
     * The left side of the  expression
     *
     * @var Expr
     */
    protected $left_expr;

    /**
     * The operator on the left and right
     *
     * @var ExprOp
     */
    protected $op;
    /**
     * The right side of the expression
     *
     * @var Expr
     */
    protected $right_expr;

    /**
     * This indicates that the expression is negative
     *
     * @var boolean
     */
    protected $not;

    protected $point;

    protected $has_text;
    protected $has_db;

    private $debug = false;

//    protected $flattened;

    protected $results;

    public function setResults($results)
    {
        $this->results=$results;
    }
    public function getResults()
    {
        return $this->results;
    }

    public function setHasDb($value=true)
    {
        $this->has_db=$value;
    }

    public function setHasText($value=true)
    {
        $this->has_text=$value;
    }

    public function setContext($context)
    {
        parent::setContext($context);
        $this->left()->setContext($context);
        $this->right()->setContext($context);
    }

    public function getHasDb()
    {
        return $this->has_db;
    }
    public function getHasText()
    {
        return $this->has_text;
    }
    public function setPoint($point)
    {
        $this->point = $point;
       /* if (!is_null($point))
        {
            $this->flattened = new FlattenedGroup($this);
        }
        else
        {
            if (!is_null($this->flattened))
            {
                unset($this->flattened);
            }
            $this->flattened = null;
        }*/
    }

    public function getPoint()
    {
        return $this->point;
    }

	public function hasSameOpAs($expr)
	{
		return $this->op() == $expr->op();
	}

	public static function rewriteString(&$left, &$op, &$right, $not=false)
    {
		if ($right->isValueExpr())
    	{
    		$value = $right->getValue();
    	}
    	else
    	{
    		$value = $right;
    	}

    	$text = array();


    	preg_match_all('/[\']([^\']*)[\']/',$value, $matches);

    	foreach($matches[0] as $item)
    	{
    		$text [] = $item;

    		$value = str_replace($item, '', $value);
    	}

    	$matches = explode(' ', $value);

    	foreach($matches as $item)
    	{
    		if (empty($item)) continue;
    		$text[] = $item;
    	}

    	if (count($text) == 1)
    	{
    		return;
    	}

    	$doctext = $left;

    	$left = new OpExpr($doctext, $op, new ValueExpr($text[0]));

    	for($i=1;$i<count($text);$i++)
    	{
    		if ($i==1)
    		{
    			$right = new OpExpr($doctext, $op, new ValueExpr($text[$i]));
    		}
    		else
    		{
    			$join = new OpExpr($doctext, $op, new ValueExpr($text[$i]));
    			$right = new OpExpr($join, ExprOp::OP_AND, $right);
    		}
    	}

    	$op = ExprOp::OP_AND;
    }


    /**
     * Constructor for the expression
     *
     * @param Expr $left
     * @param ExprOp $op
     * @param Expr $right
     */
    public function __construct($left, $op, $right, $not = false)
    {
    	// if left is a string, we assume we should convert it to a FieldExpr
        if (is_string($left))
        {
            $left = new $left;
	    }

        // if right is not an expression, we must convert it!
        if (!($right instanceof Expr))
        {
            $right = new ValueExpr($right);
        }

        if ($right->isValueListExpr())
        {
			$right->rewrite($left, $op, $right, $not);
        }
        else
        // rewriting is based on the FieldExpr, and can expand a simple expression
        // into something a little bigger.
		if ($left->isFieldExpr())
		{
			 $left->rewrite($left, $op, $right, $not);
		}

		// transformation is required to optimise the expression tree so that
		// the queries on the db and full text search are optimised.
		if (DefaultOpCollection::isBoolean($op))
		{
			$this->transform($left, $op, $right, $not);
		}

        parent::__construct();

        $left->setParent($this);
        $right->setParent($this);
        $this->left_expr=&$left;
        $this->op = $op;
        $this->right_expr=&$right;
        $this->not = $not;
        $this->has_text=false;

       // $this->setPoint('point');

        if ($left->isSearchableText())
        {
            $this->setHasText();
        }
        else if ($left->isDBExpr())
        {
            $this->setHasDb();
        }
		elseif ($left->isOpExpr())
        {
            if ($left->getHasText()) { $this->setHasText(); }
            if ($left->getHasDb())   { $this->setHasDb(); }
        }

        if ($right->isOpExpr())
        {
            if ($right->getHasText()) { $this->setHasText(); }
            if ($right->getHasDb())   { $this->setHasDb(); }
        }
     //   $this->flattened=null;

     	// $left_op, etc indicates that $left expression is a logical expression
        $left_op = ($left->isOpExpr() && DefaultOpCollection::isBoolean($left));
        $right_op = ($right->isOpExpr() && DefaultOpCollection::isBoolean($right));

		// check which trees match
        $left_op_match  = ($left_op  && $this->hasSameOpAs($left)) ;
        $right_op_match = ($right_op  && $this->hasSameOpAs($left)) ;

        $point = null;

        if ($left_op_match && $right_op_match) { $point = 'point'; }

		$left_op_match_flex  = $left_op_match || ($left->isOpExpr());
        $right_op_match_flex = $right_op_match || ($right->isOpExpr());

        if ($left_op_match_flex && $right_op_match_flex) { $point = 'point'; }

        if (!is_null($point))
        {
        	if ($left_op_match && $left->getPoint() == 'point')   { $left->setPoint(null); }
        	if ($right_op_match && $right->getPoint() == 'point') { $right->setPoint(null); }

        	if ($left->isMergePoint() && is_null($right->getPoint())) { $right->setPoint('point'); }
        	if ($right->isMergePoint() && is_null($left->getPoint())) { $left->setPoint('point'); }

        	if ($left->isMergePoint() || $right->isMergePoint())
        	{
        		$point = 'merge';

        		if (!$left->isMergePoint()) { $left->setPoint('point'); }
	        	if (!$right->isMergePoint()) { $right->setPoint('point'); }

	        	if ($this->isDBonly() || $this->isTextOnly())
        		{
					$this->clearPoint();
					$point = 'point';
        		}
        	}
        }

        if ($point == 'point')
        {
			if ($this->isDBandText())
			{
				$point = 'merge';
				$left->setPoint('point');
				$right->setPoint('point');
			}
        }
        if (is_null($point) && !DefaultOpCollection::isBoolean($op))
        {
        	$point = 'point';
        }

		$this->setPoint($point);
    }

    private function isDBonly()
    {
    	return $this->getHasDb() && !$this->getHasText();
    }

    private function isTextOnly()
    {
    	return !$this->getHasDb() && $this->getHasText();
    }

    private function isDBandText()
    {
    	return $this->getHasDb() && $this->getHasText();
    }

    public function appliesToContext()
    {
        return $this->left()->appliesToContext() | $this->right()->appliesToContext();
    }

    /**
     * Enter description here...
     *
     * @param OpExpr $expr
     */
    protected  function clearPoint()
    {
    	if (DefaultOpCollection::isBoolean($this))
    	{
			$this->left()->clearPoint();
			$this->right()->clearPoint();
    	}
    	if ($this->isMergePoint())
    	{
    		$this->setPoint(null);
    	}
    }


    protected function isMergePoint()
    {
    	return in_array($this->getPoint(), array('merge','point'));
    }

    /**
     * Returns the operator on the expression
     *
     * @return ExprOp
     */
    public function op()
    {
        return $this->op;
    }

    /**
     * Returns true if the negative of the operator should be used in evaluation
     *
     * @param boolean $not
     * @return boolean
     */
    public function not($not=null)
    {
        if (!is_null($not))
        {
            $this->not = $not;
        }

        return $this->not;
    }

    /**
     * The left side of the  expression
     *
     * @return Expr
     */
    public function &left()
    {
        return $this->left_expr;
    }

    /**
     * The right side of the  expression
     *
     * @return Expr
     */
    public function &right()
    {
        return $this->right_expr;
    }

    /**
     * Converts the expression to a string
     *
     * @return string
     */
    public function __toString()
    {
        // _kt may not translate well here.
        $expr = $this->left_expr . ' ' . _kt($this->op) .' ' .  $this->right_expr;

        if (is_null($this->parent))
        {
        	if ($this->not())
        	{
            	 $expr = "NOT $expr";
        	}
            return $expr;
        }

        if ($this->parent->isOpExpr())
        {
            if ($this->parent->op != $this->op && in_array($this->op, DefaultOpCollection::$boolean))
            {
                 $expr = "($expr)";
            }
        }

        if ($this->not())
        {
             $expr = "!($expr)";
        }

        return $expr;
    }

    /**
     * Is the expression valid
     *
     * @return boolean
     */
    public function is_valid()
    {
        $left = $this->left();
        $right = $this->right();
        return $left->is_valid() && $right->is_valid();
    }

    /**
     * Finds the results that are in both record sets.
     *
     * @param array $leftres
     * @param array $rightres
     * @return array
     */
	protected static function _intersect($leftres, $rightres)
    {
    	if (empty($leftres) || empty($rightres))
    	{
    		return array(); // small optimisation
    	}
    	$result = array();
    	foreach($leftres as $item)
    	{
    		$document_id = $item->Id;

    		if (!$item->IsLive)
    		{
    			continue;
    		}

    		if (array_key_exists($document_id, $rightres))
    		{
    			$check = $rightres[$document_id];

    			$result[$document_id] = ($item->Rank < $check->Rank)?$check:$item;
    		}
    	}
    	return $result;
    }

	protected static function intersect($leftres, $rightres)
    {
        return array(
            'docs'=>self::_intersect($leftres['docs'],$rightres['docs']),
            'folders'=>self::_intersect($leftres['folders'],$rightres['folders'])
        );
    }

	protected static function union($leftres, $rightres)
    {
        return array(
            'docs'=>self::_union($leftres['docs'],$rightres['docs']),
            'folders'=>self::_union($leftres['folders'],$rightres['folders'])
        );
    }

    /**
     * The objective of this function is to merge the results so that there is a union of the results,
     * but there should be no duplicates.
     *
     * @param array $leftres
     * @param array $rightres
     * @return array
     */
    protected static function _union($leftres, $rightres)
    {
    	if (empty($leftres))
    	{
    		return $rightres; // small optimisation
    	}
    	if (empty($rightres))
    	{
    		return $leftres; // small optimisation
    	}
    	$result = array();

    	foreach($leftres as $item)
    	{
			if ($item->IsLive)
    		{
    			$result[$item->Id] = $item;
    		}
    	}

    	foreach($rightres as $item)
    	{
    		if (!array_key_exists($item->Id, $result) || $item->Rank > $result[$item->Id]->Rank)
    		{
    			$result[$item->Id] = $item;
    		}
    	}
    	return $result;
    }

    /**
     * Enter description here...
     *
     * @param OpExpr $left
     * @param ExprOp $op
     * @param OpExpr $right
     * @param boolean $not
     */
    public function transform(& $left, & $op, & $right, & $not)
    {

    	if (!$left->isOpExpr() || !$right->isOpExpr() || !DefaultOpCollection::isBoolean($op))
    	{
    		return;
    	}

		if ($left->isTextOnly() && $right->isDBonly())
		{
			// we just swap the items around, to ease other transformations
			$tmp = $left;
			$left = $right;
			$right = $tmp;
			return;
		}

		if ($op != $right->op() || !DefaultOpCollection::isBoolean($right))
		{
			return;
		}

		if ($op == ExprOp::OP_OR && ($not || $right->not()))
		{
			// NOTE: we can't transform. e.g.
			// db or !(db or txt) => db or !db and !txt
			// so nothing to do

			// BUT: db and !(db and txt) => db and !db and !txt
			return;
		}

		$rightLeft = $right->left();
		$rightRight = $right->right();

		if ($left->isDBonly() && $rightLeft->isDBonly())
		{
			$newLeft = new OpExpr( $left, $op, $rightLeft );

			$right = $rightRight;
			$left = $newLeft;
			return;
		}

		if ($left->isTextOnly() && $rightRight->isTextOnly())
		{
			$newRight = new OpExpr($left, $op, $rightRight);
			$left = $rightLeft;
			$right = $newRight;
			return;
		}

    }

    private function findDBNode($start, $op, $what)
    {
    	if ($start->op() != $op)
    	{
    		return null;
    	}
    	switch($what)
    	{
    		case 'db':
    			if ($start->isDBonly())
    			{
    				return $start;
    			}
    			break;
    		case 'txt':
    			if ($start->isTextOnly())
    			{
    				return $start;
    			}
    			break;
    	}
    	$node = $this->findDBNode($start->left(), $op, $what);
    	if (is_null($left))
    	{
    		$node = $this->findDBNode($start->right(), $op, $what);
    	}
    	return $node;

    }

    public function traverse($object, $method, $param)
    {
    	if ($this->isOpExpr())
    	{
	    	$object->$method($param);
    	}
    }

    private function exploreItem($item, & $group, $interest)
    {
    	if (($interest == 'db' && $item->getHasDb()) ||
    		($interest == 'text' && $item->getHasText()))
    	{
			if (in_array($item->op(), array(ExprOp::OP_OR, ExprOp::OP_AND)))
			{
				$this->exploreItem($item->left(),  $group, $interest);
				$this->exploreItem($item->right(),  $group, $interest);
			}
			else
			{
				$group[] = $item;
			}
    	}
    }

    private function explore($left, $right, & $group, $interest)
    {
		$this->exploreItem($left,  $group, $interest);
		$this->exploreItem($right,  $group, $interest);
    }

    private function exec_db_query($op, $group)
    {
    	if (empty($group)) { return array(); }

    	$exprbuilder = new SQLQueryBuilder($this->getContext());

    	if (count($group) == 1)
    	{
    		$sql = $exprbuilder->buildComplexQuery($group[0]);
    	}
    	else
    	{
			$sql = $exprbuilder->buildSimpleQuery($op, $group);
    	}

    	$results = array();

    	global $default;
    	$default->log->debug("SEARCH SQL: $sql");
    	$rs = DBUtil::getResultArray($sql);

    	if (PEAR::isError($rs))
    	{
    		throw new Exception($rs->getMessage());
    	}

    	foreach($rs as $item)
    	{
    		$id = $item['id'];
    		$rank = $exprbuilder->getRanking($item);
    		if (!array_key_exists($id, $results) || $rank > $results[$id]->Rank)
    		{
    		    if ($this->context == ExprContext::DOCUMENT)
    		    {
    		        $results[$id] = new DocumentResultItem($id, $rank, $item['title'], $exprbuilder->getResultText($item));
    		    }
    		    else
    		    {
    		        $results[$id] = new FolderResultItem($id, $rank, $item['title'], $exprbuilder->getResultText($item));
    		    }
    		}
    	}

    	return $results;

    }

    private function exec_text_query($op, $group)
    {
        if (($this->getContext() != ExprContext::DOCUMENT) || empty($group))
        {
            return array();
        }

    	$exprbuilder = new TextQueryBuilder();

    	if (count($group) == 1)
    	{
    		$query = $exprbuilder->buildComplexQuery($group[0]);
    	}
    	else
    	{
			$query = $exprbuilder->buildSimpleQuery($op, $group);
    	}

    	$indexer = Indexer::get();
    	global $default;
    	$default->log->debug("SEARCH LUCENE: $query");

    	$results = $indexer->query($query);
    	foreach($results as $item)
    	{
    		$item->Rank = $exprbuilder->getRanking($item);
    		$exprbuilder->setQuery($query);
    		//$item->Text = $exprbuilder->getResultText($item); ?? wipe - done at indexer level
    	}

    	return $results;
    }

	public function evaluate($context = ExprContext::DOCUMENT_AND_FOLDER)
	{
	    if ($context == ExprContext::DOCUMENT_AND_FOLDER)
	    {
	       $docs = $this->evaluate(ExprContext::DOCUMENT);
 	       $folders = $this->evaluate(ExprContext::FOLDER);

	       return array(
	           'docs' => $docs['docs'],
	           'folders' => $folders['folders']);
	    }
	    $this->setContext($context);

		$left = $this->left();
        $right = $this->right();
        $op = $this->op();
        $point = $this->getPoint();
        $result = array();
        if (empty($point))
        {
        	$point = 'point';
        }
		$resultContext = ($this->getContext() == ExprContext::DOCUMENT)?'docs':'folders';

		if ($point == 'merge')
		{
			$leftres = $left->evaluate($context);
			$rightres = $right->evaluate($context);
			switch ($op)
			{
				case ExprOp::OP_AND:
					if ($this->debug) print "\n\nmerge: intersect\n\n";
					$result = OpExpr::intersect($leftres, $rightres);
					break;
				case ExprOp::OP_OR:
					if ($this->debug) print "\n\nmerge: union\n\n";
					$result = OpExpr::union($leftres, $rightres);
					break;
				default:
					throw new Exception("this condition should not happen");
			}
		}
		elseif ($point == 'point')
		{
			if ($this->isDBonly())
			{
				$result[$resultContext] = $this->exec_db_query($op, array($this));
			}
			elseif ($this->isTextOnly())
			{
				$result[$resultContext] = $this->exec_text_query($op, array($this));
			}
			elseif (in_array($op, array(ExprOp::OP_OR, ExprOp::OP_AND)))
			{
			    // do we get to this???
			    // TODO: remove me please.... the simpleQuery stuff should go???
				$db_group = array();
				$text_group = array();
				$this->explore($left, $right, $db_group, 'db');
				$this->explore($left, $right, $text_group, 'text');

				$db_result[$resultContext] = $this->exec_db_query($op, $db_group);
				$text_result[$resultContext] = $this->exec_text_query($op, $text_group);

				switch ($op)
				{
					case ExprOp::OP_AND:
						if ($this->debug) print "\n\npoint: intersect\n\n";
						$result[$resultContext] = OpExpr::intersect($db_result, $text_result);
						break;
					case ExprOp::OP_OR:
						if ($this->debug) print "\n\nmerge: union\n\n";
						$result[$resultContext] = OpExpr::union($db_result, $text_result);
						break;
					default:
						throw new Exception('how did this happen??');
				}
			}
			else
			{
				throw new Exception('and this?');
			}
		}
		else
		{
			// we don't have to do anything
			//throw new Exception('Is this reached ever?');
		}

		$permResults = array();
		foreach($result[$resultContext] as $idx=>$item)
		{
			$permResults[$resultContext][$idx] = $item;
		}

		return $permResults;
	}

    public function toViz(&$str, $phase)
    {
        $expr_id = $this->getExprId();
        $left = $this->left();
        $right = $this->right();
        $hastext = $this->getHasText()?'TEXT':'';
        $hasdb = $this->getHasDb()?'DB':'';
        switch ($phase)
        {
            case 0:
                $not = $this->not()?'NOT':'';
                $str .= "struct$expr_id [style=box, label=\"$expr_id: $not $this->op $this->point $hastext$hasdb\"]\n";
                break;
            case 1:
                $left_id = $left->getExprId();
                $str .= "struct$expr_id -> struct$left_id\n";
                $right_id = $right->getExprId();
                $str .= "struct$expr_id -> struct$right_id\n";
                break;
        }
        $left->toViz($str, $phase);
        $right->toViz($str, $phase);
    }
}

?>