<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use paslandau\PageRank\Calculation\PageRank;
use paslandau\PageRank\Calculation\ResultFormatter;
use paslandau\PageRank\Import\CsvImporter;
use paslandau\PhpSandbox\Sandbox;
use paslandau\PhpSandbox\WhitelistVisitor;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Yaml\Parser;

require_once __DIR__."/bootstrap.php";

$nodeWhiteList = [
    PhpParser\Node\Arg::class,
    PhpParser\Node\Const_::class,
    PhpParser\Node\Expr\ArrayDimFetch::class,
    PhpParser\Node\Expr\ArrayItem::class,
    PhpParser\Node\Expr\Array_::class,
    PhpParser\Node\Expr\Assign::class,
    PhpParser\Node\Expr\AssignOp\BitwiseAnd::class,
    PhpParser\Node\Expr\AssignOp\BitwiseOr::class,
    PhpParser\Node\Expr\AssignOp\BitwiseXor::class,
    PhpParser\Node\Expr\AssignOp\Concat::class,
    PhpParser\Node\Expr\AssignOp\Div::class,
    PhpParser\Node\Expr\AssignOp\Minus::class,
    PhpParser\Node\Expr\AssignOp\Mod::class,
    PhpParser\Node\Expr\AssignOp\Mul::class,
    PhpParser\Node\Expr\AssignOp\Plus::class,
    PhpParser\Node\Expr\AssignOp\Pow::class,
    PhpParser\Node\Expr\AssignOp\ShiftLeft::class,
    PhpParser\Node\Expr\AssignOp\ShiftRight::class,
    PhpParser\Node\Expr\AssignOp::class,
    PhpParser\Node\Expr\AssignRef::class,
    PhpParser\Node\Expr\BinaryOp\BitwiseAnd::class,
    PhpParser\Node\Expr\BinaryOp\BitwiseOr::class,
    PhpParser\Node\Expr\BinaryOp\BitwiseXor::class,
    PhpParser\Node\Expr\BinaryOp\BooleanAnd::class,
    PhpParser\Node\Expr\BinaryOp\BooleanOr::class,
    PhpParser\Node\Expr\BinaryOp\Coalesce::class,
    PhpParser\Node\Expr\BinaryOp\Concat::class,
    PhpParser\Node\Expr\BinaryOp\Div::class,
    PhpParser\Node\Expr\BinaryOp\Equal::class,
    PhpParser\Node\Expr\BinaryOp\Greater::class,
    PhpParser\Node\Expr\BinaryOp\GreaterOrEqual::class,
    PhpParser\Node\Expr\BinaryOp\Identical::class,
    PhpParser\Node\Expr\BinaryOp\LogicalAnd::class,
    PhpParser\Node\Expr\BinaryOp\LogicalOr::class,
    PhpParser\Node\Expr\BinaryOp\LogicalXor::class,
    PhpParser\Node\Expr\BinaryOp\Minus::class,
    PhpParser\Node\Expr\BinaryOp\Mod::class,
    PhpParser\Node\Expr\BinaryOp\Mul::class,
    PhpParser\Node\Expr\BinaryOp\NotEqual::class,
    PhpParser\Node\Expr\BinaryOp\NotIdentical::class,
    PhpParser\Node\Expr\BinaryOp\Plus::class,
    PhpParser\Node\Expr\BinaryOp\Pow::class,
    PhpParser\Node\Expr\BinaryOp\ShiftLeft::class,
    PhpParser\Node\Expr\BinaryOp\ShiftRight::class,
    PhpParser\Node\Expr\BinaryOp\Smaller::class,
    PhpParser\Node\Expr\BinaryOp\SmallerOrEqual::class,
    PhpParser\Node\Expr\BinaryOp\Spaceship::class,
    PhpParser\Node\Expr\BinaryOp::class,
    PhpParser\Node\Expr\BitwiseNot::class,
    PhpParser\Node\Expr\BooleanNot::class,
    PhpParser\Node\Expr\Cast\Array_::class,
    PhpParser\Node\Expr\Cast\Bool_::class,
    PhpParser\Node\Expr\Cast\Double::class,
    PhpParser\Node\Expr\Cast\Int_::class,
    PhpParser\Node\Expr\Cast\Object_::class,
    PhpParser\Node\Expr\Cast\String_::class,
    PhpParser\Node\Expr\Cast\Unset_::class,
    PhpParser\Node\Expr\Cast::class,
//    PhpParser\Node\Expr\ClassConstFetch::class,
//    PhpParser\Node\Expr\Clone_::class,
//    PhpParser\Node\Expr\Closure::class,
//    PhpParser\Node\Expr\ClosureUse::class,
//    PhpParser\Node\Expr\ConstFetch::class,
    PhpParser\Node\Expr\Empty_::class,
//    PhpParser\Node\Expr\ErrorSuppress::class,
//    PhpParser\Node\Expr\Eval_::class,
//    PhpParser\Node\Expr\Exit_::class,
    PhpParser\Node\Expr\FuncCall::class,
//    PhpParser\Node\Expr\Include_::class,
//    PhpParser\Node\Expr\Instanceof_::class,
    PhpParser\Node\Expr\Isset_::class,
    PhpParser\Node\Expr\List_::class,
//    PhpParser\Node\Expr\MethodCall::class,
//    PhpParser\Node\Expr\New_::class,
    PhpParser\Node\Expr\PostDec::class,
    PhpParser\Node\Expr\PostInc::class,
    PhpParser\Node\Expr\PreDec::class,
    PhpParser\Node\Expr\PreInc::class,
    PhpParser\Node\Expr\Print_::class,
//    PhpParser\Node\Expr\PropertyFetch::class,
//    PhpParser\Node\Expr\ShellExec::class,
//    PhpParser\Node\Expr\StaticCall::class,
//    PhpParser\Node\Expr\StaticPropertyFetch::class,
    PhpParser\Node\Expr\Ternary::class,
    PhpParser\Node\Expr\UnaryMinus::class,
    PhpParser\Node\Expr\UnaryPlus::class,
    PhpParser\Node\Expr\Variable::class,
    PhpParser\Node\Expr\YieldFrom::class,
    PhpParser\Node\Expr\Yield_::class,
    PhpParser\Node\Expr::class,
    PhpParser\Node\FunctionLike::class,
    PhpParser\Node\Name\FullyQualified::class,
    PhpParser\Node\Name\Relative::class,
    PhpParser\Node\Name::class,
    PhpParser\Node\Param::class,
    PhpParser\Node\Scalar\DNumber::class,
    PhpParser\Node\Scalar\Encapsed::class,
    PhpParser\Node\Scalar\LNumber::class,
//    PhpParser\Node\Scalar\MagicConst\Class_::class,
//    PhpParser\Node\Scalar\MagicConst\Dir::class,
//    PhpParser\Node\Scalar\MagicConst\File::class,
//    PhpParser\Node\Scalar\MagicConst\Function_::class,
//    PhpParser\Node\Scalar\MagicConst\Line::class,
//    PhpParser\Node\Scalar\MagicConst\Method::class,
//    PhpParser\Node\Scalar\MagicConst\Namespace_::class,
//    PhpParser\Node\Scalar\MagicConst\Trait_::class,
//    PhpParser\Node\Scalar\MagicConst::class,
    PhpParser\Node\Scalar\String_::class,
    PhpParser\Node\Scalar::class,
    PhpParser\Node\Stmt\Break_::class,
    PhpParser\Node\Stmt\Case_::class,
    PhpParser\Node\Stmt\Catch_::class,
//    PhpParser\Node\Stmt\ClassConst::class,
//    PhpParser\Node\Stmt\ClassLike::class,
//    PhpParser\Node\Stmt\ClassMethod::class,
//    PhpParser\Node\Stmt\Class_::class,
//    PhpParser\Node\Stmt\Const_::class,
    PhpParser\Node\Stmt\Continue_::class,
//    PhpParser\Node\Stmt\DeclareDeclare::class,
//    PhpParser\Node\Stmt\Declare_::class,
    PhpParser\Node\Stmt\Do_::class,
    PhpParser\Node\Stmt\Echo_::class,
    PhpParser\Node\Stmt\ElseIf_::class,
    PhpParser\Node\Stmt\Else_::class,
    PhpParser\Node\Stmt\Foreach_::class,
    PhpParser\Node\Stmt\For_::class,
    PhpParser\Node\Stmt\Function_::class,
//    PhpParser\Node\Stmt\Global_::class,
//    PhpParser\Node\Stmt\Goto_::class,
//    PhpParser\Node\Stmt\GroupUse::class,
//    PhpParser\Node\Stmt\HaltCompiler::class,
    PhpParser\Node\Stmt\If_::class,
//    PhpParser\Node\Stmt\InlineHTML::class,
//    PhpParser\Node\Stmt\Interface_::class,
//    PhpParser\Node\Stmt\Label::class,
//    PhpParser\Node\Stmt\Namespace_::class,
//    PhpParser\Node\Stmt\Property::class,
//    PhpParser\Node\Stmt\PropertyProperty::class,
    PhpParser\Node\Stmt\Return_::class,
//    PhpParser\Node\Stmt\StaticVar::class,
//    PhpParser\Node\Stmt\Static_::class,
    PhpParser\Node\Stmt\Switch_::class,
    PhpParser\Node\Stmt\Throw_::class,
//    PhpParser\Node\Stmt\TraitUse::class,
//    PhpParser\Node\Stmt\TraitUseAdaptation\Alias::class,
//    PhpParser\Node\Stmt\TraitUseAdaptation\Precedence::class,
//    PhpParser\Node\Stmt\TraitUseAdaptation::class,
//    PhpParser\Node\Stmt\Trait_::class,
    PhpParser\Node\Stmt\TryCatch::class,
    PhpParser\Node\Stmt\Unset_::class,
//    PhpParser\Node\Stmt\UseUse::class,
//    PhpParser\Node\Stmt\Use_::class,
    PhpParser\Node\Stmt\While_::class,
    PhpParser\Node\Stmt::class,
];

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$printer = new Standard();
$visitor = new WhitelistVisitor(["count"],$nodeWhiteList);

$sandbox = new Sandbox($visitor,$parser,$printer);


$codes = [
    '
    $a = 1+1;
    return $a;
    ',
    '
    $obj = new stdClass();
    return count($arr);
    ',
    '
    $arr = function(){
        return "foo";
    };
    return null;
    ',
];

foreach($codes as $code){
    echo "\n\nCode\n====\n";
    echo $code."\n";
    echo "====\n";
    try {
        $sandbox->validate($code);
        $res = $sandbox->executeWithArgs($code);

        echo "Result: '{$res["result"]}'\n'";
        echo "Args:\n".print_r($res["args"],true);
    }catch(Exception $e){
        echo "[".get_class($e)."] ".$e->getMessage();
    }
}
