<?php
/**
 * Description of rt_plugin_info
 *
 * @package    rtMedia
 *
 * @author udit
 */

if ( ! class_exists( 'rt_plugin_info' ) ) {
	/**
	 * Class rt_plugin_info
	 */
	class rt_plugin_info {

		/**
		 * Plugin path.
		 *
		 * @var $plugin_path
		 */
		public $plugin_path;

		/**
		 * 'Name' - Name of the plugin, must be unique.
		 *
		 * @var $name
		 */
		public $name;

		/**
		 * 'Title' - Title of the plugin and the link to the plugin's web site.
		 *
		 * @var $title
		 */
		public $title;

		/**
		 * 'Description' - Description of what the plugin does and/or notes from the author.
		 *
		 * @var $desctipriton
		 */
		public $desctipriton;

		/**
		 * 'Author' - The author's name
		 *
		 * @var $authro
		 */
		public $authro;

		/**
		 * 'AuthorURI' - The authors web site address.
		 *
		 * @var $authoruri
		 */
		public $authoruri;

		/**
		 * 'Version' - The plugin version number.
		 *
		 * @var $version
		 */
		public $version;

		/**
		 * 'PluginURI' - Plugin web site address.
		 *
		 * @var $pluginuri
		 */
		public $pluginuri;

		/**
		 * 'TextDomain' - Plugin's text domain for localization.
		 *
		 * @var $textdomain
		 */
		public $textdomain;

		/**
		 * 'DomainPath' - Plugin's relative directory path to .mo files.
		 *
		 * @var $domain_path
		 */
		public $domain_path;

		/**
		 * 'Network' - Boolean. Whether the plugin can only be activated network wide.
		 *
		 * @var $network
		 */
		public $network;

		/**
		 * Plugin data.
		 *
		 * @var $plugin_data
		 */
		public $plugin_data;

		/**
		 * __construct.
		 *
		 * @access public
		 *
		 * @param string $path Path.
		 */
		public function __construct( $path = null ) {
			$this->set_current_plugin_path( $path );
			$this->set_plugin_data();
		}

		/**
		 * Function get_plugin_data.
		 *
		 * @access public
		 *
		 * @return array
		 */
		public function get_plugin_data() {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			return @get_plugin_data( $this->plugin_path ); // @codingStandardsIgnoreLine
		}

		/**
		 * Function set_plugin_data.
		 *
		 * @access public
		 */
		public function set_plugin_data() {
			$this->plugin_data  = $this->get_plugin_data();
			$this->name         = $this->plugin_data['Name'];
			$this->title        = $this->plugin_data['Title'];
			$this->desctipriton = $this->plugin_data['Description'];
			$this->author       = $this->plugin_data['Author'];
			$this->authoruri    = $this->plugin_data['AuthorURI'];
			$this->version      = $this->plugin_data['Version'];
			$this->pluginuri    = $this->plugin_data['PluginURI'];
			$this->textdomain   = $this->plugin_data['TextDomain'];
			$this->domain_path  = $this->plugin_data['DomainPath'];
			$this->network      = $this->plugin_data['Network'];
		}

		/**
		 * Function set_current_plugin_path.
		 *
		 * @access public
		 *
		 * @param  string $path Path.
		 */
		public function set_current_plugin_path( $path ) {
			if ( null !== $path ) {
				$this->plugin_path = $path;
			} else {
				$this->plugin_path = realpath( plugin_dir_path( __FILE__ ) . '../../index.php' );
			}
		}
	}
}
