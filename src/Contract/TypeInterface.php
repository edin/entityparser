<?php

namespace EntityParser\Parser\Contract;

use \EntityParser\Parser\Ast\AnnotationCollection; 

interface TypeInterface
{
    function getName(): string;
    function getAnnotations(): AnnotationCollection;
    function getBaseType(): TypeInterface;
    function getIsPrimitiveType(): bool;
    function getIsNullable(): bool;
    function getSize(): int;
    function getScale(): int;
    function getIsEnumType(): bool;
    function getIsSetType(): bool;    
}