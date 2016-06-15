<?php

namespace EntityParser\Parser\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use EntityParser\Parser\Parser;
use EntityParser\Parser\Contract\CodeModelInterface;

class ParseCommand extends Command
{
    private $output;

    protected function configure()
    {
        $this
            ->setName('parse')
            ->setDescription('Greet someone')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Name of the input file'
            )
            ->addOption(
               'type',
               null,
               InputOption::VALUE_NONE,
               'Display all types from entity definition file'
            )
            ->addOption(
               'entity',
               null,
               InputOption::VALUE_NONE,
               'Display all entities from entity definition file'
            )    
            ->addOption(
               'const',
               null,
               InputOption::VALUE_NONE,
               'Display all constants from entity definition file'
            ) 
            ->addOption(
               'sql',
               null,
               InputOption::VALUE_NONE,
               'Sample sql output'
            ) 
            ->addOption(
               'php',
               null,
               InputOption::VALUE_NONE,
               'Sample php output'
            )                                                
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $name = $input->getArgument('name');
        $ext  = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext == "") {
            $name .= ".edf";
        }

        $src  = file_get_contents($name);
        $parser = new Parser();
        $codeModel = $parser->parse($src);

        if ($input->getOption("const"))
        {
            $this->output->write("\n");
            $this->showConstants($codeModel);
        }        

        if ($input->getOption("type"))
        {
            $this->output->write("\n");
            $this->showTypes($codeModel);
        }

        if ($input->getOption("entity"))
        {
            $this->output->write("\n");
            $this->showEntities($codeModel);
        }

        if ($input->getOption("sql"))
        {
            $this->output->write("\n");
            $this->toSql($codeModel);
        } 

        if ($input->getOption("php"))
        {
            $this->output->write("\n");
            $this->toPhp($codeModel);
        }                     
    }

    private function showTypes(CodeModelInterface $codeModel)
    {
        $this->output->write("Types: \n");

        $types = $codeModel->getTypes();
        foreach ($types as $type) 
        {
            $typeName = $type->getName();
            $length   = $type->getSize();
            $baseType = $type->getBaseType();

            $msg      = "Type: {$typeName}";

            if ($length > 0){
                $msg .= ", Length: {$length}";
            }
            
            if ($type->getBaseType() != null)
            {
                $msg .= " -> {$baseType->getName()}";                     
            }
            $this->output->write("  {$msg}\n");
        }        
    }

    private function showEntities(CodeModelInterface $codeModel)
    {
        $this->output->write("Entities: \n");

        $entities = $codeModel->getEntities();
        foreach ($entities as $e) 
        {
            $name = $e->getName();
            $msg      = "Entity: {$name}\n";

            $fields = $e->getFields();

            foreach ($fields as $f) {
                $name = $f->getName();
                $type = $f->getType()->getName();
                $msg .= "    Field: {$name} -> {$type}\n";
            }
            
            $this->output->write("  {$msg}\n");
        }        
    }  

    private function showConstants(CodeModelInterface $codeModel)
    {
        $this->output->write("Constants: \n");

        $constants = $codeModel->getConstants();
        foreach ($constants as $c) 
        {
            $name  = $c->getName();
            $value = $c->getValue();
            $msg   = "Constant: {$name} = {$value}";
            $this->output->write("  {$msg}\n");
        }        
    }   

    private function toSql(CodeModelInterface $codeModel)
    {
        $this->output->write("Demo conversion to SQL queries for schema creation. \n\n");
        $this->output->write("SQL: \n");

        $entities = $codeModel->getEntities();
        foreach ($entities as $e) 
        {
            $name = $e->getName();
            $msg  = "CREATE TABLE `{$name}` (\n";

            $fields = $e->getFields();

            foreach ($fields as $f) {
                $name = $f->getName();
                $type = $f->getType();

                $subType = $type;

                while ($subType->getBaseType() != null) {
                    $subType = $subType->getBaseType();
                }

                $typeName = strtolower($subType->getName());
                $size     = $subType->getSize();

                if ($typeName == "int") {
                    $typeName = "INT";
                }
                else if($typeName == "string") 
                {
                    $typeName = "VARCHAR";
                    if ($size > 0){
                        $typeName .= "($size)";
                    }
                }
                else if($typeName == "text") 
                {
                    $typeName = "MEDIUMTEXT";
                    if ($size > 0){
                        $typeName .= "($size)";
                    }
                }                
                else if($typeName == "date")
                {
                    $typeName = "DATE";
                }
                else 
                {
                    $typeName = "#Unknown($typeName)";
                }

                $msg .= "    `{$name}` {$typeName},\n";
            }
            $msg  = trim($msg, ",\n") . "\n";
            $msg .= ");\n";

            $this->output->write("{$msg}\n");
        }        
    } 

    private function toPhp(CodeModelInterface $codeModel)
    {
        $this->output->write("Demo conversion to PHP classes.\n\n");
        $this->output->write("PHP: \n");

        $entities = $codeModel->getEntities();
        foreach ($entities as $e) 
        {
            $name = $e->getName();
            $this->output->write("class {$name}\n");
            $this->output->write("{\n");

            foreach ($e->getFields() as $f) 
            {
                $fieldName = $f->getName();
                $this->output->write("  public \${$fieldName};\n");
            }
            $this->output->write("}\n\n");
        }        
    }                
}