<?php

namespace paslandau\PhpSandbox;

use Closure;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;

class Sandbox{

    /**
     * @var NodeVisitor
     */
    private $visitor;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var PrettyPrinterAbstract
     */
    private $printer;

    /**
     * Sandbox constructor.
     * @param $visitor
     * @param $parser
     * @param $printer
     */
    public function __construct(NodeVisitor $visitor, Parser $parser, PrettyPrinterAbstract $printer)
    {
        $this->visitor = $visitor;
        $this->parser = $parser;
        $this->printer = $printer;
    }

    public function validate($code){
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->visitor);
        $code = "<?php\n" . $code;
        $ast = $this->parser->parse($code);
        $traverser->traverse($ast);
    }

    /**
     * @param string $code
     * @return mixed
     */
    public function execute($code){
        $res = $this->executeWithArgs($code);
        return $res["result"];
    }

    /**
     * @param $code
     * @param array $args
     * @return array ["result" => $resultOfCode, "args" => []];
     * @throws \Exception
     * @throws null
     */
    public function executeWithArgs($code, array $args = []){
        /**
         * Backup GLOBALS values to avoid information leakage and manipulation
         */
        $backup = [];
        $preserved = [
            "preserved" => "",
            "backup" => "",
            "args" => "",
            "code" => ""
        ];
        if(isset($GLOBALS) && is_array($GLOBALS)) {
            foreach ($GLOBALS as $key => $val) {
                if (array_key_exists($key, $preserved)) {
                    continue;
                }
                $backup[$key] = $val;
            }
        }

        //start new scope so that  $backup wont be available to eval
        $fn = function($code) use ($args){

            if(isset($GLOBALS) && is_array($GLOBALS)) {
                foreach ($GLOBALS as $key => $val) {
                    if($key !== "GLOBALS") {
                        global $$key; // @see http://php.net/manual/en/language.variables.predefined.php#30484 && http://phpover.org/Language_Reference/Variables/_security_issue_and_workaround_
                        $$key = null;
                    }
                }
                $GLOBALS = null;
            }

            // make variables available
            foreach($args as $key => $val){
                $$key = $val;
            }

            $res = eval($code);
            // reassign to $args
            foreach($args as $key => $val){
                $args[$key] = $$key;
            }

            return ["result" => $res, "args" => $args];
        };

        $fn = Closure::bind($fn, null, null); // make $this unavailable

        $e = null; // make sure we reset the globals even if the eval error's out
        $res = null;
        try{
            $res = $fn($code);
        }catch(\Exception $e){

        }

        /**
         * Reassign GLOBAL values
         */
        if(array_key_exists("GLOBALS",$backup)){
            $GLOBALS = $backup["GLOBALS"];
            unset($backup["GLOBALS"]);
        }
        foreach($backup as $key => $val){
            if(array_key_exists($key,$preserved)){
                continue;
            }
            global $$key; // @see http://php.net/manual/en/language.variables.predefined.php#30484
            $$key = $val;
        }

        if($e !== null){
            throw $e;
        }

        return $res;
    }
}