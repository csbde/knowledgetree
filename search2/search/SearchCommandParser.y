%name SearchCommandParser
%declare_class {class SearchCommandParser}

%include_class {

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

}

%type expr {Expr}

%left OPOR.
%left OPAND.
%right NOT.
%left IS CONTAIN LIKE BETWEEN START END.
%left GT LE LT GE.

%syntax_error
{
    $this->parse_result = 'syntax';
    $this->parse_message = "";
}

%parse_accept
{
      $this->parse_result  = 'ok';
}

%parse_failure
{
    $this->parse_result = 'syntax';
}

%start_symbol   cmdline

cmdline      ::= expr(A).
{
	$this->expr_result = A;
}

expr(A)         ::= expr(B) OPAND expr(C).
{
	A = new OpExpr(B, ExprOp::OP_AND, C);
}

expr(A)         ::= expr(B) OPOR expr(C).
{
	A = new OpExpr(B, ExprOp::OP_OR, C);
}

expr(A)         ::= NOT expr(B).
{
	$expr = B;
	$expr->not(!$expr->not());
	A = $expr;
}

expr(A)         ::= PAR_OPEN expr(B) PAR_CLOSE.
{
	A = B;
}

expr(A)         ::= terminal(B) operator(C) value(D).
{
	$op = C;
	$not = false;
	if ($op == ExprOp::IS_NOT)
	{
		$op = ExprOp::IS;
		$not = true;
	}

	$fld = new OpExpr(B, $op, D);
	$fld->not($not);
	A = $fld;
}

expr(A)         ::= terminal(B) notop(C) BETWEEN value(D) OPAND value(E). [BETWEEN]
{
	$expr = new OpExpr(B, ExprOp::BETWEEN, new BetweenValueExpr(D, E));
	$expr->not(C);
	A=$expr;
}

expr(A)         ::= terminal(B) notop(C) LIKE value(D).
{
	$expr = new OpExpr(B, ExprOp::LIKE, D);
	$expr->not(C);
	A=$expr;
}

expr(A)         ::= terminal(B) IS notop(C) value(D).
{
	$expr = new OpExpr(B, ExprOp::IS, D);
	$expr->not(C);
	A=$expr;
}

expr(A)         ::= terminal(B) DOES notop(C) CONTAINS value(D).
{
	$expr = new OpExpr(B, ExprOp::CONTAINS, D);
	$expr->not(C);
	A=$expr;
}

expr(A)			::= terminal(B) COLON value(C).
{
	A = new OpExpr(B, ExprOp::CONTAINS, C);
}


notop(A)        ::= .
{
	A = false;
}

notop(A)        ::= NOT.
{
	A = true;
}

terminal(A)     ::= SQUARE_OPEN value(B) SQUARE_CLOSE SQUARE_OPEN value(C) SQUARE_CLOSE.
{
	$registry = ExprFieldRegistry::getRegistry();
	$field = $registry->resolveMetadataField(B, C);
	A = $field;
}

terminal(A)     ::= TERMINAL(B).
{
	$registry = ExprFieldRegistry::getRegistry();
	$field=$registry->resolveAlias(B);
	A = $field;
}

value(A)        ::= VALUE(B).
{
	A = B;
}

value(A)		::= PAR_OPEN valuelist(B) PAR_CLOSE.
{
	A = B;
}

valuelist(A)	::= VALUE(B) COMMA valuelist(C).
{
	C->addValue(B);
	A = C;
}

valuelist(A)	::= VALUE(B).
{
	A = new ValueListExpr(B);
}

operator(A)     ::= CONTAINS.
{
	A = ExprOp::CONTAINS;
}

operator(A)     ::= LT.
{
	A = ExprOp::LESS_THAN;
}

operator(A)     ::= GT.
{
	A = ExprOp::GREATER_THAN;
}

operator(A)     ::= LE.
{
	A = ExprOp::LESS_THAN_EQUAL;
}

operator(A)     ::= GE.
{
	A = ExprOp::GREATER_THAN_EQUAL;
}

operator(A)     ::= START WITH.
{
	A = ExprOp::STARTS_WITH;
}

operator(A)     ::= END WITH.
{
	A = ExprOp::ENDS_WITH;
}

operator(A)     ::= IS_NOT.
{
	A = ExprOp::IS_NOT;
}
