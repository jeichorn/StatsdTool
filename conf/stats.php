<?php
return [
    // we are only going to care about metrics dimensions with have these whitelisted dimensions
    'Dimensions' => [
        "InstanceId" => true,
    ],
    // Possible Values  SampleCount | Average | Sum | Minimum | Maximum 
    'Statistics' => [
        'CPUUtilization' => ['Maximum'],
        'StatusCheckFailed' => ['Sum'],
        'DiskWriteOps' => ['Average'],
        'DiskReadOps' => ['Average'],
        'NetworkIn' => ['Average'],
        'NetworkOut' => ['Average'],
        'DiskReadBytes' => ['Average'],
        'DiskWriteBytes' => ['Average'],
    ],
    // Possible Values Seconds | Microseconds | Milliseconds | Bytes | Kilobytes | Megabytes | Gigabytes | Terabytes | Bits | Kilobits | Megabits | Gigabits | Terabits | Percent | Count | Bytes/Second | Kilobytes/Second | Megabytes/Second | Gigabytes/Second | Terabytes/Second | Bits/Second | Kilobits/Second | Megabits/Second | Gigabits/Second | Terabits/Second | Count/Second | None
    'Unit' => [
        'CPUUtilization' => 'Percent',
        'StatusCheckFailed' => 'Count',
        'DiskWriteOps' => 'Count',
        'DiskReadOps' => 'Count',
        'NetworkIn' => 'Bytes',
        'NetworkOut' => 'Bytes',
        'DiskReadBytes' => 'Bytes',
        'DiskWriteBytes' => 'Bytes',
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
    ]
];
