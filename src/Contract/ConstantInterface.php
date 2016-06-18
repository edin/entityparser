<?php

namespace EntityParser\Parser\Contract;

interface ConstantInterface
{
    /**
     * @return string
     */
    function getName();

    /**
     * @return \EntityParser\Parser\Ast\AnnotationCollection
     */
    function getAnnotations();

    /**
     * @return mixed
     */
    function getValue();
}