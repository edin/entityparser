<?php

namespace EntityParser\Parser\Contract;

interface AnnotationInterface
{
    function getName(): string;
    function getDefaultValue();
    function getAttributes(): array;
}