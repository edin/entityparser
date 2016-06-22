<?php

namespace EntityParser\Parser\Contract;

use EntityParser\Parser\Ast\AnnotationCollection;

interface EnumValueInterface
{
    function getName(): string;
    function getAnnotations(): AnnotationCollection;
    function getValue();
}