<?php

namespace EntityParser\Parser\Contract;

interface FieldInterface
{
    function getName();
    function getAnnotations();
    function getType();
    function getDefaultValue();
}