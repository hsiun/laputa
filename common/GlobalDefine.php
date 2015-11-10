<?php
#global define
define("EC_OK", "2000");
define("ROOT_PATH", dirname(dirname(__FILE__)));

define("TOKEN", "weixin");
define("TEXTTPL", "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[%s]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    <FuncFlag>0</FuncFlag>
</xml>")
?>
