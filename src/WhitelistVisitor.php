<?php

namespace paslandau\PhpSandbox;


use Exception;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

class WhitelistVisitor extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private $nodeWhitelist;

    /**
     * @var string[]
     */
    private $funcWhitelist;

    /**
     * @param string[] $funcWhitelist [optional]. Default: [].
     * @param string[] $nodeWhiteList [optional]. Default: [].
     */
    public function __construct(array $funcWhitelist = [], array $nodeWhiteList = []){
        $this->funcWhitelist = array_flip($funcWhitelist);
        $this->nodeWhitelist = array_flip($nodeWhiteList);
    }

    public function leaveNode(Node $node) {
        $class = get_class($node);
        if(!empty($this->nodeWhitelist) && !array_key_exists($class,$this->nodeWhitelist)){
            throw new SandboxException("Node '$class' not allowed");
        }

        if ($node instanceof FuncCall) {
            if(!$node->name instanceof Node\Name){
                throw new Exception("FuncCall must not be dynamic");
            }
            if(count($node->name->parts) > 1){
                throw new Exception("FuncCall must not contain namespaces");
            }
            $name = reset($node->name->parts);
            if(!is_string($name)){
                throw new Exception("FuncCall must be a string");
            }
            if(!empty($this->funcWhitelist) && !array_key_exists($name,$this->funcWhitelist)){
                throw new Exception("FuncCall '{$node->name}' not on whitelist!");
            }
        }
    }
}