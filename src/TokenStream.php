<?php

namespace EntityParser\Parser;

class TokenStream
{
    private $tokens ;
    private $pos = 0;

    public  $entities  = [];
    public  $types     = [];
    public  $constants = [];
    public  $enums     = [];

    public function __construct($tokens) {
        $this->tokens = $tokens;
    }

    public function hasMore() {
        return isset($this->tokens[$this->pos+1]);
    }

    public function val() {
        return $this->tokens[$this->pos];
    }

    public function getAndMove(){
        $val = $this->tokens[$this->pos];
        $this->next();
        return $val;
    }

    public function next() {
        $this->pos++;
    }

    public function typeAt($type, $at=0)
    {
        $p = $this->pos + $at;
        if (isset($this->tokens[$p]))
        {
            $t = $this->tokens[$p];
            if ($t->type != $type) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function expect($type, $at=0)
    {
        $p = $this->pos + $at;
        if (isset($this->tokens[$p]))
        {
            $t = $this->tokens[$p];
            if ($t->type != $type) {
                $tokenType = SyntaxMap::$map[$type];
                throw ParserException::expectedToken($t, $tokenType);
            }
            return $t;
        }
        throw ParserException::indexOutOfRange($p);
    }

    public function expectAny($typeArr, $at=0)
    {
        $p = $this->pos + $at;
        if (isset($this->tokens[$p]))
        {
            $t = $this->tokens[$p];
            if (!in_array($t->type, $typeArr, true)) {
                $tokenType = [];
                foreach($typeArr as $type) {
                    $tokenType[] = SyntaxMap::$map[$type];
                }
                $tokenTypeStr = implode(",", $tokenType);
                throw ParserException::expectedToken($t, $tokenTypeStr);
            }
            return $t;
        }
        throw ParserException::indexOutOfRange($p);
    }
}