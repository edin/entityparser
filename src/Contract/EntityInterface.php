<?php

namespace EntityParser\Parser\Contract;

interface EntityInterface
{
    function getName();
    
    /**
     * @return \EntityParser\Parser\Ast\AnnotationCollection
     */    
    function getAnnotations();
    
    function getTableName();
    
    /**
     * @return \EntityParser\Parser\Ast\FieldCollection
     */    
    function getFields();
}