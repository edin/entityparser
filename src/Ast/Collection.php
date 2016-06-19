<?php

namespace EntityParser\Parser\Ast;

class Collection implements \IteratorAggregate, \Countable , \ArrayAccess
{
    private $items;

    public function __construct($items)
    {
        $this->items = array_values($items);
    }

    public function count()
    {
        return count($this->items);
    }

    public function getIterator() 
    {
        foreach ($this->items as $item) 
        {
            yield $item;
        }
    }

    public function firstOrNull()
    {
        foreach ($this->items as $key => $value) {
            return $value;
        }
        return null;
    }

    public function add($item)
    {
        $this->items[] = $item;
    }

    public function at($index)
    {
        return $this->items[$index];
    }

    protected function filterBy($callback)
    {
        $list = [];
        foreach($this->items as $item)
        {
            if ($callback($item))
            {
                $list[] = $item;
            }
        }
        return new static($list);
    }

    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset) 
    {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) 
    {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset) 
    {
        return $this->items[$offset];
    }  
}