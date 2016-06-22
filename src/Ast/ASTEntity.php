<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\EntityInterface;

class ASTEntity implements EntityInterface
{
    public $name;
    public $table;
    public $fields;
    public $annotations = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function getAnnotations(): AnnotationCollection
    {
        return new AnnotationCollection($this->annotations);
    }

    public function getTableName(): string
    {
        return $this->fields;
    }

    public function getFields(): FieldCollection
    {
        return new FieldCollection($this->fields);
    }
}