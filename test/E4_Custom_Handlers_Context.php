<?php
require("../Flashlog.php");
//require("../LogLevel.php");

use Psr\Log\LogLevel as LogLevel;

function getTimestamp($record){
    echo $record["timestamp"];
};

$log=new FlashLog();
$log->pushHandler("EMERGENCY","getTimestamp");
$log->emergency("Esto es una emergencia {nombre}",["nombre"=>"Luis"]);
print_r($log->printLog())

?>