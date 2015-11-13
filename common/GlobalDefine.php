<?php
#global define
define("EC_OK", "2000");
define("EC_OTHER", "5000");

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

$GLOBALS['DB'] = array(
    'DB' => array(
        'HOST' => 'localhost',
        'DBNAME' => 'db',
        'USER' => 'root',
        'PASSWD' => 'root',
        'PORT' => 3306
    ),
    'TEST' => array(
        'HOST' => 'localhost',
        'DBNAME' => 'test',
        'USER' => 'root',
        'PASSWD' => 'root',
        'PORT' => 3306
    )
)
?>
