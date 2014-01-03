<?php
namespace StatsdTool;

class Log
{
    public function error($msg)
    {
        echo date('[Y-m-d H:i:s] ').$msg."\n";
    }

    public function info($msg)
    {
        echo date('[Y-m-d H:i:s] ').$msg."\n";
    }

    public function debug($msg)
    {
        echo date('[Y-m-d H:i:s] ').$msg."\n";
    }
}
