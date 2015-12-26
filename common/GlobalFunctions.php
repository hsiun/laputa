<?php
/**
 * 混杂的一些函数
 **/
include_once dirname(__FILE__).'/GlobalDefine.php';
include_once dirname(__FILE__).'/MiniLog.php';

define("DEBUG", "DEBUG");
define("INFO", "INFO");
define("ERROR", "ERROR");

/**
 * 通过curl实现get请求
 **/
function doCurlGetRequest($url, $data, $timeout = 5) {
    if($url == "" || $timeout <= 0) {
        return false;
    }
    $url = $url . '?' . http_build_query($data);

    $conn = curl_init((string)$url);
    curl_setopt($conn, CURLOPT_HEADER, false);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($conn, CURLOPT_TIMEOUT, (int)$timeout);

    return curl_exec($conn);
}

/**
 * 通过curl实现post请求
 **/
function doCurlPostRequest($url, $post_data, $timeout = 5) {
    if ($url == "" || $post_data == "" || $timeout <= 0) {
        return false;
    }

    $conn = curl_init((string)$url);
    $conn = curl_setopt($conn, CURLOPT_HEADER, false);
    $conn = curl_setopt($conn, CURLOPT_POSTFIELDS, $post_data);
    $conn = curl_setopt($conn, CURLOPT_POST, true);
    $conn = curl_setopt($conn, CURLOPT_REQURENTRANSFER, true);
    $conn = curl_setopt($conn, CURLOPT_TIMEOUT,(int)$timeout);

    return curl_exec($conn);
}

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

function getIp() {
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }

    return $realip;
}

?>
