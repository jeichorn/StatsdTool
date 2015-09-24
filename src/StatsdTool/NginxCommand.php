<?php
namespace StatsdTool;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NginxCommand extends Command
{
    protected $log;
    protected $statsd;

    public function __construct(Log $log)
    {
        parent::__construct();
        $this->log = $log;

         // where we are sending data
        $connection = new \Domnikl\Statsd\Connection\UdpSocket(\Config::$statsd['host'], \Config::$statsd['port']);
        $this->statsd = new \Domnikl\Statsd\Client($connection);
    }

    protected function configure()
    {
        $this
            ->setName('nginx')
            ->setDescription('Poll a nginx status page parse the results and send them to statsd')
            ->addArgument('url', InputArgument::REQUIRED, "The url to poll")
            ->addArgument('name', InputArgument::REQUIRED, "Prefix to use on the metrics")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $stats = new \stdClass;

        $content = http_get($url);
        $lines = explode("\n", $content);
        foreach($lines as $line)
        {
            if (preg_match('/Active connections: (.+)/', $line, $match))
            {
                $stats->connections = $match[1];
            }
            elseif (preg_match('/ ([0-9]+) ([0-9]+) ([0-9]+) /', $line, $match))
            {
                $stats->accepted = $match[1];
                $stats->connection_handles = $match[2];
                $stats->request_handles = $match[3];
            }
            elseif (preg_match('/Reading: ([0-9]+) Writing: ([0-9]+) Waiting: ([0-9]+) /', $line, $match))
            {
                $stats->reading = $match[1];
                $stats->writing = $match[2];
                $stats->waiting = $match[3];
            }
        }

        $name = $input->getArgument('name');
        foreach($stats as $stat => $value)
        {
            $metric = "nginx.$name.$stat";
            $this->statsd->gauge($metric, $value);
        }
        var_dump($stats);
    }
}
