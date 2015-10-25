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

$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$printer = new Standard();
$visitor = new WhitelistVisitor();

$sandbox = new Sandbox($visitor,$parser,$printer);

$code = '
 $a = 1+1;
 return $a;
';

try {
    $sandbox->validate($code);
    $res = $sandbox->executeWithArgs($code);

    echo "Result: '{$res["result"]}'\n'";
    echo "Args:\n".print_r($res["args"],true);
}catch(Exception $e){
    echo "[".get_class($e)."] ".$e->getMessage();
}