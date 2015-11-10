<?php
require_once dirname(__FILE__) . '/WechatCallBack.php';
class WechatCallBackEchoServer extends WechatCallBack{
    
    public function process() {
        if ($this->_msgType != 'text') {
            return $this->makeHint("只支持文本信息!");
        }
        return $this->makeHint($this->_postObject->Content);
    }
}
?>
