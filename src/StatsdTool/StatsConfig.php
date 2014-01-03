<?php
namespace StatsdTool;

class StatsConfig
{
    protected $config = [];
    protected $map = [];

    public function __construct($config_path = null)
    {
        if (is_null($config_path))
        {
            $config_path = __DIR__.'/../../conf/cloudwatch.php';
        }

        $this->config = include $config_path;
    }

    public function updateIdentifierMap($map)
    {
        foreach($map as $k => $v)
            $this->map[$k] = $v;
    }

    public function metricStatistics($metric)
    {
        if (isset($this->config['Statistics'][$metric]))
            return $this->config['Statistics'][$metric];

        return ['Average'];
    }

    public function shouldUseMetric($namespace, $metric, $identifier)
    {
        if (isset($this->config[$namespace]['__global'][$metric]))
            return true;

        if (isset($this->config[$namespace][$this->mapIdentifier($identifier)][$metric]))
            return true;

        return false;
    }

    public function shouldUseDimension($name)
    {
        if(isset($this->config['Dimensions'][$name]))
            return true;

        return false;
    }

    public function mapIdentifier($identifier)
    {
        if (isset($this->map[$identifier]))
            return $this->map[$identifier];

        return $identifier;
    }

    public function transform($metric, $value)
    {
        if (isset($this->config['Transform'][$metric]))
        {
            $function = $this->config['Transform'][$metric];
            $value = $function($value);
        }

        return $value;
    }
}
