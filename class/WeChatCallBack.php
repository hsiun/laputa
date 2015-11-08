<?php
require_once dirname(__FILE__) . '/../common/GlobalDefine.php';

class WeChatCallBack {
    protected $_postObject;
    protected $_fromUserName;
    protected $_toUserName;
    protected $_createTime;
    protected $_msgType;
    protected $_msgId;
    protected $_time;

    public function getToUserName() {
        return $this->_toUserName;
    }

    protected function makeHint($hint) {
        $resultStr = sprintf(HINT_TPL, $this->_fromUserName, $this->_toUserName,
            $this->_time, 'text', $hint);

       return $resultStr; 
    }

    public function init($postObj) {
        $this->_postObject = $postObj;
        if (false == $this->_postObject) {
            return false;
        }

        $this->_fromUserName = ( string) trim ($this->_postObject->FromUserName);
        $this->_toUserName = ( string) trim ($this->_postObject->ToUserName);
        $this->_createTime = ( int) trim ($this->_postObject->CreateTime);
        $this->_msgType = ( string) trim ($this->_postObject->MsgType);
        $this->_msgId = ( int) trim ($this->_postObject->MsgId);
        $this->_time = time();

        if (!($this->_fromUserName && $this->_toUserName && $this->_msgType)) {
            return false;
        }

        return true;

    }

    public function process() {
        return $this->makeHint(HINT_NOT_IMPLEMENT);
    }
}
?>
