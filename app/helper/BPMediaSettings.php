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
        public function settings() {
            global $bp_media_addon;
            add_settings_section('bpm-settings', __('BuddyPress Media Settings', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
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
            add_settings_field('bpm-download', __('Download', BP_MEDIA_TXT_DOMAIN), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array(
                'setting' => 'bp_media_options',
                'option' => 'download_enabled',
                'desc' => __('Check to enable download functionality', BP_MEDIA_TXT_DOMAIN)
            ));
            add_settings_section('bpm-spread-the-word', __('Spread the Word', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
            add_settings_field('bpm-spread-the-word-settings', __('Spread the Word', BP_MEDIA_TXT_DOMAIN), array($this, 'radio'), 'bp-media-settings', 'bpm-spread-the-word', array(
                'setting' => 'bp_media_options',
                'option' => 'remove_linkback',
                'radios' => array(
                    2 => __('Yes, I support BuddyPress Media', BP_MEDIA_TXT_DOMAIN),
                    1 => __('No, I don\'t want to support BuddyPress Media', BP_MEDIA_TXT_DOMAIN)),
                'default' => 2)
            );
            add_settings_section('bpm-other', __('BuddyPress Media Other Options', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-settings');
            add_settings_field('bpm-other-settings', __('Re-Count Media Entries', BP_MEDIA_TXT_DOMAIN), array($this, 'button'), 'bp-media-settings', 'bpm-other', array(
                'option' => 'refresh-count',
                'name' => 'Re-Count',
                'desc' => __('It will re-count all media entries of all users and correct any discrepancies.', BP_MEDIA_TXT_DOMAIN)
            ));
            $bp_media_addon = new BPMediaAddon();
            add_settings_section('bpm-addons', __('BuddyPress Media Addons for Audio/Video Conversion', BP_MEDIA_TXT_DOMAIN), array($bp_media_addon, 'get_addons'), 'bp-media-addons');
            add_settings_section('bpm-support', __('Submit a request form', BP_MEDIA_TXT_DOMAIN), '', 'bp-media-support');
            add_settings_field('bpm-request', __('Request Type', BP_MEDIA_TXT_DOMAIN), array($this, 'dropdown'), 'bp-media-support', 'bpm-support', array('option' => 'select-request', 'none' => false, 'values' => array(
                    '' => '--Select One--',
                    'premium_support' => 'Premium Support',
                    'new_feature' => 'Suggest a New Feature',
                    'bug_report' => 'Submit a Bug Report')
            ));
            register_setting('bp_media', 'bp_media_options', array($this, 'sanitize'));
        }

        /**
         * Sanitizes the settings
         */
        public function sanitize($input) {
            global $bp_media_admin;
            if (isset($_POST['refresh-count'])) {
                if ($bp_media_admin->update_count())
                    add_settings_error(__('Recount Success', BP_MEDIA_TXT_DOMAIN), 'recount-success', __('Recounting of media files done successfully', BP_MEDIA_TXT_DOMAIN), 'updated');
                else
                    add_settings_error(__('Recount Fail', BP_MEDIA_TXT_DOMAIN), 'recount-fail', __('Recounting Failed', BP_MEDIA_TXT_DOMAIN));
            }
            do_action('bp_media_sanitize_settings', $_POST, $input);
            return $input;
        }

        /**
         * Output a checkbox
         * 
         * @global array $bp_media
         * @param array $args
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
        public function textbox($args) {
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
            <label for="<?php echo sanitize_title($option); ?>"><input value="<?php echo $options[$option]; ?>" name="<?php echo $name; ?>" id="<?php echo sanitize_title($option); ?>" type="text" /><?php
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
