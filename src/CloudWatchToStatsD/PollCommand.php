<?php
namespace CloudWatchToStatsD;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PollCommand extends Command
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
            ->setName('poll')
            ->setDescription('Do a single run against cloudwatch')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new StatsConfig();
        $app = new App($config, $this->log);
        $app->runOne();
    }
}
