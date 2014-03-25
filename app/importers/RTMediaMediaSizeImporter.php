<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaMediaSizeImporter
 *
 * @author ritz
 */
class RTMediaMediaSizeImporter {

	function __construct() {
		add_filter( "rtmedia_filter_admin_pages_array", array( $this, "rtmedia_add_admin_page_array" ), 11, 1 );
		add_action( 'wp_ajax_rtmedia_media_size_import', array( $this, "rtmedia_media_size_import" ) );
		add_action( 'admin_init', array( $this, 'add_admin_notice' ) );
		add_action( 'admin_menu', array( $this, 'menu' ), 10 );
	}

	function menu() {
		add_submenu_page( 'rtmedia-setting', __( 'Media Size Import', 'rtmedia' ), __( 'Media Size Import', 'rtmedia' ), 'manage_options', 'rtmedia-migration-media-size-import', array( $this, 'init' ) );
	}

	function rtmedia_add_admin_page_array( $admin_pages ) {
		$admin_pages[ ] = "rtmedia_page_rtmedia-media-size-import";

		return $admin_pages;
	}

	function add_admin_notice() {
		$pending = $this->get_pending_count();
		if ( $pending < 0 ){
			$pending = 0;
		}
		rtmedia_update_site_option( "rtmedia_media_size_import_pending_count", $pending );
		if ( $pending > 0 ){
			if ( ! ( isset ( $_REQUEST[ "page" ] ) && $_REQUEST[ "page" ] == "rtmedia-migration-media-size-import" ) ){
				$site_option = get_site_option( "rtmedia_media_size_import_notice" );
				if ( ! $site_option || $site_option != "hide" ){
					rtmedia_update_site_option( "rtmedia_media_size_import_notice", "show" );
					add_action( 'admin_notices', array( &$this, 'add_rtmedia_media_size_import_notice' ) );
				}
			}
		}
	}

	function add_rtmedia_media_size_import_notice() {
		if ( current_user_can( 'manage_options' ) ){
			$this->create_notice( "<p><strong>rtMedia</strong>: <a href='" . admin_url( "admin.php?page=rtmedia-migration-media-size-import&force=true" ) . "'>Click Here</a> to import media sizes.  <a href='#' onclick='rtmedia_hide_media_size_import_notice()' style='float:right'>" . __( "Hide" ) . "</a> </p>" );
			?>
			<script type="text/javascript">
				function rtmedia_hide_media_size_import_notice() {
					var data = {action: 'rtmedia_hide_media_size_import_notice'};
					jQuery.post( ajaxurl, data, function ( response ) {
						response = response.trim();
						if ( response === "1" )
							jQuery( '.rtmedia-media-size-import-error' ).remove();
					} );
				}
			</script>
		<?php
		}
	}

	function create_notice( $message, $type = "error" ) {
		echo '<div class="' . $type . ' rtmedia-media-size-import-error">' . $message . '</div>';
	}

	function init() {
		$prog    = new rtProgress();
		$pending = $this->get_pending_count();
		$total   = $this->get_total_count();
		$done    = $total - $pending;
		?>
		<div class="wrap">
			<h2>rtMedia Media Size Import</h2>
			<?php
			echo '<span class="pending">' . rtmedia_migrate_formatseconds( $total - $done ) . '</span><br />';
			echo '<span class="finished">' . $done . '</span>/<span class="total">' . $total . '</span>';
			echo '<img src="images/loading.gif" alt="syncing" id="rtMediaSyncing" style="display:none" />';

			$temp = $prog->progress( $done, $total );
			$prog->progress_ui( $temp, true );
			?>
			<script type="text/javascript">
				var false_count = 0;
				var curr_done = 0;
				jQuery( document ).ready( function ( e ) {
					jQuery( "#toplevel_page_rtmedia-settings" ).addClass( "wp-has-current-submenu" )
					jQuery( "#toplevel_page_rtmedia-settings" ).removeClass( "wp-not-current-submenu" )
					jQuery( "#toplevel_page_rtmedia-settings" ).addClass( "wp-menu-open" )
					jQuery( "#toplevel_page_rtmedia-settings>a" ).addClass( "wp-menu-open" )
					jQuery( "#toplevel_page_rtmedia-settings>a" ).addClass( "wp-has-current-submenu" )
					if ( db_total < 1 )
						jQuery( "#submit" ).attr( 'disabled', "disabled" );
				} )
				function db_start_migration( db_done, db_total ) {

					if ( db_done < db_total ) {
						jQuery( "#rtMediaSyncing" ).show();
						jQuery.ajax( {
							url: rtmedia_admin_ajax,
							type: 'post',
							data: {
								"action": "rtmedia_media_size_import",
								"done": db_done
							},
							success: function ( sdata ) {

								try {
									data = JSON.parse( sdata );
								} catch ( e ) {
									jQuery( "#submit" ).attr( 'disabled', "" );
								}
								if ( data.status ) {
									done = parseInt( data.done );
									total = parseInt( data.total );
									var progw = Math.ceil( (done / total) * 100 );
									if ( progw > 100 ) {
										progw = 100;
									}
									;
									jQuery( '#rtprogressbar>div' ).css( 'width', progw + '%' );
									jQuery( 'span.finished' ).html( done );
									jQuery( 'span.total' ).html( total );
									jQuery( 'span.pending' ).html( data.pending );
									if( curr_done == done ) {
										false_count++;
									} else {
										false_count = 0;
									}
									curr_done = done;
									if( false_count > 5 ) {
										rtm_show_file_error( done, total );
									} else {
										db_start_migration( done, total );
									}
								} else {
									alert( "Migration completed." );
									jQuery( "#rtMediaSyncing" ).hide();
								}
							},
							error: function () {
								alert( "Error During Migration, Please Refresh Page then try again" );
								jQuery( "#submit" ).removeAttr( 'disabled' );
							}
						} );
					} else {
						alert( "Migration completed." );
						jQuery( "#rtMediaSyncing" ).hide();
					}
				}
				function rtm_show_file_error( done, total ) {
					jQuery( 'span.pending' ).html( "File size of " + ( total - done ) + " file(s) are not imported. Don't worry, you can end importing media size now :)" );
					jQuery( "#rtMediaSyncing" ).hide();
				}
				var db_done = <?php echo $done; ?>;
				var db_total = <?php echo $total; ?>;
				jQuery( document ).on( 'click', '#submit', function ( e ) {
					e.preventDefault();
					db_start_migration( db_done, db_total );
					jQuery( this ).attr( 'disabled', 'disabled' );
				} );
			</script>
			<hr/>
			<?php if ( ! ( isset ( $rtmedia_error ) && $rtmedia_error === true ) ){ ?>
				<input type="button" id="submit" value="start" class="button button-primary"/>
			<?php } ?>
		</div>
	<?php
	}

	function get_pending_count() {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$query_pending = "SELECT COUNT(*) as pending from {$rtmedia_model->table_name} where file_size IS NULL AND media_type in ('photo','video','document','music','other')";
		$pending_count = $wpdb->get_results( $query_pending );
		if ( $pending_count && sizeof( $pending_count ) > 0 ){
			return $pending_count[ 0 ]->pending;
		}

		return 0;
	}

	function get_total_count() {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$query_total   = "SELECT COUNT(*) as total from {$rtmedia_model->table_name} where media_type in ('photo','video','document','music','other') ";
		$total_count   = $wpdb->get_results( $query_total );
		if ( $total_count && sizeof( $total_count ) > 0 ){
			return $total_count[ 0 ]->total;
		}

		return 0;
	}

	function rtmedia_media_size_import( $lastid = 0, $limit = 1 ) {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$get_media_sql = "SELECT * from {$rtmedia_model->table_name} where file_size is NULL and media_type in ('photo','video','document','music','other') order by id limit " . $limit;
		if ( $lastid ){
			$get_media_sql = "SELECT * from {$rtmedia_model->table_name} where id > '" . $lastid . "' AND file_size is NULL and media_type in ('photo','video','document','music','other') order by id limit " . $limit;
		}
		$result = $wpdb->get_results( $get_media_sql );
		if ( $result && sizeof( $result ) > 0 ){
			$this->migrate_single_media( $result[ 0 ] );
		}
		$this->return_migration();
	}

	function migrate_single_media( $result ) {
		global $wpdb;
		$rtmedia_model = new RTMediaModel();
		$attached_file = get_attached_file( $result->media_id );
		if ( ! file_exists( $attached_file ) ){
			$this->rtmedia_media_size_import( $result->id );
		}
		$file_size = filesize( $attached_file );
		$post      = get_post( $result->media_id );
		$post_date = $post->post_date;
		$rtmedia_model->update( array( 'upload_date' => $post_date, 'file_size' => $file_size ), array( 'id' => $result->id ) );
	}

	function return_migration() {
		$total   = $this->get_total_count();
		$pending = $this->get_pending_count();
		$done    = $total - $pending;
		if ( $pending < 0 ){
			$pending = 0;
			$done    = $total;
		}
		if ( $done > $total ){
			$done = $total;
		}
		rtmedia_update_site_option( 'rtmedia_media_size_import_pending_count', $pending );
		$pending_time = rtmedia_migrate_formatseconds( $pending ). " (estimated)";
		echo json_encode( array( "status" => true, "done" => $done, "total" => $total, "pending" => $pending_time ) );
		die();
	}
}
