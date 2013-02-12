<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMediaBPAlbumImporter
 *
 * @author saurabh
 */
class BPMediaBPAlbumImporter extends BPMediaImporter {

	function __construct() {
		parent::__construct();
		$this->active = $this->_active( 'bp-album/loader.php' );

		if($this->active!=-1){
			$this->update_table();
			echo 'updated';
			$this->set_total_count();
		}
	}

	function update_table() {
		global $wpdb;

		$wpdb->query('ALTER TABLE {$wpdb->base_prefix}bp_album ADD COLUMN import_status TINYINT (1)');
	}

	function get_total_count() {
		global $wpdb;
		$table = $wpdb->prefix . 'bp_album';
		if ( $this->table_exists( $table ) && $this->active != -1 ) {
			return $wpdb->query( "SELECT * FROM {$table}" );
		}
	}

	function set_total_count(){
		$total_count = $this->get_total_count();
		update_site_option('bp_album_import_total_count',$total_count);
		$this->import_steps = ceil( floatval( $total_count ) / 10 );
		update_site_option('bp_album_import_total_count',$total_count);

	}

	static function batch_import(){
		global $wpdb;
		$table = $wpdb->prefix . 'bp_album';
		$bp_album_data = $wpdb->get_results('SELECT * as id FROM {$table} WHERE import_status=0 LIMIT 10');

		print_r($bp_album_data);

		//foreach ()

	}

}

?>
