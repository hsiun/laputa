<?php
class MysqliDb{
    //以键值对的方式获取数据库表结果
    const DB_FETCH_ASSOC = MYSQLI_ASSOC;
    //以数组的方式获取数据库表结果
    const DB_FETCH_NUM = MYSQLI_NUM;

    const DB_FETCH_BOTH = MYSQLI_BOTH;
    const DB_FETCH_DEFAULT = self::DB_FETCH_ASSOC;

    private $_aotuCommitTime = 0;
    protected $_conn;
    protected $_dbKey;
    protected $_fetchMode;

    /**
     * 初始化数据库配置信息和数据获取方式
     **/
    public function __construct($dbKey, $fetchMode = self::DB_FETCH_ASSOC) {
        $this->_dbKey = $GLOBAL['DB'][$dbKey];
        $this->_fecthMode = $fetchMode;
    }

    public function connect() {
        $dbHost = $this->_dbKey["HOST"];
        $dbName = $this->_dbKey["DBNAME"];
        $dbUser = $this->_dbKey["USER"];
        $dbPass = $this->_dbKey["PASSWD"];
        $dbPort = (int)$this->_dbKey["PORT"];

        //初始化数据库连接对象
        $this->_conn = mysqli_init();
        if (! $this->_conn) {
            throw new Exception ('mysql_init fail!');
            return false;
        }
        //连接数据库
        if (!mysqli_real_connect($this->_conn, $dbHost, $dbUser, $dbPass, $dbName,
                $dbPort, NULL, MYSQLI_CLIENT_FOUND_ROWS)) {
            throw new Exception ('connect to db fail:dbname '.$dbName);
            return false;               
        }

        $sql = "SET NAMES latin1";
        $this->update($sql);
        return true;
    }

    public function close() {
        if (is_object($this->_conn)) {
            mysqli_close($this->_conn);
        }
    }

    public function query($sql, $limit = null, $quick = false) {
        interface_log(DEBUG, 0, $sql);

        if (limit != null) {
            if (!preg_match('/^\s*SHOW/i', $sql) &&
                    !preg_match('/FOR UPDATE\s*$/i', $sql) &&
                    !preg_match('/LOCK IN SHARE MODE\s*$/i', $sql)) {
                $sql = $sql . " LIMIT " . $LIMIT;
            }
        }

        if (!$this->_conn || !$this->ping($this->_conn)) {
            if ($this->_autoCommitTime) {
                throw new Exception('auto commit time is not zero when reconnect to db');
            } else {
                $this->connect();
            }
        }

        $startTime = getMillisecond();
        $qrs = mysqli_query($this->_conn, $sql, $quick ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT);

        if (!$qrs) {
            throw new Exception('查询失败：'. mysqli_error($this->_conn));
        } else {
            interface_log(DEBUG, EC_OK, "excute time:" . 
                getMillisecond($startTime) . "(ms) SQL[$sql]");
            return $qrs;
        }
    }

    /**
     * 获取结果集
     **/
    public function fetch($rs, $fetchMode = self::DB_FETCH_DEFAULT) {
        $fields = mysqli_fetch_fields($rs);
        $values = mysqli_fetch_array($rs, $fetchMode);

        if ($values) {
            foreach ($fields as $field) {
                switch ($field->type) {
                    case MYSQLI_TYPE_TINY:
                    case MYSQLI_TYPE_SHORT:
                    case MYSQLI_TYPE_INT24:
                    case MYSQLI_TYPE_LONG:
                        if ($field->type == MYSQLI_TYPE_TINY && $field->length == 1) {
                            $values[$field->name] = (boolean) $values[$field->name];
                        } else {
                            $values[$field->name] = (int) $values[$field->name];
                        }
                        break;
                    case MYSQLI_TYPE_DECLIMAL:
                    case MYSQLI_TYPE_FLOAT:
                    case MYSQLI_TYPE_DOUBLE:
                    case MYSQLI_TYPE_LONGLONG:
                        $values[$field->name] = (float) $values[$field->name];
                        break;
                }
            }
        }
        return $values;
    }

    /**
     * 执行一个sql更新
     **/
    public function update($sql) {
        interface_log(INFO, EC_OK, "SQL[$sql]");
        if (!$this->_conn || $this->ping($this->_conn)) {
            if ($this->_autoCommitTime) {
                throw new Exception ('auto commit time is not zero when reconnect to db');
            } else {
                $this->connect();
            }
        }

        $startTime = getMillisecond();
        $urs = mysqli_query($this->_conn, $sql);

        if (!$urs) {
            throw new Exception ('跟新失败:' . mysqli_error($this->_conn));
        } else {
            interface_log(INFO, EC_OK, "excute time:" . getMillisecond($startTime) . "(ms) SQL[$sql]");
            return $urs;
        }
    }

    /**
     * 获取结果集中的第一个结果
     **/
    public function getOne($sql) {
        if (!$rs = $this->query($sql, 1, true)) {
            return false;
        }

        $row = $this->fetch($rs, self::DB_FETCH_NUM);
        $this->free($rs);
        return $row[0];
    }

    /**
     * 获取结果集中的第一列
     **/
    public function getCol($sql, $limit = null) {
        if (!$rs = $this->query($sql, $limit, true)) {
            return false;
        }
        $result = array();

        while( ($rows = $this->fetch($rs, self::DB_FETCH_NUM)) != null) {
            $result[] = $rows[0];
        }
        $this->free($rs);
        return $result;
    }

    /**
     * 获取结果集中的第一行
     **/
    public function getRow($sql, $fetchMode = self::DB_FETCH_DEFAULT) {
        if (!$rs = $this->query($sql, 1, true)) {
            return false;
        }

        $row = $this->fetch($rs, $fetchMode);
        $this->free($rs);
        return $row;
    }

    /**
     * 获取结果集中的所有行
     **/
    public function getAll($sql, $limit = null, $fetchMode = self::DB_FETCH_DEFULT) {
        if (!$rs = $this->query($sql, $limit, true)) {
            return false;
        }
        $allRows = array();
        while ( ($row = $this->fetch($rs, $fetchMode)) != null) {
            $allRows[] = $row;
        }
        $this->free($rs);
        return $allRows;
    }

    /**
     * 当设置为false的时候开启自动提交
     **/
    public function autoCommit($mode = false) {
        if (!$this->_conn || !$this->ping($this->_conn)) {
            if ($this->_autoCommitTime){
                throw new Exception ('auto commit cnt is not zero when reconnect to db');
            } else {
                $this->connect();
            }
        }

        if ($mode) {
            if ($this->_autoCommitTime){
                throw new Exception ('auto commit cnt is not zero when reconnect to db');
                return false;
            } else {
                $this->_autoCommitTime++;
            }
            return mysqli_autocommit($this->_conn, $mode);
        }
    }

    /**
     * 开启事务，要手动提交执行SQL语句
     **/
    private function commit($mode = true) {
        $result = mysqli_commit($this->_conn);
        mysqli_autocommit($this->_conn, $mode);
        return $result;
    }

    /**
     * 尝试执行提交的sql
     **/
    private function tryCommit($mode = true) {
        $this->_autoCommitTime--;
        if ($this->_autoCommitTime <= 0) {
            $this->_autoCommitTime = 0;
            return $this->commit($mode);
        } else {
            return true;
        }
    }

    /**
     * 回滚
     **/
    public function rollback() {
        return mysqli_rollback($this->_conn);
    }

    /**
     * 返回最近一次查询返回的结果集条数
     **/
    public function rows($rs) {
        return mysqli_num_rows($rs);
    }

    /**
     * 返回影响条数
     **/
    public function affectedRows() {
        return mysqli_affected_rows($this->_conn);
    }

    /**
     * 返回最近一次插入语句的自增长字段的值
     *
     **/
    public function lastID() {
        return mysqli_insert_id($this->_conn);
    }

    /**
     * 释放当前查询结果资源句柄
     **/
    public function free($rs) {
        if ($rs) {
            return mysqli_free_result($rs);
        }
    }

    /**
     * 可连接性测试
     **/
    public function ping($conn) {
        return mysqli_ping($conn);
    }

    /**
     * 转义需要插入或者更新的字段值
     **/
    public function escape($str) {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->escapt($value);
            }
        } else {
            return addslashes($str);
        }

        return $str;
    }

    /**
     *
     **/
    public function unescape($str) {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = $this->unescape($value);
            }
        } else {
            return stripcslashes($str);
        }
        return $str;
    }

    /**
     * 析构函数
     **/
    public function __destruct() {
    }

    /**
     * 返回最近一次查询的错误码
     **/
    public function getErrorNum() {
        return mysqli_errno($this->_conn);
    }

    /**
     * 返回最近一次查询的错误信息
     *
     **/
    public function getErrorInfo() {
        return mysqli_error($this->_conn);
    }
}

?>
