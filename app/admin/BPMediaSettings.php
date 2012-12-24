<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

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
         * @global string $bp_media->text_domain
         */
        public function settings() {
            global $bp_media;
            add_settings_section('bpm-settings', __('BuddyPress Media Settings', $bp_media->text_domain), '', 'bp-media-settings');
            add_settings_field('bpm-video', __('Video', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'videos_enabled', 'desc' => __('Check to enable video upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-audio', __('Audio', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'audio_enabled', 'desc' => __('Check to enable audio upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-image', __('Images', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'images_enabled', 'desc' => __('Check to enable images upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-download', __('Download', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'download_enabled', 'desc' => __('Check to enable download functionality', $bp_media->text_domain)));
            add_settings_section('bpm-spread-the-word', __('Spread the Word', $bp_media->text_domain), '', 'bp-media-settings');
            add_settings_field('bpm-spread-the-word-settings', __('Spread the Word', $bp_media->text_domain), array($this, 'radio'), 'bp-media-settings', 'bpm-spread-the-word', array('option' => 'remove_linkback', 'radios' => array(2 => __('Yes, I support BuddyPress Media', $bp_media->text_domain), 1 => __('No, I don\'t want to support BuddyPress Media', $bp_media->text_domain)), 'default' => 2));
            add_settings_section('bpm-other', __('BuddyPress Media Other Options', $bp_media->text_domain), '', 'bp-media-settings');
            add_settings_field('bpm-other-settings', __('Re-Count Media Entries', $bp_media->text_domain), array($this, 'button'), 'bp-media-settings', 'bpm-other', array('option' => 'refresh_media_count', 'link' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings', 'bpm_refresh_count' => true, 'wp_nonce' => wp_create_nonce('bpm_refresh_count')), 'admin.php')), 'desc' => __('It will re-count all media entries of all users and correct any discrepancies.', $bp_media->text_domain)));
            add_settings_section('bpm-addons', __('BuddyPress Media Addons for Audio/Video Conversion', $bp_media->text_domain), '', 'bp-media-addons');
            $kaltura = array(
                'title' => 'BuddyPress-Media Kaltura Add-on',
                'img_src' => 'http://cdn.rtcamp.com/files/2012/10/new-buddypress-media-kaltura-logo-240x184.png',
                'product_link' => 'http://rtcamp.com/store/buddypress-media-kaltura/',
                'desc' => '<p>Add support for more video formats using Kaltura video solution.</p>
                <p>Works with Kaltura.com, self-hosted Kaltura-CE and Kaltura-on-premise.</p>',
                'price' => '$99',
                'demo_link' => 'http://demo.rtcamp.com/bpm-kaltura/',
                'buy_now' => 'http://rtcamp.com/store/?add-to-cart=15446'
            );
            add_settings_field('bpm-video', __('Video', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-addon', 'bpm-addons', array('option' => 'videos_enabled', 'desc' => __('Check to enable video upload functionality', $bp_media->text_domain)));
            add_settings_field('bpm-addons-box', '', array($this,'addon'), 'bp-media-addons', 'bpm-addons', $kaltura);
//            $ffmpeg = array(
//                'BuddyPress-Media Kaltura Add-on',
//                'http://cdn.rtcamp.com/files/2012/10/new-buddypress-media-kaltura-logo-240x184.png',
//                $product_link,
//                $desc,
//                $price,
//                $demo_link,
//                $buy_now
//            );
            register_setting('bp_media', 'bp_media_options');
        }

        /**
         * Output a checkbox
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function checkbox($args) {
            global $bp_media;
            $options = $bp_media->options;
            $defaults = array(
                'option' => '',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option)) {
                trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' ) ', $bp_media->text_domain));
                return;
            }
            if (!isset($options[$option]))
                $options[$option] = '';
            ?>
            <label for="<?php echo $option; ?>">
                <input<?php checked($options[$option]); ?> name="bp_media_options[<?php echo $option; ?>]" id="<?php echo $option; ?>" value="1" type="checkbox" />
                <?php echo $desc; ?>
            </label><?php
        }

        /**
         * Outputs Radio Buttons
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function radio($args) {
            global $bp_media;
            $options = $bp_media->options;
            $defaults = array(
                'option' => '',
                'radios' => array(),
                'default' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option) || ( 2 > count($radios) )) {
                if (empty($option))
                    trigger_error(__('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' )', $bp_media->text_domain));
                if (2 > count($radios))
                    trigger_error(__('Need to specify atleast to radios else use a checkbox instead', $bp_media->text_domain));
                return;
            }
            if (empty($options[$option])) {
                $options[$option] = $default;
            }
            foreach ($radios as $value => $desc) {
                    ?>
                <label for="<?php echo sanitize_title($desc); ?>"><input<?php checked($options[$option], $value); ?> value='<?php echo $value; ?>' name='bp_media_options[<?php echo $option; ?>]' id="<?php echo sanitize_title($desc); ?>" type='radio' /><?php echo $desc; ?></label><br /><?php
            }
        }

        /**
         * Outputs a Button
         * 
         * @global type $bp_media
         * @param array $args
         */
        public function button($args) {
            global $bp_media;
            $defaults = array(
                'option' => '',
                'link' => '',
                'desc' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            if (empty($option) || ( empty($link) )) {
                if (empty($option))
                    trigger_error('Please provide "option" value ( Required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\', \'link\' => \'linkurl\' )');
                if (empty($link))
                    trigger_error('Need to specify a link in the argument ( Required )');
                return;
            }
                ?>
            <a id="<?php echo $option; ?>" href="<?php echo $link; ?>" class="button" title="<?php echo $desc; ?>">Re-Count</a><?php if (!empty($desc)) { ?>
                <span class="description"><?php echo $desc; ?></a><?php
            }
        }

        /**
         * Outputs a BuddyPress Addon
         * 
         * @param array $args
         */
        public function addon($args) {
            print_r($args);
            echo "hi";
            new BPMediaAddon($args);
        }

    }

}
    ?>
