<?php
/**
 * Class to store rtMedia plugin info.
 *
 * @package    rtMedia
 *
 * @author udit
 */

if ( ! class_exists( 'rt_plugin_info' ) ) {
	/**
	 * Class to store rtMedia plugin info.
	 */
	class rt_plugin_info { // phpcs:ignore PEAR.NamingConventions.ValidClassName, Generic.Classes.OpeningBraceSameLine.ContentAfterBrace

		/**
		 * Plugin path.
		 *
		 * @var string $plugin_path
		 */
		public $plugin_path;

		/**
		 * 'Name' - Name of the plugin, must be unique.
		 *
		 * @var string $name
		 */
		public $name;

		/**
		 * 'Title' - Title of the plugin and the link to the plugin's web site.
		 *
		 * @var string $title
		 */
		public $title;

		/**
		 * 'Description' - Description of what the plugin does and/or notes from the author.
		 *
		 * @var string $desctipriton
		 */
		public $desctipriton; // TODO : Correct spelling error.

		/**
		 * 'Author' - The author's name
		 *
		 * @var $author
		 */
		public $author;

		/**
		 * 'AuthorURI' - The authors web site address.
		 *
		 * @var string $authoruri
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
		 * @var string $pluginuri
		 */
		public $pluginuri;

		/**
		 * 'TextDomain' - Plugin's text domain for localization.
		 *
		 * @var string $textdomain
		 */
		public $textdomain;

		/**
		 * 'DomainPath' - Plugin's relative directory path to .mo files.
		 *
		 * @var string $domain_path
		 */
		public $domain_path;

		/**
		 * 'Network' - Whether the plugin can only be activated network wide.
		 *
		 * @var bool $network
		 */
		public $network;

		/**
		 * Plugin data.
		 *
		 * @var $plugin_data
		 */
		public $plugin_data;

		/**
		 * Constructor for rt_plugin_info.
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
		 * Function to get plugin data.
		 *
		 * @access public
		 *
		 * @return array
		 */
		public function get_plugin_data() {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			return @get_plugin_data( $this->plugin_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		/**
		 * Set plugin data.
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
		 * Set current plugin path.
		 *
		 * @access public
		 *
		 * @param string $path Path.
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
