<?php
require_once("LoggerInterface.php");
require_once("LogLevel.php");
require_once("InvalidArgumentException.php");

use Psr\Log\LogLevel as LogLevel;

class FlashlogConstants{
    const PRINT_JSON    = 'json';
    const PRINT_TABLE   = 'table';
    const PRINT_ARRAY   = 'array';
}

class FlashlogException extends RuntimeException{
    /*
     * Use __toString() to get info about the message
     * 
    */
}

class Flashlog implements Psr\Log\LoggerInterface{
    protected $logArray     = array();
    public $savePath        = null;
    public $overwrite       = false;
    public $maxSize         = 100*1000*1000;
    public $backup          = true;
    public $max_warnings    = 0;

    protected $handlers    = array();
    
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
            "content"   => $message,
            "context"   => $context
        ];
        $out["content"] = $this->replaceKeys($out);
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    function alert($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::ALERT,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    function critical($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::CRITICAL,
            "content"   => $message,            
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    function error($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::ERROR,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    function warning($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::WARNING,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }
    function notice($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::NOTICE,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    function info($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::INFO,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    function debug($message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => LogLevel::DEBUG,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }
    
    function log($level,$message,$context=array()){
        $out=[
            "timestamp" => date(DATE_RFC3339),
            "level"     => $level,
            "content"   => $message,
            "context"   => $context
        ];
        array_push($this->logArray,$out);
        $this->handle($out);
        return 1;
    }

    // Here start the functions used to handle the log file

    function printLog($format=FlashlogConstants::PRINT_ARRAY){
        $out="The given format $format was not recognized";
        switch($format){
            case FlashlogConstants::PRINT_ARRAY:
                $out=$this->logArray;
            break;
            case FlashlogConstants::PRINT_JSON:
                $out=json_encode($this->logArray);
            break;
            case FlashlogConstants::PRINT_TABLE:
                $out="";
                foreach($this->logArray as $row){
                    $formattedLevel=str_pad($row["level"],9," ");
                    $out.=$row["timestamp"]."\t".$formattedLevel."\t".$row["content"]."\n";
                }
            break;
            default:
                trigger_error($out,E_USER_WARNING);
                return 0;
        }
        return $out;
    }
    function delete($lastRecords=1,$offset=0){
        $this->logArray = array_splice($this->logArray,$offset,-$lastRecords);
        return 1;
    }
    function rebase($log){
        $oldLog=$this->logArray;
        $this->logArray=array();
        $warnings=array();
        foreach($log as $l){
            if(isset($l["timestamp"],$l["level"],$l["content"],$l["context"])){
                array_push($this->logArray,$l);
            }else{
                array_push($warnings,"Invalid format, skipping record");
            }
            if(count($warnings)>$this->max_warnings){
                break;
            }
            
        }
        if(count($warnings)>$this->max_warnings){
            $this->logArray=$oldLog;
            $this->warning("Rebase failed. Too many warnings (".count($warnings)."). Restoring old log");
            trigger_error("Rebase failed. Too many warnings (".count($warnings)."). Restoring old log",E_USER_WARNING);
            return 0;
        }
        return 1;
    }

    function saveInFile(){
        if(count($this->logArray)==0){
            return 1;
        }
        if(!$this->savePath){
            trigger_error("Cannot save log in NULL directory. Set the save path with Flashlog::savePath",E_USER_WARNING);
        }
        $mode = $this->overwrite ? "w+" : "a+";

        if(!is_file($this->savePath)){
            $logFile=fopen($this->savePath,$mode);
        }elseif(filesize($this->savePath)<$this->maxSize){
            $logFile=fopen($this->savePath,$mode);
        }elseif($this->backup){
            $pathParts=pathinfo($this->savePath);
            $i=1;
            while(file_exists($pathParts["dirname"]."/".$pathParts["filename"].".$i.".$pathParts["extension"]) && filesize($pathParts["dirname"]."/".$pathParts["filename"].".$i.".$pathParts["extension"])>$this->maxSize){
                $i++;
            }
            $logFile=fopen($pathParts["dirname"]."/".$pathParts["filename"].".$i.".$pathParts["extension"],$mode);
        }else{
            trigger_error("Filesize greater than the allowed filesize");
        }
        
        if(!$logFile){
            trigger_error("Cannot open the given path ".$this->savePath,E_USER_WARNING);
        }
        $newContent=$this->printLog(FlashlogConstants::PRINT_TABLE);
        fwrite($logFile,$newContent);
        fclose($logFile);
        return 1;
    }

    function loadFromFile($path,$action="rebase"){
        if(!file_exists($path)){
            trigger_error("File does not exist, skipping...",E_USER_WARNING);
            return 0;
        }
        $logFile=fopen($path,"r");
        $tempLog=array();
        while(!feof($logFile)){
            $line=fgets($logFile);
            $record=explode("\t",$line);
            @$temp=[
                "timestamp" => str_replace(" ","",$record[0]),
                "level"     => str_replace(" ","",$record[1]),
                "content"   => str_replace("\n","",$record[2]),
                "context"   => array()
            ];
            array_push($tempLog,$temp);
        }
        fclose($logFile);
        if(!end($tempLog)["timestamp"]){
           $tempLog=array_splice($tempLog,0,-1); 
        }
        switch($action){
            case "rebase":
                return $this->rebase($tempLog);
            break;
            case "append":
                $newLog=new Flashlog;
                if($newLog->rebase($tempLog)){
                    return $this->append($newLog);
                }
                $newLog=null;
            break;
            default:
                trigger_error("Given action $action was not recognized...",E_USER_WARNING);
                return 0;
        }
        
    }

    function seeInfo(){
        
        $levels=[
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG
        ];
        $info=[
            LogLevel::EMERGENCY => 0,
            LogLevel::ALERT => 0,
            LogLevel::CRITICAL => 0,
            LogLevel::ERROR => 0,
            LogLevel::WARNING => 0,
            LogLevel::NOTICE => 0,
            LogLevel::INFO => 0,
            LogLevel::DEBUG => 0
        ];
        foreach($levels as $l){
            foreach($this->logArray as $record){
                if($record["level"] == $l){
                    $info[$l]+=1;
                }
            }
        }
        $info["total_records"] = count($this->logArray);
        $info["begins_at"] = $this->logArray[0]["timestamp"];
        $info["ends_at"] = end($this->logArray)["timestamp"];
        return $info;
    }

    function extract($levels){
        $extract=new Flashlog;
        $newLog=array();
        foreach($this->logArray as $row){
            if(in_array($row["level"],$levels)){
                array_push($newLog,$row);
            }
        }
        $extract->rebase($newLog);
        return $extract;
    }

    function append(Flashlog ...$logs){
        foreach($logs as $log){
            $this->logArray = array_merge($this->logArray,$log->printLog());
        }        
        return 1;
    }

    function sort(){
        function cmp($a,$b){
            if($a["timestamp"]==$b["timestamp"]){
                return 0;
            }
            return ($a["timestamp"]<$b["timestamp"]) ? -1:1;
        }
        usort($this->logArray,"cmp");
        return 1;
    }
    function check(){
        $diagnose=array();
        foreach($this->logArray as $row){
            if(!isset($l["timestamp"]) || !$l["level"] || !$l["content"]){
                array_push($diagnose,["SEVERE","Format isn't correct"]);
            }
            if(count($row)){
                array_push($diagnose,["MODERATE","The record has more values than expected"]);
            }
            if(!in_array($row["level"],[LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG])){
                array_push($diagnose,["SEVERE","The level of this record does not belong to the standard levels."]);
            }
        }
    }

    function pushHandler($levels,callable $handler,bool $flow=true){
        if($levels=="*"){
            $levels = [LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG];
        }elseif(is_string($levels)){
            $levels=[$levels];
        }
        
        array_push($this->handlers,[
            "handler" =>$handler,
            "level" => $levels,
            "flow" => $flow]);
    }

    protected function handle(array $record){
        foreach($this->handlers as $h){
            if(in_array($record["level"],$h["level"])){
                $h["handler"]($record);
                if(!$h["flow"]){
                    break;
                }
            }
        }
    }

    protected function replaceKeys(array $out){
        $msg=$out["content"];
        foreach($out["context"] as $key=>$val){
            $msg = str_replace("{".$key."}",$val,$msg);
        }

        return $msg;
    }
}

?>