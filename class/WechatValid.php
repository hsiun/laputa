<?php
require_once dirname(__FILE__) . '/../common/GlobalDefine.php';

class WechatValid{
	public function valid()
    {
        if (false == $this->checkSignature()) {
            exit (0);
        }

        $echoStr = $_GET["echostr"];
        if ($echoStr) {
            echo $echoStr;
            exit (0);
        } 

    }

	private function checkSignature()
	{
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
}

?>
