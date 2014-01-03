<?php
return [
    // we are only going to care about metrics dimensions with have these whitelisted dimensions
    'Dimensions' => [
        "InstanceId" => true,
    ],
    'Statistics' => [
        'CPUUtilization' => ['Maximum'],
        'StatusCheckFailed' => ['Sum'],
    ],
    'Unit' => [
        'CPUUtilization' => 'Percent',
        'StatusCheckFailed' => 'Count'
    ],
    'AWS/EC2' => [
        // metrics to pull for every machine
        "__global" => [
            'CPUUtilization' => true,
        ],
        // a per machine metric, you can use nice machine names instanceid will be looked up
        'web100.pagelydev.com' => [
            'StatusCheckFailed' => true
        ],
    ]
];
