<?php
/**
  * wechat interface code
  */
require_once dirname(__FILE__) . '/common/GlobalFunctions.php';
//define your token
define("TOKEN", "weixin");

function checkSignature(){
    // you must define TOKEN by yourself
    if (!defined("TOKEN")) {
        throw new Exception('TOKEN is not defined!');
    }
        
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];
        		
    $token = TOKEN;
    $tmpArr = array($token, $timestamp, $nonce);
    // use SORT_STRING rule
	sort($tmpArr, SORT_STRING);
	$tmpStr = implode( $tmpArr );
	$tmpStr = sha1( $tmpStr );
		
	if( $tmpStr == $signature ){
		return true;
	}else{
		return false;
	}
}

if (checkSignature()) {
    if ($_GET["echostr"]) {
        echo $_GET["echostr"];
        exit(0);
    }
}

function getWeChatObj() {
    require_once dirname(__FILE__) . '/class/WeChatCallBackEchoServer.php';
    return new WeChatCallBackEchoServer();
}

$postStr = file_get_contents("php://input");

if (empty($postStr)) {
    interface_log(ERROR, EC_OK, "error input!");
    exit(0);
}

$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$wechatObj = getWeChatObj();
$ret = $wechatObj->init($postObj);
if (!$ret) {
    interface_log(ERROR, EC_OK, "error input!");
    exit(0);
}

$retStr = $wechatObj->process();
interface_log(INFO, EC_OK, "response:" . $retStr);
echo $retStr;

?>
