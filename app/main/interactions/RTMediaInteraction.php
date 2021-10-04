<?php
/**
 * Sets up the routes and the context
 *
 * @author saurabh
 *
 * @package rtMedia
 */

/**
 * Class to Set up the routes and the context
 */
class RTMediaInteraction {

	/**
	 * Context.
	 *
	 * @var string $context
	 */
	public $context;

	/**
	 * RtMedia slug.
	 *
	 * @var array $slugs
	 */
	private $slugs = array(
		RTMEDIA_MEDIA_SLUG,
		'upload',
	);

	/**
	 * RtMedia routes.
	 *
	 * @var array $routes
	 */
	public $routes;

	/**
	 * Initialise the interaction
	 */
	public function __construct() {

		// hook into the WordPress Rewrite Endpoint API.
		add_action( 'init', array( $this, 'rewrite_rules' ) );
		add_action( 'init', array( $this, 'rewrite_tags' ) );
		add_action( 'init', array( $this, 'endpoint' ) );
		add_action( 'init', array( $this, 'flush_rules' ) );

		// set up interaction and routes.
		add_action( 'template_redirect', array( $this, 'init' ), 99 );

		add_filter( 'wp_title', array( $this, 'set_title' ), 99999, 2 );
		add_filter( 'wpseo_opengraph_title', array( $this, 'set_title' ), 9999, 1 );
		add_filter( 'wpseo_frontend_presenters', array( $this, 'rtmedia_wpseo_og_image' ), 999, 1 );
		add_filter( 'wpseo_opengraph_url', array( $this, 'rtmedia_wpseo_og_url' ), 999, 1 );
		add_filter( 'wpseo_opengraph_desc', array( $this, 'rtmedia_wpseo_og_desc' ), 999, 1 );
	}

	/**
	 * Flush rewrite rules.
	 */
	public function flush_rules() {

		$rtmedia_version = rtmedia_get_site_option( 'rtmedia_flush_rules_plugin_version' );

		if ( ! $rtmedia_version ) {
			$rtmedia_version = '0';
		}

		$plugin_data = get_plugin_data( RTMEDIA_PATH . 'index.php' );
		$new_version = '0';

		if ( isset( $plugin_data ) && isset( $plugin_data['Version'] ) ) {
			$new_version = $plugin_data['Version'];
		}

		if ( version_compare( $new_version, $rtmedia_version, '>' ) ) {
			flush_rewrite_rules( false );
			rtmedia_update_site_option( 'rtmedia_flush_rules_plugin_version', $new_version );
		}
	}

	/**
	 * Init function for class.
	 */
	public function init() {
		// set up the routes array.
		$this->route_slugs();
		$this->set_context();
		$this->set_routers();
		$this->set_query();
	}

	/**
	 * Set up the route slugs' array
	 */
	public function route_slugs() {

		// filter to add custom slugs for routes.
		$this->slugs = apply_filters( 'rtmedia_default_routes', $this->slugs );
	}

	/**
	 * Add rewrite rules with RTMedia slugs.
	 */
	public static function rewrite_rules() {
		add_rewrite_rule( '^/' . RTMEDIA_MEDIA_SLUG . '/([0-9]*)/([^/]*)/?', 'index.php?media_id=$matches[1]&action=$matches[2]', 'bottom' );
		add_rewrite_rule( '^/' . RTMEDIA_MEDIA_SLUG . '/([0-9]*)/pg/([0-9]*)/?', 'index.php?media_id=$matches[1]&pg=$matches[2]', 'bottom' );
		add_rewrite_rule( '^/' . RTMEDIA_MEDIA_SLUG . '/nonce/([^/]*)/?', 'index.php?nonce_type=$matches[1]', 'bottom' );
		add_rewrite_rule( '^/' . RTMEDIA_MEDIA_SLUG . '/([A-Za-z]*)/pg/([0-9]*)/?', 'index.php?media_type=$matches[1]&pg=$matches[2]', 'bottom' );
		add_rewrite_rule( '^/' . RTMEDIA_MEDIA_SLUG . '/pg/([0-9]*)/?', 'index.php?pg=$matches[1]', 'bottom' );
		do_action( 'rtmedia_add_rewrite_rules' );
	}

	/**
	 * Add Rewrite tags.
	 */
	public static function rewrite_tags() {
		add_rewrite_tag( '%media_id%', '([0-9]*)' );
		add_rewrite_tag( '%action%', '([^/]*)' );
		add_rewrite_tag( '%nonce_type%', '([^/]*)' );
		add_rewrite_tag( '%media_type%', '([A-Za-z]*)' );
		add_rewrite_tag( '%pg%', '([0-9]*)' );
		do_action( 'rtmedia_add_rewrite_tags' );
	}

	/**
	 * Just adds the current /{slug}/ to the rewite endpoint
	 */
	public function endpoint() {

		foreach ( $this->slugs as $slug ) {
			add_rewrite_endpoint( $slug, EP_ALL );
		}
	}

	/**
	 * Set routers.
	 */
	public function set_routers() {

		// set up routes for each slug.
		foreach ( $this->slugs as $slug ) {
			$this->routes[ $slug ] = new RTMediaRouter( $slug );
		}
	}

	/**
	 * Sets up the default context
	 */
	public function default_context() {

		return new RTMediaContext();
	}

	/**
	 * Set context.
	 *
	 * @param array|bool $context the context array.
	 *
	 * @todo the method should also allow objects
	 */
	public function set_context( $context = false ) {

		// take the context supplied and replace the context.
		if ( is_array( $context ) && isset( $context['type'] ) && isset( $context['id'] ) ) {

			$context_object->type = $context['type'];
			$context_object->id   = $context['id'];

			// if there is no context array supplied, set the default context.
		} else {

			$context_object = $this->default_context();
		}

		// set the context property.
		$this->context = $context_object;
	}

	/**
	 * Reset the context to the default context after temporarily setting it,
	 * Say, for an upload
	 */
	public function rewind_context() {

		$this->context = $this->default_context();
	}

	/**
	 * Function to set global rtMedia_query.
	 */
	public function set_query() {

		global $rtmedia_query;

		if ( $this->routes[ RTMEDIA_MEDIA_SLUG ]->is_template() ) {
			$args          = array(
				'context'    => $this->context->type,
				'context_id' => $this->context->id,
			);
			$args          = apply_filters( 'rtmedia_query_filter', $args );
			$rtmedia_query = new RTMediaQuery( $args );
		}
	}

	/**
	 * Function to filter title.
	 *
	 * @param string $default Default title.
	 * @param string $sep Separator.
	 *
	 * @return mixed
	 */
	public function set_title( $default, $sep = '|' ) {
		global $wp_query;
		global $rtmedia_seo_title;

		if ( ! array_key_exists( RTMEDIA_MEDIA_SLUG, $wp_query->query_vars ) ) {
			return $default;
		}

		global $bp, $rtmedia_query;
		$title   = '';
		$old_sep = ' ' . $sep . ' ';
		$sep     = '';

		if ( isset( $rtmedia_query->query ) && isset( $rtmedia_query->query['media_type'] ) ) {
			if ( 'album' === $rtmedia_query->query['media_type'] ) {
				if ( isset( $rtmedia_query->media_query ) && isset( $rtmedia_query->media_query['album_id'] ) ) {
					if ( is_array( $rtmedia_query->album ) && count( $rtmedia_query->album ) > 0 ) {
						foreach ( $rtmedia_query->album as $single_album ) {
							if ( intval( $single_album->id ) === intval( $rtmedia_query->media_query['album_id'] ) ) {
								$title .= $sep . stripslashes( esc_html( ucfirst( $single_album->media_title ) ) );
								$sep    = $old_sep;
							}
						}
					}
				}
			} else {
				if ( isset( $rtmedia_query->media ) && $rtmedia_query->media && count( $rtmedia_query->media ) > 0 ) {
					$title .= $sep . stripslashes( esc_html( ucfirst( $rtmedia_query->media[0]->media_title ) ) );
					$sep    = $old_sep;
				}
				$title .= $sep . ucfirst( $rtmedia_query->query['media_type'] );
				$sep    = $old_sep;
			}
		} else {
			if ( isset( $rtmedia_query->action_query ) && isset( $rtmedia_query->action_query->media_type ) ) {
				$title .= $sep . ucfirst( $rtmedia_query->action_query->media_type );
				$sep    = $old_sep;
			}
		}

		if ( function_exists( 'bp_is_group' ) ) {
			if ( bp_is_groups_component() ) {
				if ( bp_is_group() || bp_is_group_forum_topic() ) {
					if ( bp_is_group_forum_topic() ) {
						$title .= $sep . bp_get_the_topic_title();
						$sep    = $old_sep;
					}
					$title .= $sep . bp_get_current_group_name();
					$sep    = $old_sep;
				}
			}
		}

		if ( function_exists( 'bp_get_displayed_user_fullname' ) && 0 !== bp_displayed_user_id() ) {
			$title .= $sep . bp_get_displayed_user_fullname();
			$sep    = $old_sep;
		} else {
			$user_info = get_userdata( get_current_user_id() );
			if ( isset( $user_info->data->display_name ) ) {
				$title .= $sep . $user_info->data->display_name;
				$sep    = $old_sep;
			}
		}

		$title .= $sep . apply_filters( 'rtmedia_media_tab_name', RTMEDIA_MEDIA_LABEL );
		$sep    = $old_sep;
		if ( isset( $this->context->type ) ) {
			switch ( $this->context->type ) {
				case 'group':
					$title .= $sep . ucfirst( $bp->groups->slug );
					break;
				case 'profile':
					if ( class_exists( 'BuddyPress' ) ) {
						$title .= $sep . ucfirst( $bp->profile->slug );
					} else {
						$title .= $sep . get_query_var( 'author_name' );
					}
					break;
				default:
					$title .= $sep . get_post_field( 'post_title', $this->context->id );
					break;
			}
		}
		$title            .= $sep . get_bloginfo( 'name' );
		$rtmedia_seo_title = $title;

		return apply_filters( 'rtmedia_wp_title', $title, $default, $sep );
	}

	/**
	 * Change OG Image using Yoast SEO plugin's filter
	 *
	 * @param array $data Image data.
	 *
	 * @return mixed
	 */
	public function rtmedia_wpseo_og_image( $data ) {
		global $wp_query;

		if ( class_exists( 'BuddyPress' ) ) {
			global $bp;
			if ( bp_is_single_activity() ) {
				$media_obj     = new RTMediaModel();
				$media_details = $media_obj->get( array( 'activity_id' => $bp->current_action ) );
				foreach ( $media_details as $media ) {
					if ( 'photo' === $media->media_type ) {
						$img = wp_get_attachment_image_src( $media->media_id, 'full' );
						if ( $img && isset( $img[0] ) && '' !== $img[0] ) {
							echo "<meta property='og:image' content='" . esc_url( $img[0] ) . "' />\n";
						}
					}
				}
			}
		}

		if ( ( array_key_exists( 'media', $wp_query->query_vars ) ) ) {
			global $rtmedia_query;
			if ( isset( $rtmedia_query->media ) && $rtmedia_query->media && count( $rtmedia_query->media ) > 0 ) {

				foreach ( $rtmedia_query->media as $media ) {
					$img = wp_get_attachment_image_src( $media->media_id, 'full' );
					if ( $img && isset( $img[0] ) && '' !== $img[0] ) {
						echo "<meta property='og:image' content='" . esc_url( $img[0] ) . "' />\n";
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Change URL using Yoast SEO plugin's filter
	 *
	 * @param string $url OG URL.
	 *
	 * @return string
	 */
	public function rtmedia_wpseo_og_url( $url ) {
		global $wp_query;
		if ( ! array_key_exists( 'media', $wp_query->query_vars ) ) {
			return $url;
		}
		$s        = empty( $_SERVER['HTTPS'] ) ? '' : ( ( 'on' === $_SERVER['HTTPS'] ) ? 's' : '' );
		$sp       = strtolower( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) );
		$protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . $s;
		$port     = ( '80' === $_SERVER['SERVER_PORT'] ) ? '' : ( ':' . $_SERVER['SERVER_PORT'] );

		return $protocol . '://' . esc_url( $_SERVER['SERVER_NAME'] ) . esc_url( $port ) . esc_url( $_SERVER['REQUEST_URI'] );
	}

	/**
	 * Change description using Yoast SEO plugin's filter
	 *
	 * @param string $desc SEO OG description.
	 *
	 * @return mixed
	 */
	public function rtmedia_wpseo_og_desc( $desc ) {
		global $wp_query;

		if ( ! array_key_exists( RTMEDIA_MEDIA_SLUG, $wp_query->query_vars ) ) {
			return $desc;
		}

		global $rtmedia_query;

		if ( isset( $rtmedia_query->media ) && is_array( $rtmedia_query->media ) && count( $rtmedia_query->media ) > 0 ) {
			$new_desc = get_post_field( 'post_content', $rtmedia_query->media[0]->media_id );

			if ( '' === $new_desc ) {
				$new_desc = $rtmedia_query->media[0]->media_title;
				$new_desc = apply_filters( 'rtmedia_share_media_description', $new_desc, $rtmedia_query->media[0] );
			}

			echo '<meta property="og:description" content="' . esc_attr( $new_desc ) . '" />' . "\n";
		}

		return $desc;
	}
}
