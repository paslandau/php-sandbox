<?php

use paslandau\PhpSandbox\Sandbox;
use paslandau\PhpSandbox\WhitelistVisitor;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;

class SandboxTest extends PHPUnit_Framework_TestCase
{
    public $foo = "foo";

    public function test_ShouldNotChangeGlobals()
    {
        $_GET["foo"] = 1;

//        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $parser = new Parser(new Lexer);
        $printer = new Standard();
        $visitor = new WhitelistVisitor();

        $sandbox = new Sandbox($visitor,$parser,$printer);

        $code = '
        global $_GET;
$_GET["foo"] = 2;
';
        $res = $sandbox->execute($code);

        $this->assertEquals(1,$_GET["foo"]);
    }

    public function test_ShouldNotAccessGlobals()
    {
        $_GET["foo"] = 1;

//        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $parser = new Parser(new Lexer);
        $printer = new Standard();
        $visitor = new WhitelistVisitor();

        $sandbox = new Sandbox($visitor,$parser,$printer);

        $code = '
        $fn = function(){
           return $_GET["foo"];
        };
        return $fn();
';
        $res = $sandbox->execute($code);
        var_dump($res);
        $this->assertNotEquals(1,$res);
    }

    public function test_ShouldNotAccessThis()
    {
        $this->setExpectedException(PHPUnit_Framework_Error_Notice::class);
//        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $parser = new Parser(new Lexer);
        $printer = new Standard();
        $visitor = new WhitelistVisitor();

        $sandbox = new Sandbox($visitor,$parser,$printer);

        $code = '
        var_dump($this);
';
            $res = $sandbox->execute($code);
        $this->assertNotEquals(1,$res);
    }

    public function test_ShouldNotAccessShell()
    {
        $this->setExpectedException(paslandau\PhpSandbox\SandboxException::class);

//        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $parser = new Parser(new Lexer);
        $printer = new Standard();
        $visitor = new WhitelistVisitor([],[PhpParser\Node\Stmt\Return_::class]);
        // PhpParser\Node\Expr\ShellExec::class <<< is not allowed

        $sandbox = new Sandbox($visitor,$parser,$printer);

        $code = '
        return `echo foo`;
';
        $sandbox->validate($code);
        $res = $sandbox->execute($code);
        $this->assertNotEquals("foo\n",$res);
    }

    public function test_execute()
    {
//        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $parser = new Parser(new Lexer);
        $printer = new Standard();
        $visitor = new WhitelistVisitor();

        $sandbox = new Sandbox($visitor,$parser,$printer);

        $code = '
 $a = 1+1;
 return $a;
';
        $res = $sandbox->execute($code);
        $this->assertEquals(2,$res);
    }
}
