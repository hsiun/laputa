<?php
require_once dirname(__FILE__) . '/../common/GlobalDefine.php';

class WechatCallBack{
    protected $_postObject;
    protected $_fromUserName;
    protected $_toUserName;
    protected $_createTime;
    protected $_msgType;
    protected $_msgId;
    protected $time;

    protected function makeHint($hint) {
        $resultStr = sprintf(TEXTTPL, $this->_fromUserName, $this->_toUserName, $this->_time,
            'text', $hint);
        return $resultStr;
    }

    /* 初始化成员变量 */
    public function init($postObj) {
        $this->_postObject = $postObj;
        if (false == $this->_postObject) {
            return false;
        }

        $this->_fromUserName = (string) trim ($this->_postObject->FromUserName);
        $this->_toUserName = (string) trim ($this->_postObject->ToUserName);
        $this->_msgType = (string) trim ($this->_postObject->MsgType);
        $this->_createTime = (int) trim ($this->_postObject->CreateTime);
        $this->_msgId = (int) trim ($this->_postObject->MsgId);
        $this->_time = time();

        if (! ($this->_fromUserName && $this->_toUserName && $this->_msgType)) {
            return false;
        }
        return true;
    }

    /* 实际调用的处理方法 */
    public function process() {
        return $this->makeHint("方法没有实现!!!");
    }
}
?>
