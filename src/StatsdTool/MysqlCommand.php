<?php
namespace StatsdTool;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use mysqli;

class MysqlCommand extends Command
{
    protected $log;
    protected $statsd;

    public function __construct(Log $log)
    {
        parent::__construct();
        $this->log = $log;

         // where we are sending data
        $connection = new \Domnikl\Statsd\Connection\Socket(\Config::$statsd['host'], \Config::$statsd['port']);
        $this->statsd = new \Domnikl\Statsd\Client($connection);

    }

    protected function configure()
    {
        $this
            ->setName('mysql')
            ->setDescription('Poll a mysql server grabbing stats them to statsd')
            ->addArgument('name', InputArgument::REQUIRED, "Name of config and statsd prefix")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('name');
        $mysql = new mysqli(\Config::$mysql[$server]['host'], \Config::$mysql[$server]['user'], \Config::$mysql[$server]['password']);

        $query = "SHOW GLOBAL STATUS";

        $config = include __DIR__.'/../../conf/mysql.php';

        $r = $mysql->query($query);
        while($row = $r->fetch_assoc())
        {
            $name = $row['Variable_name'];
            $value = $row['Value'];
            if (isset($config[$name]))
            {
                $i = strpos($name, '_');
                $metric = $name;
                if ($i > 0)
                    $metric[$i] = '.';
                $this->statsd->gauge("mysql.$metric.$server", $value);
                echo "mysql.$metric.$server $value\n";
            }
        }
    }
}
