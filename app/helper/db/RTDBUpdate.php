<?php

/**
 * Description of RTDBUpdate
 * Required : rt_plugin_info.php
 * @author faishal
 */
class RTDBUpdate {
    /**
     *
     * @var type String
     */
    public $db_version;
    public $install_db_version;
    public $schema_path = '/../../schema/';
    public $db_version_option_name;
    public $rt_plugin_info;

    /**
     * Set db current and installed version and also plugin info in rt_plugin_info variable.
     *
     * @param type string $current_version Optional if not defined then will use plugin version
     */
    public function __construct($current_version = false) {
        $this->rt_plugin_info = new rt_plugin_info(RTMEDIA_PATH.'index.php');
        if ($current_version == false) {
           $current_version = $this->rt_plugin_info->version;
        }
        $this->db_version = $current_version;
        $this->db_version_option_name = $this->get_db_version_option_name();
        $this->install_db_version = $this->get_install_db_version();
    }

    public function create_table($sql) {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public function get_db_version_option_name() {
        return strtoupper("RT_" . str_replace("-", "_", sanitize_title($this->rt_plugin_info->name)) . "_DB_VERSIONS");
    }

    public function get_install_db_version() {
        return get_site_option($this->db_version_option_name, "0.0");
    }

    public function check_upgrade() {
        return version_compare($this->db_version, $this->install_db_version, '>');
    }

    public function do_upgrade() {
        if (version_compare($this->db_version, $this->install_db_version, '>')) {
            do_action("rt_db_upgrade");
            $path = realpath(dirname(__FILE__) . $this->schema_path);
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        if (strpos($entry, ".schema") !== false && file_exists($path . "/" . $entry)) {
                            $this->create_table($this->genrate_sql($entry, file_get_contents($path . "/" . $entry)));
                        }
                    }
                }
                closedir($handle);
            }
            update_site_option($this->db_version_option_name, $this->db_version);
        }
    }
    static function table_exists($table) {
        global $wpdb;

        if ($wpdb->query("SHOW TABLES LIKE '" . $table . "'") == 1) {
            return true;
        }

        return false;
    }

    public function genrate_sql($file_name, $file_content) {
        return sprintf($file_content, $this->genrate_table_name($file_name));
    }

    public function genrate_table_name($file_name) {
        global $wpdb;
        return $wpdb->prefix . "rt_" . str_replace(".schema", "", strtolower($file_name));
    }

}
