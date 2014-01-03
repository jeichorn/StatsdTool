<?php
namespace StatsdTool;

// really simple db of what the last timestamp for each datapoint, we use this to dedupe
class LastStat
{
    protected $db = [];

    public function __construct()
    {
        if (file_exists('/tmp/StatsdTool.db'))
        {
            $this->db = unserialize(file_get_contents('/tmp/StatsdTool.db'));
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
        file_put_contents('/tmp/StatsdTool.db', serialize($this->db));
    }
}
