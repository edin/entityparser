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
    $it->getToken(SyntaxMap::Keyword);
    $identifier = $it->getToken(SyntaxMap::Identifier);
    $it->getToken(SyntaxMap::AssignOp);
    $value = $it->getToken(SyntaxMap::String);
    $it->getToken(SyntaxMap::Semicolon);

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
    $it->getToken(SyntaxMap::Keyword);
    $identifier = $it->getToken(SyntaxMap::Identifier);

    $type->name = $identifier->value;
    $type->type = ParseDataType($it);

    $key = strtolower($type->name);
    if (isset($it->types[$key])) {
        throw ParserException::redeclaredType($identifier);
    }
    $it->types[$key] = $type;

    $it->expect(SyntaxMap::Semicolon, 0);
    $it->getAndMove();

    return $type;
}

function ParseEntity(TokenStream $it)
{
    $entity = new ASTEntity;

    $it->getToken(SyntaxMap::Keyword);
    $identifier = $it->getToken(SyntaxMap::Identifier);

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
        $it->getToken(SyntaxMap::OpenParen);
        $tableName = $it->getToken(SyntaxMap::Identifier);
        $it->getToken(SyntaxMap::CloseParen);

        $entity->table = $tableName->value;
    }

    $it->getToken(SyntaxMap::OpenBrace);

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

    $it->getToken(SyntaxMap::Keyword);
    $identifier = $it->getToken(SyntaxMap::Identifier);

    $enum->name = $identifier->value;

    //Verify that entity is not redeclared
    $key = strtolower($identifier->value);
    if (isset($it->types[$key])){
        throw ParserException::redeclaredEnum($identifier);
    } else {
        $it->types[$key] = $enum;
        $it->enums[$key] = [];
    }

    $it->getToken(SyntaxMap::OpenBrace);

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

    $identifier = $it->getToken([SyntaxMap::Identifier, SyntaxMap::DataType]);
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
        $val = $it->getToken([SyntaxMap::String, SyntaxMap::Number]);
        $enumValue->value = $val->getValue();
    }

    $it->getToken(SyntaxMap::Semicolon);

    return $enumValue;
}

function ParseAnnotation(TokenStream $it)
{
    $ast = new ASTAnnotation;

    $type = $it->getToken(SyntaxMap::Annotation);
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
                $it->getToken(SyntaxMap::Comma);
            }

            $identifier = $it->getToken(SyntaxMap::Identifier);
            $it->getToken(SyntaxMap::AssignOp);
            $tokenValue = $it->getToken([SyntaxMap::String, SyntaxMap::Number]);
            
            $ast->attributes[$identifier->value] = $tokenValue->getValue();
            $expectComma = true;
        }
        $it->getToken(SyntaxMap::CloseParen);
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
    $isSetType = false;

    if ($it->typeAt(SyntaxMap::Keyword, 0))
    {
        $tok = $it->getToken(SyntaxMap::Keyword);
        if ($tok->value == "setof") 
        {
            $isSetType = true;
        }
        else
        {
            throw ParserException::expectedSetOfKeyword($tok);
        }
    }

    $type = $it->getToken([SyntaxMap::Identifier, SyntaxMap::DataType]);
    $dt->name = $type->value;

    if ($type->type == SyntaxMap::Identifier) {
        $key = strtolower($type->value);
        if (!isset($it->types[$key])){
            throw ParserException::undefinedType($type);
        }
        $dt->type = $it->types[$key];

        if ($isSetType)
        {
            if ($dt->type->getIsEnumType() == false)
            {
                throw ParserException::enumTypeIsRequired($type);
            }
            
            $dt->setIsSetType(true);
        }
    }

    if ($it->typeAt(SyntaxMap::QuestionMark, 0)) {
        $it->getAndMove();
        $dt->nullable = true;
        //TODO: Should set types be nullable ?
    }

    if ($type->type === SyntaxMap::DataType && $it->typeAt(SyntaxMap::OpenParen,0))
    {
        //TODO: Should set types have size and scale specifier ?
        $it->getAndMove();  //Open brace
        if ($it->typeAt(SyntaxMap::Number, 0)) {
            $num  = $it->getAndMove();
            $dt->size = $num->getValue();
        }
        if ($it->typeAt(SyntaxMap::Comma, 0)) {
            $it->getAndMove();
            $num  = $it->getToken(SyntaxMap::Number);
            $dt->scale = $num->getValue();
        }
        $it->getToken(SyntaxMap::CloseParen);
    }
    return $dt;
}

function ParseField(TokenStream $it, ASTEntity $entity)
{
    $f = new ASTField();
    $f->annotations = ParseAnnotations($it);

    $type = ParseDataType($it);

    $identifier = $it->getToken([SyntaxMap::Identifier, SyntaxMap::DataType]);

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
        $val = $it->getToken([SyntaxMap::String, SyntaxMap::Number]);
        $f->default = $val;
    }
    $it->getToken(SyntaxMap::Semicolon);

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