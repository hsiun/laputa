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




}
?>
