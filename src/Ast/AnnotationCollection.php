<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\AnnotationInterface;

class AnnotationCollection extends Collection
{
    /**
     * @return boolean
     */
    public function contains($name)
    {
        return $this->find($name)->firstOrNull() != null;
    }

    /**
     * @return AnnotationInterface[]
     */
    public function find($name)
    {
        return $this->filterBy(function($e) use ($name) {
            return $e->getName() == $name;
        });
    }    

    /**
     * @return AnnotationInterface | null
     */
    public function findFirstOrNull($name)
    {
        return $this->find($name)->firstOrNull();
    }

    /**
     * @return AnnotationCollection
     */
    public function includeFromBase(AnnotationCollection $base)
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