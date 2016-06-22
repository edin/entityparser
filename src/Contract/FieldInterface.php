<?php

namespace EntityParser\Parser\Contract;

use \EntityParser\Parser\Ast\AnnotationCollection; 

interface FieldInterface
{
    function getName(): string;
    function getAnnotations(): AnnotationCollection;
    function getType(): TypeInterface;
    function getDefaultValue();
}