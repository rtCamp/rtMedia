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
            global $bp_media_addon;
            add_settings_section('bpm-media-type', __('Enable BuddyPress Media on', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
//            add_settings_field('bpm-admin-profile', __('User profiles', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-type', array(
//                'setting' => 'bp_media_options',
//                'option' => 'enable_on_profile',
//                'desc' => __('Check to enable BuddyPress Media on User profiles', BP_MEDIA_TXT_DOMAIN)
//                    )
//            );
            add_settings_field('bpm-admin-group', __('Groups', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-media-type', array(
                'setting' => 'bp_media_options',
                'option' => 'enable_on_group',
                'desc' => __('Check to enable BuddyPress Media in Groups', BP_MEDIA_TXT_DOMAIN)
                    )
            );

            add_settings_section('bpm-settings', __('Enable Media Types on', BP_MEDIA_TXT_DOMAIN), is_multisite() ? array($this, 'network_notices') : '', 'bp-media-settings');
            add_settings_field('bpm-video', __('Video', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'videos_enabled',
                'desc' => __('Check to enable video upload functionality', BP_MEDIA_TXT_DOMAIN)
            ));
            add_settings_field('bpm-audio', __('Audio', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'audio_enabled',
                'desc' => __('Check to enable audio upload functionality', BP_MEDIA_TXT_DOMAIN)
            ));
            add_settings_field('bpm-image', __('Images', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'images_enabled',
                'desc' => __('Check to enable images upload functionality', BP_MEDIA_TXT_DOMAIN)
            ));

            add_settings_section('bpm-miscellaneous', __('Miscellaneous Settings', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
            add_settings_field('bpm-download', __('Download', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'setting' => 'bp_media_options',
                'option' => 'download_enabled',
                'desc' => __('Check to enable download functionality', BP_MEDIA_TXT_DOMAIN)
            ));
            add_settings_field('bpm-admin-bar-menu', __('Admin bar menu', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'setting' => 'bp_media_options',
                'option' => 'show_admin_menu',
                'desc' => __('Check to enable menu in WordPress admin bar', BP_MEDIA_TXT_DOMAIN)
                    )
            );
            add_settings_field('bpm-other-settings', __('Re-Count Media Entries', BP_MEDIA_TXT_DOMAIN), array($this, 'button'), 'bp-media-settings', 'bpm-miscellaneous', array(
                'option' => 'refresh-count',
                'name' => __('Re-Count', BP_MEDIA_TXT_DOMAIN),
                'desc' => __('It will re-count all media entries of all users and correct any discrepancies.', BP_MEDIA_TXT_DOMAIN)
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
            if (!isset($_POST['bp_media_options']['enable_on_profile']) && !isset($_POST['bp_media_options']['enable_on_group'])) {
                if (is_multisite())
                    update_site_option('bpm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', BP_MEDIA_TXT_DOMAIN));
                else
                    add_settings_error(__('Enable BuddyPress Media', BP_MEDIA_TXT_DOMAIN), 'bpm-media-enable', __('Enable BuddyPress Media on either User Profiles or Groups or both. Atleast one should be selected.', BP_MEDIA_TXT_DOMAIN));
                $input['enable_on_profile'] = 1;
            }
            if (!isset($_POST['bp_media_options']['videos_enabled']) && !isset($_POST['bp_media_options']['audio_enabled']) && !isset($_POST['bp_media_options']['images_enabled'])) {
                if (is_multisite())
                    update_site_option('bpm-media-type', __('Atleast one Media Type Must be selected', BP_MEDIA_TXT_DOMAIN));
                else
                    add_settings_error(__('Media Type', BP_MEDIA_TXT_DOMAIN), 'bpm-media-type', __('Atleast one Media Type Must be selected', BP_MEDIA_TXT_DOMAIN));
                $input['images_enabled'] = 1;
            }
            if (is_multisite())
                update_site_option('bpm-settings-saved', __('Settings saved.', BP_MEDIA_FFMPEG_TXT_DOMAIN));
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
                <label for="<?php echo sanitize_title($desc); ?>"><input<?php checked($options[$option], $value); ?> value="<?php echo $value; ?>" name="<?php echo $name; ?>" id="<?php echo sanitize_title($desc); ?>" type="radio" /><?php echo $desc; ?></label><br /><?php
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
            <label for="<?php echo sanitize_title($option); ?>"><input value="<?php echo $options[$option]; ?>" name="<?php echo $name; ?>" id="<?php echo sanitize_title($option); ?>" type="<?php echo $password ? 'password' : 'text'; ?>" /><?php
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
                    <option><?php __e('None', BP_MEDIA_TXT_DOMAIN); ?></option><?php
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

    }

}
    ?>
