#!/usr/bin/php
<?php
include_once 'LogMaker.php';
include_once 'controller/monitor.controller.php';

$logMaker = LogMaker::getLogMakerInstance();
$fileName = __FILE__;

try {
    if (count($argv) < 2) throw new Exception('No command received with script call.');

    switch ($argv[1]) {
        case 'monitor':
            $fileName = getMonitorFileName();
            startMonitorService();
            break;

        default:
            throw new Exception("Could not recognize command: $argv[1].");
    }
} catch (Exception $err) {
    if (str_contains($fileName, 'monitor.controller.php')) $errorTitle = 'Error in the monitor service';
    else $errorTitle = 'Script call failed';

    $logMaker->writeErrorLog($errorTitle, $err->getMessage());
    echo "$errorTitle. See 'errorLog.log' for more information.\n" .
         "Write the order 'help' to see the list of accepted orders\n";
}


