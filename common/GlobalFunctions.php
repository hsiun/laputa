<?php
/**
 * 混杂的一些函数
 **/
include_once dirname(__FILE__).'/GlobalDefine.php';
include_once dirname(__FILE__).'/Minilog.php';

define("DEBUG", "DEBUG");
define("INFO", "INFO");
define("ERROR", "ERROR");

/**
 * 根据是否包含NO_判断日志是否开启
 **/
function isLogLevelOff($logLevel){
    $switchFile = ROOT_PATH . '/log/' . 'NO_' . $logLevel;
    if (file_exists($switchFile)) {
        return true;
    } else {
        return false;
    }
}

function laputa_log($confName, $logLevel, $errorCode, $logMessage="no error"){
    if (isLogLevelOff($logLevel)) {
        return;
    }

    $st = debug_backtrace();

    $function = '';
    $file = '';
    $line = '';

    /**
     * 从st中找出调用interface_log文件，函数和行数
     * 从调用interface_log在往后推一个函数
     **/
    foreach ($st as $item) {
        if ($file) {
            $function = $item['function'];
            break;
        }

        if ($item['function'] == 'interface_log') {
            $file = $item['file'];
            $line = $item['line'];
        }
    }
    
    $function = $function ? $function : 'main';

    //截取文件名，只保留最后一部分
    $file = explode("/", rtrim($file, '/'));
    $file = $file[count($file)-1];

    $preffix = "[$file] [$function] [$line] [$logLevel] [$errorCode] ";
    if ($logLevel == INFO || $logLevel == STAT) {
        $preffix = "[$logLevel]" ;
    }

    $logFileName = $confName . "_" . strtolower($logLevel);
    MiniLog::instance(ROOT_PATH . "/log/")->log($logFileName, $preffix . $logMessage);

    if (isLogLevelOff("DEBUG") || $logLevel == "DEBUG") {
        return ;
    } else {
        MiniLog::instance(ROOT_PATH . "/log/")->log($confName . "_" . "debug", 
            $preffix . $logMessage);
    }
}

function interface_log($logLevel, $errorCode, $logMessage = "no error msg") {
    laputa_log('interface', $logLevel, $errorCode, $logMessage);
}

?>
