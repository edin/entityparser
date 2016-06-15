<?php

namespace EntityParser\Parser\Exception;

use EntityParser\Parser\Token;

class ParserException extends \Exception
{
    public static function unexpectedCharacter($value, StringIterator $it) {
        return new static("Unexpected character '{$value}' at line {$it->line}.");
    }

    public static function expectedToken(Token $token, $tokenType){
        return new static("Error at line {$token->line}. Expected token type $tokenType but found token '{$token->value}'.");
    }

    public static function indexOutOfRange($index) {
        return new static("Index $index out of range.");
    }

    public static function unexpectedKeyword(Token $token) {
        return new static("Unexpected keyword '{$token->value}' at line {$token->line}.");
    }

    public static function redeclaredField(ASTEntity $entity, Token $token) {
        return new static("Redeclared field '{$token->value}' in entity '{$entity->name}' at line {$token->line}.");
    }

    public static function redeclaredEntity(Token $token) {
        return new static("Redeclared entity '{$token->value}' at line {$token->line}.");
    }

    public static function redeclaredType(Token $token) {
        return new static("Redeclared type '{$token->value}' at line {$token->line}.");
    }

    public static function constantDefined(Token $token) {
        return new static("Constant '{$token->value}' already defined  at line {$token->line}.");
    }

    public static function undefinedType(Token $token) {
        return new static("Undefined type '{$token->value}' at line {$token->line}.");
    }
}