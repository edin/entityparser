<?php

namespace EntityParser\Parser\Ast;

use EntityParser\Parser\Contract\AnnotationInterface;

class AnnotationCollection implements \IteratorAggregate
{
    /**
     * @var AnnotationInterface[] $annotation;
     */        
    private $annotations;

    public function __constuct($annotations)
    {
        $this->annotations = array_values($annotations);
    }

    public function getIterator() 
    {
        foreach ($this->annotations as $annotation) 
        {
            yield $annotation;
        }
    }

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
        $list = [];
        foreach($this->annotations as $annotation)
        {
            if (strtolower($annotation->getName()) == $name)
            {
                $list[] = $annotation;
            }
        }
        return $list;
    }    

    /**
     * @return AnnotationInterface | null
     */
    public function findFirstOrNull($name)
    {
        return $this->find($name)->firstOrNull();
    }

    /**
     * @return AnnotationInterface | null
     */
    public function firstOrNull()
    {
        if (isset($this->annotations[0])) {
            return $this->annotations[0];
        }
        return null;
    }
}