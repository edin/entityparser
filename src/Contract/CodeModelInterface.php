<?php

namespace EntityParser\Parser\Contract;

interface CodeModelInterface
{
    function getConstants();
    function getTypes();
    function getEntities();
}