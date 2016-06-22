<?php

namespace EntityParser\Parser;

class SyntaxMap 
{
    const OpenBrace    = 1;
    const CloseBrace   = 2;
    const Semicolon    = 3;
    const Identifier   = 4;
    const DataType     = 5;
    const Keyword      = 6;
    const OpenParen    = 7;
    const CloseParen   = 8;
    const Number       = 9;
    const Comma        = 10;
    const AssignOp     = 11;
    const String       = 12;
    const Annotation   = 14;
    const QuestionMark = 15;
    const Dot          = 16;
    const SetOf        = 17;

    public static $map = [
        self::OpenBrace    => 'OpenBrace',
        self::CloseBrace   => 'CloseBrace',
        self::Semicolon    => 'Semicolon',
        self::Identifier   => 'Identifier',
        self::DataType     => 'DataType',
        self::Keyword      => 'Keyword',
        self::OpenParen    => 'OpenParen',
        self::CloseParen   => 'CloseParen',
        self::Number       => 'Number',
        self::Comma        => 'Comma',
        self::AssignOp     => 'AssignOp',
        self::String       => 'String',
        self::Annotation   => 'Annotation',
        self::QuestionMark => 'QuestionMark',
        self::Dot          => 'Dot',
        self::SetOf        => 'SetOf'
    ];

    public static $keywords  = ['entity', 'type', 'const', 'enum', 'setof'];
    public static $dataTypes = ['int', 'string','date', 'text', 'decimal', 'float'];
}