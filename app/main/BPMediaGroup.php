<?php
/**
 * Description of BPMediaGroupLoader
 *
 * @author faishal
 */
if ( class_exists( 'BP_Group_Extension' ) ) :// Recommended, to prevent problems during upgrade or when Groups are disabled
	class BP_Media_Groups_Extension extends BP_Group_Extension {

		/**
		 * Constructor for the BP_Group_Extension adding values to the variables defined
		 *
		 * @uses global $bp
		 *
		 * @since BP Media 2.3
		 */
	    function __construct() {
			global $bp;
	        $this->name = BP_MEDIA_LABEL;
	        $this->slug = BP_MEDIA_SLUG;
	        $this->create_step_position = 21;
	        $this->nav_item_position = 31;
	    }

	    function create_screen() {
	        if ( !bp_is_group_creation_step( $this->slug ) )
	            return false;
	        ?>
			<h4>Album Creation Control</h4>
			<p>Who can create Albums in this group?</p>
			<div class="radio">
				<label>
					<input name="bp_album_creation_control" type="radio" id="bp_media_group_level_moderators" checked="checked" value="all">
					<strong>All Group Members</strong>
				</label>
				<label>
					<input name="bp_album_creation_control" type="radio" id="bp_media_group_level_moderators" value="moderators">
					<strong>Group Admins and Mods only</strong>
				</label>
				<label>
					<input name="bp_album_creation_control" type="radio" id="bp_media_group_level_admin" value="admin">
					<strong>Group Admin only</strong>
				</label>
			</div>

	        <?php
	        wp_nonce_field( 'groups_create_save_' . $this->slug );
	    }

	    function create_screen_save() {
	        global $bp;

	        check_admin_referer( 'groups_create_save_' . $this->slug );

	        /* Save any details submitted here */
			if(isset($_POST['bp_album_creation_control'])&&$_POST['bp_album_creation_control']!='')
				groups_update_groupmeta( $bp->groups->new_group_id, 'bp_media_group_control_level', $_POST['bp_album_creation_control'] );
	    }

	    function edit_screen() {
	        if ( !bp_is_group_admin_screen( $this->slug ) )
	            return false;
			$current_level = groups_get_groupmeta(bp_get_current_group_id(),'bp_media_group_control_level');
			?>

	        <h4>Album Creation Control</h4>
			<p>Who can create Albums in this group?</p>
			<div class="radio">
				<label>
					<input name="bp_album_creation_control" type="radio" id="bp_media_group_level_moderators"  value="all"<?php checked($current_level, 'all', true) ?>>
					<strong>All Group Members</strong>
				</label>
				<label>
					<input name="bp_album_creation_control" type="radio" id="bp_media_group_level_moderators" value="moderators" <?php checked($current_level, 'moderators', true) ?>>
					<strong>Group Admins and Mods only</strong>
				</label>
				<label>
					<input name="bp_album_creation_control" type="radio" id="bp_media_group_level_admin" value="admin" <?php checked($current_level, 'admin', true) ?>>
					<strong>Group Admin only</strong>
				</label>
			</div>
			<hr>
	        <input type="submit" name="save" value="Save Changes" />
	        <?php
	        wp_nonce_field( 'groups_edit_save_' . $this->slug );
	    }

	    function edit_screen_save() {
	        global $bp;

	        if ( !isset( $_POST['save'] ) )
	            return false;

	        check_admin_referer( 'groups_edit_save_' . $this->slug );

	        if(isset($_POST['bp_album_creation_control'])&&$_POST['bp_album_creation_control']!='')
				$success = groups_update_groupmeta(bp_get_current_group_id(), 'bp_media_group_control_level', $_POST['bp_album_creation_control'] );
			else
				$success = false;

	        /* To post an error/success message to the screen, use the following */
	        if ( !$success )
	            bp_core_add_message( __( 'There was an error saving, please try again', 'buddypress' ), 'error' );
	        else
	            bp_core_add_message( __( 'Settings saved successfully', 'buddypress' ) );

	        bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	    }

		/**
		 * The display method for the extension
		 *
		 * @since BP Media 2.3
		 */
	    function display() {
			BPMediaGroup::bp_media_groups_display_screen();
	    }


	    function widget_display() { ?>
	        <div class="info-group">
	            <h4><?php echo esc_attr( $this->name ) ?></h4>
	            <p>
	                You could display a small snippet of information from your group extension here. It will show on the group
	                home screen.
	            </p>
	        </div>
	        <?php
	    }
	}
endif; // class_exists( 'BP_Group_Extension' )
class BPMediaGroup {
	function __construct($initFlag=ture) {
		if($initFlag){
			if ( class_exists( 'BP_Group_Extension' ) ) :
				bp_register_group_extension( 'BP_Media_Groups_Extension' );
				/**
				 * This loop creates dummy classes for images, videos, audio and albums so that the url structuring
				 * can be uniform as it is in the members section.
				 */
				foreach(array('IMAGES','VIDEOS','AUDIO','ALBUMS') as $item){
					eval(' 					
						class BP_Media_Group_Extension_'.constant('BP_MEDIA_'.$item.'_SLUG').' extends BP_Group_Extension{
							var $enable_edit_item = false;
							var $enable_create_step = false;
							function __construct(){
								$this->name = BP_MEDIA_'.$item.'_LABEL;
								$this->slug = BP_MEDIA_'.$item.'_SLUG;
								$enable_create_step = false;
								$enable_edit_item = false;
							}
							function display(){BPMediaGroup::bp_media_groups_display_screen();}
							function widget_display(){}
						}
						bp_register_group_extension("BP_Media_Group_Extension_'.constant('BP_MEDIA_'.$item.'_SLUG').'" );
					');
				}
			endif;
			add_action('bp_actions',array($this,'bp_media_groups_custom_nav'),999);
			add_filter('bp_media_multipart_params_filter',array($this,'bp_media_groups_multipart_params_handler'));
		}
	}

	/**
	 * Handles the custom navigation structure of the BuddyPress Group Extension Media
	 *
	 * @uses global $bp
	 *
	 * @since BP Media 2.3
	 */
	function bp_media_groups_custom_nav(){
		global $bp;
		$current_group = isset($bp->groups->current_group->slug)?$bp->groups->current_group->slug:null;
		if(!$current_group)
			return;
		if(!(isset($bp->bp_options_nav[$current_group])&&  is_array($bp->bp_options_nav[$current_group])))
			return;

		/** This line might break a thing or two in custom themes and widgets */
		remove_filter('bp_activity_get_user_join_filter','bp_media_activity_query_filter',10);

		foreach ($bp->bp_options_nav[$current_group] as $key => $nav_item) {
			switch($nav_item['slug']){
				case BP_MEDIA_IMAGES_SLUG:
				case BP_MEDIA_VIDEOS_SLUG:
				case BP_MEDIA_AUDIO_SLUG:
				case BP_MEDIA_ALBUMS_SLUG:
					unset($bp->bp_options_nav[$current_group][$key]);

			}
			switch($bp->current_action){
				case BP_MEDIA_IMAGES_SLUG:
				case BP_MEDIA_VIDEOS_SLUG:
				case BP_MEDIA_AUDIO_SLUG:
				case BP_MEDIA_ALBUMS_SLUG:
					$count = count($bp->action_variables);
					for ($i = $count; $i > 0; $i--) {
						$bp->action_variables[$i] = $bp->action_variables[$i - 1];
					}
					$bp->action_variables[0] = $bp->current_action;
					$bp->current_action = BP_MEDIA_SLUG;
			}
		}
	}
	/**
	 * Adds the current group id as parameter for plupload
	 *
	 * @param Array $multipart_params Array of Multipart Parameters to be passed on to plupload script
	 *
	 * @since BP Media 2.3
	 */
	function bp_media_groups_multipart_params_handler($multipart_params){
		if(is_array($multipart_params)){
			global $bp;
			if(isset($bp->current_action)&&$bp->current_action==BP_MEDIA_SLUG
					&&isset($bp->action_variables)&&empty($bp->action_variables)
					&&isset($bp->current_component)&&$bp->current_component=='groups'
					&&isset($bp->groups->current_group->id)){
				$multipart_params['bp_media_group_id']=$bp->groups->current_group->id;
			}
		}
		return $multipart_params;
	}
	/**
	 * Displays the navigation available to the group media tab for the
	 * logged in user.
	 *
	 * @uses $bp Global Variable set by BuddyPress
	 *
	 * @since BP Media 2.3
	 */
	static function bp_media_groups_display_navigation_menu(){
		global $bp;

		if(!isset($bp->current_action)||$bp->current_action!=BP_MEDIA_SLUG)
			return false;
		$current_tab = BPMediaGroup::bp_media_groups_can_upload()?BP_MEDIA_UPLOAD_SLUG:BP_MEDIA_IMAGES_SLUG;
		if(isset($bp->action_variables[0])){
			$current_tab = $bp->action_variables[0];
		}

		if(BPMediaGroup::bp_media_groups_can_upload()){
			$bp_media_nav[BP_MEDIA_UPLOAD_SLUG] = array(
				'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).BP_MEDIA_SLUG,
				'label'	=>	BP_MEDIA_UPLOAD_LABEL,
			);
		}
		else{
			$bp_media_nav = array();
		}

		foreach(array('IMAGES','VIDEOS','AUDIO','ALBUMS') as $type){
			$bp_media_nav[constant('BP_MEDIA_' . $type . '_SLUG')] = array(
				'url'	=> trailingslashit(bp_get_group_permalink( $bp->groups->current_group )).constant('BP_MEDIA_' . $type . '_SLUG'),
				'label'	=>	constant('BP_MEDIA_' . $type . '_LABEL'),
			);
		}

		/** This variable will be used to display the tabs in group component */
		$bp_media_group_tabs = apply_filters('bp_media_group_tabs', $bp_media_nav,$current_tab);
		?>
		<div class="item-list-tabs no-ajax bp-media-group-navigation" id="subnav">
			<ul>
				<?php
					foreach($bp_media_group_tabs as $tab_slug=>$tab_info){
						echo '<li id="'.$tab_slug.'-group-li" '.($current_tab==$tab_slug?'class="current selected"':'').'><a id="'.$tab_slug.'" href="'.$tab_info['url'].'" title="'.$tab_info['label'].'">'.$tab_info['label'].'</a></li>';
					}
				?>
			</ul>
		</div>
		<?php
	}
	/**
	 * Checks whether the current logged in user has the ability to upload on
	 * the given group or not
	 *
	 * @since BP Media 2.3
	 */
	static function bp_media_groups_can_upload(){
		/** @todo Implementation Pending */
		global $bp;
		if(isset($bp->loggedin_user->id)&&is_numeric($bp->loggedin_user->id)){
			return groups_is_user_member($bp->loggedin_user->id, bp_get_current_group_id());
		}
		else{
			return false;
		}

		return true;
	}

	/**
	 * Adds the Media Settings menu for groups in the admin bar
	 *
	 * @uses global $bp,$wp_admin_bar
	 *
	 * @since BP Media 2.3
	 */
	function bp_media_groups_adminbar(){
		global $wp_admin_bar, $bp;
		$wp_admin_bar->add_menu( array(
			'parent' => $bp->group_admin_menu_id,
			'id'     => 'bp-media-group',
			'title'  => __( 'Media Settings', 'buddypress' ),
			'href'   =>  bp_get_groups_action_link( 'admin/media' )
		) );
	}
	//add_action('admin_bar_menu','bp_media_groups_adminbar',99);
	/* This will need some handling for checking if its a single group page or not, also whether the person can
	 * edit media settings or not
	 */

	/**
	 * Checks whether a user can create an album in the given group or not
	 *
	 * @param string $group_id The group id to check against
	 * @param string $user_id The user to be checked for permission
	 *
	 * @return boolean True if the user can create an album in the group, false if not
	 */
	static function bp_media_groups_user_can_create_album($group_id, $user_id = 0){
		if($user_id == 0)
			$user_id = get_current_user_id ();
		$current_level = groups_get_groupmeta($group_id,'bp_media_group_control_level');
		switch($current_level){
			case 'all':
				return groups_is_user_member($user_id, $group_id)||groups_is_user_mod($user_id, $group_id)||groups_is_user_admin($user_id, $group_id);
				break;
			case 'moderators':
				return groups_is_user_mod($user_id, $group_id)||groups_is_user_admin($user_id, $group_id);
				break;
			case 'admin':
				return groups_is_user_admin($user_id, $group_id);
				break;
			default :
				return groups_is_user_admin($user_id, $group_id);
		}
		return false;
	}
function bp_media_groups_display_screen() {
        global $bp_media_group_sub_nav, $bp;
        BPMediaGroupAction::bp_media_groups_set_query();
        BPMediaGroup::bp_media_groups_display_navigation_menu();
        if (bp_action_variable(0)) {
            switch (bp_action_variable(0)) {
                case BP_MEDIA_IMAGES_SLUG:
                    BPMediaGroup::bp_media_groups_images_screen();
                    break;
                case BP_MEDIA_VIDEOS_SLUG:
                    if (isset($bp->action_variables[1])) {
                        switch ($bp->action_variables[1]) {
                            case BP_MEDIA_VIDEOS_EDIT_SLUG:
                                //Edit screen for image
                                break;
                            case BP_MEDIA_DELETE_SLUG:
                                //Delete function for media file
                                break;
                            default:
                                if (intval(bp_action_variable(1)) > 0) {
                                    global $bp_media_current_entry;
                                    try {
                                        $bp_media_current_entry = new BP_Media_Host_Wordpress(bp_action_variable(1));
                                        if ($bp_media_current_entry->get_group_id() != bp_get_current_group_id())
                                            throw new Exception(__('Sorry, the requested media does not belong to the group'));
                                    } catch (Exception $e) {
                                        /** Error Handling when media not present or not belong to the group */
                                        echo '<div id="message" class="error">';
                                        echo '<p>' . $e->getMessage() . '</p>';
                                        echo '</div>';
                                        return;
                                    }
                                    bp_media_videos_entry_screen_content();
                                    break;
                                } else {
                                    /** @todo display 404 */
                                }
                        }
                    } else {
                        bp_media_videos_screen_content();
                    }
                    break;
                case BP_MEDIA_AUDIO_SLUG:
                    if (isset($bp->action_variables[1])) {
                        switch ($bp->action_variables[1]) {
                            case BP_MEDIA_AUDIO_EDIT_SLUG:
                                //Edit screen for image
                                break;
                            case BP_MEDIA_DELETE_SLUG:
                                //Delete function for media file
                                break;
                            default:
                                if (intval(bp_action_variable(1)) > 0) {
                                    global $bp_media_current_entry;
                                    try {
                                        $bp_media_current_entry = new BP_Media_Host_Wordpress(bp_action_variable(1));
                                        if ($bp_media_current_entry->get_group_id() != bp_get_current_group_id())
                                            throw new Exception(__('Sorry, the requested media does not belong to the group'));
                                    } catch (Exception $e) {
                                        /** Error Handling when media not present or not belong to the group */
                                        echo '<div id="message" class="error">';
                                        echo '<p>' . $e->getMessage() . '</p>';
                                        echo '</div>';
                                        return;
                                    }
                                    bp_media_audio_entry_screen_content();
                                    break;
                                } else {
                                    /** @todo display 404 */
                                }
                        }
                    } else {
                        bp_media_audio_screen_content();
                    }
                    break;
                case BP_MEDIA_ALBUMS_SLUG:
                    if (isset($bp->action_variables[1])) {
                        switch ($bp->action_variables[1]) {
                            case BP_MEDIA_ALBUMS_EDIT_SLUG:
                                //Edit screen for image
                                break;
                            case BP_MEDIA_DELETE_SLUG:
                                //Delete function for media file
                                break;
                            default:
                                if (intval(bp_action_variable(1)) > 0) {
                                    global $bp_media_current_album;
                                    try {
                                        $bp_media_current_album = new BP_Media_Host_Wordpress(bp_action_variable(1));
                                        if ($bp_media_current_album->get_group_id() != bp_get_current_group_id())
                                            throw new Exception(__('Sorry, the requested album does not belong to the group'));
                                    } catch (Exception $e) {
                                        /** Error Handling when media not present or not belong to the group */
                                        echo '<div id="message" class="error">';
                                        echo '<p>' . $e->getMessage() . '</p>';
                                        echo '</div>';
                                        return;
                                    }
                                    bp_media_albums_entry_screen_content();
                                    break;
                                } else {
                                    /** @todo display 404 */
                                }
                        }
                    } else {
                        BPMediaGroupAction::bp_media_groups_albums_set_query();
                        bp_media_albums_screen_content();
                    }
                    break;
                default:
                /** @todo Error is to be displayed for 404 */
            }
        } else {
            if (BPMediaGroup::bp_media_groups_can_upload())
                bp_media_upload_screen_content();
            else {
                $bp->action_variables[0] = BP_MEDIA_IMAGES_SLUG;
                BPMediaGroupAction::bp_media_groups_set_query();
                BPMediaGroup::bp_media_groups_images_screen();
            }
        }
    }

    static function bp_media_groups_images_screen() {
        global $bp_media_current_entry;
        if (bp_action_variable(1)) {
            switch (bp_action_variable(1)) {
                case BP_MEDIA_IMAGES_EDIT_SLUG:
                    //Edit screen for image
                    break;
                case BP_MEDIA_DELETE_SLUG:
                    //Delete function for media file
                    break;
                default:
                    if (intval(bp_action_variable(1)) > 0) {
                        global $bp_media_current_entry;
                        try {
                            $bp_media_current_entry = new BP_Media_Host_Wordpress(bp_action_variable(1));
                            if ($bp_media_current_entry->get_group_id() != bp_get_current_group_id())
                                throw new Exception(__('Sorry, the requested media does not belong to the group'));
                        } catch (Exception $e) {
                            /** Error Handling when media not present or not belong to the group */
                            echo '<div id="message" class="error">';
                            echo '<p>' . $e->getMessage() . '</p>';
                            echo '</div>';
                            return;
                        }
                        bp_media_images_entry_screen_content();
                        break;
                    } else {
                        /** @todo display 404 */
                    }
            }
        } else {
            bp_media_images_screen_content();
        }
    }
}