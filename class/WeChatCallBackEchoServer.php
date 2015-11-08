<?php
require_once dirname(__FILE__) . '/WeChatCallBack.php';
class WeChatCallBackEchoServer extends WeChatCallBack{
    
    public function process() {
        if ($this->_msgType != 'text') {
            return $this->makeHint("只支持文本!");
        }

        return $this->makeHint($this->_postObject->Content);
    }
}
?>
