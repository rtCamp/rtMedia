<?php
/**
 * Description of BPMediaSettings
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaSettings')) {

    class BPMediaSettings {

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
         * @global string 'buddypress-media'
         */

        /**
         *
         * @global BPMediaAddon $bp_media_addon
         */
        public function settings() {
            global $bp_media, $bp_media_addon, $wpdb;
            add_settings_section('bpm-settings', __('Enabled Media Types', 'buddypress-media'), is_multisite() ? array($this, 'allowed_types') : '', 'bp-media-settings');
            add_settings_field('bpm-image', __('Photos', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'images_enabled',
                'desc' => __('Enable Photos', 'buddypress-media')
            ));
            add_settings_field('bpm-video', __('Video', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'videos_enabled',
                'desc' => __('Enable Video (mp4)', 'buddypress-media')
            ));
            add_settings_field('bpm-audio', __('Audio', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'audio_enabled',
                'desc' => __('Enable Audio (mp3)', 'buddypress-media')
            ));
            
            add_settings_section('bpm-featured', __('Enable Featured Media', 'buddypress-media'), '', 'bp-media-settings');
            add_settings_field('bpm-featured-image', __('Photos', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-featured', array(
                'setting' => 'bp_media_options',
                'option' => 'featured_image',
                'desc' => __('Enable Featured Photos', 'buddypress-media')
            ));
            add_settings_field('bpm-featured-video', __('Video', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-featured', array(
                'setting' => 'bp_media_options',
                'option' => 'featured_video',
                'desc' => __('Enable Featured Video', 'buddypress-media')
            ));
            add_settings_field('bpm-featured-audio', __('Audio', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-featured', array(
                'setting' => 'bp_media_options',
                'option' => 'featured_audio',
                'desc' => __('Enable Featured Audio', 'buddypress-media')
            ));
            add_settings_field('bpm-featured-media-dimensions', __('Featured Media Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-featured', array(
                'setting' => 'bp_media_options',
                'type' => 'media',
                'size' => 'featured',
                'crop' => true
//                'desc' => __('Used in albums, sidebar media widget acitvity stream', 'buddypress-media')
            ));
            

            add_settings_section('bpm-image-settings', __('Image Settings', 'buddypress-media'), array($this, 'image_settings_intro'), 'bp-media-settings');
            add_settings_field('bpm-image-thumbnail', __('Thumbnail Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-image-settings', array(
                'type' => 'image',
                'size' => 'thumbnail',
                'crop' => true,
                'desc' => __('Used in albums, sidebar media widget acitvity stream', 'buddypress-media')
            ));
            add_settings_field('bpm-image-medium', __('Medium Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-image-settings', array(
                'type' => 'image',
                'size' => 'medium',
                'crop' => true,
                'desc' => __('Used in activity stream for single media uploads', 'buddypress-media')
            ));
            add_settings_field('bpm-image-large', __('Large Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-image-settings', array(
                'type' => 'image',
                'size' => 'large',
                'crop' => true,
                'desc' => __('Used in single media and thickbox', 'buddypress-media')
            ));

            add_settings_section('bpm-video-settings', __('Video Payer Settings', 'buddypress-media'), is_multisite() ? array($this, 'network_notices') : '', 'bp-media-settings');
            add_settings_field('bpm-video-medium', __('Activity Player Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-video-settings', array(
                'type' => 'video',
                'size' => 'medium'
            ));
            add_settings_field('bpm-video-large', __('Single Player Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-video-settings', array(
                'type' => 'video',
                'size' => 'large'
            ));

            add_settings_section('bpm-audio-settings', __('Audio Player Settings', 'buddypress-media'), is_multisite() ? array($this, 'network_notices') : '', 'bp-media-settings');
            add_settings_field('bpm-audio-medium', __('Activity Player Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-audio-settings', array(
                'type' => 'audio',
                'size' => 'medium',
                'height' => false
            ));
            add_settings_field('bpm-audio-large', __('Single Player Size', 'buddypress-media'), array($this, 'dimensions'), 'bp-media-settings', 'bpm-audio-settings', array(
                'type' => 'audio',
                'size' => 'large',
                'height' => false
            ));

            if (bp_is_active('activity')) {
                add_settings_section('bpm-activity-upload', __('Activity Upload', 'buddypress-media'), '', 'bp-media-settings');
                add_settings_field('bpm-activity', __('Activity Uploads', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-activity-upload', array(
                    'setting' => 'bp_media_options',
                    'option' => 'activity_upload',
                    'desc' => __('Enable Activity Uploading', 'buddypress-media')
                        )
                );
            }

            add_settings_section('bpm-media-lightbox', __('Lightbox Integration', 'buddypress-media'), '', 'bp-media-settings');
            add_settings_field('bpm-media-lightbox-option', __('Lightbox', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-lightbox', array(
                'setting' => 'bp_media_options',
                'option' => 'enable_lightbox',
                'desc' => __('Enable Lighbox on Media', 'buddypress-media')
                    )
            );

            if (bp_is_active('groups')) {
                add_settings_section('bpm-media-type', __('Groups Integration', 'buddypress-media'), '', 'bp-media-settings');
//            add_settings_field('bpm-admin-profile', __('User profiles', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-type', array(
//                'setting' => 'bp_media_options',
//                'option' => 'enable_on_profile',
//                'desc' => __('Check to enable BuddyPress Media on User profiles', 'buddypress-media')
//                    )
//            );
                add_settings_field('bpm-admin-group', __('Groups', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-type', array(
                    'setting' => 'bp_media_options',
                    'option' => 'enable_on_group',
                    'desc' => __('Allow Media in Groups', 'buddypress-media')
                        )
                );
            }

            add_settings_section('bpm-media-fine', __('Display Settings', 'buddypress-media'), '', 'bp-media-settings');
            add_settings_field('bpm-media-count', __('Number of media', 'buddypress-media'), array($this, 'textbox'), 'bp-media-settings', 'bpm-media-fine', array(
                'setting' => 'bp_media_options',
                'option' => 'default_count',
                'number' => true
            ));
            add_settings_field('bpm-download', __('Download Button', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-fine', array(
                'setting' => 'bp_media_options',
                'option' => 'download_enabled',
                'desc' => __('Display download button under media', 'buddypress-media')
            ));

            if (BPMediaPrivacy::is_installed()) {
                add_settings_section('bpm-privacy', __('Privacy Settings', 'buddypress-media'), '', 'bp-media-settings');
                add_settings_field('bpm-privacy-enabled', __('Enable Privacy', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-privacy', array(
                    'setting' => 'bp_media_options',
                    'option' => 'privacy_enabled',
                    'desc' => __('Enable privacy', 'buddypress-media')
                ));

                $settings = array(
                    6 => __('<strong>Private</strong> - Visible only to the user', 'buddypress-media'),
                    4 => __('<strong>Friends</strong> - Visible to user\'s friends', 'buddypress-media'),
                    2 => __('<strong>Users</strong> - Visible to registered users', 'buddypress-media'),
                    0 => __('<strong>Public</strong> - Visible to the world', 'buddypress-media')
                );
                if (!bp_is_active('friends')) {
                    unset($settings[4]);
                }
                add_settings_field('bpm-privacy-private-enabled', __('Default Privacy', 'buddypress-media'), array($this, 'radio'), 'bp-media-settings', 'bpm-privacy', array(
                    'setting' => 'bp_media_options',
                    'option' => 'default_privacy_level',
                    'radios' => $settings,
                    'default' => 0,
                ));
                add_settings_field('bpm-privacy-override-enabled', __('User Override', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-privacy', array(
                    'setting' => 'bp_media_options',
                    'option' => 'privacy_override_enabled',
                    'desc' => __('Allow users to override admin defaults (<em>Recommended</em>)', 'buddypress-media')
                ));
            }
            add_settings_section('bpm-miscellaneous', __('Other Settings', 'buddypress-media'), '', 'bp-media-settings');

            add_settings_field('bpm-admin-bar-menu', __('Admin bar menu', 'buddypress-media'), array($this, 'checkbox'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'setting' => 'bp_media_options',
                'option' => 'show_admin_menu',
                'desc' => __('Enable menu in WordPress admin bar', 'buddypress-media')
                    )
            );
            add_settings_field('bpm-other-settings', __('Recount', 'buddypress-media'), array($this, 'button'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'option' => 'refresh-count',
                'name' => __('Recount', 'buddypress-media'),
                'desc' => '<br />' . __('Repair media counts', 'buddypress-media')
            ));

            $bp_media_addon = new BPMediaAddon();
            add_settings_section('bpm-addons', __('BuddyPress Media Addons for Photos', 'buddypress-media'), array($bp_media_addon, 'get_addons'), 'bp-media-addons');
            
            add_settings_section('bpm-support', __('Support', 'buddypress-media'), array($this, 'bp_media_support_intro'), 'bp-media-support');

            if (!BPMediaPrivacy::is_installed()) {
                $bp_media_privacy = new BPMediaPrivacySettings();
                add_filter('bp_media_add_sub_tabs', array($bp_media_privacy, 'ui'), 99, 2);
                add_settings_section('bpm-privacy', __('Update Database', 'buddypress-media'), array($bp_media_privacy, 'init'), 'bp-media-privacy');
            }

            $bp_media_album_importer = new BPMediaAlbumimporter();
            add_settings_section('bpm-bp-album-importer', __('BP-Album Importer', 'buddypress-media'), array($bp_media_album_importer, 'ui'), 'bp-media-importer');
            register_setting('bp_media', 'bp_media_options', array($this, 'sanitize'));
        }

        public function network_notices() {
            $flag = 1;
            if (get_site_option('bpm-media-enable', false)) {
                echo '<div id="setting-error-bpm-media-enable" class="error"><p><strong>' . get_site_option('bpm-media-enable') . '</strong></p></div>';
                delete_site_option('bpm-media-enable');
                $flag = 0;
            }
            if (get_site_option('bpm-media-type', false)) {
                echo '<div id="setting-error-bpm-media-type" class="error"><p><strong>' . get_site_option('bpm-media-type') . '</strong></p></div>';
                delete_site_option('bpm-media-type');
                $flag = 0;
            }
            if (get_site_option('bpm-media-default-count', false)) {
                echo '<div id="setting-error-bpm-media-default-count" class="error"><p><strong>' . get_site_option('bpm-media-default-count') . '</strong></p></div>';
                delete_site_option('bpm-media-default-count');
                $flag = 0;
            }

            if (get_site_option('bpm-recount-success', false)) {
                echo '<div id="setting-error-bpm-recount-success" class="updated"><p><strong>' . get_site_option('bpm-recount-success') . '</strong></p></div>';
                delete_site_option('bpm-recount-success');
                $flag = 0;
            } elseif (get_site_option('bpm-recount-fail', false)) {
                echo '<div id="setting-error-bpm-recount-fail" class="error"><p><strong>' . get_site_option('bpm-recount-fail') . '</strong></p></div>';
                delete_site_option('bpm-recount-fail');
                $flag = 0;
            }

            if (get_site_option('bpm-settings-saved') && $flag) {
                echo '<div id="setting-error-bpm-settings-saved" class="updated"><p><strong>' . get_site_option('bpm-settings-saved') . '</strong></p></div>';
            }
            delete_site_option('bpm-settings-saved');
        }

        public function allowed_types() {
            $allowed_types = get_site_option('upload_filetypes', 'jpg jpeg png gif');
            $allowed_types = explode(' ', $allowed_types);
            $allowed_types = implode(', ', $allowed_types);
            echo '<span class="description">' . sprintf(__('Currently your network allows uploading of the following file types. You can change the settings <a href="%s">here</a>.<br /><code>%s</code></span>', 'buddypress-media'), network_admin_url('settings.php#upload_filetypes'), $allowed_types);
        }

        /**
         * Sanitizes the settings
         */

        /**
         *
         * @global type $bp_media_admin
         * @param type $input
         * @return type
         */
        public function sanitize($input) {
            global $bp_media_admin;
            if (isset($_POST['refresh-count'])) {
                if ($bp_media_admin->update_count()) {
                    if (is_multisite())
                        update_site_option('bpm-recount-success', __('Recounting of media files done successfully', 'buddypress-media'));
                    else
                        add_settings_error(__('Recount Success', 'buddypress-media'), 'bpm-recount-success', __('Recounting of media files done successfully', 'buddypress-media'), 'updated');
                } else {
                    if (is_multisite())
                        update_site_option('bpm-recount-fail', __('Recounting Failed', 'buddypress-media'));
                    else
                        add_settings_error(__('Recount Fail', 'buddypress-media'), 'bpm-recount-fail', __('Recounting Failed', 'buddypress-media'));
                }
            }
//            if (!isset($_POST['bp_media_options']['enable_on_profile']) && !isset($_POST['bp_media_options']['enable_on_group'])) {
//                if (is_multisite())
//                    update_site_option('bpm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', 'buddypress-media'));
//                else
//                    add_settings_error(__('Enable BuddyPress Media', 'buddypress-media'), 'bpm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', 'buddypress-media'));
//                $input['enable_on_profile'] = 1;
//            }
            if (!isset($_POST['bp_media_options']['videos_enabled']) && !isset($_POST['bp_media_options']['audio_enabled']) && !isset($_POST['bp_media_options']['images_enabled'])) {
                if (is_multisite())
                    update_site_option('bpm-media-type', __('Atleast one Media Type Must be selected', 'buddypress-media'));
                else
                    add_settings_error(__('Media Type', 'buddypress-media'), 'bpm-media-type', __('Atleast one Media Type Must be selected', 'buddypress-media'));
                $input['images_enabled'] = 1;
            }

            $input['default_count'] = intval($_POST['bp_media_options']['default_count']);
            if (!is_int($input['default_count']) || ($input['default_count'] < 0 ) || empty($input['default_count'])) {
                if (is_multisite())
                    update_site_option('bpm-media-default-count', __('"Number of media" count value should be numeric and greater than 0.', 'buddypress-media'));
                else
                    add_settings_error(__('Default Count', 'buddypress-media'), 'bpm-media-default-count', __('"Number of media" count value should be numeric and greater than 0.', 'buddypress-media'));
                $input['default_count'] = 10;
            }
            if (is_multisite())
                update_site_option('bpm-settings-saved', __('Settings saved.', 'buddypress-media'));
            do_action('bp_media_sanitize_settings', $_POST, $input);
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
            echo '<span class="description">' . sprintf(__('If you make changes to width, height or crop settings, you must use "<a href="%s">Regenerate Thumbnail Plugin</a>" to regenerate old images."', 'buddypress-media'), $regenerate_link) . '</span>';
        }

        /**
         * Output a checkbox
         *
         * @global array $bp_media
         * @param array $args
         */

        /**
         *
         * @global array $bp_media
         * @param type $args
         * @return type
         */
        public function checkbox($args) {
            global $bp_media;
            $options = $bp_media->options;
            $defaults = array(
                'setting' => '',
                'option' => '',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' ) ', 'buddypress-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = bp_get_option($setting);
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
         * @global array $bp_media
         * @param array $args
         */

        /**
         *
         * @global array $bp_media
         * @param type $args
         * @return type
         */
        public function radio($args) {
            global $bp_media;
            $options = $bp_media->options;
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
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'buddypress-media'));
                if (2 > count($radios))
                    trigger_error(__('Need to specify atleast to radios else use a checkbox instead', 'buddypress-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = bp_get_option($setting);
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
         * @global array $bp_media
         * @param array $args
         */

        /**
         *
         * @global array $bp_media
         * @param type $args
         * @return type
         */
        public function textbox($args) {
            global $bp_media;
            $options = $bp_media->options;
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
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'buddypress-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = bp_get_option($setting);
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
         * @global array $bp_media
         * @param type $args
         * @return type
         */
        public function dimensions($args) {
            global $bp_media;
            $defaults = array(
                'type' => 'image',
                'size' => 'thumbnail',
                'height' => true,
                'crop' => false,
                'desc' => ''
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);

            $options = bp_get_option('bp_media_options');

            $w = $options['sizes'][$type][$size]['width'];
            if ($height) {
                $h = $options['sizes'][$type][$size]['height'];
            }
            if ($crop) {
                $c = $options['sizes'][$type][$size]['crop'];
            }
                ?>
            <label for="<?php echo sanitize_title("{$type}_{$size}_w"); ?>"><?php _e('Width', 'buddypress-media'); ?> <input value="<?php echo $w; ?>" name="<?php echo "bp_media_options[sizes][$type][$size][width]"; ?>" id="<?php echo sanitize_title("{$type}_{$size}_w"); ?>" type="number" class="small-text" /></label>
            <?php if ($height) { ?><label for="<?php echo sanitize_title("{$type}_{$size}_h"); ?>"><?php _e('Height', 'buddypress-media'); ?> <input value="<?php echo $h; ?>" name="<?php echo "bp_media_options[sizes][$type][$size][height]"; ?>" id="<?php echo sanitize_title("{$type}_{$size}_h"); ?>" type="number" class="small-text" /></label> <?php } ?>
            <?php if ($crop) { ?><label for="<?php echo sanitize_title("{$type}_{$size}_c"); ?>"> <input value="1"<?php checked($c ? $c : 0, 1); ?> name="<?php echo "bp_media_options[sizes][$type][$size][crop]"; ?>" id="<?php echo sanitize_title("{$type}_{$size}_c"); ?>" type="checkbox" /> <?php _e('Crop', 'buddypress-media'); ?></label><?php } ?>
            <?php if ($desc) { ?><br /><span class="description"><?php echo $desc; ?></span><?php
            }
        }

        /**
         * Outputs Dropdown
         *
         * @global array $bp_media
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
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', 'buddypress-media'));
                if (empty($values))
                    trigger_error(__('Please provide some values to populate the dropdown. Format : array( \'value\' => \'option\' )', 'buddypress-media'));
                return;
            }

            if (!empty($setting)) {
                $name = $setting . '[' . $option . ']';
                $options = bp_get_option($setting);
            } else
                $name = $option;

            if ((isset($options[$option]) && empty($options[$option])) || !isset($options[$option])) {
                $options[$option] = '';
            }
            ?>
            <select name="<?php echo $name; ?>" id="<?php echo $option; ?>"><?php if ($none) { ?>
                    <option><?php _e('None', 'buddypress-media'); ?></option><?php
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
         * @global array $bp_media
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
                if (BPMediaPrivacy::is_installed())
                    return;
                $url = add_query_arg(
                        array('page' => 'bp-media-privacy'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php'))
                );

                $notice = '
				<div class="error">
				<p>' . __('BuddyPress Media 2.6 requires a database upgrade. ', 'buddypress-media')
                        . '<a href="' . $url . '">' . __('Update Database', 'buddypress-media') . '.</a></p>
				</div>
				';
                echo $notice;
            }
        }

        public function bp_media_support_intro() {
            echo '<p>' . __('If your site has some issues due to BuddyPress Media and you want one on one support then you can create a support topic on the <a target="_blank" href="http://rtcamp.com/groups/buddypress-media/forum/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">rtCamp Support Forum</a>.', 'buddypress-media') . '</p>';
            echo '<p>' . __('If you have any suggestions, enhancements or bug reports, then you can open a new issue on <a target="_blank" href="https://github.com/rtCamp/buddypress-media/issues/new">GitHub</a>.', 'buddypress-media') . '</p>';
        }
        
    }

}
?>
