<?php

namespace EntityParser\Parser\Contract;

interface EntityInterface
{
    function getName();
    function getAnnotations();
    function getTableName();
    function getFields();
}