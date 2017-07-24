<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaPrivacy
 *
 * @author saurabh
 */
class RTMediaPrivacy {

	/**
	 *
	 * @var object default application wide privacy levels
	 */
	public $default_privacy;

	public $rtm_activity_table_alias = 'ra';

	function __construct( $flag = true ) {
		if ( is_rtmedia_privacy_enable() && $flag ) {
			add_action( 'bp_init', array( $this, 'add_nav' ) );
			add_action( 'bp_template_content', array( $this, 'content' ) );
			add_filter( 'bp_activity_get_user_join_filter', array( $this, 'activity_privacy' ), 10, 6 );

			// Filter bp_activity_get_user_join_filter to get activity privacy field in loop
			add_filter( 'bp_activity_get_user_join_filter', array( $this, 'activity_privacy_sql_field' ), 10, 6 );

			add_filter( 'bp_use_legacy_activity_query', array( $this, 'enable_buddypress_privacy' ), 10, 3 );
			add_filter( 'bp_activity_has_more_items', array( $this, 'enable_buddypress_load_more' ), 10, 1 );
			add_action( 'bp_actions', array( $this, 'rt_privacy_settings_action' ) );

			// show change privacy option in activity meta.
			add_action( 'bp_activity_entry_meta', array( $this, 'update_activity_privacy_option' ) );

			// Add nonce field to change activity privacy option
			add_action( 'template_notices', array( $this, 'add_activity_privacy_nonce' ) );

			// save update privacy value
			add_action( 'wp_ajax_rtm_change_activity_privacy', array( $this, 'rtm_change_activity_privacy' ) );

		}
		add_action( 'friends_friendship_accepted', array( 'RTMediaFriends', 'refresh_friends_cache' ) );
		add_action( 'friends_friendship_deleted', array( 'RTMediaFriends', 'refresh_friends_cache' ) );
	}

	/**
	 * Hooked to `bp_activity_entry_meta`
	 *
	 * Show privacy dropdown inside activity loop along with activity meta buttons.
	 */
	function update_activity_privacy_option() {
		global $activities_template;
		$rtmedia_activity_types = array( 'rtmedia_comment_activity', 'rtmedia_like_activity', 'activity_comment' );
		if ( function_exists( 'bp_activity_user_can_delete' ) && bp_activity_user_can_delete()
			&& ( ! bp_is_groups_component() ) && is_rtmedia_privacy_user_overide()
			&& apply_filters( 'rtm_load_bp_activity_privacy_update_ui', true )
			&& isset( $activities_template->activity ) && isset( $activities_template->activity->type ) &&! in_array( $activities_template->activity->type , $rtmedia_activity_types )
		) {

			$selected = 0;
			if ( isset( $activities_template->activity->privacy ) ) {
				$selected = intval( $activities_template->activity->privacy );
			}

			//todo strict standard error
			if ( isset( $activities_template->activity->privacy ) && '80' != $activities_template->activity->privacy ) {
				self::select_privacy_ui( true, 'rtm-ac-privacy-' . $activities_template->activity->id, array( 'rtm-activity-privacy-opt' ), $selected );
			}
		}
	}

	/**
	 * Add nonce field for activity privacy change action verification
	 */
	function add_activity_privacy_nonce() {
		wp_nonce_field( 'rtmedia_activity_privacy_nonce', 'rtmedia_activity_privacy_nonce' );
	}

	function rtm_change_activity_privacy() {
		$nonce       = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_STRING );
		$privacy     = filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_NUMBER_INT );
		$activity_id = filter_input( INPUT_POST, 'activity_id', FILTER_SANITIZE_NUMBER_INT );

		if ( wp_verify_nonce( $nonce, 'rtmedia_activity_privacy_nonce' ) ) {
			$media_ids_of_activity = array();
			$rtm_activity_model  = new RTMediaActivityModel();
			$is_ac_privacy_exist = $rtm_activity_model->check( $activity_id );

			$privacy     = intval( $privacy );
			$activity_id = intval( $activity_id );

			if ( ! $is_ac_privacy_exist ) {
				// Very first privacy entry for this activity
				$status = $rtm_activity_model->insert( array(
					'privacy'     => $privacy,
					'activity_id' => $activity_id,
					'user_id'     => get_current_user_id(),
				) );
			} else {
				// Just update the existing value
				$status = $rtm_activity_model->update( array( 'privacy' => $privacy ), array( 'activity_id' => $activity_id ) );
			}

			// update privacy of corresponding media
			$media_model    = new RTMediaModel();
			$activity_media = $media_model->get( array( 'activity_id' => $activity_id ) );
			if ( ! empty( $activity_media ) && is_array( $activity_media ) ) {
				foreach ( $activity_media as $single_media ) {
					/* get all the media ids in the activity */
					$media_ids_of_activity[] = $single_media->id;

					$where   = array( 'id' => $single_media->id );
					$columns = array( 'privacy' => $privacy );

					// update media privacy
					$media_model->update( $columns, $where );
				}
			}

			/* is the activate has any media then move the like and comment of that media to for the privacy */
			$rtm_activity_model->profile_activity_update( $media_ids_of_activity, $privacy, $activity_id );

			if ( false === $status ) {
				$status = 'false';
			} else {
				$status = 'true';
			}

			echo esc_html( $status );
			wp_die();
		}
	}

	function enable_buddypress_load_more( $has_more_items ) {
		global $activities_template;

		return true;
	}

	function enable_buddypress_privacy( $flag, $method, $func_args ) {
		global $rtmedia;
		$option = $rtmedia->options;
		if ( isset( $option['privacy_enabled'] ) && 0 !== intval( $option['privacy_enabled'] ) ) {
			if ( 'BP_Activity_Activity::get' === $method ) {
				$flag = true;
			}
		}

		return $flag;
	}

	function select_privacy_ui( $echo = true, $element_id = false, $element_class = array(), $selected = false ) {
		global $rtmedia;

		if ( ! is_rtmedia_privacy_enable() ) {
			return false;
		}

		if ( ! is_rtmedia_privacy_user_overide() ) {
			return false;
		}

		global $rtmedia_media;

		if ( false !== $selected ) {
			$default = $selected;
		} elseif ( isset( $rtmedia_media->privacy ) ) {
			$default = $rtmedia_media->privacy;
		} else {
			//todo user attribute
			$default = get_user_meta( get_current_user_id(), 'rtmedia-default-privacy', true );
			if ( ( false === $default ) || '' === $default ) {
				$default = get_rtmedia_default_privacy();
			}
		}

		$form = new rtForm();

		$attributes_class = array( 'privacy' );

		if ( ! empty( $element_class ) ) {
			if ( ! is_array( $element_class ) ) {
				$attributes_class = array_merge( $attributes_class, (array) $element_class );
			} else {
				$attributes_class = array_merge( $attributes_class, $element_class );
			}
		}
		$attributes = array(
			'name'  => 'privacy',
			'class' => $attributes_class,
		);
		if ( $element_id && '' !== $element_id ) {
			$attributes['id'] = $element_id;
		}
		global $rtmedia;
		$privacy_levels = $rtmedia->privacy_settings['levels'];
		if ( class_exists( 'BuddyPress' ) ) {
			if ( ! bp_is_active( 'friends' ) ) {
				unset( $privacy_levels[40] );
			}
		} else {
			unset( $privacy_levels[40] );
		}
		foreach ( $privacy_levels as $key => $value ) {
			$privacy                        = explode( ' - ', $value );
			$attributes['rtForm_options'][] = array(
				$privacy[0] => $key,
				'selected'  => ( intval( $default ) === $key ) ? 1 : 0,
			);
		}

		if ( $echo ) {
			echo $form->get_select( $attributes ); // @codingStandardsIgnoreLine
		} else {
			return $form->get_select( $attributes );
		}
	}

	public
	function system_default() {
		return 0;
	}

	public
	function site_default() {
		global $rtmedia;

		return rtmedia_get_site_option( 'privacy_settings' );
	}

	public
	function user_default() {
		return;
	}

	public
	function get_default() {
		$default_privacy = $this->user_default();

		if ( false === $default_privacy ) {
			$default_privacy = $this->site_default();
		}

		if ( ! false === $default_privacy ) {
			$default_privacy = $this->system_default();
		}
	}

	static
	function is_enabled() {
		global $bp_media;
		$options = $bp_media->options;
		if ( ! array_key_exists( 'privacy_enabled', $options ) ) {
			return false;
		} else {
			if ( true !== $options['privacy_enabled'] ) {
				return false;
			}
		}

		return true;
	}

	static
	function save_user_default(
		$level = 0, $user_id = false
	) {
		if ( false === $user_id ) {
			global $bp;
			$user_id = $bp->loggedin_user->id;
		}

		//todo user attribute
		return update_user_meta( $user_id, 'bp_media_privacy', $level );
	}

	static function get_user_default( $user_id = false ) {
		if ( false === $user_id ) {
			global $bp;
			$user_id = $bp->loggedin_user->id;
		}
		//todo user attribute
		$user_privacy = get_user_meta( $user_id, 'bp_media_privacy', true );

		return $user_privacy;
	}

	static
	function required_access(
		$object_id = false
	) {
		if ( false === BPMediaPrivacy::is_enabled() ) {
			return;
		}
		if ( false === $object_id ) {
			return;
		}
		$privacy        = BPMediaPrivacy::get_privacy( $object_id );
		$parent         = get_post_field( 'post_parent', $object_id, 'raw' );
		$parent_privacy = BPMediaPrivacy::get_privacy( $parent );

		if ( false === $privacy ) {
			if ( false !== $parent_privacy ) {
				$privacy = $parent_privacy;
			} else {
				$privacy = BPMediaPrivacy::default_privacy();
			}
		}

		return $privacy;
	}

	function add_nav() {

		if ( bp_displayed_user_domain() ) {
			$user_domain = bp_displayed_user_domain();
		} elseif ( bp_loggedin_user_domain() ) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			return;
		}

		if ( ! is_rtmedia_profile_media_enable() ) {
			return;
		}
		if ( ! is_rtmedia_privacy_enable() ) {
			return;
		}
		if ( ! is_rtmedia_privacy_user_overide() ) {
			return;
		}

		$settings_link = trailingslashit( $user_domain . 'settings' );

		$defaults = array(
			'name'            => $this->title(), // Display name for the nav item
			'slug'            => 'privacy', // URL slug for the nav item
			'parent_slug'     => 'settings', // URL slug of the parent nav item
			'parent_url'      => $settings_link, // URL of the parent item
			'item_css_id'     => 'rtmedia-privacy-settings', // The CSS ID to apply to the HTML of the nav item
			'user_has_access' => true, // Can the logged in user see this nav item?
			'site_admin_only' => false, // Can only site admins see this nav item?
			'position'        => 80, // Index of where this nav item should be positioned
			'screen_function' => array( $this, 'settings_ui' ), // The name of the function to run when clicked
			'link'            => '',// The link for the subnav item; optional, not usually required.
		);
		bp_core_new_subnav_item( $defaults );
	}

	function settings_ui() {
		if ( bp_action_variables() ) {
			bp_do_404();

			return;
		}

		// Load the template
		bp_core_load_template( apply_filters( 'bp_settings_screen_delete_account', 'members/single/plugins' ) );
	}

	/**
	 * changing and saving of privacy setting save action
	 */
	function rt_privacy_settings_action() {
		if ( 'privacy' !== buddypress()->current_action ) {
			return;
		}

		$default_privacy = filter_input( INPUT_POST, 'rtmedia-default-privacy', FILTER_SANITIZE_STRING );
		$nonce           = filter_input( INPUT_POST, 'rtmedia_member_settings_privacy', FILTER_SANITIZE_STRING );

		if ( null !== $default_privacy ) {
			$status = false;
			if ( wp_verify_nonce( $nonce, 'rtmedia_member_settings_privacy' ) ) {
				//todo user attribute
				$status = update_user_meta( get_current_user_id(), 'rtmedia-default-privacy', $default_privacy );
			}
			if ( false === $status ) {
				$feedback      = esc_html__( 'No changes were made to your account.', 'buddypress-media' );
				$feedback_type = 'error';
			} else if ( true === $status ) {
				$feedback      = esc_html__( 'Your default privacy settings saved successfully.', 'buddypress-media' );
				$feedback_type = 'success';
			}
			bp_core_add_message( $feedback, $feedback_type );
			do_action( 'bp_core_general_settings_after_save' );
			bp_core_redirect( bp_displayed_user_domain() . bp_get_settings_slug() . '/privacy/' );
		}
	}

	function content() {
		if ( 'privacy' !== buddypress()->current_action ) {
			return;
		}
		//todo user attribute
		$default_privacy = get_user_meta( get_current_user_id(), 'rtmedia-default-privacy', true );
		if ( false === $default_privacy || '' === $default_privacy ) {
			$default_privacy = get_rtmedia_default_privacy();
		}
		global $rtmedia;
		?>
		<form method="post">
			<div class="rtm_bp_default_privacy">
				<?php wp_nonce_field( 'rtmedia_member_settings_privacy', 'rtmedia_member_settings_privacy' ); ?>
				<div class="section">
					<div class="rtm-title"><h3><?php esc_html_e( 'Default Privacy', 'buddypress-media' ); ?></h3></div>
					<div class="rtm-privacy-levels">
						<?php foreach ( $rtmedia->privacy_settings['levels'] as $level => $data ) { ?>
							<label><input type='radio' value='<?php echo esc_attr( $level ); ?>'
							              name='rtmedia-default-privacy' <?php echo esc_attr( ( intval( $default_privacy ) === $level ) ? 'checked' : '' ); ?> /> <?php echo esc_html( $data ); ?>
							</label><br/>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="submit">
				<input type="submit" name="submit" value="<?php esc_attr_e( 'Save Changes', 'buddypress-media' ); ?>"
				       id="submit" class="auto">
			</div>
		</form>
		<?php
	}

	function title() {
		return esc_html__( 'Privacy', 'buddypress-media' );
	}

	function activity_privacy( $sql, $select_sql, $from_sql, $where_sql, $sort, $pag_sql = '' ) {
		if ( is_rt_admin() ) {
			return $sql;
		}

		$sql   = '';
		$where = '';
		global $bp, $wpdb;
		$rtmedia_model = new RTMediaModel();

		if ( is_user_logged_in() ) {
			$user = get_current_user_id();
		} else {
			$user = 0;
		}

		// admin has upgraded rtmedia activity so we can use rt_rtm_activity table for rtmedia related activity filters
		if ( $this->can_use_rtm_ac_privacy() ) {
			$rtmedia_activity_model = new RTMediaActivityModel();
			$where .= " ({$this->rtm_activity_table_alias}.privacy is NULL OR {$this->rtm_activity_table_alias}.privacy <= 0) ";
			if ( $user ) {
				$where .= "OR (({$this->rtm_activity_table_alias}.privacy=20)";
				$where .= " OR (a.user_id={$user} AND {$this->rtm_activity_table_alias}.privacy >= 40)";
				if ( class_exists( 'BuddyPress' ) ) {
					if ( bp_is_active( 'friends' ) ) {
						$friendship = new RTMediaFriends();
						$friends    = $friendship->get_friends_cache( $user );
						if ( isset( $friends ) && ! empty( $friends ) ) {
							$in_str_arr = array_fill( 0, count( $friends ), '%d' );
							$in_str     = join( ',', $in_str_arr );
							$where .= $wpdb->prepare( " OR ({$this->rtm_activity_table_alias}.privacy=40 AND a.user_id IN ({$in_str}) )", $friends ); // @codingStandardsIgnoreLine
						}
					}
				}
				$where .= ')';
			}

			if ( strpos( $select_sql, 'SELECT DISTINCT' ) === false ) {
				$select_sql = str_replace( 'SELECT', 'SELECT DISTINCT', $select_sql );
			}

			$select_sql .= " ,{$this->rtm_activity_table_alias}.privacy ";

			$from_sql = $wpdb->prepare( " FROM {$bp->activity->table_name} a LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID LEFT JOIN {$rtmedia_activity_model->table_name} {$this->rtm_activity_table_alias} ON ( a.id = {$this->rtm_activity_table_alias}.activity_id and ra.blog_id = %d ) ", get_current_blog_id() ); // @codingStandardsIgnoreLine

			// removed NOT EXISTS check for `rtmedia_privacy` activty meta value.
			// check git history for more details ;)
			$where_sql = $where_sql . " AND ({$where})";
			$newsql    = "{$select_sql} {$from_sql} {$where_sql} ORDER BY a.date_recorded {$sort} {$pag_sql}";
		} else {
			$where .= ' (m.max_privacy is NULL OR m.max_privacy <= 0) ';
			if ( $user ) {
				$where .= 'OR ((m.max_privacy=20)';
				$where .= " OR (a.user_id={$user} AND m.max_privacy >= 40)";
				if ( class_exists( 'BuddyPress' ) ) {
					if ( bp_is_active( 'friends' ) ) {
						$friendship = new RTMediaFriends();
						$friends    = $friendship->get_friends_cache( $user );
						if ( isset( $friends ) && ! empty( $friends ) ) {
							$where .= " OR (m.max_privacy=40 AND a.user_id IN ('" . implode( "','", $friends ) . "'))";
						}
					}
				}
				$where .= ')';
			}
			if ( function_exists( 'bp_core_get_table_prefix' ) ) {
				$bp_prefix = bp_core_get_table_prefix();
			} else {
				$bp_prefix = '';
			}
			if ( strpos( $select_sql, 'SELECT DISTINCT' ) === false ) {
				$select_sql = str_replace( 'SELECT', 'SELECT DISTINCT', $select_sql );
			}
			$media_table = "SELECT *, max( privacy ) as max_privacy from {$rtmedia_model->table_name} group by activity_id";
			$from_sql    = $wpdb->prepare( " FROM {$bp->activity->table_name} a LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID LEFT JOIN ( $media_table ) m ON ( a.id = m.activity_id AND m.blog_id = %d ) ", get_current_blog_id() ); // @codingStandardsIgnoreLine
			$where_sql   = $where_sql . " AND (NOT EXISTS (SELECT m.activity_id FROM {$bp_prefix}bp_activity_meta m WHERE m.meta_key='rtmedia_privacy' AND m.activity_id=a.id) OR ( {$where} ) )";
			$newsql      = "{$select_sql} {$from_sql} {$where_sql} ORDER BY a.date_recorded {$sort} {$pag_sql}";
		}

		return $newsql;
	}

	/**
	 * Hooked to `bp_activity_get_user_join_filter` filter. Get activity privacy field inside loop.
	 *
	 * Use only if current user has admin capability because for non admin users privacy field will be added in
	 * privacy filter query itself.
	 *
	 * @param $sql
	 * @param $select_sql
	 * @param $from_sql
	 * @param $where_sql
	 * @param $sort
	 * @param string $pag_sql
	 *
	 * @return string
	 */
	function activity_privacy_sql_field( $sql, $select_sql, $from_sql, $where_sql, $sort, $pag_sql = '' ) {
		global $wpdb, $bp;

		if ( $this->can_use_rtm_ac_privacy() && is_rt_admin() ) {
			$rtmedia_activity_model = new RTMediaActivityModel();
			if ( strpos( $sql, $rtmedia_activity_model->table_name ) === false ) {
				$select_sql .= " ,{$this->rtm_activity_table_alias}.privacy ";
				$from_sql = $wpdb->prepare( " FROM {$bp->activity->table_name} a LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID LEFT JOIN {$rtmedia_activity_model->table_name} {$this->rtm_activity_table_alias} ON ( a.id = {$this->rtm_activity_table_alias}.activity_id and ra.blog_id = %d ) ", get_current_blog_id() ); // @codingStandardsIgnoreLine
				$sql      = "{$select_sql} {$from_sql} {$where_sql} ORDER BY a.date_recorded {$sort} {$pag_sql}";
			}
		}

		return $sql;
	}

	/**
	 * Check if activity privacy migration is done or not.
	 *
	 * @return bool|mixed|void
	 */
	function can_use_rtm_ac_privacy() {
		return rtmedia_get_site_option( 'rtmedia_activity_done_upgrade' );
	}
}
