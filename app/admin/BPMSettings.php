<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BPMSettings
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMSettings {

    public function __construct() {
        add_action('admin_init', array($this, 'settings'));
        add_action('admin_init', array($this, 'metaboxes'));
    }

    /**
     * Register Settings
     * 
     * @global string $bp_media->text_domain
     */
    public function settings() {
        global $bp_media;
        add_settings_section('bpm-settings', "", "", 'bp-media-settings');
        add_settings_field('bpm-video', __('Video', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'videos_enabled', 'desc' => __('Check to enable video upload functionality', $bp_media->text_domain)));
        add_settings_field('bpm-audio', __('Audio', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'audio_enabled', 'desc' => __('Check to enable audio upload functionality', $bp_media->text_domain)));
        add_settings_field('bpm-image', __('Images', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'images_enabled', 'desc' => __('Check to enable images upload functionality', $bp_media->text_domain)));
        add_settings_field('bpm-download', __('Download', $bp_media->text_domain), array($this, 'checkbox'), 'bp-media-settings', 'bpm-settings', array('option' => 'download_enabled', 'desc' => __('Check to enable download functionality', $bp_media->text_domain)));
        add_settings_section('bpm-spread-the-word', "", "", 'bp-media-settings');
        add_settings_field('bpm-spread-the-word-settings', __('Spread the Word', $bp_media->text_domain), array($this, 'radio'), 'bp-media-settings', 'bpm-spread-the-word', array('option' => 'remove_linkback', 'radios' => array( 2 => ' Yes, I support BuddyPress Media', 1 => 'No, I don\'t want to support BuddyPress Media' )));
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
            trigger_error('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' ) ');
            return;
        }
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
        );
        $args = wp_parse_args($args, $defaults);
        extract($args);
        if (empty($option) || ( 2 > count($radios) )) {
            empty($option) ? trigger_error('Please provide "option" value ( required ) in the argument. Pass argument to add_settings_field in the follwoing format array( \'option\' => \'option_name\' )') : trigger_error('Need to specify atleast to radios else use a checkbox instead');
            return;
        }
        foreach ($radios as $value => $desc ) { ?>
            <label for=""><input<?php checked( $options[$option], $value ); ?> value='<?php echo $value; ?>' name='bp_media_options[<?php echo $option; ?>]' id="<?php echo $option; ?>" type='radio' /><?php echo $desc; ?></label><br /><?php
        }
    }

    /**
     * Load the metaboxes
     * 
     * @global string $bp_media->text_domain
     */
    public function metaboxes() {
        global $bp_media;
        /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

// Check to see which tab we are on$tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";

        switch ($tab) {
            case 'bp-media-addons' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_addons_list_metabox', __('BuddyPress Media Addons for Audio/Video Conversion', $bp_media->text_domain), 'bp_media_addons_list', 'bp-media-settings', 'normal', 'core');
                break;
            case 'bp-media-support' :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_support_metabox', __('BuddyPress Media Support', $bp_media->text_domain), 'bp_media_support', 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_form_report_metabox', __('Submit a request form', $bp_media->text_domain), 'bp_media_send_request', 'bp-media-settings', 'normal', 'core');
                break;
            case $tab :
// All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bpm_settings_metabox', __('BuddyPress Media Settings', $bp_media->text_domain), array($this, 'settings_metabox'), 'bp-media-settings', 'normal', 'core');
                add_meta_box('bpm_spread_the_word_metabox', __('Spread the word', $bp_media->text_domain), array($this, 'spread_the_word_metabox'), 'bp-media-settings', 'normal', 'core');
                add_meta_box('bpm_other_options_metabox', __('BuddyPress Media Other options', $bp_media->text_domain), 'bp_media_settings_other_options', 'bp-media-settings', 'normal', 'core');
                break;
        }
    }

    public function settings_metabox() {
        do_settings_sections("bpm-settings");
        submit_button();
    }
    
    public function spread_the_word_metabox() {
        //do_settings_sections("bpm-spread-the");
        submit_button();
    }

}
    ?>
