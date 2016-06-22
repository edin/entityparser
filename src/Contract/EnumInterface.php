<?php

namespace EntityParser\Parser\Contract;

use EntityParser\Parser\Ast\AnnotationCollection;
use EntityParser\Parser\Ast\EnumValueCollection;

interface EnumInterface
{
    function getName(): string;
    function getAnnotations(): AnnotationCollection; 
    function getValues(): EnumValueCollection;
}