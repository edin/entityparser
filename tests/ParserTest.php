<?php

use \PHPUnit_Framework_TestCase as TestCase;

use EntityParser\Parser\Parser;
use EntityParser\Parser\Contract\CodeModelInterface;
use EntityParser\Parser\Contract\ConstantInterface;

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

    public function testEmptySource()
    {
        $empty = "";

        $cm = $this->parse($empty);
        $this->assertInstanceOf(CodeModelInterface::class, $cm);

        $types    = $cm->getTypes();
        $consts   = $cm->getConstants();
        $entities = $cm->getEntities();

        $this->assertEmpty($types);
        $this->assertEmpty($consts);
        $this->assertEmpty($entities);
    }

    public function testConstant()
    {
        $source = "
            const OutputDirectory = \"src/result\";
        ";

        $cm = $this->parse($source);
        $constants  = $cm->getConstants();

        $this->assertArrayHasKey("OutputDirectory", $constants);

        $const = $constants["OutputDirectory"];

        //$this->assertInstanceOf(ConstantInterface::cl
    }
}