<?php
/**
 * Handle query object changes.
 *
 * @package rtMedia
 */

/**
 * Class to handle rtMedia query changes.
 *
 * @author saurabh
 */
class RTMediaQuery {

	/**
	 * The query arguments for the current instance
	 *
	 * @var array $query
	 */
	public $query = '';

	/**
	 * The query arguments for the current instance (variable)
	 *
	 * @var array $media_query
	 */
	public $media_query = '';

	/**
	 * The current action object (edit/delete/custom)
	 *
	 * @var object $action_query
	 */
	public $action_query = false;

	/**
	 * The currently relevant interaction object
	 *
	 * @var object $interaction
	 */
	private $interaction;

	/**
	 * Variable to store Original query.
	 *
	 * @var object $original_query
	 */
	public $original_query;

	/**
	 * The actions recognised for the object
	 *
	 * @var array $actions
	 */
	public $actions = array(
		'edit'           => array( 'Edit', false ),
		'delete'         => array( 'Delete', false ),
		'comment'        => array( 'Comment', true ),
		'delete-comment' => array( 'Comment Deleted', false ),
	);

	/**
	 * Media to process.
	 *
	 * @var string|array $media
	 */
	public $media = '';

	/**
	 * Stores media count.
	 *
	 * @var int $media_count
	 */
	public $media_count = 0;

	/**
	 * Current Media number.
	 *
	 * @var int $current_media
	 */
	public $current_media = - 1;

	/**
	 * In media loop or not.
	 *
	 * @var bool $in_the_media_loop
	 */
	public $in_the_media_loop = false;

	/**
	 * Data format.
	 *
	 * @var bool|string $format
	 */
	public $format = false;

	/**
	 * Global shortcode.
	 *
	 * @var bool|string $shortcode_global
	 */
	public $shortcode_global = false;

	/**
	 * Object of RTMediaModel class.
	 *
	 * @var RTMediaModel
	 */
	public $model;

	/**
	 * Object of RTMediaFriends class
	 *
	 * @var RTMediaFriends
	 */
	public $friendship;

	/**
	 * Initialise the query
	 *
	 * @param array|bool $args The query arguments.
	 *
	 * @global object    $rtmedia_interaction The global interaction object
	 */
	public function __construct( $args = false ) {

		// set up the interaction object relevant to just the query.
		// we only need information related to the media route.
		global $rtmedia_interaction;

		$this->model = new RTMediaModel();

		$this->interaction = ( ! empty( $rtmedia_interaction->routes[ RTMEDIA_MEDIA_SLUG ] ) ) ? $rtmedia_interaction->routes[ RTMEDIA_MEDIA_SLUG ] : null;

		$this->friendship = new RTMediaFriends();

		// action manipulator hook.
		$this->set_actions();

		// check and set the format to json, if needed.
		$this->set_json_format();

		// set up the action query from the URL.
		$this->set_action_query();

		add_filter( 'rtmedia-model-where-query', array( $this, 'privacy_filter' ), 1, 2 );

		// if no args were supplied, initialise the $args.
		if ( empty( $args ) ) {

			$this->init();

			// otherwise just populate the query.
		} else {

			$this->query( $args );
		}

		do_action( 'rtmedia_query_construct' );
	}

	/**
	 * Initialise the default args for the query
	 */
	public function init() {

	}

	/**
	 * Function to set media type in query var.
	 */
	public function set_media_type() {
		if ( ! isset( $this->query['media_type'] ) ) {
			if ( isset( $this->action_query->id ) ) {
				$media = $this->model->get( array( 'id' => $this->action_query->id ) );
				if ( is_array( $media ) && count( $media ) > 0 ) {
					$media_type                = $media[0]->media_type;
					$this->query['media_type'] = $media_type;
				}
			}
		}
	}

	/**
	 * Function to check if it's single page.
	 *
	 * @return bool
	 */
	public function is_single() {
		/**
		 * // check the condition
		 * wont be true in case of multiple albums
		 */
		if ( ! isset( $this->action_query->id ) || $this->is_album() ) {
			return false;
		} else {
			if ( isset( $this->query['media_type'] ) && 'album' === $this->query['media_type'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Function to check if it's album.
	 *
	 * @return bool
	 */
	public function is_album() {
		if ( isset( $this->query['media_type'] ) && 'album' === $this->query['media_type'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check group album.
	 *
	 * @return bool
	 */
	public function is_group_album() {
		if ( $this->is_album() && ( isset( $this->query['context'] ) && 'group' === $this->query['context'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check gallery.
	 *
	 * @return bool
	 */
	public function is_gallery() {
		if ( ! $this->is_single() ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check album gallery page.
	 *
	 * @return bool
	 */
	public function is_album_gallery() {
		if ( isset( $this->action_query->media_type ) && 'album' === $this->action_query->media_type ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check playlist gallery page
	 *
	 * @return bool
	 */
	public function is_playlist_gallery() {
		if ( isset( $this->action_query->media_type ) && 'playlist' === $this->action_query->media_type ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check playlist page
	 *
	 * @return bool
	 */
	public function is_playlist() {
		if ( isset( $this->query['media_type'] ) && 'playlist' === $this->query['media_type'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Function to check page single edit page
	 *
	 * @return bool
	 */
	public function is_single_edit() {
		if ( $this->is_single() && ( isset( $this->action_query->action ) && 'edit' === $this->action_query->action ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Function for json request
	 */
	public function set_json_format() {

		$json = sanitize_text_field( filter_input( INPUT_GET, 'json', FILTER_SANITIZE_STRING ) );

		if ( ! empty( $json ) ) {
			$this->format = 'json';
		}
	}

	/**
	 * Function to set action query.
	 */
	public function set_action_query() {
		if ( isset( $this->interaction ) && isset( $this->interaction->query_vars ) ) {
			$raw_query = $this->interaction->query_vars;
		} else {
			$raw_query = array();
		}

		if ( isset( $raw_query ) && is_array( $raw_query ) && count( $raw_query ) > 1 ) {
			if ( empty( $raw_query[0] ) && ! empty( $raw_query[1] ) ) {

				$temp_query = array();
				$row_count  = count( $raw_query );

				for ( $rt_count = 1; $rt_count < $row_count; $rt_count++ ) {
					$temp_query[] = $raw_query[ $rt_count ];
				}

				$raw_query = $temp_query;
			}
		}

		$bulk           = false;
		$action         = false;
		$attribute      = false;
		$modifier_type  = 'default';
		$modifier_value = false;
		$format         = '';
		$pageno         = 1;

		$json = sanitize_text_field( filter_input( INPUT_GET, 'json', FILTER_SANITIZE_STRING ) );

		// Get page number for json response.
		if ( ! empty( $json ) ) {
			$rtmedia_page = filter_input( INPUT_GET, 'rtmedia_page', FILTER_VALIDATE_INT );
			$pageno       = ( ! empty( $rtmedia_page ) ) ? $rtmedia_page : 1;
		}

		// Get page number for none json response.
		if ( empty( $json ) ) {
			$pageno = ( get_query_var( 'pg' ) ) ? get_query_var( 'pg' ) : 1;
		}

		$attributes = '';

		// The first part of the query /media/{*}/ .
		if ( is_array( $raw_query ) && count( $raw_query ) && ! empty( $raw_query[0] ) ) {

			// set the modifier value beforehand.
			$modifier_value = $raw_query[0];

			if ( 'album' === $modifier_value && ! is_rtmedia_album_enable() ) {
				include get_404_template();
				die();
			}

			do_action( 'rtmedia_slug_404_handler' ); // disable  media type 404 handler.

			// requesting nonce /media/nonce/edit/ | /media/nonce/comment.
			// | /media/nonce/delete .
			if ( 'nonce' === $modifier_value ) {

				$modifier_type = 'nonce';

				// requesting media id /media/{id}/.
			} elseif ( is_numeric( $modifier_value ) ) {

				$modifier_type = 'id';

				$request_action = sanitize_text_field( filter_input( INPUT_POST, 'request_action', FILTER_SANITIZE_STRING ) );
				// this block is unnecessary, please delete, asap.
				if ( 'delete' === $request_action ) {

					$action = 'delete';
				}

				// requesting an upload screen /media/upload/.
			} elseif ( array_key_exists( $modifier_value, $this->actions ) ) {
				// /media/edit/ | media/delete/ | /media/like/
				$action = $modifier_value;
				$bulk   = true;
			} elseif ( 'upload' === $modifier_value ) {

				$modifier_type = 'upload';
				$action        = 'upload';

				// /media/pg/2/
			} elseif ( 'pg' === $modifier_value ) {

				// paginating default query.
				$modifier_type = 'pg';
			} else {

				// requesting by media type /media/photos | /media/videos/.
				$modifier_type = 'media_type';
			}
		}

		$modifier_type  = apply_filters( 'rtmedia_action_query_modifier_type', $modifier_type, $raw_query );
		$modifier_value = apply_filters( 'rtmedia_action_query_modifier_value', $modifier_value, $raw_query );

		if ( isset( $raw_query[1] ) ) {

			$second_modifier = $raw_query[1];

			switch ( $modifier_type ) {

				case 'nonce':
					// /media/nonce/edit/ | /media/nonce/delete/
					if ( array_key_exists( $second_modifier, $this->actions ) ) {

						$nonce_type = $second_modifier;
					}

					break;

				case 'id':
					// /media/23/edit/ | media/23/delete/ | /media/23/like/
					if ( array_key_exists( $second_modifier, $this->actions ) ) {

						$action = $second_modifier;
					} else {
						if ( 'pg' === $second_modifier ) {
							if ( isset( $raw_query[2] ) && is_numeric( $raw_query[2] ) ) {
								$pageno = $raw_query[2];
							} elseif ( 'edit' === $raw_query[2] ) {
								/**
								 * Fix for URL
								 * like http://website.com/members/<user>/media/2/pg/edit/
								 *
								 * Fix for 'pg' (pagination) in URL
								 */
								$action = 'edit';
							}
						}
					}
					break;

				case 'pg':
					// /media/page/2/ | /media/page/3/
					if ( is_numeric( $second_modifier ) ) {

						$pageno = $second_modifier;
					}
					break;

				case 'media_type':
					// /media/photos/edit/ | /media/videos/edit/
					if ( array_key_exists( $second_modifier, $this->actions ) ) {

						$action = $second_modifier;
						$bulk   = true;
					}
					break;

				default:
					break;
			}
		}

		// The third part of the query /media/modifier/second_modifier/{*}.
		if ( isset( $raw_query[2] ) ) {

			$third_modifier = $raw_query[2];

			switch ( $modifier_type ) {

				case 'nonce':
					/**
					 * Leaving here for more granular nonce, in future, for eg,
					 * /media/nonce/edit/title/
					 */
					break;

				case 'id':
					/**
					 * Leaving here for more granular editing, in future, for eg,
					 * /media/23/edit/title/
					 */
					break;

				case 'media_type':
					/**
					 * /media/photos/edit/ | /media/videos/edit/.
					 * leaving here for more granular editing, in future,
					 *  for eg,
					 * /media/photos/edit/title/
					 * /media/photos/page/2/.
					 */
					if ( 'pg' === $second_modifier && is_numeric( $third_modifier ) ) {

						$pageno = $third_modifier;
					}
					break;

				case 'pg':
				default:
					break;
			}
		}

		global $rtmedia;

		/**
		 * Set action query object
		 * setting parameters in action query object for pagination
		 */
		$per_page_media = intval( $rtmedia->options['general_perPageMedia'] );
		$per_page_media = intval( apply_filters( 'rtmedia_per_page_media', $per_page_media ) );

		$this->action_query = (object) array(
			$modifier_type   => $modifier_value,
			'action'         => $action,
			'bulk'           => $bulk,
			'page'           => intval( $pageno ),
			'per_page_media' => $per_page_media,
			'attributes'     => $attributes,
		);
	}

	/**
	 * Additional actions to be added via action hook
	 */
	public function set_actions() {
		$this->actions = apply_filters( 'rtmedia_query_actions', $this->actions );
	}

	/**
	 * Get media query for the request
	 *
	 * @param object $query Query object.
	 *
	 * @return array
	 */
	public function &query( $query ) {
		$this->original_query = $query;

		/**
		 * Comment.
		 *
		 * @chandrapatel commented below code. here we are merging new query vars and previous query vars
		 * which is cause an issue. For example, First time query vars contains media_type and second time query vars not contain media_type
		 * then media not listed properly.
		 *
		 * Later on, remove this comment and below commented code.
		 * $this->query          = wp_parse_args( $query, $this->query );
		 */
		$this->query = $query;

		// Set Json.
		$allowed_query = apply_filters(
			'rtmedia_allowed_query',
			array(
				'id',
				'media_id',
				'media_type',
				'media_author',
				'album_id',
				'context',
				'context_id',
				'global',
				'privacy',
				'per_page',
				'lightbox',
				'media_title',
			)
		);

		$rtmedia_shortcode = sanitize_text_field( filter_input( INPUT_GET, 'rtmedia_shortcode', FILTER_SANITIZE_STRING ) );

		if ( ! empty( $rtmedia_shortcode ) ) {
			$query_data = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			foreach ( $query_data as $key => $val ) {
				if ( ! in_array( $key, $allowed_query, true ) ) {
					unset( $query_data[ $key ] );
				}
			}

			$this->query = wp_parse_args( $query_data, $this->query );

		} else {
			if ( isset( $this->is_gallery_shortcode ) && true === $this->is_gallery_shortcode ) {
				foreach ( $this->query as $key => $val ) {
					if ( ! in_array( $key, $allowed_query, true ) ) {
						unset( $this->query[ $key ] );
					}
				}
			}

			/**
			 * In gallery shortcode with uploader set to true, $this->is_gallery_shortcode won't be set for very first time and hence
			 * will miss the check for "$this->query['uploader']"
			*/
			if ( isset( $this->query['uploader'] ) ) {
				unset( $this->query['uploader'] );
			}
		}

		if ( isset( $this->query['context'] ) && 'activity' === $this->query['context'] ) {
			$this->query['activity_id'] = array( 'value' );
			global $wpdb;
			// todo cache.
			$sql_query                           = "select id from {$wpdb->prefix}bp_activity where item_id = 0  and type = 'rtmedia_update'";
			$this->query['activity_id']['value'] = $wpdb->get_col( $sql_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( isset( $this->query ) && isset( $this->query['global'] ) ) {
			if ( 'true' === $this->query['global'] ) {
				$this->shortcode_global = true;
				add_filter(
					'rtmedia-model-where-query',
					array(
						'RTMediaGalleryShortcode',
						'rtmedia_query_where_filter',
					),
					10,
					3
				);

				$remove_comment_media = apply_filters( 'rtmedia_query_where_filter_remove_comment_media', true, 'galleryshortcode' );
				if ( isset( $remove_comment_media ) && ! empty( $remove_comment_media ) ) {
					add_filter( 'rtmedia-model-where-query', array( 'RTMediaGalleryShortcode', 'rtmedia_query_where_filter_remove_comment_media' ), 11, 3 );
				}

				if ( isset( $this->query['context_id'] ) ) {
					unset( $this->query['context_id'] );
				}
				if ( isset( $this->query['context'] ) ) {
					unset( $this->query['context'] );
				}

				if ( isset( $this->query['media_type'] ) && 'album' === $this->query['media_type'] ) {

					add_filter(
						'rtmedia-before-template',
						array(
							&$this,
							'register_set_gallery_template_filter',
						),
						10,
						2
					);
				}
			}
			unset( $this->query['global'] );
		}

		$this->set_media_type();
		$this->media_query = $this->query;
		do_action( 'rtmedia_set_query' );

		return $this->get_data();
	}

	/**
	 * Function to remove gallery template filter.
	 *
	 * @param string $template Template.
	 * @param array  $attr Attributes.
	 *
	 * @return string
	 */
	public function register_set_gallery_template_filter( $template, $attr ) {
		remove_filter( 'rtmedia-before-template', array( &$this, 'register_set_gallery_template_filter' ), 10, 2 );

		return 'album-gallery';

	}

	/**
	 * Function to filter user privacy for query.
	 *
	 * @param string $where Where clause.
	 * @param string $table_name Table name.
	 *
	 * @return string
	 */
	public function privacy_filter( $where, $table_name ) {
		if ( is_rt_admin() ) {
			return $where;
		}
		$user = $this->get_user();

		$where .= " AND ({$table_name}.privacy is NULL OR {$table_name}.privacy=0";
		if ( $user ) {
			$where .= " OR ({$table_name}.privacy=20)";
			$where .= " OR ({$table_name}.media_author={$user} AND {$table_name}.privacy>=40)";
			if ( class_exists( 'BuddyPress' ) ) {
				if ( bp_is_active( 'friends' ) ) {
					$friends = $this->friendship->get_friends_cache( $user );
					$where  .= " OR ({$table_name}.privacy=40 AND {$table_name}.media_author IN ('" . implode( "','", $friends ) . "'))";
				}
			}
		}

		return $where . ')';
	}

	/**
	 * Get current user.
	 *
	 * @return int|mixed|void
	 */
	public function get_user() {
		if ( is_user_logged_in() ) {
			$user = apply_filters( 'rtmedia_current_user', get_current_user_id() );
		} else {
			$user = 0;
		}

		return $user;
	}

	/**
	 * Set user privacy.
	 */
	public function set_privacy() {
		$user = $this->get_user();
		if ( ! $user ) {
			$privacy = 0;
		} else {
			$privacy = 20;
		}
	}

	/**
	 * Function to populate media.
	 *
	 * @return array|bool
	 */
	public function populate_media() {

		global $rtmedia_query;

		// Check if the page is gallery shortcode or not.
		$is_gallery_shortcode = ( isset( $rtmedia_query->is_gallery_shortcode ) && true === $rtmedia_query->is_gallery_shortcode ) ? true : false;

		$this->set_privacy();
		if ( $this->is_single() ) {
			$this->media_query['id'] = $this->action_query->id;
		}

		$allowed_media_types = array();

		// is this an album or some other media.
		$this->album_or_media();

		$order_by = $this->order_by();

		if ( isset( $this->media_query['context'] ) ) {

			if ( 'profile' === $this->media_query['context'] ) {

				if ( ! $this->is_album_gallery() ) {
					$this->media_query['media_author'] = $this->media_query['context_id'];
				} else {
					$author = $this->media_query['context_id'];
				}

				// If it is a media single page, then unset the context and context id.
				if ( $this->is_single() ) {
					unset( $this->media_query['context'] );
					unset( $this->media_query['context_id'] );
				}
			} else {
				if ( 'group' === $this->media_query['context'] ) {
					$group_id = $this->media_query['context_id'];
				}
			}

			// Multiple context_id support.
			if ( isset( $this->media_query['context_id'] ) && count( explode( ',', $this->media_query['context_id'] ) ) > 1 ) {
				$this->media_query['context_id'] = array(
					'compare' => 'in',
					'value'   => explode( ',', $this->media_query['context_id'] ),
				);
			}
		}

		// Multiple album_id support.
		if ( isset( $this->media_query['album_id'] ) && count( explode( ',', $this->media_query['album_id'] ) ) > 1 ) {
			$this->media_query['album_id'] = array(
				'compare' => 'in',
				'value'   => explode( ',', $this->media_query['album_id'] ),
			);
		}

		if ( isset( $this->media_query['per_page'] ) ) {
			// Do not include per_page in sql query to get media.
			$this->action_query->per_page_media = intval( $this->media_query['per_page'] );
			unset( $this->media_query['per_page'] );
		}

		// Lightbox option.
		if ( isset( $this->media_query['lightbox'] ) ) {
			if ( 'false' === $this->media_query['lightbox'] ) {
				// Add filter to add no-popup class in a tag.
				add_filter( 'rtmedia_gallery_list_item_a_class', 'rtmedia_add_no_popup_class', 10, 1 );
			}
			// Unset the lightbox parameter from media query.
			unset( $this->media_query['lightbox'] );
		}

		// Media title option.
		if ( isset( $this->media_query ) ) {
			if ( ( isset( $this->media_query['media_title'] ) && 'false' === $this->media_query['media_title'] ) || ( true === $is_gallery_shortcode && ! isset( $this->media_query['media_title'] ) ) ) {
				// Add filter show media title.
				add_filter( 'rtmedia_media_gallery_show_media_title', 'rtmedia_gallery_do_not_show_media_title', 10, 1 );
			}
			// Unset the media title parameter from media query.
			unset( $this->media_query['media_title'] );
		}

		$this->media_query = apply_filters( 'rtmedia_media_query', $this->media_query, $this->action_query, $this->query );

		if ( $this->is_album_gallery() ) {

			if ( isset( $author ) ) {
				$query_function = 'get_user_albums';
				$context_id     = $author;
			} elseif ( isset( $group_id ) ) {
				$query_function = 'get_group_albums';
				$context_id     = $group_id;
			}

			$media_for_total_count = count( $this->model->{$query_function}( $context_id, false, false ) );

			$this->action_query = apply_filters( 'rtmedia_action_query_in_populate_media', $this->action_query, $media_for_total_count );

			if ( ' ' === $order_by ) {
				$pre_media = $this->model->{$query_function}( $context_id, ( $this->action_query->page - 1 ) * $this->action_query->per_page_media, $this->action_query->per_page_media );
			} else {
				$pre_media = $this->model->{$query_function}( $context_id, ( $this->action_query->page - 1 ) * $this->action_query->per_page_media, $this->action_query->per_page_media, $order_by );
			}
		} else {

			/**
			 * Count total medias in album irrespective of pagination
			 */
			$media_for_total_count = $this->model->get_media( $this->media_query, false, false, false, true );

			$this->action_query = apply_filters( 'rtmedia_action_query_in_populate_media', $this->action_query, $media_for_total_count );

			/**
			 * Fetch media entries from rtMedia context
			 */
			if ( ' ' === $order_by ) {
				$pre_media = $this->model->get_media( $this->media_query, ( $this->action_query->page - 1 ) * $this->action_query->per_page_media, $this->action_query->per_page_media );
			} else {
				$pre_media = $this->model->get_media( $this->media_query, ( $this->action_query->page - 1 ) * $this->action_query->per_page_media, $this->action_query->per_page_media, $order_by );
			}
		}

		$this->media_count = intval( $media_for_total_count );

		if ( ! $pre_media ) {
			return false;
		} else {
			return $pre_media;
		}

	}

	/**
	 * Function to check media type.
	 */
	public function album_or_media() {

		global $rtmedia;

		foreach ( $rtmedia->allowed_types as $value ) {
			$allowed_media_types[] = $value['name'];
		}

		if ( ! isset( $this->media_query['media_type'] ) ) {
			if ( isset( $this->action_query->media_type ) && ( in_array( $this->action_query->media_type, $allowed_media_types, true ) || 'album' === $this->action_query->media_type ) ) {
				$this->media_query['media_type'] = $this->action_query->media_type;
			} else {
				$this->media_query['media_type'] = array(
					'compare' => 'IN',
					'value'   => array( 'music', 'video', 'photo' ),
				);
				$this->media_query['media_type'] = apply_filters( 'rtmedia_query_media_type_filter', $this->media_query['media_type'] ); // Can add more types here.
			}
		}
	}

	/**
	 * Function to filter order by.
	 *
	 * @return mixed|string|void
	 */
	public function order_by() {
		/**
		 * Handle order of the result set
		 */
		$order_by = '';
		$order    = '';
		if ( isset( $this->media_query['order'] ) ) {
			$order = $this->media_query['order'];
			unset( $this->media_query['order'] );
		}

		if ( isset( $this->media_query['order_by'] ) ) {
			$order_by = $this->media_query['order_by'];
			unset( $this->media_query['order_by'] );
			if ( 'ratings' === $order_by ) {
				$order_by = 'ratings_average ' . $order . ', ratings_count';
			}
		}
		$order_by .= ' ' . $order;

		$order_by = apply_filters( 'rtmedia_model_order_by', $order_by );

		return $order_by;
	}

	/**
	 * Function to populate album.
	 *
	 * @return array|bool
	 */
	public function populate_album() {
		$this->album                   = $this->media;
		$this->media_query['album_id'] = $this->action_query->id;

		if ( apply_filters( 'rtmedia_unset_action_query_id_album', true ) ) {
			unset( $this->action_query->id );
		}

		unset( $this->media_query['id'] );
		unset( $this->media_query['media_type'] );

		return $this->populate_media();
	}

	/**
	 * Function to populate comments.
	 *
	 * @return array|int
	 */
	public function populate_comments() {

		$this->model = new RTMediaCommentModel();
		global $rtmedia_interaction;

		return $this->model->get( array( 'post_id' => $rtmedia_interaction->context->id ) );
	}

	/**
	 * Populate the data object for the page/album
	 *
	 * @return null
	 */
	public function populate_data() {

		unset( $this->media_query->meta_query );
		unset( $this->media_query->tax_query );
		$this->current_media = - 1;
		if ( 'comments' === $this->action_query->action && ! isset( $this->action_query->id ) ) {
			$this->media = $this->populate_comments();
		} elseif ( $this->is_album() && ! $this->shortcode_global ) {
			$this->media = $this->populate_album();
		} else {
			$this->media = $this->populate_media();
		}

		if ( empty( $this->media ) ) {
			return;
		}

		/**
		 * Multisite manipulation
		 */
		if ( is_multisite() ) {
			$blogs = array();
			foreach ( $this->media as $media ) {
				$blogs[ $media->blog_id ][] = $media;
			}

			foreach ( $blogs as $blog_id => &$media ) {
				// todo something for below statement.
				switch_to_blog( $blog_id );
				if ( ! ( 'comments' === $this->action_query->action && ! isset( $this->action_query->id ) ) ) {
					$this->populate_post_data( $media );
					wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_reset_query_wp_reset_query
				}
			}
			restore_current_blog();
		} else {
			if ( ! ( 'comments' === $this->action_query->action && ! isset( $this->action_query->id ) ) ) {
				$this->populate_post_data( $this->media );
			}
		}
		if ( $this->shortcode_global ) {
			remove_filter(
				'rtmedia-model-where-query',
				array(
					'RTMediaGalleryShortcode',
					'rtmedia_query_where_filter',
				),
				10
			);
		}

	}

	/**
	 * Helper method to fetch media id of each media from the map
	 *
	 * @param object $media Media object.
	 *
	 * @return int
	 */
	public function get_media_id( $media ) {
		return $media->media_id;
	}

	/**
	 * Helper method to find the array entry for the given media id
	 *
	 * @param int $id Media Id.
	 *
	 * @return null
	 */
	public function get_media_by_media_id( $id ) {

		foreach ( $this->media as $key => $media ) {
			if ( intval( $media->media_id ) === intval( $id ) ) {
				return $key;
			}
		}

		return null;
	}

	/**
	 * Populate the post data for the fetched media from rtMedia context
	 *
	 * @param array $media Medias to process.
	 */
	public function populate_post_data( $media ) {
		if ( ! empty( $media ) && is_array( $media ) ) {

			/**
			 * Setting up query vars for WP_Query
			 */
			$media_post_query_args = array(
				'orderby'             => 'ID',
				'order'               => 'DESC',
				'post_type'           => 'any',
				'post_status'         => 'any',
				'post__in'            => array_map( array( $this, 'get_media_id' ), $media ),
				'ignore_sticky_posts' => 1,
				'posts_per_page'      => $this->action_query->per_page_media,
			);

			/**
			 * Setting up meta query vars
			 */
			if ( isset( $this->query_vars->meta_query ) ) {
				$media_post_query_args['meta_query'] = $this->query_vars->meta_query;
			}
			/**
			 * Setting up taxonomy query vars
			 */
			if ( isset( $this->query_vars->tax_query ) ) {
				$media_post_query_args['tax_query'] = $this->query_vars->tax_query;
			}

			/**
			 * Fetch relative data from WP_POST
			 */
			$media_post_query = new WP_Query( $media_post_query_args );

			/**
			 * Merge the data with media object of the album
			 */
			$media_post_data = $media_post_query->posts;

			foreach ( $media_post_data as $array_key => $post ) {
				$key = $this->get_media_by_media_id( $post->ID );

				$this->media[ $key ] = (object) ( array_merge( (array) $this->media[ $key ], (array) $post ) );

				$this->media[ $key ]->id = intval( $this->media[ $key ]->id );

				unset( $this->media[ $key ]->ID );
			}
		}
	}

	/**
	 * Checks at any point of time any media is left to be processed in the db pool
	 *
	 * @return boolean
	 */
	public function have_media() {

		$total    = $this->media_count;
		$curr     = $this->current_media;
		$per_page = $this->action_query->per_page_media;
		$offset   = ( $this->action_query->page - 1 ) * $this->action_query->per_page_media;

		if ( $curr + 1 < $per_page && $total > $offset + $curr + 1 ) {
			return true;
		} elseif ( $curr + 1 === $per_page && $per_page > 0 ) {
			do_action_ref_array( 'rtmedia_loop_end', array( &$this ) );
			// Do some cleaning up after the loop.
			$this->rewind_media();
		}

		$this->in_the_media_loop = false;

		return false;
	}

	/**
	 * Moves ahead in the loop of media within the album
	 *
	 * @global object $rtmedia_media
	 * @return object
	 */
	public function rtmedia() {

		global $rtmedia_media;

		$this->in_the_media_loop = true;

		if ( -1 === intval( $this->current_media ) ) { // loop has just started.
			do_action_ref_array( 'rtmedia_loop_start', array( &$this ) );
		}

		$rtmedia_media = $this->next_media();

		return $rtmedia_media;

	}

	/**
	 * Helper method for rt_album to move ahead in the db pool
	 *
	 * @return array
	 */
	public function next_media() {
		$this->current_media++;

		$this->rtmedia = $this->media[ $this->current_media ];

		return $this->rtmedia;
	}

	/**
	 * Function for get parent permalink.
	 *
	 * @return mixed
	 */
	public function permalink() {

		global $rtmedia_media;
		$parent_link = '';

		if ( function_exists( 'bp_core_get_user_domain' ) ) {
			$parent_link = bp_core_get_user_domain( $rtmedia_media->media_author );
		} else {
			$parent_link = get_author_posts_url( $rtmedia_media->media_author );
		}

		$link = trailingslashit( $parent_link . RTMEDIA_MEDIA_SLUG . $rtmedia_media->id );

		return $link;
	}

	/**
	 * Rewinds the db pool of media album and resets it to begining
	 */
	public function rewind_media() {
		$this->current_media = - 1;
		if ( $this->action_query->per_page_media > 0 ) {
			$this->media = $this->media[0];
		}
	}

	/**
	 * Function to get data.
	 *
	 * @return array
	 */
	public function &get_data() {

		$this->populate_data();

		return $this->media;
	}
}
