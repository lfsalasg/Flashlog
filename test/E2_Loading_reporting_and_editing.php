<?php
require("../Flashlog.php");
//require("../LogLevel.php");

use Psr\Log\LogLevel as LogLevel;

$log=new FlashLog();

$log->loadFromFile("example1.log");
//See information about the file
print_r($log->seeInfo());
// Get only the warning messages
echo "WARNING MESSAGES:\n";
$warnings = $log->extract([LogLevel::WARNING]);
echo $warnings->printLog(FlashlogConstants::PRINT_TABLE);
//Delete last three records from the warnings log
$warnings->delete(3);
print_r($warnings->seeInfo());
?>