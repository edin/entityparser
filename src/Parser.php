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

class SyntaxMap {
    public static $map = [
        OpenBrace  => 'OpenBrace',
        CloseBrace => 'CloseBrace',
        Semicolon  => 'Semicolon',
        Identifier => 'Identifier',
        DataType   => 'DataType',
        Keyword    => 'Keyword',
        OpenParen  => 'OpenParen',
        CloseParen => 'CloseParen',
        Number     => 'Number',
        Comma      => 'Comma',
        AssignOp   => 'AssignOp',
        String       => 'String',
        Annotation => 'Annotation',
        QuestionMark => 'QuestionMark',
        Dot             => 'Dot'
    ];
    public static $keywords  = ['entity', 'type', 'const'];
    public static $dataTypes = ['int', 'string','date', 'text', 'decimal', 'float'];
}

class InputStream
{
    private $str;
    private $pos;
    private $len;

    public $line  = 1;

    public function __construct($str) {
        $this->str = $str;
        $this->pos = 0;
        $this->len = strlen($str);
    }

    private function getChar($index) {
        if ($index >= 0 && $index < $this->len) return $this->str[$index];
        return null;
    }

    public function val() {
        return $this->getChar($this->pos);
    }

    public function peek() {
        return $this->getChar($this->pos+1);
    }

    public function at($offset) {
        $pos = $this->pos + $offset;
        return $this->getChar($pos);
    }

    public function next() {
        $this->pos = $this->pos + 1;
    }
    public function back() {
        $this->pos = $this->pos - 1;
    }

    public function hasMore() {
        return $this->pos < $this->len;
    }

    public function skipWhiteSpace()
    {
        while ($this->hasMore()) {
            $ch = $this->val();
            $isWhiteSpace = ($ch == " "  || $ch == "\n" || $ch == "\r" || $ch == "\t");
            if ($isWhiteSpace) {
                if ($ch == "\n") {
                    $this->line++;
                }
            } else {
                break;
            }
            $this->next();
        }
    }
}

class Token
{
    public $type;
    public $value;
    public $line;
}

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
                $tokens[] = NewToken($value, OpenBrace);
            } break;
            case "}": {
                $tokens[] = NewToken($value, CloseBrace);
            } break;
            case "(": {
                $tokens[] = NewToken($value, OpenParen);
            } break;
            case ")": {
                $tokens[] = NewToken($value, CloseParen);
            } break;
            case ",": {
                $tokens[] = NewToken($value, Comma);
            } break;
            case ".": {
                $tokens[] = NewToken($value, Dot);
            } break;
            case ";": {
                $tokens[] = NewToken($value, Semicolon);
            } break;
            case "=": {
                $tokens[] = NewToken($value, AssignOp);
            } break;
            case "?": {
                $tokens[] = NewToken($value, QuestionMark);
            } break;
            case "\"": {
                $string = GetString($it);
                $tokens[] = NewToken($string, String);
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
                $tokens[]   = NewToken($annotation, Annotation);
            } break;
            case (ctype_digit($value)): {
                $number     = GetNumber($it);
                $tokens[]   = NewToken($number, Number);
            } break;
            case (ctype_alpha($value)): {

                $value = GetIdentifier($it);
                $v = strtolower($value);

                if (in_array($v,SyntaxMap::$keywords))
                {
                    $tokens[] = NewToken($value, Keyword);
                }
                else if (in_array($v, SyntaxMap::$dataTypes))
                {
                    $tokens[] = NewToken($value, DataType);
                }
                else
                {
                    $tokens[] = NewToken($value, Identifier);
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

class TokenStream
{
    private $tokens ;
    private $pos = 0;

    public  $entities  = [];
    public  $types     = [];
    public  $constants = [];

    public function __construct($tokens) {
        $this->tokens = $tokens;
    }

    public function hasMore() {
        return isset($this->tokens[$this->pos+1]);
    }

    public function val() {
        return $this->tokens[$this->pos];
    }

    public function getAndMove(){
        $val = $this->tokens[$this->pos];
        $this->next();
        return $val;
    }

    public function next() {
        $this->pos++;
    }

    public function typeAt($type, $at=0)
    {
        $p = $this->pos + $at;
        if (isset($this->tokens[$p]))
        {
            $t = $this->tokens[$p];
            if ($t->type != $type) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function expect($type, $at=0)
    {
        $p = $this->pos + $at;
        if (isset($this->tokens[$p]))
        {
            $t = $this->tokens[$p];
            if ($t->type != $type) {
                $tokenType = SyntaxMap::$map[$type];
                throw ParserException::expectedToken($t, $tokenType);
            }
            return $t;
        }
        throw ParserException::indexOutOfRange($p);
    }

    public function expectAny($typeArr, $at=0)
    {
        $p = $this->pos + $at;
        if (isset($this->tokens[$p]))
        {
            $t = $this->tokens[$p];
            if (!in_array($t->type, $typeArr, true)) {
                $tokenType = [];
                foreach($typeArr as $type) {
                    $tokenType[] = SyntaxMap::$map[$type];
                }
                $tokenTypeStr = implode(",", $tokenType);
                throw ParserException::expectedToken($t, $tokenTypeStr);
            }
            return $t;
        }
        throw ParserException::indexOutOfRange($p);
    }
}


function ParseDefinition(TokenStream $it)
{
    $root = new ASTRoot;

    while ($it->hasMore() && $it->expectAny([Annotation,Keyword], 0))
    {
        $annotations = ParseAnnotations($it);
        $token = $it->val();
        switch(strtolower($token->value))
        {
            case "type": {
                $type = ParseType($it);
                $type->annotations = $annotations;
                $root->types[] = $type;
            } break;
            case "entity": {
                $entity = ParseEntity($it);
                $entity->annotations = $annotations;
                $root->entities[] = $entity;
            } break;
            case "const": {
                $const = ParseConstant($it);
                $const->annotations = $annotations;
                $root->constants[$const->name] = $const;
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
    $it->expect(Keyword, 0);
    $it->expect(Identifier, 1);
    $it->expect(AssignOp, 2);
    $it->expect(String, 3);
    $it->expect(Semicolon,4);

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
    $it->expect(Keyword,    0);
    $it->expect(Identifier, 1);

    $it->getAndMove();
    $identifier   = $it->getAndMove();

    $type->name = $identifier->value;
    $type->type = ParseDataType($it);

    $key = strtolower($type->name);
    if (isset($it->types[$key])) {
        throw ParserException::redeclaredType($identifier);
    }
    $it->types[strtolower($type->name)] = $type;

    $it->expect(Semicolon, 0);
    $it->getAndMove();

    return $type;
}

function ParseEntity(TokenStream $it)
{
    $entity = new ASTEntity;

    $it->expect(Keyword,    0);
    $it->expect(Identifier, 1);
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
    if ($it->typeAt(OpenParen, 0)) {
        $it->expect(OpenParen, 0);
        $it->expect(Identifier,1);
        $it->expect(CloseParen,2);

        $it->getAndMove();
        $tableName = $it->getAndMove();
        $it->getAndMove();
        $entity->table = $tableName->value;
    }

    $it->expect(OpenBrace,  0);
    $it->getAndMove();

    $entity->name = $identifier->value;

    while (!$it->typeAt(CloseBrace, 0))
    {
        $field = ParseField($it, $entity);
        $entity->fields[] = $field;
    }

    $it->getAndMove();
    return $entity;
}

function ParseAnnotation(TokenStream $it)
{
    $it->expect(Annotation,   0);
    $ast = new ASTAnnotation;

    $type  = $it->getAndMove();
    $ast->name = $type->value;

    if ($it->typeAt(OpenParen,0))
    {
        $it->getAndMove();
        $expectComma = false;

        //Parse default
        $isVal =  $it->typeAt(String,0) || $it->typeAt(Number);
        if ($isVal) {
            $tokenValue = $it->getAndMove();
            $ast->default = $tokenValue->value;
            $expectComma = true;
        }

        //Read key = value
        while (!$it->typeAt(CloseParen, 0))
        {
            if ($expectComma) {
                $it->expect(Comma, 0);
                $it->getAndMove();
            }

            $it->expect(Identifier, 0);
            $it->expect(AssignOp, 1);
            $it->expectAny([String, Number], 2);
            $identifier = $it->getAndMove();
            $it->getAndMove();
            $tokenValue = $it->getAndMove();
            $ast->attributes[$identifier->value] = $tokenValue;

            $expectComma = true;
        }
        $it->expect(CloseParen, 0);
        $it->getAndMove();
    }
    return $ast;
}

function ParseAnnotations(TokenStream $it)
{
    $list = [];
    while ($it->typeAt(Annotation, 0))
    {
        $list[] = ParseAnnotation($it);
    }
    return $list;
}

function ParseDataType(TokenStream $it) {
    $dt = new ASTDataType;

    $it->expectAny([Identifier, DataType],   0);
    $type = $it->getAndMove();
    $dt->name = $type->value;

    if ($type->type == Identifier) {
        $key = strtolower($type->value);
        if (!isset($it->types[$key])){
            throw ParserException::undefinedType($type);
        }
        $dt->type = $it->types[$key];
        //print_r($dt); exit;
    }

    if ($it->typeAt(QuestionMark, 0)) {
        $it->getAndMove();
        $dt->nullable = true;
    }

    if ($type->type === DataType && $it->typeAt(OpenParen,0))
    {
        $it->getAndMove();  //Open brace
        if ($it->typeAt(Number, 0)) {
            $num  = $it->getAndMove();
            $dt->size = $num->value;
        }
        if ($it->typeAt(Comma, 0)) {
            $it->expect(Number, 1);
            $it->getAndMove();
            $num   = $it->getAndMove();
            $dt->scale = $num->value;
        }
        $it->expect(CloseParen);
        $it->getAndMove();
    }
    return $dt;
}

function ParseField(TokenStream $it, ASTEntity $entity)
{
    $f = new ASTField();
    $f->annotations = ParseAnnotations($it);

    $type = ParseDataType($it);

    $it->expectAny([Identifier, DataType], 0);
    $identifier = $it->getAndMove();

    //Verify if entity is already declared
    $entityKey = strtolower($entity->name);
    $key = strtolower($identifier->value);

    if (isset($it->entities[$entityKey][$key])) {
        throw ParserException::redeclaredField($entity, $identifier);
    } else {
        $it->entities[$entityKey][$key] = [];
    }

    if ($it->typeAt(AssignOp, 0))
    {
        $it->getAndMove();
        $it->expectAny([String, Number],0);
        $val = $it->getAndMove();
        $f->default = $val;
    }
    $it->expect(Semicolon,  0);
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