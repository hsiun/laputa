<?php

class MiniLog {
    private $_instance;
    private $_path; //log path
    private $_pid;  
    private $_handleArr; //保存文件fd的数组

    /**
     * 构造函数
     **/
    function __construct($path) {
        $this->_path = $path;
        $this->_pid = getmypid();
    }

    private function __clone() {
        
    }

    /**
     * 单实例函数
     * 保证此函数只能同时被调用一次
     **/
    public static function instance($path = '/tmp/') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($path);
        }

        return self::$_instance;
    }

    /**
     * 获取文件描述符
     * 如果handleArr中没有，则打开文件创建新的文件描述符
     **/
    private function getHandle($filename) {
        if ($this->_handleArr[$filename]) {
            return $this->_handleArr[$filename];
        }

        date_default_timezone_set('PRC');
        $nowTime = time();
        $logSuffix = date('Ymd', $nowTime);
        $handle = fopen($this->_path.'/'.$filename.$logSuffix.".log",'a');
        $this->_handleArr[$filename] = $handle;
        return $handle;
    }

    /**
     * 将对应日志信息记录到对应文件中
     **/
    public function log($filename, $message) {
        $handle = $this->_handleArr[$filename];

        $nowTime = time();
        $logPreffix = date('Y-m-d H:i:s', $nowTime);

        fwrite($handle, "[$logPreffix] [$this->_pid] $message\n");
        return true;
    }

    /**
     * 析构函数
     * 关闭所有打开的文件描述符
     **/
    function __destruct() {
        foreach ($this->_handleArr as $key => $item) {
            if ($item) {
                fclose($item);
            }
        }
    }
}
?>

