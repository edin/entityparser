<?php

namespace EntityParser\Parser\Contract;

use EntityParser\Parser\Ast\ConstCollection;
use EntityParser\Parser\Ast\TypeCollection;
use EntityParser\Parser\Ast\EntityCollection;
use EntityParser\Parser\Ast\EnumCollection;

interface CodeModelInterface
{
    function getConstants(): ConstCollection;
    function getTypes(): TypeCollection;
    function getEntities(): EntityCollection;
    function getEnums(): EnumCollection;
}