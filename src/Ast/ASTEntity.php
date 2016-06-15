<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\EntityInterface;

class ASTEntity implements EntityInterface
{
    public $name;
    public $table;
    public $fields;
    public $annotations = [];

    public function getName()
    {
        return $this->name;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function getTableName()
    {
        return $this->fields;
    }

    public function getFields()
    {
        return $this->fields;
    }
}