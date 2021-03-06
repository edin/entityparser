<?php

use \PHPUnit_Framework_TestCase as TestCase;

use EntityParser\Parser\Parser;
use EntityParser\Parser\Contract\CodeModelInterface;
use EntityParser\Parser\Contract\ConstantInterface;
use EntityParser\Parser\Contract\TypeInterface;
use EntityParser\Parser\Contract\EntityInterface;
use EntityParser\Parser\Contract\FieldInterface;
use EntityParser\Parser\Contract\AnnotationInterface;
use EntityParser\Parser\Contract\EnumValueInterface;
use EntityParser\Parser\Contract\EnumInterface;

use EntityParser\Parser\Ast\FieldCollection;
use EntityParser\Parser\Ast\TypeCollection;
use EntityParser\Parser\Ast\EntityCollection;
use EntityParser\Parser\Ast\ConstCollection;
use EntityParser\Parser\Ast\AnnotationCollection;
use EntityParser\Parser\Ast\EnumCollection;
use EntityParser\Parser\Ast\EnumValueCollection;

class ParserTest extends TestCase
{
    /**
     * @return CodeModelInterface
     */
    private function parse($source)
    {
        $parser = new Parser();
        $codeModel = $parser->parse($source);
        return $codeModel;
    }

    public function emptySourceProvider()
    {
        return [
            [""],
            ["     "],
            ["//Single line comment"],
            ["
             /*
              Multiline comment
             */
            "],
            ["
             /*
              This is commented 
              entity Sample { }
             */
            "],
            ["//type CustomType string(200);"]
        ];
    }

    /**
     * @dataProvider emptySourceProvider
     */
    public function testEmptySource($source)
    {
        $cm = $this->parse($source);
        $this->assertInstanceOf(CodeModelInterface::class, $cm);

        $types    = $cm->getTypes();
        $consts   = $cm->getConstants();
        $entities = $cm->getEntities();

        $this->assertEmpty($types);
        $this->assertEmpty($consts);
        $this->assertEmpty($entities);

        $this->assertInstanceOf(TypeCollection::class, $types);
        $this->assertInstanceOf(ConstCollection::class, $consts);
        $this->assertInstanceOf(EntityCollection::class, $entities);    
    }

    public function testConstant()
    {
        $source = <<<TEXT
            const OutputDirectory = "src/result";
TEXT;

        $cm = $this->parse($source);
        $constants = $cm->getConstants();

        $this->assertSame($constants->contains("OutputDirectory"), true, "Const collection does not contain OutputDirectory constant");
        $outputDir = $constants->findFirstOrNull("OutputDirectory");

        $this->assertInstanceOf(ConstantInterface::class, $outputDir);
        $this->assertSame($outputDir->getValue(), "src/result");
    }

    public function testType()
    {
        $source = <<<TEXT
            type UserId      int;
            type Email       string;
            type ShortString string(100);
            type LongString  string(10000);
TEXT;

        $cm     = $this->parse($source);
        $types  = $cm->getTypes();

        $this->assertTrue($types->contains("UserId"));
        $this->assertTrue($types->contains("Email"));
        $this->assertTrue($types->contains("ShortString"));
        $this->assertTrue($types->contains("LongString"));
        $this->assertFalse($types->contains("Foo"));


        $this->assertCount(4, $types);

        $userId      = $types->findFirstOrNull("UserId");
        $email       = $types->findFirstOrNull("Email");
        $shortString = $types->findFirstOrNull("ShortString");
        $longString  = $types->findFirstOrNull("LongString");
        $foo         = $types->findFirstOrNull("Foo");


        $this->assertInstanceOf(TypeInterface::class, $userId);
        $this->assertInstanceOf(TypeInterface::class, $email);
        $this->assertInstanceOf(TypeInterface::class, $shortString);
        $this->assertInstanceOf(TypeInterface::class, $longString);
        $this->assertNull($foo);


        $this->assertSame($userId->getName(), "UserId");
        $this->assertSame($email->getName(), "Email");
        $this->assertSame($shortString->getName(), "ShortString");
        $this->assertSame($longString->getName(), "LongString");

        $this->assertSame($userId->getBaseType()->getName(), "int");
        $this->assertSame($email->getBaseType()->getName(), "string");
        $this->assertSame($shortString->getBaseType()->getName(), "string");
        $this->assertSame($longString->getBaseType()->getName(), "string");
        
        $this->assertSame($userId->getBaseType()->getSize(), 0);
        $this->assertSame($email->getBaseType()->getSize(), 0);
        $this->assertSame($shortString->getBaseType()->getSize(), 100);
        $this->assertSame($longString->getBaseType()->getSize(), 10000);
    }    

    public function testEntity() 
    {
        $source = <<<TEXT
            type UserId      int;
            type Email       string;
            type ShortString string(100);
            type LongString  string(10000);

            entity User
            {
                UserId Id;
                ShortString FirstName;
                ShortString LastName;
                ShortString Username;
                Email       Email;
            }
TEXT;

        $cm     = $this->parse($source);
        $entities  = $cm->getEntities();

        $this->assertInstanceOf(EntityCollection::class, $entities);
        $this->assertCount(1, $entities);

        $this->assertTrue($entities->contains("User"));
        $this->assertInstanceOf(EntityInterface::class, $entities[0]);

        $userEntity = $entities->findFirstOrNull("User");
        $this->assertSame($userEntity, $entities[0]);

        $fields = $userEntity->getFields();
        $this->assertCount(5, $fields);

        $this->assertTrue($fields->contains("Id"));
        $this->assertTrue($fields->contains("FirstName"));
        $this->assertTrue($fields->contains("LastName"));
        $this->assertTrue($fields->contains("Username"));
        $this->assertTrue($fields->contains("Email"));
        $this->assertFalse($fields->contains("MissingFieldName"));


        $Id        = $fields->findFirstOrNull("Id");
        $FirstName = $fields->findFirstOrNull("FirstName");
        $LastName  = $fields->findFirstOrNull("LastName");
        $Username  = $fields->findFirstOrNull("Username");
        $Email     = $fields->findFirstOrNull("Email");

        $this->assertInstanceOf(FieldInterface::class, $Id);
        $this->assertInstanceOf(FieldInterface::class, $FirstName);
        $this->assertInstanceOf(FieldInterface::class, $LastName);
        $this->assertInstanceOf(FieldInterface::class, $Username);
        $this->assertInstanceOf(FieldInterface::class, $Email);

        $this->assertSame($Id->getType()->getName(), "UserId");
        $this->assertSame($FirstName->getType()->getName(), "ShortString");
        $this->assertSame($LastName->getType()->getName(), "ShortString");
        $this->assertSame($Username->getType()->getName(), "ShortString");
        $this->assertSame($Email->getType()->getName(), "Email");


        $this->assertSame($FirstName->getType()->getSize(), 100);
        $this->assertSame($LastName->getType()->getSize(),  100);
        $this->assertSame($Username->getType()->getSize(),  100);        
    }

    public function testAnnotations()
    {
        $source = <<<TEXT
            type UserId      int;
            type Email       string;

            @Required
            type ShortString string(100);
            type LongString  string(10000);

            @Entity("Application\Domain\Entity\User")
            entity User
            {
                @PrimaryKey
                UserId Id;
                
                @DisplayValue("First Name")
                ShortString FirstName;

                @DisplayValue("Last Name")
                ShortString LastName;

                @DisplayValue("Username")
                ShortString Username;
                Email       Email;
            }
TEXT;

        $cm       = $this->parse($source);
        $entities = $cm->getEntities();
        $shortString   = $cm->getTypes()->findFirstOrNull("ShortString");

        $userEntity = $entities->findFirstOrNull("User");
        $userFields = $userEntity->getFields();

        $this->assertInstanceOf(EntityCollection::class, $entities);
        $this->assertCount(5, $userFields);

        $annotations = $userEntity->getAnnotations();

        $this->assertInstanceOf(AnnotationCollection::class, $annotations);
        $this->assertCount(1, $annotations);

        $this->assertTrue($annotations->contains("Entity"), "Annotation collection does not contains Entity annotation.");

        $entityAnnotation = $annotations->findFirstOrNull("Entity");
        $this->assertInstanceOf(AnnotationInterface::class, $entityAnnotation);

        $this->assertSame($entityAnnotation->getDefaultValue(), "Application\Domain\Entity\User");

        $typeAnnotations = $shortString->getAnnotations();
        $this->assertInstanceOf(AnnotationCollection::class, $typeAnnotations);
        $this->assertCount(1, $typeAnnotations);
        $this->assertTrue($typeAnnotations->contains("Required"), "Annotation collection does not contains Required annotation.");


        $FirstName = $userFields->findFirstOrNull("FirstName");
        $LastName  = $userFields->findFirstOrNull("LastName");
        $Username  = $userFields->findFirstOrNull("Username");

       
        $this->assertHasAnnotation($FirstName->getType()->getAnnotations(), "Required");
        $this->assertHasAnnotation($LastName->getType()->getAnnotations(), "Required");
        $this->assertHasAnnotation($Username->getType()->getAnnotations(), "Required");

        $this->assertHasAnnotation($FirstName->getAnnotations(), "DisplayValue");
        $this->assertHasAnnotation($LastName->getAnnotations(), "DisplayValue");
        $this->assertHasAnnotation($Username->getAnnotations(), "DisplayValue");        
    } 

    private function assertHasAnnotation($collection, $annotationName)
    {
        $this->assertTrue($collection->contains($annotationName), "Annotation collection does not contains $annotationName annotation.");
    }


    public function testEnum()
    {
        $source = <<<TEXT
            enum Language
            {
                PHP         = 1;
                JavaScript  = 2;
                VisualBasic = 3;
                CSharp      = 4;
                Delphi      = 5;
                Ruby        = 6;
                Python      = 7;
                DLanguage   = 8;
                Cpp         = 9;
                Nim         = 10;
            }
TEXT;

        $cm    = $this->parse($source);
        $enums = $cm->getEnums();

        $langEnum = $enums->findFirstOrNull("Language");
        $this->assertInstanceOf(EnumInterface::class, $langEnum);


        $values = $langEnum->getValues();
        $this->assertInstanceOf(EnumValueCollection::class, $values);

        $this->assertTrue($values->contains("PHP"));
        $this->assertTrue($values->contains("JavaScript"));
        $this->assertTrue($values->contains("VisualBasic"));
        $this->assertTrue($values->contains("CSharp"));
        $this->assertTrue($values->contains("Delphi"));
        $this->assertTrue($values->contains("Ruby"));
        $this->assertTrue($values->contains("Python"));
        $this->assertTrue($values->contains("DLanguage"));
        $this->assertTrue($values->contains("Cpp"));
        $this->assertTrue($values->contains("Nim"));

        $php = $values->findFirstOrNull("PHP");
        $dlang = $values->findFirstOrNull("DLanguage");
        $nim = $values->findFirstOrNull("Nim");

        $this->assertInstanceOf(EnumValueInterface::class, $php);

        $this->assertSame(1, $php->getValue());
        $this->assertSame(8, $dlang->getValue());
        $this->assertSame(10, $nim->getValue());
    }

    public function testEntityWithEnumType()
    {
        $source = <<<TEXT
            enum Language
            {
                PHP         = 1;
                JavaScript  = 2;
                VisualBasic = 3;
                CSharp      = 4;
                Delphi      = 5;
                Ruby        = 6;
                Python      = 7;
                DLanguage   = 8;
                Cpp         = 9;
                Nim         = 10;
            }

            entity Book
            {
                string   Title;
                Language Language;
            }
TEXT;

        $cm    = $this->parse($source);
        $book = $cm->getEntities()->findFirstOrNull("Book");
        $this->assertInstanceOf(EntityInterface::class, $book);

        $Language = $book->getFields()->findFirstOrNull("Language");

        $this->assertInstanceOf(TypeInterface::class, $Language->getType());
        $this->assertInstanceOf(TypeInterface::class, $Language->getType()->getBaseType());
    }   

    public function testSetTypes()
    {
        $source = <<<TEXT
            enum Language
            {
                PHP         = 1;
                JavaScript  = 2;
                VisualBasic = 3;
                CSharp      = 4;
                Delphi      = 5;
                Ruby        = 6;
                Python      = 7;
                DLanguage   = 8;
                Cpp         = 9;
                Nim         = 10;
            }

            type LanguageSet setof Language;

            entity User{
                int Id;
                string FirstName;
                string LastName;
                LanguageSet Languages;
            }
TEXT;

        $cm    = $this->parse($source);
        $enums = $cm->getEnums();
        $types = $cm->getTypes();
        $entities = $cm->getEntities();

        $langType = $enums->findFirstOrNull("Language");
        $langSetType = $types->findFirstOrNull("LanguageSet");
        $user = $entities->findFirstOrNull("User");
        $languagesField = $user->getFields()->findFirstOrNull("Languages");
        
        
        $this->assertInstanceOf(TypeInterface::class, $langType, "Language is not instance of TypeInterface.");
        $this->assertInstanceOf(TypeInterface::class, $langSetType, "LanguageSet is not instance of TypeInterface.");

        $this->assertTrue($langType->getIsEnumType(),    "Language is not enum type.");
        $this->assertTrue($langSetType->getIsSetType(),  "LanguageSet is not 'setof enum' type.");
        $this->assertTrue($langSetType->getIsEnumType(), "LanguageSet is not 'enum' type.");

        $typeAlias = $langSetType;               //type LanguageSet
        $setType   = $typeAlias->getBaseType();  // setof Language
        $enumType  = $setType->getBaseType();    // enum  Language

        $this->assertSame($enumType, $langType, "Language base type does not match with enum type.");

        //TODO: Flaten out 
        //$this->assertSame($languagesField->getType(),  $langSetType);
    }    
}