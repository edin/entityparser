<?php

namespace EntityParser\Parser\Contract;

interface AnnotationInterface
{
    function getName();
    function getDefaultValue();
    function getAttributes();
}