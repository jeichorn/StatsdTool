<?php
namespace CloudWatchToStatsD;

// really simple db of what the last timestamp for each datapoint, we use this to dedupe
class LastStat
{
    protected $db = [];

    public function __construct()
    {
        if (file_exists('/tmp/CloudWatchToStatsD.db'))
        {
            $this->db = unserialize(file_get_contents('/tmp/CloudWatchToStatsD.db'));
//           echo "Loading db with ".count($this->db)." items\n";
        }
    }

    public function get($metric)
    {
        if (isset($this->db[$metric]))
            return $this->db[$metric];

        return 0;
    }

    public function set($metric, $timestamp)
    {
        $this->db[$metric] = $timestamp;
    }

    public function __destruct()
    {
//        echo "Writing out db with ".count($this->db)." items\n";
        file_put_contents('/tmp/CloudWatchToStatsD.db', serialize($this->db));
    }
}
