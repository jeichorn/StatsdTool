<?php
namespace StatsdTool;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CloudWatchCommand extends Command
{
    protected $log;

    public function __construct(Log $log)
    {
        parent::__construct();
        $this->log = $log;
    }

    protected function configure()
    {
        $this
            ->setName('cloudwatch')
            ->setDescription('Grab the newest data from cloudwatch and send it to statsd')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new StatsConfig();
        $app = new App($config, $this->log);
        $app->runOne();
    }
}
