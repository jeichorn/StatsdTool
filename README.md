StatsdTool
==================

Tool for sending data to statsd from various sources

access keys to various external services live in config/config.php

Cloudwatch config lives in
config/cloudwatch.php

run ./bin/StatsdTool cloudwatch to import the last set of data points


run ./bin/StatsdTool logtail format file
format implmentations live in src/StatsdTool/formats/FormatName.php
