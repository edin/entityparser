<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\EnumValueInterface;

class ASTEnumValue implements EnumValueInterface
{
    public $name;
    public $value;
    public $annotations = [];

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @return \EntityParser\Parser\Ast\AnnotationCollection
     */    
    public function getAnnotations()
    {
        return new AnnotationCollection($this->annotations);
    }
    
    /**
     * @return mixed 
     */
    public function getValue()
    {
        return $this->value;
    }
}