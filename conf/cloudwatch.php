<?php
return [
    // we are only going to care about metrics dimensions with have these whitelisted dimensions
    'Dimensions' => [
        "InstanceId" => true,
        "LoadBalancerName" => true,
    ],
    'Transform' => [
        // change Latency to miliseconds we need an int
        'Latency' => function($in)
            {
                return (int)($in * 1000);
            },
    ],
    // Possible Values  SampleCount | Average | Sum | Minimum | Maximum 
    'Statistics' => [
        'CPUUtilization' => ['Maximum'],
        'StatusCheckFailed' => ['Sum'],
        'DiskWriteOps' => ['Average'],
        'DiskReadOps' => ['Average'],
        'NetworkIn' => ['Sum'],
        'NetworkOut' => ['Sum'],
        'DiskReadBytes' => ['Sum'],
        'DiskWriteBytes' => ['Sum'],
        'RequestCount' => ['Sum'],
        'HTTPCode_Backend_5XX' => ['Sum'],
        'HTTPCode_Backend_2XX' => ['Sum'],
    ],
    'AWS/EC2' => [
        // metrics to pull for every machine
        "__global" => [
            'CPUUtilization' => true,
            'DiskReadOps' => true,
            'DiskWriteOps' => true,
            'DiskReadBytes' => true,
            'DiskWriteBytes' => true,
            'NetworkIn' => true,
            'NetworkOut' => true,
        ],
        // a per machine metric, you can use nice machine names instanceid will be looked up
        'web100.pagelydev.com' => [
            'StatusCheckFailed' => true
        ],
    ],
    'AWS/ELB' => [
        'Cloud' => [
            'Latency' => true,
            'RequestCount' => true,
            'HTTPCode_Backend_5XX',
            'HTTPCode_Backend_2XX'
        ]
    ]
];
