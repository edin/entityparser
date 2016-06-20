<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\EnumInterface;

class ASTEnum implements EnumInterface
{
    public $name;
    public $annotations = [];
    public $values;

    public function __construct()
    {
        $this->values = new EnumValueCollection();
    }

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
     * @return EnumValueCollection 
     */
    public function getValues()
    {
        return $this->values;
    }
}