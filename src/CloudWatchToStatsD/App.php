<?php
namespace CloudWatchToStatsD;

use Aws\CloudWatch\CloudWatchClient;
use Aws\Ec2\Ec2Client;

Class App
{
    protected $instances = [];
    protected $instanceTags = [];
    protected $cloudwatch;
    protected $ec2;
    protected $statsd;
    protected $log;
    protected $config;
    protected $lastStat;

    public function __construct(StatsConfig $config, Log $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    public function runOne()
    {
        $this->init();

        $data = $this->getData();
        foreach($data as $metric => $value)
        {
            $this->statsd->gauge($metric, $value);
        }
    }

    protected function init()
    {
        // needed for stats
        $this->cloudwatch = CloudWatchClient::factory(\Config::$aws);

        // needed for metadata
        $this->ec2 = Ec2Client::factory(\Config::$aws);

        // where we are sending data
        $connection = new \Domnikl\Statsd\Connection\Socket(\Config::$statsd['host'], \Config::$statsd['port']);
        $this->statsd = new \Domnikl\Statsd\Client($connection);


        // create lookup maps that we can use to move from InstanceIds volume ids to Tag names for
        // mapping against the stats config
        $this->populateInstances();
        $this->config->updateIdentifierMap($this->instances);

        // @todo need to grab volume names

        $this->lastStat = new LastStat();
    }

    protected function populateInstances()
    {
        $result = $this->ec2->describeInstances();
        foreach($result['Reservations'] as $reservation)
        {
            foreach($reservation['Instances'] as $instance)
            {
                $this->instanceTags[$instance['InstanceId']] = [];
                foreach($instance['Tags'] as $tag)
                    $this->instanceTags[$instance['InstanceId']][$tag['Key']] = $tag['Value'];

                if (isset($this->instanceTags[$instance['InstanceId']]['Name']))
                {
                    list($name) = explode('.',$this->instanceTags[$instance['InstanceId']]['Name']);
                    $this->instances[$instance['InstanceId']] = $name;
                }
                else
                {
                    $this->instances[$instance['InstanceId']] = $instance['InstanceId'];
                }
            }
        }
    }

    protected function getData()
    {
        $metrics = $this->getMetrics();
        $this->log->info("Found ".count($metrics)." metrics");

        $data = [];
        foreach($metrics as $name => $dimensions)
        {
            $this->log->info("Trying to get data for $name");
            foreach($dimensions as $dimension)
            {
                list($namespace, $metric) = explode('.', $name);
                $now = time();
                $statistics = $this->config->metricStatistics($metric);
                $stats = $this->cloudwatch->getMetricStatistics([
                    'Namespace'  => $namespace,
                    'MetricName' => $metric,
                    'Dimensions' => [$dimension],
                    'StartTime'  => $this->startTime(strtotime('- '.\Config::$PollInterval, $now), \Config::$Period),
                    'EndTime'    => $now,
                    'Period'     => \Config::$Period,
                    'Statistics' => $statistics,
                    'Unit'       => $this->config->metricUnit($metric),
                ]);

                $stat_name = str_replace('/','.',$namespace)
                             .'.'.$this->config->mapIdentifier($dimension['Value'])
                             .'.'.$metric;


                // statsd is only for realtime, so lets only take the newest item,
                // we keep a log of what was the last timestamp for each metric

                $this->log->debug("Found ".count($stats['Datapoints'])." for $stat_name");
                foreach($stats['Datapoints'] as $point)
                {
                    $ts = strtotime($point['Timestamp']);

                    $last = $this->lastStat->get($stat_name);

                    if ($ts <= $last)
                        continue;

                    $this->log->debug("Using $stat_name $point[Timestamp]");
                    foreach($statistics as $stat)
                    {
                        $this->lastStat->set($stat_name, $ts);
                        $data[$stat_name] = $point[$stat];
                    }
                }
            }
        }

        $this->log->info("Found ".count($data)." Usable data points");
        return $data;
    }

    protected function getMetrics()
    {
        $iterator = $this->cloudwatch->getIterator('ListMetrics', [
//            'Namespace' => 'AWS/EC2',
        ]);

        $metrics = [];

        foreach($iterator as $metric)
        {
            if (!isset($metric['Dimensions'][0]['Name']))
                continue;

            $dimension = $metric['Dimensions'][0]['Name'];

            if (!$this->config->shouldUseDimension($dimension))
                continue;

            $identifier = $metric['Dimensions'][0]['Value'];
            if ($this->config->shouldUseMetric($metric['Namespace'], $metric['MetricName'], $identifier))
            {
                $name = $metric['Namespace'].'.'.$metric['MetricName'];
                if (!isset($metrics[$name]))
                    $metrics[$name] = [];

                $metrics[$name][] = ['Name' => $dimension, 'Value' => $identifier];
            }
        }

        return $metrics;

    }

    protected function startTime($ts, $period)
    {
        // floors to the nearest period
        return $ts - ($ts % $period);
    }
}
