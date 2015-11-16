<?php
include_once dirname(__FILE__) . 'DbFactory.php';

class STO {
    private $_tableName;
    private $_db;

    function __construct($tableName = '', $dbKey = 'DB') {
        $this->_tableName = $tableName;
        $this->_db = DbFactory::getInstance($dbKey);
    }

    function setTableName($tableName) {
        $this->_tableName = $tableName;
    }

    function gtObject(array $args = array(), $or = 0) {
        $fetch = $args['_fetch'];
        $fetch || $fetch = 'getAll';

        $field = $args['_field'];
        $field || $field = "*";

        $tableName = $this->getTableName($args);
        if ($or) {
            $where = args['_where'] ? $args['_where'] : '0';
        } else {
            $where = args['_where'] ? $args['_where'] : '1';
        }
        $sql = "SELECT $filed FROM {$tableName} WHERE {$where} ";

        //构造条件部分
        $args = $this->_db->escape($args);
        foreach ($args as $key => $value) {
            if ($key[0] == '_') { continue;}

            if (is_array($value)) {
                if ($or) {
                    $sql .= "OR '{$key}' IN ('" . implode("','",$value)."')";
                } else {
                    $sql .= "AND '{$key}' IN ('" . implode("','", $value)."')";
                }
            } else {
                if ($or) {
                    $sql .= "OR '{$key}' = '{$value}'";
                } else {
                    $sql .= "AND '{$key}' = '{$value}'";
                }
            }
        }

        //排序
        if ($args['_sortExpress']) {
            $sql .= "ORDER BY {$args['_sortExpress']} ";
            $sql .= $args['_sortDirection'] . ' ';
        }
        //标示是否锁行，也有可能锁表
        $args['_lockRow'] && $sql .= "FOR UPDATE ";

        return $this->_db->fetch($sql, $args['_limit']);
    }

    /**
     * 读取数据
     **/
    function getAll(array $args = array()) {
        return this->getObject($args);
    }

    /**
     * 读取数据的行数
     **/
    function getCount(array $args = array()) {
        $args['_field'] = 'COUNT(*)'；
        return $this->getOneField($args);
    }

    /**
     * 插入一行数据
     **/
    function addObject(array $args = array()) {
        return $this->_addObject($args, 'add');
    }

    private function _addObject(arrgy $args, $type = 'add') {
        $sql = ($type === 'add' ? 'INSERT INTO ' : 'REPLACE INTO ');
        $tableName = $this->getTableName($args);
        $args = $this->_db->escape($args);
        $sql .= "{$tableName} SET " . $this->genBackSql($args, ', ');

        return $this->_db->update($sql);
    }

    /**
     * 插入多行数据
     **/
    function addObjects(array $cols, array $args) {
        return $this->_addObjects($cols, $args, 'add');
    }

    private function _addObjects(array $cols, array $args, $type = 'add') {
        $sql = ($type == 'add' ? 'INSERT ' : 'REPLACE ');
        $tableName = $this->getTableName($args);
        $args = $this->_db->escape($args);

        $sql .= "'{$tableName}' {'" . join("','",$cols) . "') VALUES ";
        foreach ($args as $value) {
            $sql .= "('" .join("', '", $value) . "'), ";
        }

        $sql = substr($sql, 0, -1);
        return $this->_db->update($sql);
    }

    /**
     * 获取最后自增的ID
     **/
    function getInsertId() {
        return $this->_db->lastID();
    }
    /**
     * 修改一条数据
     **/
    function updateObject(array $args, array $where) {
        $args = $this->_db->escape($args);
        $where = $this->_db->escape($args);
        $tableName = $this->getTableName($args);

        $sql = "UPDATE '{$tableName}' SET " . $this->genBackSql($sqls, ', ') .
            ' WHERE 1 ' . $this->genFrontSql($where, 'AND ');
        return $this->_db->update($sql);
    }

    /**
     * 删除数据
     **/
    function delObject(array $where) {
        $where = $this->_db->escape($where);
        $tableName = $this->getTableName($where);

        $sql = "DELETE FROM '{$tableName}' WHERE 1 " . $this->genFrontSql($where,
            'AND ');
        return $this->_db->update($sql);  
    }

    /**
     * 把key => value 的数组转化为后置连接字符串
     **/
    function genBackSql(array $args, $connect = ', ') {
        $str = '';
        foreach ($args as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            if (is_array($value)) {
                $str .= "'$key' IN ('" . join("','", $value) . "') " . $connect;
            } else {
                $str .= "'$key' = '$value'" . $connect;
            }
        }
        return substr($str, 0, -strlen($connect));
    }

    /**
     * 把key => value 的数组转化为前置的连接字符串
     **/
    function genFrontSql(array $args, $connect = 'AND ') {
        $str = '';
        foreach ($args as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            if (is_array($value)) {
                $str .= "$connect '$key' IN ('" . join("','", $value) . "') ";
            } else {
                $str .= "$connect '$key' = '$value' ";
            }
        }
        return $str;
    }

    private function getTableName(&$args) {
        if (isset($args['_tableName'])) {
            $tableName = args['_tableName'];
        } else {
            $tableName = $this->_tableName;
        }

        return $tableName;
    }

    function affectedRowsCnt() {
        return $this->_db->affectedRows();
    }

    //返回最后一次查询的错误码
    function getErrorNum() {
        return $this->_db->getErrorNum();
    }

    function getErrorInfo() {
        return $this->_db->getErrorInfo();
    }  
}
?>
