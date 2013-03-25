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
         * @global string BP_MEDIA_TXT_DOMAIN
         */

        /**
         *
         * @global BPMediaAddon $bp_media_addon
         */
        public function settings() {
            global $bp_media, $bp_media_addon;
            add_settings_section('bpm-settings', __('Enabled Media Types', BP_MEDIA_TXT_DOMAIN), is_multisite() ? array($this, 'network_notices') : '', 'bp-media-settings');
            add_settings_field('bpm-image', __('Photos', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'images_enabled',
                'desc' => __('Enable Photos', BP_MEDIA_TXT_DOMAIN)
            ));
            add_settings_field('bpm-video', __('Video', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'videos_enabled',
                'desc' => __('Enable Video (mp4)', BP_MEDIA_TXT_DOMAIN)
            ));
            add_settings_field('bpm-audio', __('Audio', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'audio_enabled',
                'desc' => __('Enable Audio (mp3)', BP_MEDIA_TXT_DOMAIN)
            ));
            if (bp_is_active('activity')) {
                add_settings_section('bpm-activity-upload', __('Activity Upload', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
                add_settings_field('bpm-activity', __('Activity Uploads', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-activity-upload', array(
                    'setting' => 'bp_media_options',
                    'option' => 'activity_upload',
                    'desc' => __('Enable Activity Uploading', BP_MEDIA_TXT_DOMAIN)
                        )
                );
            }
            
            add_settings_section('bpm-media-lightbox', __('Lightbox Integration', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
            add_settings_field('bpm-media-lightbox-option', __('Lightbox', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-lightbox', array(
                    'setting' => 'bp_media_options',
                    'option' => 'enable_lightbox',
                    'desc' => __('Enable Lighbox on Media', BP_MEDIA_TXT_DOMAIN)
                        )
                );
            
            if (bp_is_active('groups')) {
                add_settings_section('bpm-media-type', __('Groups Integration', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
//            add_settings_field('bpm-admin-profile', __('User profiles', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-type', array(
//                'setting' => 'bp_media_options',
//                'option' => 'enable_on_profile',
//                'desc' => __('Check to enable BuddyPress Media on User profiles', BP_MEDIA_TXT_DOMAIN)
//                    )
//            );
                add_settings_field('bpm-admin-group', __('Groups', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-type', array(
                    'setting' => 'bp_media_options',
                    'option' => 'enable_on_group',
                    'desc' => __('Allow Media in Groups', BP_MEDIA_TXT_DOMAIN)
                        )
                );
            }



            add_settings_section('bpm-media-fine', __('Display Settings', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
            add_settings_field('bpm-media-count', __('Number of media', BP_MEDIA_TXT_DOMAIN), array($this, 'textbox'), 'bp-media-settings', 'bpm-media-fine', array(
                'setting' => 'bp_media_options',
                'option' => 'default_count',
                'number' => true
            ));
            add_settings_field('bpm-download', __('Download Button', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-fine', array(
                'setting' => 'bp_media_options',
                'option' => 'download_enabled',
                'desc' => __('Display download button under media', BP_MEDIA_TXT_DOMAIN)
            ));

            if (BPMediaPrivacy::is_installed()) {
                add_settings_section('bpm-privacy', __('Privacy Settings', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
                add_settings_field('bpm-privacy-enabled', __('Enable Privacy', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-privacy', array(
                    'setting' => 'bp_media_options',
                    'option' => 'privacy_enabled',
                    'desc' => __('Enable privacy', BP_MEDIA_TXT_DOMAIN)
                ));

                $settings = array(
                    6 => __('<strong>Private</strong> - Visible only to the user', BP_MEDIA_TXT_DOMAIN),
                    4 => __('<strong>Friends</strong> - Visible to user\'s friends', BP_MEDIA_TXT_DOMAIN),
                    2 => __('<strong>Users</strong> - Visible to registered users', BP_MEDIA_TXT_DOMAIN),
                    0 => __('<strong>Public</strong> - Visible to the world', BP_MEDIA_TXT_DOMAIN)
                );
                if (!bp_is_active('friends')) {
                    unset($settings[4]);
                }
                add_settings_field('bpm-privacy-private-enabled', __('Default Privacy', BP_MEDIA_TXT_DOMAIN), array($this, 'radio'), 'bp-media-settings', 'bpm-privacy', array(
                    'setting' => 'bp_media_options',
                    'option' => 'default_privacy_level',
                    'radios' => $settings,
                    'default' => 0,
                ));
                add_settings_field('bpm-privacy-override-enabled', __('User Override', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-privacy', array(
                    'setting' => 'bp_media_options',
                    'option' => 'privacy_override_enabled',
                    'desc' => __('Allow users to override admin defaults (<em>Recommended</em>)', BP_MEDIA_TXT_DOMAIN)
                ));
            }
            add_settings_section('bpm-miscellaneous', __('Other Settings', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');

            add_settings_field('bpm-admin-bar-menu', __('Admin bar menu', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'setting' => 'bp_media_options',
                'option' => 'show_admin_menu',
                'desc' => __('Enable menu in WordPress admin bar', BP_MEDIA_TXT_DOMAIN)
                    )
            );
            add_settings_field('bpm-other-settings', __('Recount', BP_MEDIA_TXT_DOMAIN), array($this, 'button'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'option' => 'refresh-count',
                'name' => __('Recount', BP_MEDIA_TXT_DOMAIN),
                'desc' => '<br />'.__('Repair media counts', BP_MEDIA_TXT_DOMAIN)
            ));

            $bp_media_addon = new BPMediaAddon();
            add_settings_section('bpm-addons', __('BuddyPress Media Addons for Audio/Video Conversion', BP_MEDIA_TXT_DOMAIN), array($bp_media_addon, 'get_addons'), 'bp-media-addons');
            add_settings_section('bpm-support', __('Submit a request form', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-support');
            add_settings_field('bpm-request', __('Request Type', BP_MEDIA_TXT_DOMAIN), array($this, 'dropdown'), 'bp-media-support', 'bpm-support', array('option' => 'select-request', 'none' => false, 'values' => array(
                    '' => '-- ' . __('Select One', BP_MEDIA_TXT_DOMAIN) . ' --',
                    'premium_support' => __('Premium Support', BP_MEDIA_TXT_DOMAIN),
                    'new_feature' => __('Suggest a New Feature', BP_MEDIA_TXT_DOMAIN),
                    'bug_report' => __('Submit a Bug Report', BP_MEDIA_TXT_DOMAIN))
            ));
            if (!BPMediaPrivacy::is_installed()) {
                $bp_media_privacy = new BPMediaPrivacySettings();
                add_filter('bp_media_add_sub_tabs', array($bp_media_privacy, 'ui'), 99, 2);
                add_settings_section('bpm-privacy', __('Update Database', BP_MEDIA_TXT_DOMAIN), array($bp_media_privacy, 'init'), 'bp-media-privacy');
            }

            add_settings_section('bpm-convert-videos', '', array($this, 'convert_videos_form'), 'bp-media-convert-videos');

            register_setting('bp_media', 'bp_media_options', array($this, 'sanitize'));
        }

        public function convert_videos_form() {
            global $current_user;
            get_currentuserinfo();
            ?>
            <div id="video-transcoding-main-container">
                <h2>Survey</h2>
                <p class="para-blockquote">We are planning an encoding service where you can convert videos without having to install/configure anything on your server.</p>
                <h3>Would you be interested?</h3>
                <label><input class="interested" name="interested" type="radio" value="Yes" required="required" /> Yes</label>&nbsp;&nbsp;&nbsp;
                <label><input class="not-interested" name="interested" type="radio" value="No" required="required" /> No</label>
                <div class="interested-container hidden">
                    <p class="para-blockquote">Glad to see your interest.<br />
                        Please provide a little more information to help us plan this service better.</p>
                    <label><h3>Email</h3> <input class="email" type="email" name="email" size="35" value="<?php echo $current_user->user_email; ?>" placeholder="Email" /></label>

                    <h3>How would you use this feature?</h3>
                    <ul>
                        <li><label><input class="choice-free" type="radio" name="choice" value="Free" /> Free-only. I will use free-encoding quota only.</label></li>
                        <li><label><input type="radio" name="choice" value="$9" /> I am ready to pay $9 per month for generous encoding quota.</label></li>
                        <li><label><input type="radio" name="choice" value="$99" /> I am ready to pay $99 per month for unlimited video encoding!</label></li>
                </div>
                <input class="url" type="hidden" name="url" value="<?php echo home_url(); ?>" />
                <br />
                <br />
                <input class="button button-primary video-transcoding-survey" type="submit" value="Submit" />
            </div><?php
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
                        update_site_option('bpm-recount-success', __('Recounting of media files done successfully', BP_MEDIA_TXT_DOMAIN));
                    else
                        add_settings_error(__('Recount Success', BP_MEDIA_TXT_DOMAIN), 'bpm-recount-success', __('Recounting of media files done successfully', BP_MEDIA_TXT_DOMAIN), 'updated');
                } else {
                    if (is_multisite())
                        update_site_option('bpm-recount-fail', __('Recounting Failed', BP_MEDIA_TXT_DOMAIN));
                    else
                        add_settings_error(__('Recount Fail', BP_MEDIA_TXT_DOMAIN), 'bpm-recount-fail', __('Recounting Failed', BP_MEDIA_TXT_DOMAIN));
                }
            }
//            if (!isset($_POST['bp_media_options']['enable_on_profile']) && !isset($_POST['bp_media_options']['enable_on_group'])) {
//                if (is_multisite())
//                    update_site_option('bpm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', BP_MEDIA_TXT_DOMAIN));
//                else
//                    add_settings_error(__('Enable BuddyPress Media', BP_MEDIA_TXT_DOMAIN), 'bpm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', BP_MEDIA_TXT_DOMAIN));
//                $input['enable_on_profile'] = 1;
//            }
            if (!isset($_POST['bp_media_options']['videos_enabled']) && !isset($_POST['bp_media_options']['audio_enabled']) && !isset($_POST['bp_media_options']['images_enabled'])) {
                if (is_multisite())
                    update_site_option('bpm-media-type', __('Atleast one Media Type Must be selected', BP_MEDIA_TXT_DOMAIN));
                else
                    add_settings_error(__('Media Type', BP_MEDIA_TXT_DOMAIN), 'bpm-media-type', __('Atleast one Media Type Must be selected', BP_MEDIA_TXT_DOMAIN));
                $input['images_enabled'] = 1;
            }
            
            $input['default_count'] = intval($_POST['bp_media_options']['default_count']);
            if (!is_int($input['default_count']) || ($input['default_count'] < 0 ) || empty($input['default_count']) ) {
                if (is_multisite())
                    update_site_option('bpm-media-default-count', __('"Number of media" count value should be numeric and greater than 0.', BP_MEDIA_TXT_DOMAIN));
                else
                    add_settings_error(__('Default Count', BP_MEDIA_TXT_DOMAIN), 'bpm-media-default-count', __('"Number of media" count value should be numeric and greater than 0.', BP_MEDIA_TXT_DOMAIN));
                $input['default_count'] = 10;
            }
            if (is_multisite())
                update_site_option('bpm-settings-saved', __('Settings saved.', BP_MEDIA_TXT_DOMAIN));
            do_action('bp_media_sanitize_settings', $_POST, $input);
            return $input;
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
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' ) ', BP_MEDIA_TXT_DOMAIN));
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
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', BP_MEDIA_TXT_DOMAIN));
                if (2 > count($radios))
                    trigger_error(__('Need to specify atleast to radios else use a checkbox instead', BP_MEDIA_TXT_DOMAIN));
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
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', BP_MEDIA_TXT_DOMAIN));
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
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the following format array( \'option\' => \'option_name\' )', BP_MEDIA_TXT_DOMAIN));
                if (empty($values))
                    trigger_error(__('Please provide some values to populate the dropdown. Format : array( \'value\' => \'option\' )', BP_MEDIA_TXT_DOMAIN));
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
                    <option><?php _e('None', BP_MEDIA_TXT_DOMAIN); ?></option><?php
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
            if (BPMediaPrivacy::is_installed())
                return;
            $url = add_query_arg(
                    array('page' => 'bp-media-privacy'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php'))
            );

            $notice = '
				<div class="error">
				<p>' . __('BuddyPress Media 2.6 requires a database upgrade. ', BP_MEDIA_TXT_DOMAIN)
                    . '<a href="' . $url . '">' . __('Update Database', BP_MEDIA_TXT_DOMAIN) . '.</a></p>
				</div>
				';
            echo $notice;
        }

    }

}
    ?>
