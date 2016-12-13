<?php

ini_set('max_execution_time',0);
ini_set("memory_limit", "-1");

$workingDirectory = getcwd();
chdir(__DIR__);
include_once("../../../pimcore/cli/startup.php");
chdir($workingDirectory);

$lockkey = "exporttoolkit_" . $argv[1];
Tool_Lock::acquire($lockkey);

$service = new \ExportToolkit\ExportService();
$service->executeExport($argv[1]);


Tool_Lock::release($lockkey);

echo "done\n";