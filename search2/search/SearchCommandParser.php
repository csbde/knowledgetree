<?php
/* Driver template for the PHP_SearchCommandParserrGenerator parser generator. (PHP port of LEMON)
*/

/**
 * This can be used to store both the string representation of
 * a token, and any useful meta-data associated with the token.
 *
 * meta-data should be stored as an array
 */
class SearchCommandParseryyToken implements ArrayAccess
{
    public $string = '';
    public $metadata = array();

    function __construct($s, $m = array())
    {
        if ($s instanceof SearchCommandParseryyToken) {
            $this->string = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof SearchCommandParseryyToken) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    function __toString()
    {
        return $this->_string;
    }

    function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof SearchCommandParseryyToken) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);
                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof SearchCommandParseryyToken) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

/** The following structure represents a single element of the
 * parser's stack.  Information stored includes:
 *
 *   +  The state number for the parser at this level of the stack.
 *
 *   +  The value of the token stored at this level of the stack.
 *      (In other words, the "major" token.)
 *
 *   +  The semantic value stored at this level of the stack.  This is
 *      the information used by the action routines in the grammar.
 *      It is sometimes called the "minor" token.
 */
class SearchCommandParseryyStackEntry
{
    public $stateno;       /* The state-number */
    public $major;         /* The major token value.  This is the code
                     ** number for the token at this stack level */
    public $minor; /* The user-supplied minor token value.  This
                     ** is the value of the token  */
};

// code external to the class is included here

// declare_class is output here
#line 2 "SearchCommandParser.y"
class SearchCommandParser#line 102 "SearchCommandParser.php"
{
/* First off, code is included which follows the "include_class" declaration
** in the input file. */
#line 4 "SearchCommandParser.y"


    private $expr_result;
    private $parse_result;

    public function __construct()
    {
        $this->parse_result = 'ok';
    }

    public function getExprResult()
    {
        return $this->expr_result;
    }

    public function isExprOk()
    {
        return $this->parse_result == 'ok';
    }

#line 128 "SearchCommandParser.php"

/* Next is all token values, as class constants
*/
/* 
** These constants (all generated automatically by the parser generator)
** specify the various kinds of tokens (terminals) that the parser
** understands. 
**
** Each symbol here is a terminal symbol in the grammar.
*/
    const OPOR                           =  1;
    const OPAND                          =  2;
    const NOT                            =  3;
    const IS                             =  4;
    const CONTAIN                        =  5;
    const LIKE                           =  6;
    const BETWEEN                        =  7;
    const START                          =  8;
    const END                            =  9;
    const GT                             = 10;
    const LE                             = 11;
    const LT                             = 12;
    const GE                             = 13;
    const PAR_OPEN                       = 14;
    const PAR_CLOSE                      = 15;
    const DOES                           = 16;
    const CONTAINS                       = 17;
    const COLON                          = 18;
    const SQUARE_OPEN                    = 19;
    const SQUARE_CLOSE                   = 20;
    const TERMINAL                       = 21;
    const VALUE                          = 22;
    const COMMA                          = 23;
    const WITH                           = 24;
    const IS_NOT                         = 25;
    const YY_NO_ACTION = 84;
    const YY_ACCEPT_ACTION = 83;
    const YY_ERROR_ACTION = 82;

/* Next are that tables used to determine what action to take based on the
** current state and lookahead token.  These tables are used to implement
** functions that take a state number and lookahead value and return an
** action integer.  
**
** Suppose the action integer is N.  Then the action is determined as
** follows
**
**   0 <= N < self::YYNSTATE                              Shift N.  That is,
**                                                        push the lookahead
**                                                        token onto the stack
**                                                        and goto state N.
**
**   self::YYNSTATE <= N < self::YYNSTATE+self::YYNRULE   Reduce by rule N-YYNSTATE.
**
**   N == self::YYNSTATE+self::YYNRULE                    A syntax error has occurred.
**
**   N == self::YYNSTATE+self::YYNRULE+1                  The parser accepts its
**                                                        input. (and concludes parsing)
**
**   N == self::YYNSTATE+self::YYNRULE+2                  No such action.  Denotes unused
**                                                        slots in the yy_action[] table.
**
** The action table is constructed as a single large static array $yy_action.
** Given state S and lookahead X, the action is computed as
**
**      self::$yy_action[self::$yy_shift_ofst[S] + X ]
**
** If the index value self::$yy_shift_ofst[S]+X is out of range or if the value
** self::$yy_lookahead[self::$yy_shift_ofst[S]+X] is not equal to X or if
** self::$yy_shift_ofst[S] is equal to self::YY_SHIFT_USE_DFLT, it means that
** the action is not in the table and that self::$yy_default[S] should be used instead.  
**
** The formula above is for computing the action when the lookahead is
** a terminal symbol.  If the lookahead is a non-terminal (as occurs after
** a reduce action) then the static $yy_reduce_ofst array is used in place of
** the static $yy_shift_ofst array and self::YY_REDUCE_USE_DFLT is used in place of
** self::YY_SHIFT_USE_DFLT.
**
** The following are the tables generated in this section:
**
**  self::$yy_action        A single table containing all actions.
**  self::$yy_lookahead     A table containing the lookahead for each entry in
**                          yy_action.  Used to detect hash collisions.
**  self::$yy_shift_ofst    For each state, the offset into self::$yy_action for
**                          shifting terminals.
**  self::$yy_reduce_ofst   For each state, the offset into self::$yy_action for
**                          shifting non-terminals after a reduce.
**  self::$yy_default       Default action for each state.
*/
    const YY_SZ_ACTTAB = 70;
static public $yy_action = array(
 /*     0 */    52,   15,    3,    5,   39,   23,   22,   37,   34,   54,
 /*    10 */    33,   47,    4,   16,   50,    9,   44,   21,   83,    1,
 /*    20 */     8,    7,   36,    2,    3,    5,    6,   17,   13,   26,
 /*    30 */    32,    1,   35,   27,   19,   41,    1,   46,   14,    1,
 /*    40 */    20,   38,   45,    5,    1,   10,   12,   31,   42,   24,
 /*    50 */    53,   18,   28,   30,   52,   63,   49,   63,   63,   63,
 /*    60 */    63,   48,   29,   40,   63,   43,   51,   63,   11,   25,
    );
    static public $yy_lookahead = array(
 /*     0 */     3,    4,    1,    2,   24,    8,    9,   10,   11,   12,
 /*    10 */    13,   33,    3,   16,   17,   18,   15,   27,   28,   29,
 /*    20 */     6,    7,   25,   14,    1,    2,    2,   14,   19,   27,
 /*    30 */    21,   29,   20,   22,   27,   22,   29,   27,   30,   29,
 /*    40 */    32,   24,   27,    2,   29,   19,   17,   20,   15,   33,
 /*    50 */    31,   23,   31,   31,    3,   34,   31,   34,   34,   34,
 /*    60 */    34,   31,   31,   31,   34,   31,   31,   34,   32,   32,
);
    const YY_SHIFT_USE_DFLT = -21;
    const YY_SHIFT_MAX = 31;
    static public $yy_shift_ofst = array(
 /*     0 */     9,   -3,    9,    9,    9,    9,   13,   13,   13,   13,
 /*    10 */    13,   13,   13,   13,   13,   51,   51,   11,   11,    1,
 /*    20 */    14,   23,   17,  -20,   33,   29,   41,   28,   27,   24,
 /*    30 */    12,   26,
);
    const YY_REDUCE_USE_DFLT = -23;
    const YY_REDUCE_MAX = 18;
    static public $yy_reduce_ofst = array(
 /*     0 */   -10,    8,    7,    2,   10,   15,   30,   31,   32,   25,
 /*    10 */    22,   19,   35,   21,   34,   36,   37,   16,  -22,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(3, 14, 19, 21, ),
        /* 1 */ array(3, 4, 8, 9, 10, 11, 12, 13, 16, 17, 18, 25, ),
        /* 2 */ array(3, 14, 19, 21, ),
        /* 3 */ array(3, 14, 19, 21, ),
        /* 4 */ array(3, 14, 19, 21, ),
        /* 5 */ array(3, 14, 19, 21, ),
        /* 6 */ array(14, 22, ),
        /* 7 */ array(14, 22, ),
        /* 8 */ array(14, 22, ),
        /* 9 */ array(14, 22, ),
        /* 10 */ array(14, 22, ),
        /* 11 */ array(14, 22, ),
        /* 12 */ array(14, 22, ),
        /* 13 */ array(14, 22, ),
        /* 14 */ array(14, 22, ),
        /* 15 */ array(3, ),
        /* 16 */ array(3, ),
        /* 17 */ array(22, ),
        /* 18 */ array(22, ),
        /* 19 */ array(1, 2, 15, ),
        /* 20 */ array(6, 7, ),
        /* 21 */ array(1, 2, ),
        /* 22 */ array(24, ),
        /* 23 */ array(24, ),
        /* 24 */ array(15, ),
        /* 25 */ array(17, ),
        /* 26 */ array(2, ),
        /* 27 */ array(23, ),
        /* 28 */ array(20, ),
        /* 29 */ array(2, ),
        /* 30 */ array(20, ),
        /* 31 */ array(19, ),
        /* 32 */ array(),
        /* 33 */ array(),
        /* 34 */ array(),
        /* 35 */ array(),
        /* 36 */ array(),
        /* 37 */ array(),
        /* 38 */ array(),
        /* 39 */ array(),
        /* 40 */ array(),
        /* 41 */ array(),
        /* 42 */ array(),
        /* 43 */ array(),
        /* 44 */ array(),
        /* 45 */ array(),
        /* 46 */ array(),
        /* 47 */ array(),
        /* 48 */ array(),
        /* 49 */ array(),
        /* 50 */ array(),
        /* 51 */ array(),
        /* 52 */ array(),
        /* 53 */ array(),
        /* 54 */ array(),
);
    static public $yy_default = array(
 /*     0 */    82,   66,   82,   82,   82,   82,   82,   82,   82,   82,
 /*    10 */    82,   82,   82,   82,   82,   66,   66,   82,   82,   82,
 /*    20 */    82,   55,   82,   82,   82,   82,   57,   73,   82,   82,
 /*    30 */    82,   82,   69,   78,   77,   68,   81,   76,   80,   79,
 /*    40 */    62,   70,   71,   60,   59,   56,   58,   72,   61,   65,
 /*    50 */    74,   64,   67,   63,   75,
);
/* The next thing included is series of defines which control
** various aspects of the generated parser.
**    self::YYNOCODE      is a number which corresponds
**                        to no legal terminal or nonterminal number.  This
**                        number is used to fill in empty slots of the hash 
**                        table.
**    self::YYFALLBACK    If defined, this indicates that one or more tokens
**                        have fall-back values which should be used if the
**                        original value of the token will not parse.
**    self::YYSTACKDEPTH  is the maximum depth of the parser's stack.
**    self::YYNSTATE      the combined number of states.
**    self::YYNRULE       the number of rules in the grammar
**    self::YYERRORSYMBOL is the code number of the error symbol.  If not
**                        defined, then do no error processing.
*/
    const YYNOCODE = 35;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 55;
    const YYNRULE = 27;
    const YYERRORSYMBOL = 26;
    const YYERRSYMDT = 'yy0';
    const YYFALLBACK = 0;
    /** The next table maps tokens into fallback tokens.  If a construct
     * like the following:
     * 
     *      %fallback ID X Y Z.
     *
     * appears in the grammer, then ID becomes a fallback token for X, Y,
     * and Z.  Whenever one of the tokens X, Y, or Z is input to the parser
     * but it does not parse, the type of the token is changed to ID and
     * the parse is retried before an error is thrown.
     */
    static public $yyFallback = array(
    );
    /**
     * Turn parser tracing on by giving a stream to which to write the trace
     * and a prompt to preface each trace message.  Tracing is turned off
     * by making either argument NULL 
     *
     * Inputs:
     * 
     * - A stream resource to which trace output should be written.
     *   If NULL, then tracing is turned off.
     * - A prefix string written at the beginning of every
     *   line of trace output.  If NULL, then tracing is
     *   turned off.
     *
     * Outputs:
     * 
     * - None.
     * @param resource
     * @param string
     */
    static function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        self::$yyTraceFILE = $TraceFILE;
        self::$yyTracePrompt = $zTracePrompt;
    }

    /**
     * Output debug information to output (php://output stream)
     */
    static function PrintTrace()
    {
        self::$yyTraceFILE = fopen('php://output', 'w');
        self::$yyTracePrompt = '';
    }

    /**
     * @var resource|0
     */
    static public $yyTraceFILE;
    /**
     * String to prepend to debug output
     * @var string|0
     */
    static public $yyTracePrompt;
    /**
     * @var int
     */
    public $yyidx;                    /* Index of top element in stack */
    /**
     * @var int
     */
    public $yyerrcnt;                 /* Shifts left before out of the error */
    /**
     * @var array
     */
    public $yystack = array();  /* The parser's stack */

    /**
     * For tracing shifts, the names of all terminals and nonterminals
     * are required.  The following table supplies these names
     * @var array
     */
    static public $yyTokenName = array( 
  '$',             'OPOR',          'OPAND',         'NOT',         
  'IS',            'CONTAIN',       'LIKE',          'BETWEEN',     
  'START',         'END',           'GT',            'LE',          
  'LT',            'GE',            'PAR_OPEN',      'PAR_CLOSE',   
  'DOES',          'CONTAINS',      'COLON',         'SQUARE_OPEN', 
  'SQUARE_CLOSE',  'TERMINAL',      'VALUE',         'COMMA',       
  'WITH',          'IS_NOT',        'error',         'expr',        
  'cmdline',       'terminal',      'operator',      'value',       
  'notop',         'valuelist',   
    );

    /**
     * For tracing reduce actions, the names of all rules are required.
     * @var array
     */
    static public $yyRuleName = array(
 /*   0 */ "cmdline ::= expr",
 /*   1 */ "expr ::= expr OPAND expr",
 /*   2 */ "expr ::= expr OPOR expr",
 /*   3 */ "expr ::= NOT expr",
 /*   4 */ "expr ::= PAR_OPEN expr PAR_CLOSE",
 /*   5 */ "expr ::= terminal operator value",
 /*   6 */ "expr ::= terminal notop BETWEEN value OPAND value",
 /*   7 */ "expr ::= terminal notop LIKE value",
 /*   8 */ "expr ::= terminal IS notop value",
 /*   9 */ "expr ::= terminal DOES notop CONTAINS value",
 /*  10 */ "expr ::= terminal COLON value",
 /*  11 */ "notop ::=",
 /*  12 */ "notop ::= NOT",
 /*  13 */ "terminal ::= SQUARE_OPEN value SQUARE_CLOSE SQUARE_OPEN value SQUARE_CLOSE",
 /*  14 */ "terminal ::= TERMINAL",
 /*  15 */ "value ::= VALUE",
 /*  16 */ "value ::= PAR_OPEN valuelist PAR_CLOSE",
 /*  17 */ "valuelist ::= VALUE COMMA valuelist",
 /*  18 */ "valuelist ::= VALUE",
 /*  19 */ "operator ::= CONTAINS",
 /*  20 */ "operator ::= LT",
 /*  21 */ "operator ::= GT",
 /*  22 */ "operator ::= LE",
 /*  23 */ "operator ::= GE",
 /*  24 */ "operator ::= START WITH",
 /*  25 */ "operator ::= END WITH",
 /*  26 */ "operator ::= IS_NOT",
    );

    /**
     * This function returns the symbolic name associated with a token
     * value.
     * @param int
     * @return string
     */
    function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count(self::$yyTokenName)) {
            return self::$yyTokenName[$tokenType];
        } else {
            return "Unknown";
        }
    }

    /**
     * The following function deletes the value associated with a
     * symbol.  The symbol can be either a terminal or nonterminal.
     * @param int the symbol code
     * @param mixed the symbol's value
     */
    static function yy_destructor($yymajor, $yypminor)
    {
        switch ($yymajor) {
        /* Here is inserted the actions which take place when a
        ** terminal or non-terminal is destroyed.  This can happen
        ** when the symbol is popped from the stack during a
        ** reduce or during error processing or when a parser is 
        ** being destroyed before it is finished parsing.
        **
        ** Note: during a reduce, the only symbols destroyed are those
        ** which appear on the RHS of the rule, but which are not used
        ** inside the C code.
        */
            default:  break;   /* If no destructor action specified: do nothing */
        }
    }

    /**
     * Pop the parser's stack once.
     *
     * If there is a destructor routine associated with the token which
     * is popped from the stack, then call it.
     *
     * Return the major token number for the symbol popped.
     * @param SearchCommandParseryyParser
     * @return int
     */
    function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if (self::$yyTraceFILE && $this->yyidx >= 0) {
            fwrite(self::$yyTraceFILE,
                self::$yyTracePrompt . 'Popping ' . self::$yyTokenName[$yytos->major] .
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        $this->yyidx--;
        return $yymajor;
    }

    /**
     * Deallocate and destroy a parser.  Destructors are all called for
     * all stack elements before shutting the parser down.
     */
    function __destruct()
    {
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        if (is_resource(self::$yyTraceFILE)) {
            fclose(self::$yyTraceFILE);
        }
    }

    /**
     * Based on the current state and parser stack, get a list of all
     * possible lookahead tokens
     * @param int
     * @return array
     */
    function yy_get_expected_tokens($token)
    {
        $state = $this->yystack[$this->yyidx]->stateno;
        $expected = self::$yyExpectedTokens[$state];
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return $expected;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return array_unique($expected);
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate])) {
                        $expected += self::$yyExpectedTokens[$nextstate];
                            if (in_array($token,
                                  self::$yyExpectedTokens[$nextstate], true)) {
                            $this->yyidx = $yyidx;
                            $this->yystack = $stack;
                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new SearchCommandParseryyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return $expected;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        return array_unique($expected);
    }

    /**
     * Based on the parser state and current parser stack, determine whether
     * the lookahead token is possible.
     * 
     * The parser will convert the token value to an error token if not.  This
     * catches some unusual edge cases where the parser would fail.
     * @param int
     * @return bool
     */
    function yy_is_expected_token($token)
    {
        if ($token === 0) {
            return true; // 0 is not part of this
        }
        $state = $this->yystack[$this->yyidx]->stateno;
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return true;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate]) &&
                          in_array($token, self::$yyExpectedTokens[$nextstate], true)) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new SearchCommandParseryyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        if (!$token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        $this->yyidx = $yyidx;
        $this->yystack = $stack;
        return true;
    }

    /**
     * Find the appropriate action for a parser given the terminal
     * look-ahead token iLookAhead.
     *
     * If the look-ahead token is YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return YY_NO_ACTION.
     * @param int The look-ahead token
     */
    function yy_find_shift_action($iLookAhead)
    {
        $stateno = $this->yystack[$this->yyidx]->stateno;
     
        /* if ($this->yyidx < 0) return self::YY_NO_ACTION;  */
        if (!isset(self::$yy_shift_ofst[$stateno])) {
            // no shift actions
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_shift_ofst[$stateno];
        if ($i === self::YY_SHIFT_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            if (count(self::$yyFallback) && $iLookAhead < count(self::$yyFallback)
                   && ($iFallback = self::$yyFallback[$iLookAhead]) != 0) {
                if (self::$yyTraceFILE) {
                    fwrite(self::$yyTraceFILE, self::$yyTracePrompt . "FALLBACK " .
                        self::$yyTokenName[$iLookAhead] . " => " .
                        self::$yyTokenName[$iFallback] . "\n");
                }
                return $this->yy_find_shift_action($iFallback);
            }
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Find the appropriate action for a parser given the non-terminal
     * look-ahead token $iLookAhead.
     *
     * If the look-ahead token is self::YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return self::YY_NO_ACTION.
     * @param int Current state number
     * @param int The look-ahead token
     */
    function yy_find_reduce_action($stateno, $iLookAhead)
    {
        /* $stateno = $this->yystack[$this->yyidx]->stateno; */

        if (!isset(self::$yy_reduce_ofst[$stateno])) {
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_reduce_ofst[$stateno];
        if ($i == self::YY_REDUCE_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Perform a shift action.
     * @param int The new state to shift in
     * @param int The major token to shift in
     * @param mixed the minor token to shift in
     */
    function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        $this->yyidx++;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            $this->yyidx--;
            if (self::$yyTraceFILE) {
                fprintf(self::$yyTraceFILE, "%sStack Overflow!\n", self::$yyTracePrompt);
            }
            while ($this->yyidx >= 0) {
                $this->yy_pop_parser_stack();
            }
            /* Here code is inserted which will execute if the parser
            ** stack ever overflows */
            return;
        }
        $yytos = new SearchCommandParseryyStackEntry;
        $yytos->stateno = $yyNewState;
        $yytos->major = $yyMajor;
        $yytos->minor = $yypMinor;
        array_push($this->yystack, $yytos);
        if (self::$yyTraceFILE && $this->yyidx > 0) {
            fprintf(self::$yyTraceFILE, "%sShift %d\n", self::$yyTracePrompt,
                $yyNewState);
            fprintf(self::$yyTraceFILE, "%sStack:", self::$yyTracePrompt);
            for($i = 1; $i <= $this->yyidx; $i++) {
                fprintf(self::$yyTraceFILE, " %s",
                    self::$yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite(self::$yyTraceFILE,"\n");
        }
    }

    /**
     * The following table contains information about every rule that
     * is used during the reduce.
     *
     * <pre>
     * array(
     *  array(
     *   int $lhs;         Symbol on the left-hand side of the rule
     *   int $nrhs;     Number of right-hand side symbols in the rule
     *  ),...
     * );
     * </pre>
     */
    static public $yyRuleInfo = array(
  array( 'lhs' => 28, 'rhs' => 1 ),
  array( 'lhs' => 27, 'rhs' => 3 ),
  array( 'lhs' => 27, 'rhs' => 3 ),
  array( 'lhs' => 27, 'rhs' => 2 ),
  array( 'lhs' => 27, 'rhs' => 3 ),
  array( 'lhs' => 27, 'rhs' => 3 ),
  array( 'lhs' => 27, 'rhs' => 6 ),
  array( 'lhs' => 27, 'rhs' => 4 ),
  array( 'lhs' => 27, 'rhs' => 4 ),
  array( 'lhs' => 27, 'rhs' => 5 ),
  array( 'lhs' => 27, 'rhs' => 3 ),
  array( 'lhs' => 32, 'rhs' => 0 ),
  array( 'lhs' => 32, 'rhs' => 1 ),
  array( 'lhs' => 29, 'rhs' => 6 ),
  array( 'lhs' => 29, 'rhs' => 1 ),
  array( 'lhs' => 31, 'rhs' => 1 ),
  array( 'lhs' => 31, 'rhs' => 3 ),
  array( 'lhs' => 33, 'rhs' => 3 ),
  array( 'lhs' => 33, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
  array( 'lhs' => 30, 'rhs' => 2 ),
  array( 'lhs' => 30, 'rhs' => 2 ),
  array( 'lhs' => 30, 'rhs' => 1 ),
    );

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     * 
     * If a rule is not set, it has no handler.
     */
    static public $yyReduceMap = array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        16 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
        11 => 11,
        12 => 12,
        13 => 13,
        14 => 14,
        15 => 15,
        17 => 17,
        18 => 18,
        19 => 19,
        20 => 20,
        21 => 21,
        22 => 22,
        23 => 23,
        24 => 24,
        25 => 25,
        26 => 26,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 53 "SearchCommandParser.y"
    function yy_r0(){
	$this->expr_result = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 900 "SearchCommandParser.php"
#line 58 "SearchCommandParser.y"
    function yy_r1(){
	$this->_retvalue = new OpExpr($this->yystack[$this->yyidx + -2]->minor, ExprOp::OP_AND, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 905 "SearchCommandParser.php"
#line 63 "SearchCommandParser.y"
    function yy_r2(){
	$this->_retvalue = new OpExpr($this->yystack[$this->yyidx + -2]->minor, ExprOp::OP_OR, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 910 "SearchCommandParser.php"
#line 68 "SearchCommandParser.y"
    function yy_r3(){
	$expr = $this->yystack[$this->yyidx + 0]->minor;
	$expr->not(!$expr->not());
	$this->_retvalue = $expr;
    }
#line 917 "SearchCommandParser.php"
#line 75 "SearchCommandParser.y"
    function yy_r4(){
	$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
    }
#line 922 "SearchCommandParser.php"
#line 80 "SearchCommandParser.y"
    function yy_r5(){
	$op = $this->yystack[$this->yyidx + -1]->minor;
	$not = false;
	if ($op == ExprOp::IS_NOT)
	{
		$op = ExprOp::IS;
		$not = true;
	}

	$fld = new OpExpr($this->yystack[$this->yyidx + -2]->minor, $op, $this->yystack[$this->yyidx + 0]->minor);
	$fld->not($not);
	$this->_retvalue = $fld;
    }
#line 937 "SearchCommandParser.php"
#line 95 "SearchCommandParser.y"
    function yy_r6(){
	$expr = new OpExpr($this->yystack[$this->yyidx + -5]->minor, ExprOp::BETWEEN, new BetweenValueExpr($this->yystack[$this->yyidx + -2]->minor, $this->yystack[$this->yyidx + 0]->minor));
	$expr->not($this->yystack[$this->yyidx + -4]->minor);
	$this->_retvalue=$expr;
    }
#line 944 "SearchCommandParser.php"
#line 102 "SearchCommandParser.y"
    function yy_r7(){
	$expr = new OpExpr($this->yystack[$this->yyidx + -3]->minor, ExprOp::LIKE, $this->yystack[$this->yyidx + 0]->minor);
	$expr->not($this->yystack[$this->yyidx + -2]->minor);
	$this->_retvalue=$expr;
    }
#line 951 "SearchCommandParser.php"
#line 109 "SearchCommandParser.y"
    function yy_r8(){
	$expr = new OpExpr($this->yystack[$this->yyidx + -3]->minor, ExprOp::IS, $this->yystack[$this->yyidx + 0]->minor);
	$expr->not($this->yystack[$this->yyidx + -1]->minor);
	$this->_retvalue=$expr;
    }
#line 958 "SearchCommandParser.php"
#line 116 "SearchCommandParser.y"
    function yy_r9(){
	$expr = new OpExpr($this->yystack[$this->yyidx + -4]->minor, ExprOp::CONTAINS, $this->yystack[$this->yyidx + 0]->minor);
	$expr->not($this->yystack[$this->yyidx + -2]->minor);
	$this->_retvalue=$expr;
    }
#line 965 "SearchCommandParser.php"
#line 123 "SearchCommandParser.y"
    function yy_r10(){
	$this->_retvalue = new OpExpr($this->yystack[$this->yyidx + -2]->minor, ExprOp::CONTAINS, $this->yystack[$this->yyidx + 0]->minor);
    }
#line 970 "SearchCommandParser.php"
#line 129 "SearchCommandParser.y"
    function yy_r11(){
	$this->_retvalue = false;
    }
#line 975 "SearchCommandParser.php"
#line 134 "SearchCommandParser.y"
    function yy_r12(){
	$this->_retvalue = true;
    }
#line 980 "SearchCommandParser.php"
#line 139 "SearchCommandParser.y"
    function yy_r13(){
	$registry = ExprFieldRegistry::getRegistry();
	$field = $registry->resolveMetadataField($this->yystack[$this->yyidx + -4]->minor, $this->yystack[$this->yyidx + -1]->minor);
	$this->_retvalue = $field;
    }
#line 987 "SearchCommandParser.php"
#line 146 "SearchCommandParser.y"
    function yy_r14(){
	$registry = ExprFieldRegistry::getRegistry();
	$field=$registry->resolveAlias($this->yystack[$this->yyidx + 0]->minor);
	$this->_retvalue = $field;
    }
#line 994 "SearchCommandParser.php"
#line 153 "SearchCommandParser.y"
    function yy_r15(){
	$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 999 "SearchCommandParser.php"
#line 163 "SearchCommandParser.y"
    function yy_r17(){
	$this->yystack[$this->yyidx + 0]->minor->addValue($this->yystack[$this->yyidx + -2]->minor);
	$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;
    }
#line 1005 "SearchCommandParser.php"
#line 169 "SearchCommandParser.y"
    function yy_r18(){
	$this->_retvalue = new ValueListExpr($this->yystack[$this->yyidx + 0]->minor);
    }
#line 1010 "SearchCommandParser.php"
#line 174 "SearchCommandParser.y"
    function yy_r19(){
	$this->_retvalue = ExprOp::CONTAINS;
    }
#line 1015 "SearchCommandParser.php"
#line 179 "SearchCommandParser.y"
    function yy_r20(){
	$this->_retvalue = ExprOp::LESS_THAN;
    }
#line 1020 "SearchCommandParser.php"
#line 184 "SearchCommandParser.y"
    function yy_r21(){
	$this->_retvalue = ExprOp::GREATER_THAN;
    }
#line 1025 "SearchCommandParser.php"
#line 189 "SearchCommandParser.y"
    function yy_r22(){
	$this->_retvalue = ExprOp::LESS_THAN_EQUAL;
    }
#line 1030 "SearchCommandParser.php"
#line 194 "SearchCommandParser.y"
    function yy_r23(){
	$this->_retvalue = ExprOp::GREATER_THAN_EQUAL;
    }
#line 1035 "SearchCommandParser.php"
#line 199 "SearchCommandParser.y"
    function yy_r24(){
	$this->_retvalue = ExprOp::STARTS_WITH;
    }
#line 1040 "SearchCommandParser.php"
#line 204 "SearchCommandParser.y"
    function yy_r25(){
	$this->_retvalue = ExprOp::ENDS_WITH;
    }
#line 1045 "SearchCommandParser.php"
#line 209 "SearchCommandParser.y"
    function yy_r26(){
	$this->_retvalue = ExprOp::IS_NOT;
    }
#line 1050 "SearchCommandParser.php"

    /**
     * placeholder for the left hand side in a reduce operation.
     * 
     * For a parser with a rule like this:
     * <pre>
     * rule(A) ::= B. { A = 1; }
     * </pre>
     * 
     * The parser will translate to something like:
     * 
     * <code>
     * function yy_r0(){$this->_retvalue = 1;}
     * </code>
     */
    private $_retvalue;

    /**
     * Perform a reduce action and the shift that must immediately
     * follow the reduce.
     * 
     * For a rule such as:
     * 
     * <pre>
     * A ::= B blah C. { dosomething(); }
     * </pre>
     * 
     * This function will first call the action, if any, ("dosomething();" in our
     * example), and then it will pop three states from the stack,
     * one for each entry on the right-hand side of the expression
     * (B, blah, and C in our example rule), and then push the result of the action
     * back on to the stack with the resulting state reduced to (as described in the .out
     * file)
     * @param int Number of the rule by which to reduce
     */
    function yy_reduce($yyruleno)
    {
        //int $yygoto;                     /* The next state */
        //int $yyact;                      /* The next action */
        //mixed $yygotominor;        /* The LHS of the rule reduced */
        //SearchCommandParseryyStackEntry $yymsp;            /* The top of the parser's stack */
        //int $yysize;                     /* Amount to pop the stack */
        $yymsp = $this->yystack[$this->yyidx];
        if (self::$yyTraceFILE && $yyruleno >= 0 
              && $yyruleno < count(self::$yyRuleName)) {
            fprintf(self::$yyTraceFILE, "%sReduce (%d) [%s].\n",
                self::$yyTracePrompt, $yyruleno,
                self::$yyRuleName[$yyruleno]);
        }

        $this->_retvalue = $yy_lefthand_side = null;
        if (array_key_exists($yyruleno, self::$yyReduceMap)) {
            // call the action
            $this->_retvalue = null;
            $this->{'yy_r' . self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for($i = $yysize; $i; $i--) {
            // pop all of the right-hand side parameters
            array_pop($this->yystack);
        }
        $yyact = $this->yy_find_reduce_action($this->yystack[$this->yyidx]->stateno, $yygoto);
        if ($yyact < self::YYNSTATE) {
            /* If we are not debugging and the reduce action popped at least
            ** one element off the stack, then we can push the new element back
            ** onto the stack here, and skip the stack overflow test in yy_shift().
            ** That gives a significant speed improvement. */
            if (!self::$yyTraceFILE && $yysize) {
                $this->yyidx++;
                $x = new SearchCommandParseryyStackEntry;
                $x->stateno = $yyact;
                $x->major = $yygoto;
                $x->minor = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    /**
     * The following code executes when the parse fails
     * 
     * Code from %parse_fail is inserted here
     */
    function yy_parse_failed()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sFail!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser fails */
#line 46 "SearchCommandParser.y"

    $this->parse_result = 'syntax';
#line 1155 "SearchCommandParser.php"
    }

    /**
     * The following code executes when a syntax error first occurs.
     * 
     * %syntax_error code is inserted here
     * @param int The major type of the error token
     * @param mixed The minor type of the error token
     */
    function yy_syntax_error($yymajor, $TOKEN)
    {
#line 35 "SearchCommandParser.y"

    $this->parse_result = 'syntax';
    $this->parse_message = "";
#line 1172 "SearchCommandParser.php"
    }

    /**
     * The following is executed when the parser accepts
     * 
     * %parse_accept code is inserted here
     */
    function yy_accept()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sAccept!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $stack = $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser accepts */
#line 41 "SearchCommandParser.y"

      $this->parse_result  = 'ok';
#line 1194 "SearchCommandParser.php"
    }

    /**
     * The main parser program.
     * 
     * The first argument is the major token number.  The second is
     * the token value string as scanned from the input.
     *
     * @param int the token number
     * @param mixed the token value
     * @param mixed any extra arguments that should be passed to handlers
     */
    function doParse($yymajor, $yytokenvalue)
    {
//        $yyact;            /* The parser action. */
//        $yyendofinput;     /* True if we are at the end of input */
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */
        
        /* (re)initialize the parser, if necessary */
        if ($this->yyidx === null || $this->yyidx < 0) {
            /* if ($yymajor == 0) return; // not sure why this was here... */
            $this->yyidx = 0;
            $this->yyerrcnt = -1;
            $x = new SearchCommandParseryyStackEntry;
            $x->stateno = 0;
            $x->major = 0;
            $this->yystack = array();
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor==0);
        
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sInput %s\n",
                self::$yyTracePrompt, self::$yyTokenName[$yymajor]);
        }
        
        do {
            $yyact = $this->yy_find_shift_action($yymajor);
            if ($yymajor < self::YYERRORSYMBOL &&
                  !$this->yy_is_expected_token($yymajor)) {
                // force a syntax error
                $yyact = self::YY_ERROR_ACTION;
            }
            if ($yyact < self::YYNSTATE) {
                $this->yy_shift($yyact, $yymajor, $yytokenvalue);
                $this->yyerrcnt--;
                if ($yyendofinput && $this->yyidx >= 0) {
                    $yymajor = 0;
                } else {
                    $yymajor = self::YYNOCODE;
                }
            } elseif ($yyact < self::YYNSTATE + self::YYNRULE) {
                $this->yy_reduce($yyact - self::YYNSTATE);
            } elseif ($yyact == self::YY_ERROR_ACTION) {
                if (self::$yyTraceFILE) {
                    fprintf(self::$yyTraceFILE, "%sSyntax Error!\n",
                        self::$yyTracePrompt);
                }
                if (self::YYERRORSYMBOL) {
                    /* A syntax error has occurred.
                    ** The response to an error depends upon whether or not the
                    ** grammar defines an error token "ERROR".  
                    **
                    ** This is what we do if the grammar does define ERROR:
                    **
                    **  * Call the %syntax_error function.
                    **
                    **  * Begin popping the stack until we enter a state where
                    **    it is legal to shift the error symbol, then shift
                    **    the error symbol.
                    **
                    **  * Set the error count to three.
                    **
                    **  * Begin accepting and shifting new tokens.  No new error
                    **    processing will occur until three tokens have been
                    **    shifted successfully.
                    **
                    */
                    if ($this->yyerrcnt < 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $yymx = $this->yystack[$this->yyidx]->major;
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit ){
                        if (self::$yyTraceFILE) {
                            fprintf(self::$yyTraceFILE, "%sDiscard input token %s\n",
                                self::$yyTracePrompt, self::$yyTokenName[$yymajor]);
                        }
                        $this->yy_destructor($yymajor, $yytokenvalue);
                        $yymajor = self::YYNOCODE;
                    } else {
                        while ($this->yyidx >= 0 &&
                                 $yymx != self::YYERRORSYMBOL &&
        ($yyact = $this->yy_find_shift_action(self::YYERRORSYMBOL)) >= self::YYNSTATE
                              ){
                            $this->yy_pop_parser_stack();
                        }
                        if ($this->yyidx < 0 || $yymajor==0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit = 1;
                } else {
                    /* YYERRORSYMBOL is not defined */
                    /* This is what we do if the grammar does not define ERROR:
                    **
                    **  * Report an error message, and throw away the input token.
                    **
                    **  * If the input token is $, then fail the parse.
                    **
                    ** As before, subsequent error messages are suppressed until
                    ** three input tokens have been successfully shifted.
                    */
                    if ($this->yyerrcnt <= 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $this->yyerrcnt = 3;
                    $this->yy_destructor($yymajor, $yytokenvalue);
                    if ($yyendofinput) {
                        $this->yy_parse_failed();
                    }
                    $yymajor = self::YYNOCODE;
                }
            } else {
                $this->yy_accept();
                $yymajor = self::YYNOCODE;
            }            
        } while ($yymajor != self::YYNOCODE && $this->yyidx >= 0);
    }
}