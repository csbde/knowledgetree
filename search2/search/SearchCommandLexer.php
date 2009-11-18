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

class SearchCommandLexer
{
    private $data;
    public $offset;
    public $length;
    public $token;
    public $value;
    private $state;
    private $escaped;
    private $exit;
    private $lookahead;
    private $char;



    public function __construct($data)
    {
        $this->offset=0;
        $this->data=$data;
        $this->token=null;
        $this->value='';
        $this->length=strlen($data);
        $this->state = 0;
        $this->escaped=false;
        $this->exit=false;
        $this->lookahead=null;
        $this->char=null;
    }

    private function processNormalChar()
    {
        $append=true;
        $clear=false;
        $checkwords=false;
        $word='';

        if (in_array($this->char, array('=','(',')','[',']',',','!','<','>','"')) && !empty($this->value))
        {
        	$word=$this->value;
        	$checkwords=true;
        	$this->offset--;
        	$append=false;
        	$clear=false;
        }
        else
        switch ($this->char)
        {
            case ' ':
            case "\t":
            case "\r":
            case "\n":
                if (!empty($this->value))
                {
                    $word=$this->value;
                    $checkwords=true;
                }
                $append=false;
                $clear=true;
                break;
            case '~':
            	$this->token=SearchCommandParser::TILDE;
                break;
            case '=':
                $this->token=SearchCommandParser::IS;
                break;
            case '(':
                $this->token=SearchCommandParser::PAR_OPEN;
                break;
            case ')':
                $this->token=SearchCommandParser::PAR_CLOSE;
                break;
            case ',':
                $this->token=SearchCommandParser::COMMA;
                break;
            case ':':
                $this->token=SearchCommandParser::COLON;
                break;
            case '[':
                $this->token=SearchCommandParser::SQUARE_OPEN;
                break;
            case ']':
                $this->token=SearchCommandParser::SQUARE_CLOSE;
                break;
            case '!':
            	if ($this->lookahead == '=')
                {
                    $this->zap();
                    $this->token=SearchCommandParser::IS_NOT;
                }
                else
                {
                	throw new Exception(sprintf(_kt('Unexpected token: %s'), $this->lookahead));
                }
            	break;
            case '<':
            case '>':
                if ($this->lookahead == '>')
                {
                    $this->zap();
                    $this->token=SearchCommandParser::IS_NOT;
                }
                elseif ($this->lookahead == '=')
                {
                    $this->token=($this->char == '<')?(SearchCommandParser::LE):(SearchCommandParser::GE);
                    $this->zap();
                }
                else
                {
                    $this->token=($this->char == '<')?(SearchCommandParser::LT):(SearchCommandParser::GT);
                }
                break;
            case '"':
                $clear=true;
                $this->state=1;
                break;

        }
        if ($clear)
        {
            $this->char='';
            $this->value='';
            $this->token=null;
        }
        if ($append)
        {
            $this->value .= $this->char;
        }
        if (!is_null($this->token))
        {
            $this->exit=true;
        }
        if ($checkwords)
        {
            $this->exit=true;
            $this->value = $word;
            switch (strtolower($word))
            {
                case 'not':
                    $this->token = SearchCommandParser::NOT;
                    break;
                case 'with':
                    $this->token = SearchCommandParser::WITH;
                    break;
                case 'like':
                    $this->token = SearchCommandParser::LIKE;
                    break;
                case 'contains':
                case 'contain':
                    $this->token = SearchCommandParser::CONTAINS ;
                    break;
                case 'starts':
                case 'start':
                    $this->token = SearchCommandParser::START ;
                    break;
                case 'ends':
                case 'end':
                    $this->token = SearchCommandParser::END ;
                    break;
                case 'does':
                    $this->token = SearchCommandParser::DOES ;
                    break;
                case 'is':
                    $this->token = SearchCommandParser::IS ;
                    break;
                case 'between':
                    $this->token = SearchCommandParser::BETWEEN ;
                    break;
                case 'or':
                    $this->token = SearchCommandParser::OPOR ;
                    break;
                case 'and':
                    $this->token = SearchCommandParser::OPAND ;
                    break;

                default:

                    $this->token = SearchCommandParser::TERMINAL;
                    break;

            }
        }

    }

    private function processStringChar()
    {
        if ($this->escaped)
        {
            switch($this->char)
            {
            	case '"':
                    $this->value .= '"';
                    break;
                case 'r':
                    $this->value .= "\r";
                    break;
                case 'n':
                    $this->value .= "\n";
                    break;
                case 't':
                    $this->value .= "\t";
                    break;
                default:
                    $this->value .= $this->char;
            }
            $this->escaped=false;
        }
        else
        {
            switch($this->char)
            {
                case '\\':
                    $this->escaped=true;
                    break;
                case '"':
                    $this->escaped=false;
                    $this->state=0;
                    $this->exit=true;
                    $this->token = SearchCommandParser::VALUE;
                    break;
                default:
                    $this->value .= $this->char;
            }
        }
    }

    private function zap()
    {
        $this->char = substr($this->data,$this->offset++,1);
        if ($this->offset <= $this->length)
        {
            $this->lookahead= substr($this->data,$this->offset,1);
        }
        else
        {
            $this->lookahead=null;
        }
    }

    public function yylex()
    {
        $this->exit=false;
        $this->token=null;
        $this->value='';
        while (!$this->exit)
        {
            if ($this->length <= $this->offset)
            {
                return false;
            }

            $this->zap();
            switch($this->state)
            {
                case 0: // initial
                    $this->processNormalChar();
                    break;
                case 1: // instring
                    $this->processStringChar();
                    break;
            }

            if (is_null($this->lookahead) || !is_null($this->token))
            {
                $this->exit=true;
            }
        }
        return true;
    }
}

?>
