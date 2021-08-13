<?php

/*
Perform a run over 100 random values. If the value is odd or even, an info message
will be saved on the log object. If a string appears, the exception causes a WARNIG,
wich is saved on the log 
*/

include("Flashlog.php");
$log=new FlashLog();

for($i=0;$i<100;$i++){
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

function is_odd($n){
    if(!is_numeric($n)){
        throw new Exception("Input is not a number!");
    }
    if($n % 2){
        return 1;
    }
    return 0;
}

echo $log->printLog(ConstantFlashLog::PRINT_TABLE);
$log->savePath="./test.log";
$log->overwrite=false;
$log->maxSize=30*1000;
$log->saveInFile()
?>