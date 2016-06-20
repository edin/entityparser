<?php

namespace EntityParser\Parser;

class InputStream
{
    private $str;
    private $pos;
    private $len;

    public $line  = 1;

    public function __construct($str) {
        $this->str = $str;
        $this->pos = 0;
        $this->len = strlen($str);
    }

    private function getChar($index) {
        if ($index >= 0 && $index < $this->len) return $this->str[$index];
        return null;
    }

    public function val() {
        return $this->getChar($this->pos);
    }

    public function peek() {
        return $this->getChar($this->pos+1);
    }

    public function at($offset) {
        $pos = $this->pos + $offset;
        return $this->getChar($pos);
    }

    public function next() {
        $this->pos = $this->pos + 1;
    }
    public function back() {
        $this->pos = $this->pos - 1;
    }

    public function hasMore() {
        return $this->pos < $this->len;
    }

    public function skipWhiteSpace()
    {
        while ($this->hasMore()) {
            $ch = $this->val();
            $isWhiteSpace = ($ch == " "  || $ch == "\n" || $ch == "\r" || $ch == "\t");
            if ($isWhiteSpace) {
                if ($ch == "\n") {
                    $this->line++;
                }
            } else {
                break;
            }
            $this->next();
        }
    }
}