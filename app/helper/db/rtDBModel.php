<?php

/**
 * Description of rtDBModel
 *
 * @author faishal
 */
class rtDBModel {

    public $table_name;
    public $per_page;

    /**
     *
     * @param string $table_name Table name for model
     * @param boolean $withprefix Set true if $tablename is with prefix otherwise it will prepend wordpress prefix with "rt_"
     */
    function __construct($table_name, $withprefix = false, $per_page = 10) {
        $this->set_table_name($table_name, $withprefix);
        $this->set_per_page($per_page);
    }

    /**
     *
     * @global type $wpdb
     * @param string $table_name
     * @param type $withprefix
     */
    public function set_table_name($table_name, $withprefix = false) {
        global $wpdb;
        if (!$withprefix) {
            $table_name = $wpdb->prefix . "rt_" . $table_name;
        }
        $this->table_name = $table_name;
    }

    /**
     *
     * @param type $per_page
     */
    public function set_per_page($per_page) {
        $this->per_page = $per_page;
    }

    /**
     *
     * @global type $wpdb
     * @param type $name - Added get_by_<coulmname>(value,pagging=true,page_no=1)
     * @param type $arguments
     * @return type result array
     */
    function __call($name, $arguments) {
        $column_name = str_replace("get_by_", "", strtolower($name));
        $paging = false;
        $page = 1;
        if ($arguments && !empty($arguments)) {
            if (!isset($arguments[1])) {
                $paging = true;
            } else {
                $paging = $arguments[1];
            }

            if (!isset($arguments[2])) {
                $page = 1;
            } else {
                $page = $arguments[2];
            }

            $this->per_page = apply_filters("rt_db_model_per_page", $this->per_page, $this->table_name);
            $return_array = Array();
            $return_array["result"] = false;
            global $wpdb;
            $return_array["total"] = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $this->table_name . " WHERE {$column_name} = %s", $arguments[0])));
            if ($return_array["total"] > 0) {
                $other = "";
                if ($paging) {
                    $offset = ($page - 1) * $this->per_page;
                    if ($offset < $return_array["total"]) {
                        $other = " LIMIT " . $offset . "," . $this->per_page;
                    }
                }
                echo $wpdb->prepare("SELECT * FROM " . $this->table_name . " WHERE {$column_name} = %s {$other}", $arguments[0]);
                $return_array["result"] = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $this->table_name . " WHERE {$column_name} = %s {$other}", $arguments[0]));
            }
            return $return_array;
        } else {
            return false;
        }
    }

    /**
     *
     * @global type $wpdb
     * @param type $row
     * @return type
     */
    function insert($row) {
        global $wpdb;
        $wpdb->insert($this->table_name, $row);
        return $wpdb->insert_id;
    }

    /**
     *
     * @global type $wpdb
     * @param type $data
     * @param type $where
     */
    function update($data, $where) {
        global $wpdb;
        $wpdb->update($this->table_name, $data, $where);
    }

    /**
     *
     * @global type $wpdb
     * @param type $columns
     * @return type
     */
    function get($columns) {
        $sql = "SELECT * FROM {$this->table_name} WHERE 2=2 ";
        foreach ($columns as $colname => $colvalue) {
            $sql .= " AND {$colname} = '{$colvalue}'";
        }
        global $wpdb;
        return $wpdb->get_results($sql);
    }

    function delete($media_id) {
        global $wpdb;
        $wpdb->delete($this->table_name, array('media_id' => $media_id));
    }

}
