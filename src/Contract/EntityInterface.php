<?php

namespace EntityParser\Parser\Contract;

use EntityParser\Parser\Ast\AnnotationCollection;
use EntityParser\Parser\Ast\FieldCollection;

interface EntityInterface
{
    function getName(): string;
    function getAnnotations(): AnnotationCollection;
    function getTableName(): string;
    function getFields(): FieldCollection;
}