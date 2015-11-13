<?php
/**
  * wechat interface code
  */
ini_set('display_errors', '1');
//include other file
require_once dirname(__FILE__) . '/class/WechatValid.php';
require_once dirname(__FILE__) . '/class/WechatCallBackEchoServer.php';
require_once dirname(__FILE__) . '/common/GlobalFunctions.php';

$wechatValid = new WechatValid();
$wechatValid->valid();
$wechatObj = new WechatCallBackEchoServer();
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
interface_log(INFO, EC_OK, "******************************************************");
interface_log(INFO, EC_OK, "*************** interface requrest start *************");
interface_log(INFO, EC_OK, 'request:' . $postStr);
interface_log(INFO, EC_OK, 'get:' . var_export($_GET, true));

if (empty($postStr)) {
    interface_log(ERROR, EC_OK, "post data error!");
    exit (0);
}

$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
$ret = $wechatObj->init($postObj);
if (!$ret) {
    interface_log(ERROR, EC_OK, "init post object error!");
    exit (0);
}
$retStr = $wechatObj->process();
interface_log(INFO, EC_OK, "response:" . $retStr);
echo $retStr;

interface_log(INFO, EC_OK, "*************** interface request end ****************");
interface_log(INFO, EC_OK, "******************************************************");
interface_log(INFO, EC_OK, "");
?>
