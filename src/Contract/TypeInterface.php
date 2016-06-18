<?php

namespace EntityParser\Parser\Contract;

interface TypeInterface
{
    function getName();
    function getAnnotations();
    function getBaseType();
    function getIsBaseType();
    function getIsNullable();
    function getSize();
    function getPrecision();
}