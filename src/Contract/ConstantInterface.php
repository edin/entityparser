<?php

namespace EntityParser\Parser\Contract;

interface ConstantInterface
{
    function getName();
    function getAnnotations();
    function getValue();
}