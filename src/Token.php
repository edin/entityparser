<?php

namespace EntityParser\Parser;

class Token
{
    public $type;
    public $value;
    public $line;

    public function getValue()
    {
        if ($this->type == SyntaxMap::Number)
        {
            if (strpos($this->value, ".") !== false)
            {
                return (float)$this->value;
            }
            return (int)$this->value; 
        }
        return $this->value;
    }
}