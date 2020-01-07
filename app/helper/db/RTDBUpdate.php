<?php
/**
 * Class to update DB for rtMedia.
 * Required : rt_plugin_info.php
 *
 * @package    rtMedia
 *
 * @author udit
 * version 1.1
 */

if ( ! class_exists( 'RTDBUpdate' ) ) {
	/**
	 * Class to update DB for rtMedia.
	 */
	class RTDBUpdate {

		/**
		 * DB version.
		 *
		 * @var $db_version
		 */
		public $db_version;

		/**
		 * Install DB Version.
		 *
		 * @var $install_db_version
		 */
		public $install_db_version;

		/**
		 * Schema path.
		 *
		 * @var string $schema_path
		 */
		public $schema_path;

		/**
		 * Plugin path.
		 *
		 * @var string $plugin_path
		 */
		public $plugin_path;

		/**
		 * DB version option.
		 *
		 * @var string $db_version_option_name
		 */
		public $db_version_option_name;

		/**
		 * Plugin info.
		 *
		 * @var object $rt_plugin_info
		 */
		public $rt_plugin_info;

		/**
		 * Single table.
		 *
		 * @var $mu_single_table
		 */
		public $mu_single_table;

		/**
		 * RTDBUpdate constructor.
		 * Set db current and installed version and also plugin info in rt_plugin_info variable.
		 *
		 * @param string|bool $current_version Optional if not defined then will use plugin version.
		 * @param bool|string $plugin_path Plugin path.
		 * @param bool|string $schema_path Schema path.
		 * @param bool|string $mu_single_table mu single table.
		 */
		public function __construct( $current_version = false, $plugin_path = false, $schema_path = false, $mu_single_table = false ) {

			if ( false !== $schema_path ) {
				$this->schema_path = $schema_path;
			} else {
				$this->schema_path = realpath( dirname( __FILE__ ) . $this->schema_path );
			}

			if ( false !== $plugin_path ) {
				$this->plugin_path = $plugin_path;
			} else {
				$this->plugin_path = realpath( dirname( __FILE__ ) . $this->plugin_path );
			}

			$this->mu_single_table = $mu_single_table;

			$this->rt_plugin_info = new rt_plugin_info( $this->plugin_path );
			if ( false === $current_version ) {
				$current_version = $this->rt_plugin_info->version;
			}
			$this->db_version             = $current_version;
			$this->db_version_option_name = $this->get_db_version_option_name();
			$this->install_db_version     = $this->get_install_db_version();
		}

		/**
		 * Create table using dbDelta.
		 *
		 * @access public
		 *
		 * @param  string $sql SQL query string.
		 *
		 * @return void
		 */
		public function create_table( $sql ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		/**
		 * Get db_version option name.
		 *
		 * @access public
		 *
		 * @return string
		 */
		public function get_db_version_option_name() {
			return strtoupper( 'RT_' . str_replace( '-', '_', sanitize_title( $this->rt_plugin_info->name ) ) . '_DB_VERSIONS' );
		}

		/**
		 * Get installed db_version.
		 *
		 * @access public
		 *
		 * @return string
		 */
		public function get_install_db_version() {
			return ( $this->mu_single_table ) ? get_site_option( $this->db_version_option_name, '0.0' ) : get_option( $this->db_version_option_name, '0.0' );
		}

		/**
		 * Check upgrade by comparing version db_version.
		 *
		 * @access public
		 *
		 * @return bool
		 */
		public function check_upgrade() {
			return version_compare( $this->db_version, $this->install_db_version, '>' );
		}

		/**
		 * Do upgrade by comparing version db_version.
		 * If db_version > install_db_version, then perform.
		 *
		 * @access public
		 *
		 * @return void
		 */
		public function do_upgrade() {
			global $wpdb;

			if ( version_compare( $this->db_version, $this->install_db_version, '>' ) ) {

				$path = $this->schema_path;

				if ( $handle = opendir( $path ) ) { // phpcs:ignore

					while ( false !== ( $entry = readdir( $handle ) ) ) { // phpcs:ignore

						if ( '.' !== $entry && '..' !== $entry ) {

							if ( false !== strpos( $entry, '.schema' ) && file_exists( $path . '/' . $entry ) ) {
								if ( is_multisite() ) {
									$table_name = str_replace( '.schema', '', strtolower( $entry ) );
									$check_res  = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', '%rt_' . $table_name ), ARRAY_N );
									if ( $check_res && count( $check_res ) > 0 && is_array( $check_res ) && isset( $check_res[0][0] ) ) {
										$tb_name    = $check_res[0][0];
										$table_name = ( ( $this->mu_single_table ) ? $wpdb->base_prefix : $wpdb->prefix ) . 'rt_' . $table_name;
										if ( $tb_name !== $table_name ) {
											$alter_sql = 'ALTER TABLE ' . $tb_name . ' RENAME TO ' . $table_name;
											$wpdb->query( $alter_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
										}
									}
								}
								$this->create_table( $this->genrate_sql( $entry, file_get_contents( $path . '/' . $entry ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
							}
						}
					}
					closedir( $handle );
				}
				if ( $this->mu_single_table ) {
					update_site_option( $this->db_version_option_name, $this->db_version );
				} else {
					update_option( $this->db_version_option_name, $this->db_version );
				}
				do_action( 'rt_db_upgrade' );
			}
		}

		/**
		 * Check if given table exists.
		 *
		 * @access static
		 *
		 * @param  string $table Table name.
		 *
		 * @return bool
		 */
		public static function table_exists( $table ) {
			global $wpdb;

			if ( 1 === intval( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Generate sql query.
		 *
		 * @access public
		 *
		 * @param  string $file_name File name.
		 * @param  string $file_content File content.
		 *
		 * @return string sql query
		 */
		public function genrate_sql( $file_name, $file_content ) {
			// TODO: change Function name.
			return sprintf( $file_content, $this->genrate_table_name( $file_name ) );
		}

		/**
		 * Generate table name according to filename.
		 *
		 * @access public
		 *
		 * @param  string $file_name File name.
		 *
		 * @return string
		 */
		public function genrate_table_name( $file_name ) {
			global $wpdb;

			return ( ( $this->mu_single_table ) ? $wpdb->base_prefix : $wpdb->prefix ) . 'rt_' . str_replace( '.schema', '', strtolower( $file_name ) );
		}
	}

}
