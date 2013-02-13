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
			$this->set_total_count();
		}
	}

	function update_table() {
		global $wpdb;
		$wpdb->query(
				"ALTER TABLE {$wpdb->base_prefix}bp_album ADD COLUMN
					import_status TINYINT (1) NOT NULL DEFAULT 0"
				);
	}

	function create_album($album_name = 'BP Album'){
		parent::create_album($album_name);
	}

	function get_total_count() {
		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
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

	static function batch_import($offset=0){
		global $wpdb;
		$table = $wpdb->base_prefix . 'bp_album';
		$bp_album_data = $wpdb->get_results(
				"SELECT * FROM {$table} WHERE import_status='0'
					LIMIT 10 OFFSET {$offset}"
					);

		return $bp_album_data;

	}

	static function bpmedia_ajax_import_callback(){

		$offset = 0;//$_GET['offset'];

		$bp_album_data = BPMediaBPAlbumImporter::batch_import($offset);

		foreach ($bp_album_data as &$bp_album_item){
			$album_id=BPMediaBPAlbumImporter::create_album('',$bp_album_item->owner_id);
			BPMediaImporter::add_media(
					$album_id,
					$bp_album_item->title,
					$bp_album_item->description,
					$bp_album_item->pic_org_path,
					$bp_album_item->privacy,
					$bp_album_item->owner_id
				);
		}
	}

}

?>
