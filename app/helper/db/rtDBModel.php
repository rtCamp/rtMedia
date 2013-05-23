<?php

/**
 * Description of bpm_media
 *
 * @author faishal
 */
class rtDBModel {

    public $table_name;
    public $where;
    public $order_by;

    function __construct($table_name) {
        $this->set_table_name($table_name);
    }

    public function set_table_name($table_name) {
        $this->table_name = $table_name;
    }
    /**
     * 
     * @global type $wpdb
     * @param type $name - Added get_by_<coulmname>
     * @param type $arguments
     * @return type result array
     */
    function __call($name, $arguments) {
        $array_name = split("_", $name);
        if ($arguments) {
            global $wpdb;
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $this->table_name . " WHERE {$array_name[2]} = ?", $arguments[0]));
        }
    }

    function insert($row) {
        global $wpdb;
        $wpdb->insert($this->table_name, $row);
        return $wpdb->insert_id;
    }

    function update($data, $where) {
        global $wpdb;
        $wpdb->update($this->table_name, $data, $where);
    }
    function get($columns){
        $sql = "select * from {$this->table_name} where 2=2 ";
        foreach($columns as $colname=>$colvalue){
            $sql .= " and {$colname} = '{$colvalue}'";
        }
        global $wpdb;
        return $wpdb->get_results($sql);
    }

}