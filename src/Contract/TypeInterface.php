<?php

namespace EntityParser\Parser\Contract;

interface TypeInterface
{
    function getName();
    function getAnnotations();
    function getBaseType();
    function getAttributes();
    function getIsBaseType();
    function getIsNullable();

    function getSize();
}