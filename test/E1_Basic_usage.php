<?php

/*
Perform a run over 100 random values. If the value is odd or even, an info message
will be saved on the log object. If a string appears, the exception causes a WARNIG,
wich is saved on the log 
*/

require("../Flashlog.php");

$log=new FlashLog();

for($i=0;$i<20;$i++){
    // Generate random records with the INFO and WARNING levels
    $e=random_int(0,100);
    $e = $e>70 ? "I'M TEXT" : $e;
    try{
        if(is_odd($e)){
            $log->info("The number $e is odd");
        }else{
            $log->info("The number $e is even");
        }
    }catch(Exception $e){
        $log->warning($e->getMessage());
    }
}

// Main Script

echo $log->printLog(FlashlogConstants::PRINT_TABLE);
$log->savePath="./example2.log";
$log->overwrite=true;
$log->maxSize=30*1024*1024;
$log->saveInFile();

function is_odd($n){
    if(!is_numeric($n)){
        throw new Exception("Input is not a number!");
    }
    if($n % 2){
        return 1;
    }
    return 0;
}

?>