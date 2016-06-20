<?php

namespace EntityParser\Parser;

use EntityParser\Parser\Exception\ParserException;
use EntityParser\Parser\Ast\ASTConst;
use EntityParser\Parser\Ast\ASTDataType;
use EntityParser\Parser\Ast\ASTEntity;
use EntityParser\Parser\Ast\ASTRoot;
use EntityParser\Parser\Ast\ASTType;
use EntityParser\Parser\Ast\ASTField;
use EntityParser\Parser\Ast\ASTAnnotation;
use EntityParser\Parser\Ast\ASTEnum;
use EntityParser\Parser\Ast\ASTEnumValue;


function NewToken($value, $type)
{
    $t = new Token;
    $t->value = $value;
    $t->type = $type;
    return $t;
}

function IsLower($ch) { return $ch >= 'a' && $ch <= 'z';}
function IsUpper($ch) { return $ch >= 'A' && $ch <= 'Z';}
function IsDigit($ch) { return $ch >= '0' && $ch <= '9';}

function GetString(InputStream $it) {
    $buffer = "";

    $it->next(); // Skip starting {"} char

    while ($it->hasMore())
    {
        $val = $it->val();
        if ($val == "\\") {
            if ($it->peek() == "\"") {
                $it->next();
                $it->next();
                $buffer .= "\"";
                continue;
            }
        }
        if ($val == "\"") {
            //$it->next();
            break;
        }

        $buffer .= $val;
        $it->next();
    }
    return $buffer;
}

function GetAnnotation(InputStream $it) {
    $buffer = "";
    $it->next(); // Skip starting {@} char

    while ($it->hasMore())
    {
        $val = $it->val();
        if (IsUpper($val) || IsLower($val) || $val == "\\" || $val == "_") {
            $buffer .= $val;
        } else {
            $it->back();
            break;
        }
        $it->next();
    }
    return $buffer;
}

function GetComment(InputStream $it) {
    $buffer = "";
    $it->next(); // Skip starting {/} char
    $it->next(); // Skip char     {*}

    while ($it->hasMore())
    {
        $val = $it->val();
        if ($val == "*") {
            if ($it->peek() == "/") {
                $it->next(); // Skip {*}
                //$it->next(); // Skip {/}
                break;
            }
        }
        if ($val == "\n") {
            $it->line++;
        }
        $buffer .= $val;
        $it->next();
    }
    return $buffer;
}

function GetSingleLineComment(InputStream $it) {
    $buffer = "";
    $it->next(); // Skip starting {/} char
    $it->next(); // Skip char     {/}

    while ($it->hasMore())
    {
        $val = $it->val();
        if ($val == "\n") {
            $it->line++;
            break;
        }
        $buffer .= $val;
        $it->next();
    }
    return $buffer;
}

function GetIdentifier(InputStream $it) {
    $buffer = "";

    while ($it->hasMore())
    {
        $val = $it->val();

        if (IsLower($val) || IsUpper($val) || IsDigit($val) || $val == "_") {
            $buffer .= $val;
        } else {
            $it->back();
            break;
        }
        $it->next();
    }
    return $buffer;
}

function GetDigits(InputStream $it) {
    $buffer = "";
    while ($it->hasMore())
    {
        $val = $it->val();
        if (IsDigit($val)){
            $buffer .= $val;
        } else {
            break;
        }
        $it->next();
    }
    return $buffer;
}

function GetNumber(InputStream $it) {

    $buffer = "";
    $val = $it->val();
    if ($val == "-") {
        $buffer = "-";
        $it->next();
    }
    $buffer .= GetDigits($it);

    if ($it->val() == ".") {
        $it->next();
        $decimal = GetDigits($it);
        $buffer = $buffer . "." . $decimal;
    }
    $it->back();

    return $buffer;
}

function GetTokens($def)
{
    $it = new InputStream($def);
    $tokens = [];

    while ($it->hasMore())
    {
        $it->skipWhiteSpace();
        $value = $it->val();

        switch($value) {
            case "{": {
                $tokens[] = NewToken($value, SyntaxMap::OpenBrace);
            } break;
            case "}": {
                $tokens[] = NewToken($value, SyntaxMap::CloseBrace);
            } break;
            case "(": {
                $tokens[] = NewToken($value, SyntaxMap::OpenParen);
            } break;
            case ")": {
                $tokens[] = NewToken($value, SyntaxMap::CloseParen);
            } break;
            case ",": {
                $tokens[] = NewToken($value, SyntaxMap::Comma);
            } break;
            case ".": {
                $tokens[] = NewToken($value, SyntaxMap::Dot);
            } break;
            case ";": {
                $tokens[] = NewToken($value, SyntaxMap::Semicolon);
            } break;
            case "=": {
                $tokens[] = NewToken($value, SyntaxMap::AssignOp);
            } break;
            case "?": {
                $tokens[] = NewToken($value, SyntaxMap::QuestionMark);
            } break;
            case "\"": {
                $string = GetString($it);
                $tokens[] = NewToken($string, SyntaxMap::String);
            } break;
            case "/": {
                $it->next();
                $value = $it->val();
                if ($value == "*") {
                    GetComment($it);
                } else if ($value == "/") {
                    GetSingleLineComment($it);
                } else {
                    throw ParserException::unexpectedCharacter($value, $it);
                }
            } break;
            case "@": {
                $annotation = GetAnnotation($it);
                $tokens[]   = NewToken($annotation, SyntaxMap::Annotation);
            } break;
            case (ctype_digit($value)): {
                $number     = GetNumber($it);
                $tokens[]   = NewToken($number, SyntaxMap::Number);
            } break;
            case (ctype_alpha($value)): {

                $value = GetIdentifier($it);
                $v = strtolower($value);

                if (in_array($v,SyntaxMap::$keywords))
                {
                    $tokens[] = NewToken($value, SyntaxMap::Keyword);
                }
                else if (in_array($v, SyntaxMap::$dataTypes))
                {
                    $tokens[] = NewToken($value, SyntaxMap::DataType);
                }
                else
                {
                    $tokens[] = NewToken($value, SyntaxMap::Identifier);
                }
            } break;
            default: {
                throw ParserException::unexpectedCharacter($value, $it);
            }
        }

        if (count($tokens) > 0) {
            $lastToken = $tokens[count($tokens)-1];
            if (!isset($lastToken->line)) {
                $lastToken->line = $it->line;
            }
        }

        $it->next();
    }
    return $tokens;
}

function ParseDefinition(TokenStream $it)
{
    $root = new ASTRoot();

    while ($it->hasMore() && $it->expectAny([SyntaxMap::Annotation, SyntaxMap::Keyword], 0))
    {
        $annotations = ParseAnnotations($it);
        $token = $it->val();
        switch(strtolower($token->value))
        {
            case "type": {
                $type = ParseType($it);
                $type->annotations = $annotations;
                $root->types->add($type);
            } break;
            case "entity": {
                $entity = ParseEntity($it);
                $entity->annotations = $annotations;
                $root->entities->add($entity);
            } break;
            case "const": {
                $const = ParseConstant($it);
                $const->annotations = $annotations;
                $root->constants->add($const);
            } break;
            case "enum": {
                $enum = ParseEnum($it);
                $enum->annotations = $annotations;
                $root->enums->add($enum);
            } break;
            default: {
                throw ParserException::unexpectedKeyword($token);
            }
        }
    }
    return $root;
}

function ParseConstant(TokenStream $it)
{
    $const = new ASTConst;
    $it->expect(SyntaxMap::Keyword, 0);
    $it->expect(SyntaxMap::Identifier, 1);
    $it->expect(SyntaxMap::AssignOp, 2);
    $it->expect(SyntaxMap::String, 3);
    $it->expect(SyntaxMap::Semicolon,4);

    $it->getAndMove();
    $identifier = $it->getAndMove();
    $it->getAndMove();
    $value = $it->getAndMove();
    $it->getAndMove();

    $const->name = $identifier->value;
    $const->value = $value->value;
    $key = strtolower($const->name);
    if (isset($it->constants[$key])){
        throw ParserException::constantDefined($identifier);
    }

    $it->constants[$key] = $const;
    return $const;
}

function ParseType(TokenStream $it)
{
    $type = new ASTType;
    $it->expect(SyntaxMap::Keyword,    0);
    $it->expect(SyntaxMap::Identifier, 1);

    $it->getAndMove();
    $identifier   = $it->getAndMove();

    $type->name = $identifier->value;
    $type->type = ParseDataType($it);

    $key = strtolower($type->name);
    if (isset($it->types[$key])) {
        throw ParserException::redeclaredType($identifier);
    }
    $it->types[strtolower($type->name)] = $type;

    $it->expect(SyntaxMap::Semicolon, 0);
    $it->getAndMove();

    return $type;
}

function ParseEntity(TokenStream $it)
{
    $entity = new ASTEntity;

    $it->expect(SyntaxMap::Keyword,    0);
    $it->expect(SyntaxMap::Identifier, 1);
    $it->getAndMove();
    $identifier = $it->getAndMove();

    $entity->table = $identifier->value;

    //Verify that entity is not redeclared
    $key = strtolower($identifier->value);
    if (isset($it->entities[$key])){
        throw ParserException::redeclaredEntity($identifier);
    } else {
        $it->entities[$key] = [];
    }

    // Parse table name
    if ($it->typeAt(SyntaxMap::OpenParen, 0)) {
        $it->expect(SyntaxMap::OpenParen, 0);
        $it->expect(SyntaxMap::Identifier,1);
        $it->expect(SyntaxMap::CloseParen,2);

        $it->getAndMove();
        $tableName = $it->getAndMove();
        $it->getAndMove();
        $entity->table = $tableName->value;
    }

    $it->expect(SyntaxMap::OpenBrace,  0);
    $it->getAndMove();

    $entity->name = $identifier->value;

    while (!$it->typeAt(SyntaxMap::CloseBrace, 0))
    {
        $field = ParseField($it, $entity);
        $entity->fields[] = $field;
    }

    $it->getAndMove();
    return $entity;
}

function ParseEnum(TokenStream $it)
{
    $enum = new ASTEnum();

    $it->expect(SyntaxMap::Keyword,    0);
    $it->expect(SyntaxMap::Identifier, 1);
    $it->getAndMove();
    $identifier = $it->getAndMove();

    $enum->name = $identifier->value;

    //Verify that entity is not redeclared
    $key = strtolower($identifier->value);
    if (isset($it->types[$key])){
        throw ParserException::redeclaredEnum($identifier);
    } else {
        $it->types[$key] = $enum;
        $it->enums[$key] = [];
    }

    $it->expect(SyntaxMap::OpenBrace,  0);
    $it->getAndMove();

    while (!$it->typeAt(SyntaxMap::CloseBrace, 0))
    {
        $enumValue = ParseEnumValue($it, $enum);
        $enum->values->add($enumValue);
    }

    $it->getAndMove();
    return $enum;
}

function ParseEnumValue(TokenStream $it, ASTEnum $enum)
{
    $enumValue = new ASTEnumValue();
    $enumValue->annotations = ParseAnnotations($it);

    $it->expectAny([SyntaxMap::Identifier, SyntaxMap::DataType], 0);
    $identifier = $it->getAndMove();

    $enumValue->name = $identifier->value;

    //Verify if entity is already declared
    $entityKey = strtolower($enum->name);
    $key = strtolower($identifier->value);

    if (isset($it->enums[$entityKey][$key])) {
        throw ParserException::redeclaredEnumValue($enum, $identifier);
    } else {
        $it->enums[$entityKey][$key] = [];
    }

    if ($it->typeAt(SyntaxMap::AssignOp, 0))
    {
        $it->getAndMove();
        $it->expectAny([SyntaxMap::String, SyntaxMap::Number], 0);
        $val = $it->getAndMove();

        $enumValue->value = $val->getValue();
    }

    $it->expect(SyntaxMap::Semicolon,  0);
    $it->getAndMove();

    return $enumValue;
}

function ParseAnnotation(TokenStream $it)
{
    $it->expect(SyntaxMap::Annotation,   0);
    $ast = new ASTAnnotation;

    $type  = $it->getAndMove();
    $ast->name = $type->value;

    if ($it->typeAt(SyntaxMap::OpenParen,0))
    {
        $it->getAndMove();
        $expectComma = false;

        //Parse default
        $isVal =  $it->typeAt(SyntaxMap::String,0) || $it->typeAt(SyntaxMap::Number);
        if ($isVal) {
            $tokenValue = $it->getAndMove();
            $ast->default = $tokenValue->value;
            $expectComma = true;
        }

        //Read key = value
        while (!$it->typeAt(SyntaxMap::CloseParen, 0))
        {
            if ($expectComma) {
                $it->expect(SyntaxMap::Comma, 0);
                $it->getAndMove();
            }

            $it->expect(SyntaxMap::Identifier, 0);
            $it->expect(SyntaxMap::AssignOp, 1);
            $it->expectAny([SyntaxMap::String, SyntaxMap::Number], 2);
            $identifier = $it->getAndMove();
            $it->getAndMove();
            $tokenValue = $it->getAndMove();
            $ast->attributes[$identifier->value] = $tokenValue->value;

            $expectComma = true;
        }
        $it->expect(SyntaxMap::CloseParen, 0);
        $it->getAndMove();
    }
    return $ast;
}

function ParseAnnotations(TokenStream $it)
{
    $list = [];
    while ($it->typeAt(SyntaxMap::Annotation, 0))
    {
        $list[] = ParseAnnotation($it);
    }
    return $list;
}

function ParseDataType(TokenStream $it) {
    $dt = new ASTDataType;

    $it->expectAny([SyntaxMap::Identifier, SyntaxMap::DataType],   0);
    $type = $it->getAndMove();
    $dt->name = $type->value;

    if ($type->type == SyntaxMap::Identifier) {
        $key = strtolower($type->value);
        if (!isset($it->types[$key])){
            throw ParserException::undefinedType($type);
        }
        $dt->type = $it->types[$key];
    }

    if ($it->typeAt(SyntaxMap::QuestionMark, 0)) {
        $it->getAndMove();
        $dt->nullable = true;
    }

    if ($type->type === SyntaxMap::DataType && $it->typeAt(SyntaxMap::OpenParen,0))
    {
        $it->getAndMove();  //Open brace
        if ($it->typeAt(SyntaxMap::Number, 0)) {
            $num  = $it->getAndMove();
            $dt->size = $num->value;
        }
        if ($it->typeAt(SyntaxMap::Comma, 0)) {
            $it->expect(SyntaxMap::Number, 1);
            $it->getAndMove();
            $num   = $it->getAndMove();
            $dt->scale = $num->value;
        }
        $it->expect(SyntaxMap::CloseParen);
        $it->getAndMove();
    }
    return $dt;
}

function ParseField(TokenStream $it, ASTEntity $entity)
{
    $f = new ASTField();
    $f->annotations = ParseAnnotations($it);

    $type = ParseDataType($it);

    $it->expectAny([SyntaxMap::Identifier, SyntaxMap::DataType], 0);
    $identifier = $it->getAndMove();

    //Verify if entity is already declared
    $entityKey = strtolower($entity->name);
    $key = strtolower($identifier->value);

    if (isset($it->entities[$entityKey][$key])) {
        throw ParserException::redeclaredField($entity, $identifier);
    } else {
        $it->entities[$entityKey][$key] = [];
    }

    if ($it->typeAt(SyntaxMap::AssignOp, 0))
    {
        $it->getAndMove();
        $it->expectAny([SyntaxMap::String, SyntaxMap::Number],0);
        $val = $it->getAndMove();
        $f->default = $val;
    }
    $it->expect(SyntaxMap::Semicolon,  0);
    $it->getAndMove();

    $f->type = $type;
    $f->name = $identifier->value;
    return $f;
}

class Parser
{
    public function parse($source)
    {
        $tokens = GetTokens($source);
        $it = new TokenStream($tokens);
        return ParseDefinition($it);
    }
}