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

    public function get_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $this->table_name . " WHERE id=?", $id));
    }

}