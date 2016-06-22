<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\AnnotationInterface;

class AnnotationCollection extends Collection
{
    public function contains($name): bool
    {
        return $this->find($name)->firstOrNull() != null;
    }

    public function find($name): AnnotationCollection
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    public function findFirstOrNull($name) /* ?AnnotationInterface */
    {
        return $this->find($name)->firstOrNull();
    }

    public function includeFromBase(AnnotationCollection $base): AnnotationCollection
    {
        foreach ($base as $baseAnnotation) 
        {
            if (!$this->contains($baseAnnotation->getName())) {
                $this->add($baseAnnotation);
            }
        }
        return $this;
    }    
}