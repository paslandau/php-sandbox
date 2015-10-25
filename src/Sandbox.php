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
                    global $$key; // @see http://php.net/manual/en/language.variables.predefined.php#30484 && http://phpover.org/Language_Reference/Variables/_security_issue_and_workaround_
                    $$key = null;
                }
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
        $res = $fn($code);

        /**
         * Reassign GLOBAL values
         */
        foreach($backup as $key => $val){
            if(array_key_exists($key,$preserved)){
                continue;
            }
            global $$key; // @see http://php.net/manual/en/language.variables.predefined.php#30484
            $$key = $val;
        }

        return $res;
    }
}