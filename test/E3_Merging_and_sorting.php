<?php
require("../Flashlog.php");
//require("../LogLevel.php");

use Psr\Log\LogLevel as LogLevel;

$log=new FlashLog();

$log->loadFromFile("example2.log");
print_r($log->seeInfo());
$log->loadFromFile("example1.log","append");
print_r($log->seeInfo());
$log->sort();
print_r($log->seeInfo());
?>