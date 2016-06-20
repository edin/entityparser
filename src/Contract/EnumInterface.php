<?php

namespace EntityParser\Parser\Contract;

interface EnumInterface
{
    /**
     * @return string
     */
    function getName();
    
    /**
     * @return \EntityParser\Parser\Ast\AnnotationCollection
     */    
    function getAnnotations();
    
    /**
     * @return EnumValueCollection 
     */
    function getValues();
}