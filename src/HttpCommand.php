<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/8
 * Time: 19:50
 */

namespace rabbit\httpserver;


use rabbit\console\Command;
use rabbit\core\ObjectFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HttpCommand extends Command
{
    protected function configure()
    {
        $this->setName('http:server')->setDescription('start|stop|reload httpserver')->setHelp('This command allows you to start|stop|reload httpserver.')
            ->addArgument('cmd', InputArgument::REQUIRED, 'start|stop|reload');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');
        ObjectFactory::get('httpserver')->$cmd();
    }
}