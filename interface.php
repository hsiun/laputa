<?php
/**
  * wechat interface code
  */

//include other file
require_once dirname(__FILE__) . '/class/WechatValid.php';
require_once dirname(__FILE__) . '/class/WechatCallBackEchoServer.php';

$wechatValid = new WechatValid();
$wechatValid->valid();
$wechatObj = new WechatCallBackEchoServer();
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
if (!empty($postStr)) {
    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $ret = $wechatObj->init($postObj);
    $retStr = $wechatObj->process();
    echo $retStr;
}


?>
