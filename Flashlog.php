<?php
require_once("LoggerInterface.php");
require_once("LogLevel.php");
require_once("InvalidArgumentException.php");

use Psr\Log\LogLevel as LogLevel;

class ConstantFlashLog{
    const PRINT_JSON    = 'json';
    const PRINT_TABLE   = 'table';
    const PRINT_ARRAY   = 'array';
}

class Flashlog implements Psr\Log\LoggerInterface{
    protected $logArray     = array();
    public $savePath        = null;
    public $overwrite       = false;
    public $maxSize         = 100*1000*1000;
    
    function __construct($arg=null){
        if(!$arg){
            return 1;
        }
        isset($arg["savePath"]) ? $savePath=$arg["savePath"] : null;
        isset($arg["overwrite"]) ? $savePath=$arg["overwrite"] : null;
        isset($arg["max-size"]) ? $maxSize=$arg["max-size"] : null;
    }
    function emergency($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::EMERGENCY,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function alert($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::ALERT,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function critical($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::CRITICAL,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function error($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::ERROR,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function warning($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::WARNING,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }
    function notice($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::NOTICE,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function info($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::INFO,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function debug($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::DEBUG,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }
    
    function log($level,$message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => $level,
            "content"   => $message
        ];
        array_push($this->logArray,$out);
        return 1;
    }

    function printLog($format){
        $out="The given format was not recognized";
        switch($format){
            case ConstantFlashLog::PRINT_ARRAY:
                $out=$this->logArray;
            case ConstantFlashLog::PRINT_JSON:
                $out=json_encode($this->logArray);
            break;
            case ConstantFlashLog::PRINT_TABLE:
                $out="";
                foreach($this->logArray as $row){
                    $formattedLevel=str_pad($row["level"],9," ");
                    $out.=$row["timestamp"]."\t".$formattedLevel."\t".$row["content"]."\n";
                }
            break;
        }
        return $out;
    }

    function saveInFile(){
        if(count($this->logArray)==0){
            return 1;
        }
        if(!$this->savePath){
            trigger_error("Cannot save log in NULL directory. Set the save path with Flashlog::savePath",E_USER_WARNING);
        }
        $mode = $this->overwrite ? "w+" : "a+";
        if(filesize($this->savePath)<$this->maxSize){
            $logFile=fopen($this->savePath,$mode);
        }else{
            $pathParts=pathinfo($this->savePath);
            $i=1;
            while(file_exists($pathParts["dirname"]."/".$pathParts["filename"].".$i.".$pathParts["extension"]) && filesize($pathParts["dirname"]."/".$pathParts["filename"].".$i.".$pathParts["extension"])>$this->maxSize){
                $i++;
            }
            $logFile=fopen($pathParts["dirname"]."/".$pathParts["filename"].".$i.".$pathParts["extension"],$mode);
        }
        
        if(!$logFile){
            trigger_error("Cannot open the given path ".$this->savePath,E_USER_WARNING);
        }
        $newContent=$this->printLog(ConstantFlashLog::PRINT_TABLE);
        fwrite($logFile,$newContent);
        fclose($logFile);
        return 1;
    }


}

?>