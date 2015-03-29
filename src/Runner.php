<?php



namespace App;



use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;



class Runner extends Application
{



    protected function getCommandName(InputInterface $input)
    {
        return 'rename';
    }



    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new RenameCommand();
        return $defaultCommands;
    }



    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        return $inputDefinition;
    }



}
