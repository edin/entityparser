<?php

namespace EntityParser\Parser\Contract;

use EntityParser\Parser\Ast\AnnotationCollection;

interface ConstantInterface
{
    function getName(): string;
    function getAnnotations(): AnnotationCollection;
    function getValue();
}