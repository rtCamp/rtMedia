<?php
/**
 * Description of RTMediaSettings
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('RTMediaSettings')) {

    class RTMediaSettings {

        public function __construct() {
            add_action('admin_init', array($this, 'settings'));
            if (is_multisite()) {
                add_action('network_admin_notices', array($this, 'privacy_notice'));
            } else {
                add_action('admin_notices', array($this, 'privacy_notice'));
            }
        }

        /**
         * Register Settings
         *
         * @global string 'rt-media'
         */

        /**
         *
         * @global BPMediaAddon $rt_media_addon
         */
        public function settings() {
            global $rt_media, $rt_media_addon, $wpdb;
            add_settings_section('rtm-settings', __('Enabled Media Types', 'rt-media'), is_multisite() ? array($this, 'allowed_types') : '', 'rt-media-settings');
            add_settings_field('rtm-image', __('Photos', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-settings', array(
                'setting' => 'rt_media_options',
                'option' => 'images_enabled',
                'desc' => __('Enable Photos', 'rt-media')
            ));
            add_settings_field('rtm-video', __('Video', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-settings', array(
                'setting' => 'rt_media_options',
                'option' => 'videos_enabled',
                'desc' => __('Enable Video (mp4)', 'rt-media')
            ));
            add_settings_field('rtm-audio', __('Audio', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-settings', array(
                'setting' => 'rt_media_options',
                'option' => 'audio_enabled',
                'desc' => __('Enable Audio (mp3)', 'rt-media')
            ));

            add_settings_section('rtm-featured', __('Enable Featured Media', 'rt-media'), '', 'rt-media-settings');
            add_settings_field('rtm-featured-image', __('Photos', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-featured', array(
                'setting' => 'rt_media_options',
                'option' => 'featured_image',
                'desc' => __('Enable Featured Photos', 'rt-media')
            ));
            add_settings_field('rtm-featured-video', __('Video', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-featured', array(
                'setting' => 'rt_media_options',
                'option' => 'featured_video',
                'desc' => __('Enable Featured Video', 'rt-media')
            ));
            add_settings_field('rtm-featured-audio', __('Audio', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-featured', array(
                'setting' => 'rt_media_options',
                'option' => 'featured_audio',
                'desc' => __('Enable Featured Audio', 'rt-media')
            ));
            add_settings_field('rtm-featured-media-dimensions', __('Featured Media Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-featured', array(
                'setting' => 'rt_media_options',
                'type' => 'media',
                'size' => 'featured',
                'crop' => true
//                'desc' => __('Used in albums, sidebar media widget acitvity stream', 'rt-media')
            ));


            add_settings_section('rtm-image-settings', __('Image Settings', 'rt-media'), array($this, 'image_settings_intro'), 'rt-media-settings');
            add_settings_field('rtm-image-thumbnail', __('Thumbnail Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-image-settings', array(
                'type' => 'image',
                'size' => 'thumbnail',
                'crop' => true,
                'desc' => __('Used in albums, sidebar media widget acitvity stream', 'rt-media')
            ));
            add_settings_field('rtm-image-medium', __('Medium Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-image-settings', array(
                'type' => 'image',
                'size' => 'medium',
                'crop' => true,
                'desc' => __('Used in activity stream for single media uploads', 'rt-media')
            ));
            add_settings_field('rtm-image-large', __('Large Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-image-settings', array(
                'type' => 'image',
                'size' => 'large',
                'crop' => true,
                'desc' => __('Used in single media and thickbox', 'rt-media')
            ));

            add_settings_section('rtm-video-settings', __('Video Payer Settings', 'rt-media'), is_multisite() ? array($this, 'network_notices') : '', 'rt-media-settings');
            add_settings_field('rtm-video-medium', __('Activity Player Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-video-settings', array(
                'type' => 'video',
                'size' => 'medium'
            ));
            add_settings_field('rtm-video-large', __('Single Player Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-video-settings', array(
                'type' => 'video',
                'size' => 'large'
            ));

            add_settings_section('rtm-audio-settings', __('Audio Player Settings', 'rt-media'), is_multisite() ? array($this, 'network_notices') : '', 'rt-media-settings');
            add_settings_field('rtm-audio-medium', __('Activity Player Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-audio-settings', array(
                'type' => 'audio',
                'size' => 'medium',
                'height' => false
            ));
            add_settings_field('rtm-audio-large', __('Single Player Size', 'rt-media'), array('RTMediaFormHandler', 'dimensions'), 'rt-media-settings', 'rtm-audio-settings', array(
                'type' => 'audio',
                'size' => 'large',
                'height' => false
            ));

            if (bp_is_active('activity')) {
                add_settings_section('rtm-activity-upload', __('Activity Upload', 'rt-media'), '', 'rt-media-settings');
                add_settings_field('rtm-activity', __('Activity Uploads', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-activity-upload', array(
                    'setting' => 'rt_media_options',
                    'option' => 'activity_upload',
                    'desc' => __('Enable Activity Uploading', 'rt-media')
                        )
                );
            }

            add_settings_section('rtm-media-lightbox', __('Lightbox Integration', 'rt-media'), '', 'rt-media-settings');
            add_settings_field('rtm-media-lightbox-option', __('Lightbox', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-media-lightbox', array(
                'setting' => 'rt_media_options',
                'option' => 'enable_lightbox',
                'desc' => __('Enable Lighbox on Media', 'rt-media')
                    )
            );

            if (bp_is_active('groups')) {
                add_settings_section('rtm-media-type', __('Groups Integration', 'rt-media'), '', 'rt-media-settings');
//            add_settings_field('rtm-admin-profile', __('User profiles', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-media-type', array(
//                'setting' => 'rt_media_options',
//                'option' => 'enable_on_profile',
//                'desc' => __('Check to enable BuddyPress Media on User profiles', 'rt-media')
//                    )
//            );
                add_settings_field('rtm-admin-group', __('Groups', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-media-type', array(
                    'setting' => 'rt_media_options',
                    'option' => 'enable_on_group',
                    'desc' => __('Allow Media in Groups', 'rt-media')
                        )
                );
            }

            add_settings_section('rtm-media-fine', __('Display Settings', 'rt-media'), '', 'rt-media-settings');
            add_settings_field('rtm-media-count', __('Number of media', 'rt-media'), array('RTMediaFormHandler', 'number'), 'rt-media-settings', 'rtm-media-fine', array(
                'setting' => 'rt_media_options',
                'option' => 'default_count',
                'number' => true
            ));
            add_settings_field('rtm-download', __('Download Button', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-media-fine', array(
                'setting' => 'rt_media_options',
                'option' => 'download_enabled',
                'desc' => __('Display download button under media', 'rt-media')
            ));

//            if (BPMediaPrivacy::is_installed()) {
                add_settings_section('rtm-privacy', __('Privacy Settings', 'rt-media'), '', 'rt-media-settings');
                add_settings_field('rtm-privacy-enabled', __('Enable Privacy', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-privacy', array(
                    'setting' => 'rt_media_options',
                    'option' => 'privacy_enabled',
                    'desc' => __('Enable privacy', 'rt-media')
                ));

                $settings = array(
                    6 => __('<strong>Private</strong> - Visible only to the user', 'rt-media'),
                    4 => __('<strong>Friends</strong> - Visible to user\'s friends', 'rt-media'),
                    2 => __('<strong>Users</strong> - Visible to registered users', 'rt-media'),
                    0 => __('<strong>Public</strong> - Visible to the world', 'rt-media')
                );
                if (!bp_is_active('friends')) {
                    unset($settings[4]);
                }
                add_settings_field('rtm-privacy-private-enabled', __('Default Privacy', 'rt-media'), array('RTMediaFormHandler', 'radio'), 'rt-media-settings', 'rtm-privacy', array(
                    'setting' => 'rt_media_options',
                    'option' => 'default_privacy_level',
                    'radios' => $settings,
                    'default' => 0,
                ));
                add_settings_field('rtm-privacy-override-enabled', __('User Override', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-privacy', array(
                    'setting' => 'rt_media_options',
                    'option' => 'privacy_override_enabled',
                    'desc' => __('Allow users to override admin defaults (<em>Recommended</em>)', 'rt-media')
                ));
//            }
            add_settings_section('rtm-miscellaneous', __('Other Settings', 'rt-media'), '', 'rt-media-settings');

            add_settings_field('rtm-admin-bar-menu', __('Admin bar menu', 'rt-media'), array('RTMediaFormHandler', 'checkbox'), 'rt-media-settings', 'rtm-miscellaneous', array(
                'setting' => 'rt_media_options',
                'option' => 'show_admin_menu',
                'desc' => __('Enable menu in WordPress admin bar', 'rt-media')
                    )
            );
            add_settings_field('rtm-other-settings', __('Recount', 'rt-media'), array($this, 'button'), 'rt-media-settings', 'rtm-miscellaneous', array(
                'option' => 'refresh-count',
                'name' => __('Recount', 'rt-media'),
                'desc' => '<br />' . __('Repair media counts', 'rt-media')
            ));

            $rt_media_addon = new RTMediaAddon();
            add_settings_section('rtm-addons', __('BuddyPress Media Addons for Photos', 'rt-media'), array($rt_media_addon, 'get_addons'), 'rt-media-addons');

            add_settings_section('rtm-support', __('Support', 'rt-media'), array($this, 'rt_media_support_intro'), 'rt-media-support');

//            if (!BPMediaPrivacy::is_installed()) {
//                $rt_media_privacy = new BPMediaPrivacySettings();
//                add_filter('rt_media_add_sub_tabs', array($rt_media_privacy, 'ui'), 99, 2);
//                add_settings_section('rtm-privacy', __('Update Database', 'rt-media'), array($rt_media_privacy, 'init'), 'rt-media-privacy');
//            }

            $rt_media_album_importer = new BPMediaAlbumimporter();
            add_settings_section('rtm-rt-album-importer', __('BP-Album Importer', 'rt-media'), array($rt_media_album_importer, 'ui'), 'rt-media-importer');
            register_setting('rt_media', 'rt_media_options', array($this, 'sanitize'));
        }

        public function network_notices() {
            $flag = 1;
            if (get_site_option('rtm-media-enable', false)) {
                echo '<div id="setting-error-bpm-media-enable" class="error"><p><strong>' . get_site_option('rtm-media-enable') . '</strong></p></div>';
                delete_site_option('rtm-media-enable');
                $flag = 0;
            }
            if (get_site_option('rtm-media-type', false)) {
                echo '<div id="setting-error-bpm-media-type" class="error"><p><strong>' . get_site_option('rtm-media-type') . '</strong></p></div>';
                delete_site_option('rtm-media-type');
                $flag = 0;
            }
            if (get_site_option('rtm-media-default-count', false)) {
                echo '<div id="setting-error-bpm-media-default-count" class="error"><p><strong>' . get_site_option('rtm-media-default-count') . '</strong></p></div>';
                delete_site_option('rtm-media-default-count');
                $flag = 0;
            }

            if (get_site_option('rtm-recount-success', false)) {
                echo '<div id="setting-error-bpm-recount-success" class="updated"><p><strong>' . get_site_option('rtm-recount-success') . '</strong></p></div>';
                delete_site_option('rtm-recount-success');
                $flag = 0;
            } elseif (get_site_option('rtm-recount-fail', false)) {
                echo '<div id="setting-error-bpm-recount-fail" class="error"><p><strong>' . get_site_option('rtm-recount-fail') . '</strong></p></div>';
                delete_site_option('rtm-recount-fail');
                $flag = 0;
            }

            if (get_site_option('rtm-settings-saved') && $flag) {
                echo '<div id="setting-error-bpm-settings-saved" class="updated"><p><strong>' . get_site_option('rtm-settings-saved') . '</strong></p></div>';
            }
            delete_site_option('rtm-settings-saved');
        }

        public function allowed_types() {
            $allowed_types = get_site_option('upload_filetypes', 'jpg jpeg png gif');
            $allowed_types = explode(' ', $allowed_types);
            $allowed_types = implode(', ', $allowed_types);
            echo '<span class="description">' . sprintf(__('Currently your network allows uploading of the following file types. You can change the settings <a href="%s">here</a>.<br /><code>%s</code></span>', 'rt-media'), network_admin_url('settings.php#upload_filetypes'), $allowed_types);
        }

        /**
         * Sanitizes the settings
         */

        /**
         *
         * @global type $rt_media_admin
         * @param type $input
         * @return type
         */
        public function sanitize($input) {
            global $rt_media_admin;
            if (isset($_POST['refresh-count'])) {
                if ($rt_media_admin->update_count()) {
                    if (is_multisite())
                        update_site_option('rtm-recount-success', __('Recounting of media files done successfully', 'rt-media'));
                    else
                        add_settings_error(__('Recount Success', 'rt-media'), 'rtm-recount-success', __('Recounting of media files done successfully', 'rt-media'), 'updated');
                } else {
                    if (is_multisite())
                        update_site_option('rtm-recount-fail', __('Recounting Failed', 'rt-media'));
                    else
                        add_settings_error(__('Recount Fail', 'rt-media'), 'rtm-recount-fail', __('Recounting Failed', 'rt-media'));
                }
            }
//            if (!isset($_POST['rt_media_options']['enable_on_profile']) && !isset($_POST['rt_media_options']['enable_on_group'])) {
//                if (is_multisite())
//                    update_site_option('rtm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', 'rt-media'));
//                else
//                    add_settings_error(__('Enable BuddyPress Media', 'rt-media'), 'rtm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', 'rt-media'));
//                $input['enable_on_profile'] = 1;
//            }
            if (!isset($_POST['rt_media_options']['videos_enabled']) && !isset($_POST['rt_media_options']['audio_enabled']) && !isset($_POST['rt_media_options']['images_enabled'])) {
                if (is_multisite())
                    update_site_option('rtm-media-type', __('Atleast one Media Type Must be selected', 'rt-media'));
                else
                    add_settings_error(__('Media Type', 'rt-media'), 'rtm-media-type', __('Atleast one Media Type Must be selected', 'rt-media'));
                $input['images_enabled'] = 1;
            }

            $input['default_count'] = intval($_POST['rt_media_options']['default_count']);
            if (!is_int($input['default_count']) || ($input['default_count'] < 0 ) || empty($input['default_count'])) {
                if (is_multisite())
                    update_site_option('rtm-media-default-count', __('"Number of media" count value should be numeric and greater than 0.', 'rt-media'));
                else
                    add_settings_error(__('Default Count', 'rt-media'), 'rtm-media-default-count', __('"Number of media" count value should be numeric and greater than 0.', 'rt-media'));
                $input['default_count'] = 10;
            }
            if (is_multisite())
                update_site_option('rtm-settings-saved', __('Settings saved.', 'rt-media'));
            do_action('rt_media_sanitize_settings', $_POST, $input);
            return $input;
        }

        public function image_settings_intro() {
            if (is_plugin_active('regenerate-thumbnails/regenerate-thumbnails.php')) {
                $regenerate_link = admin_url('/tools.php?page=regenerate-thumbnails');
            } elseif (array_key_exists('regenerate-thumbnails/regenerate-thumbnails.php', get_plugins())) {
                $regenerate_link = admin_url('/plugins.php#regenerate-thumbnails');
            } else {
                $regenerate_link = wp_nonce_url(admin_url('update.php?action=install-plugin&plugin=regenerate-thumbnails'), 'install-plugin_regenerate-thumbnails');
            }
            echo '<span class="description">' . sprintf(__('If you make changes to width, height or crop settings, you must use "<a href="%s">Regenerate Thumbnail Plugin</a>" to regenerate old images."', 'rt-media'), $regenerate_link) . '</span>';
			echo '<div class="clearfix">&nbsp;</div>';
        }

        /**
         * Output a checkbox
         *
         * @global array $rt_media
         * @param array $args
         */

        /**
         *
         * @global array $rt_media
         * @param type $args
         * @return type
         */
        public function checkbox($args) {
            global $rt_media;
            $options = $rt_media->options;
            $defaults = array(
                'setting' => '',
                'option' => '',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' ) ', 'rt-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = get_site_option($setting);
            } else
                $name = $option;

            if (!isset($options[$option]))
                $options[$option] = '';
            ?>
            <label for="<?php echo $option; ?>">
                <input<?php checked($options[$option]); ?> name="<?php echo $name; ?>" id="<?php echo $option; ?>" value="1" type="checkbox" />
                <?php echo $desc; ?>
            </label><?php
        }

        /**
         * Outputs Radio Buttons
         *
         * @global array $rt_media
         * @param array $args
         */

        /**
         *
         * @global array $rt_media
         * @param type $args
         * @return type
         */
        public function radio($args) {
            global $rt_media;
            $options = $rt_media->options;
            $defaults = array(
                'setting' => '',
                'option' => '',
                'radios' => array(),
                'default' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option) || ( 2 > count($radios) )) {
                if (empty($option))
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'rt-media'));
                if (2 > count($radios))
                    trigger_error(__('Need to specify atleast to radios else use a checkbox instead', 'rt-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = get_site_option($setting);
            } else
                $name = $option;

            if ((isset($options[$option]) && empty($options[$option])) || !isset($options[$option])) {
                $options[$option] = $default;
            }

            foreach ($radios as $value => $desc) {
                    ?>
                <label for="<?php echo sanitize_title($desc); ?>"><input<?php checked($options[$option], $value); ?> value="<?php echo $value; ?>" name="<?php echo $name; ?>" id="<?php echo sanitize_title($desc); ?>" type="radio" />&nbsp;<?php echo $desc; ?></label><br /><?php
            }
        }

        /**
         * Outputs Textbox
         *
         * @global array $rt_media
         * @param array $args
         */

        /**
         *
         * @global array $rt_media
         * @param type $args
         * @return type
         */
        public function textbox($args) {
            global $rt_media;
            $options = $rt_media->options;
            $defaults = array(
                'setting' => '',
                'option' => '',
                'desc' => '',
                'password' => false,
                'hidden' => false,
                'number' => false,
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'rt-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = get_site_option($setting);
            } else
                $name = $option;

            if ((isset($options[$option]) && empty($options[$option])) || !isset($options[$option])) {
                $options[$option] = '';
            }
                ?>
            <label for="<?php echo sanitize_title($option); ?>"><input value="<?php echo $options[$option]; ?>" name="<?php echo $name; ?>" id="<?php echo sanitize_title($option); ?>" type="<?php echo $password ? 'password' : ($hidden ? 'hidden' : ($number ? 'number' : 'text')); ?>" /><?php
            if (!empty($desc)) {
                echo '<br /><span class="description">' . $desc . '</span>';
            }
                ?></label><br /><?php
        }

        /**
         *
         * @global array $rt_media
         * @param type $args
         * @return type
         */
        public function dimensions($args) {
            global $rt_media;
            $defaults = array(
                'type' => 'image',
                'size' => 'thumbnail',
                'height' => true,
                'crop' => false,
                'desc' => ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);

            $options = get_site_option('rt_media_options');

            $w = $options['sizes'][$type][$size]['width'];
            if ($height) {
                $h = $options['sizes'][$type][$size]['height'];
            }
            if ($crop) {
                $c = $options['sizes'][$type][$size]['crop'];
            }
                ?>
            <label for="<?php echo sanitize_title("{$type}_{$size}_w"); ?>"><?php _e('Width', 'rt-media'); ?> <input value="<?php echo $w; ?>" name="<?php echo "rt_media_options[sizes][$type][$size][width]"; ?>" id="<?php echo sanitize_title("{$type}_{$size}_w"); ?>" type="number" class="small-text" /></label>
            <?php if ($height) { ?><label for="<?php echo sanitize_title("{$type}_{$size}_h"); ?>"><?php _e('Height', 'rt-media'); ?> <input value="<?php echo $h; ?>" name="<?php echo "rt_media_options[sizes][$type][$size][height]"; ?>" id="<?php echo sanitize_title("{$type}_{$size}_h"); ?>" type="number" class="small-text" /></label> <?php } ?>
            <?php if ($crop) { ?><label for="<?php echo sanitize_title("{$type}_{$size}_c"); ?>"> <input value="1"<?php checked($c ? $c : 0, 1); ?> name="<?php echo "rt_media_options[sizes][$type][$size][crop]"; ?>" id="<?php echo sanitize_title("{$type}_{$size}_c"); ?>" type="checkbox" /> <?php _e('Crop', 'rt-media'); ?></label><?php } ?>
            <?php if ($desc) { ?><br /><span class="description"><?php echo $desc; ?></span><?php
            }
        }

        /**
         * Outputs Dropdown
         *
         * @global array $rt_media
         * @param array $args
         */

        /**
         *
         * @param type $args
         * @return type
         */
        public function dropdown($args) {
            $defaults = array(
                'setting' => '',
                'option' => '',
                'none' => true,
                'values' => ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option) || empty($values)) {
                if (empty($option))
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'rt-media'));
                if (empty($values))
                    trigger_error(__('Please provide some values to populate the dropdown. Format : array( \'value\' => \'option\' )', 'rt-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = get_site_option($setting);
            } else
                $name = $option;

            if ((isset($options[$option]) && empty($options[$option])) || !isset($options[$option])) {
                $options[$option] = '';
            }
            ?>
            <select name="<?php echo $name; ?>" id="<?php echo $option; ?>"><?php if ($none) { ?>
                    <option><?php _e('None', 'rt-media'); ?></option><?php
            }
            foreach ($values as $value => $text) {
                ?>
                    <option<?php selected($options[$option], $value); ?> value="<?php echo $value; ?>"><?php echo $text; ?></option><?php }
            ?>
            </select><?php
        }

        /**
         * Outputs a Button
         *
         * @global array $rt_media
         * @param array $args
         */

        /**
         *
         * @param type $args
         * @return type
         */
        public function button($args) {
            $defaults = array(
                'setting' => '',
                'option' => '',
                'name' => 'Save Changes',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error('Please provide "option" value ( Required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\', \'link\' => \'linkurl\' )');
                return;
            }
            if (!empty($setting)) {
                $button = $setting . '[' . $option . ']';
            } else
                $button = $option;
            submit_button($name, '', $button, false);
            if (!empty($desc)) {
                ?>
                <span class="description"><?php echo $desc; ?></a><?php
            }
        }

        public function privacy_notice() {
            if (current_user_can('create_users')) {
//                if (BPMediaPrivacy::is_installed())
//                    return;
                $url = add_query_arg(
                        array('page' => 'rt-media-privacy'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php'))
                );

                $notice = '
				<div class="error">
				<p>' . __('BuddyPress Media 2.6 requires a database upgrade. ', 'rt-media')
                        . '<a href="' . $url . '">' . __('Update Database', 'rt-media') . '.</a></p>
				</div>
				';
                echo $notice;
            }
        }

        public function rt_media_support_intro() {
            echo '<p>' . __('If your site has some issues due to BuddyPress Media and you want one on one support then you can create a support topic on the <a target="_blank" href="http://rtcamp.com/groups/buddypress-media/forum/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">rtCamp Support Forum</a>.', 'rt-media') . '</p>';
            echo '<p>' . __('If you have any suggestions, enhancements or bug reports, then you can open a new issue on <a target="_blank" href="https://github.com/rtCamp/buddypress-media/issues/new">GitHub</a>.', 'rt-media') . '</p>';
        }

    }

}
?>
