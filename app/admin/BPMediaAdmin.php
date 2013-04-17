<?php
/**
 * Description of BPMediaAdmin
 *
 * @package BuddyPressMedia
 * @subpackage Admin
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaAdmin')) {

    class BPMediaAdmin {

        public $bp_media_upgrade;
        public $bp_media_settings;
        public $bp_media_support;
        public $bp_media_feed;

        public function __construct() {
            add_action('init', array($this, 'video_transcoding_survey_response'));
            if (is_multisite()) {
                add_action('network_admin_notices', array($this, 'upload_filetypes_error'));
                add_action('admin_notices', array($this, 'upload_filetypes_error'));
            }
            $bp_media_feed = new BPMediaFeed();
            add_action('wp_ajax_bp_media_fetch_feed', array($bp_media_feed, 'fetch_feed'), 1);
            $this->bp_media_support = new BPMediaSupport();
            add_action('wp_ajax_bp_media_select_request', array($this->bp_media_support, 'get_form'), 1);
            add_action('wp_ajax_bp_media_cancel_request', create_function('', 'do_settings_sections("bp-media-support"); die();'), 1);
            add_action('wp_ajax_bp_media_submit_request', array($this->bp_media_support, 'submit_request'), 1);
            add_action('wp_ajax_bp_media_fetch_feed', array($bp_media_feed, 'fetch_feed'), 1);
            add_action('wp_ajax_bp_media_linkback', array($this, 'linkback'), 1);
            add_action('wp_ajax_bp_media_bp_album_deactivate', 'BPMediaAlbumimporter::bp_album_deactivate', 1);
            add_action('wp_ajax_bp_media_bp_album_import', 'BPMediaAlbumimporter::bpmedia_ajax_import_callback', 1);
            add_action('wp_ajax_bp_media_bp_album_import_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_favorites', 1);
            add_action('wp_ajax_bp_media_bp_album_import_step_favorites', 'BPMediaAlbumimporter::bpmedia_ajax_import_step_favorites', 1);
            add_action('wp_ajax_bp_media_bp_album_cleanup', 'BPMediaAlbumimporter::cleanup_after_install');
            add_action('wp_ajax_bp_media_convert_videos_form', array($this, 'convert_videos_mailchimp_send'), 1);
            add_action('wp_ajax_bp_media_correct_upload_filetypes', array($this, 'correct_upload_filetypes'), 1);
            add_filter('plugin_row_meta', array($this, 'plugin_meta_premium_addon_link'), 1, 4);
            if (is_admin()) {
                add_action('admin_enqueue_scripts', array($this, 'ui'));
                add_action(bp_core_admin_hook(), array($this, 'menu'));
                if (current_user_can('manage_options'))
                    add_action('bp_admin_tabs', array($this, 'tab'));
                if (is_multisite())
                    add_action('network_admin_edit_bp_media', array($this, 'save_multisite_options'));
            }
            $this->bp_media_settings = new BPMediaSettings();
        }

        /**
         * Generates the Admin UI.
         *
         * @param string $hook
         */

        /**
         *
         * @param type $hook
         */
        public function ui($hook) {
            $admin_ajax = admin_url('admin-ajax.php');
            wp_enqueue_script('bp-media-admin', BP_MEDIA_URL . 'app/assets/js/admin.js', '', BP_MEDIA_VERSION);
            wp_localize_script('bp-media-admin', 'bp_media_admin_ajax', $admin_ajax);
            $bp_media_admin_strings = array(
                'no_refresh' => __('Please do not refresh this page.', 'buddypress-media'),
                'something_went_wrong' => __('Something went wronng. Please <a href onclick="location.reload();">refresh</a> page.', 'buddypress-media')
            );
            wp_localize_script('bp-media-admin', 'bp_media_admin_strings', $bp_media_admin_strings);
            wp_localize_script('bp-media-admin', 'settings_url', add_query_arg(
                            array('page' => 'bp-media-settings'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php'))
                    ) . '#privacy_enabled');
            wp_localize_script('bp-media-admin', 'settings_bp_album_import_url', add_query_arg(
                            array('page' => 'bp-media-settings'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php'))
                    ));
            wp_enqueue_style('bp-media-admin', BP_MEDIA_URL . 'app/assets/css/main.css', '', BP_MEDIA_VERSION);
        }

        /**
         * Admin Menu
         *
         * @global string 'buddypress-media'
         */
        public function menu() {
            global $wpdb;
            add_menu_page(__('BuddyPress Media Component', 'buddypress-media'), __('BuddyPress Media', 'buddypress-media'), 'manage_options', 'bp-media-settings', array($this, 'settings_page'));
            add_submenu_page('bp-media-settings', __('BuddyPress Media Settings', 'buddypress-media'), __('Settings', 'buddypress-media'), 'manage_options', 'bp-media-settings', array($this, 'settings_page'));
            if (!BPMediaPrivacy::is_installed()) {
                add_submenu_page('bp-media-settings', __('BuddyPress Media Database Update', 'buddypress-media'), __('Update Database', 'buddypress-media'), 'manage_options', 'bp-media-privacy', array($this, 'privacy_page'));
            }

            add_submenu_page('bp-media-settings', __('Importer', 'buddypress-media'), __('Importer', 'buddypress-media'), 'manage_options', 'bp-media-importer', array($this, 'bp_importer_page'));

            add_submenu_page('bp-media-settings', __('BuddyPress Media Addons', 'buddypress-media'), __('Addons', 'buddypress-media'), 'manage_options', 'bp-media-addons', array($this, 'addons_page'));
            add_submenu_page('bp-media-settings', __('BuddyPress Media Support', 'buddypress-media'), __('Support ', 'buddypress-media'), 'manage_options', 'bp-media-support', array($this, 'support_page'));
            if (bp_get_option('bp-media-survey', true)) {
                add_submenu_page('bp-media-settings', __('BuddyPress Media Convert Videos', 'buddypress-media'), __('Convert Videos', 'buddypress-media'), 'manage_options', 'bp-media-convert-videos', array($this, 'convert_videos_page'));
            }
        }

        /**
         * Render the BuddyPress Media Settings page
         */
        public function settings_page() {
            $this->render_page('bp-media-settings', 'bp_media');
        }

        public function privacy_page() {
            $this->render_page('bp-media-privacy');
        }

        public function bp_importer_page() {
            $this->render_page('bp-media-importer');
        }

        public function convert_videos_page() {
            $this->render_page('bp-media-convert-videos');
        }

        /**
         * Render the BuddyPress Media Addons page
         */
        public function addons_page() {
            $this->render_page('bp-media-addons');
        }

        /**
         * Render the BuddyPress Media Support page
         */
        public function support_page() {
            $this->render_page('bp-media-support');
        }

        /**
         *
         * @return type
         */
        static function get_current_tab() {
            return isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
        }

        /**
         * Render BPMedia Settings
         *
         * @global string 'buddypress-media'
         */

        /**
         *
         * @param type $page
         * @param type $option_group
         */
        public function render_page($page, $option_group = NULL) {
            ?>

            <div class="wrap bp-media-admin <?php echo $this->get_current_tab(); ?>">
                <div id="icon-buddypress" class="icon32"><br></div>
                <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(__('Media', 'buddypress-media')); ?></h2>
                <?php settings_errors(); ?>
                <div class="columns-2">
                    <h3 class="bp-media-settings-tabs"><?php
            $this->sub_tabs();
                ?>
                    </h3>

                    <div id="bp-media-settings-boxes">
                        <?php
                        $settings_url = ( is_multisite() ) ? network_admin_url('edit.php?action=' . $option_group) : 'options.php';
                        ?>
                        <form id="bp_media_settings_form" name="bp_media_settings_form" action="<?php echo $settings_url; ?>" method="post" enctype="multipart/form-data">
                            <div class="bp-media-metabox-holder"><?php
            if ($option_group) {
                settings_fields($option_group);
                do_settings_sections($page);
                submit_button();
            } else {
                do_settings_sections($page);
            }
                        ?>
                                <div class="rt-link alignright"><?php _e('By', 'buddypress-media'); ?> <a href="http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media" title="<?php _e('Empowering The Web With WordPress', 'buddypress-media'); ?>"><img src="<?php echo BP_MEDIA_URL; ?>app/assets/img/rtcamp-logo.png"></a></div>
                            </div>

                        </form>
                    </div><!-- .bp-media-settings-boxes -->
                    <div class="metabox-fixed metabox-holder alignright bp-media-metabox-holder">
                        <?php $this->admin_sidebar(); ?>
                    </div>
                </div><!-- .metabox-holder -->
            </div><!-- .bp-media-admin --><?php
            do_action('bp_media_admin_page_append', $page);
        }

        /**
         * Adds a tab for Media settings in the BuddyPress settings page
         *
         * @global type $bp_media
         */
        public function tab() {

            $tabs_html = '';
            $idle_class = 'nav-tab';
            $active_class = 'nav-tab nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = $this->get_current_tab();
            /* BuddyPress Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('BuddyPress Media', 'buddypress-media'),
                'name' => __('BuddyPress Media', 'buddypress-media'),
                'class' => ($tab == 'bp-media-settings' || $tab == 'bp-media-addons' || $tab == 'bp-media-support') ? $active_class : $idle_class
            );


            foreach ($tabs as $tab) {
                $tabs_html.= '<a id="bp-media" title= "' . $tab['title'] . '"  href="' . $tab['href'] . '" class="' . $tab['class'] . '">' . $tab['name'] . '</a>';
            }
            echo $tabs_html;
        }

        /**
         * Adds a sub tabs to the BuddyPress Media settings page
         *
         * @global type $bp_media
         */
        public function sub_tabs() {
            $tabs_html = '';
            $idle_class = 'nav-tab';
            $active_class = 'nav-tab nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = $this->get_current_tab();
            /* BuddyPress Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('BuddyPress Media Settings', 'buddypress-media'),
                'name' => __('Settings', 'buddypress-media'),
                'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class . ' first_tab'
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-addons'), 'admin.php')),
                'title' => __('BuddyPress Media Addons', 'buddypress-media'),
                'name' => __('Addons', 'buddypress-media'),
                'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-support'), 'admin.php')),
                'title' => __('BuddyPress Media Support', 'buddypress-media'),
                'name' => __('Support', 'buddypress-media'),
                'class' => ($tab == 'bp-media-support') ? $active_class : $idle_class . ' last_tab'
            );

            if (bp_get_option('bp-media-survey', true)) {
                $tabs[] = array(
                    'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-convert-videos'), 'admin.php')),
                    'title' => __('BuddyPress Media Covert Videos', 'buddypress-media'),
                    'name' => __('Convert Videos', 'buddypress-media'),
                    'class' => ($tab == 'bp-media-convert-videos') ? $active_class : $idle_class . ' last_tab'
                );
            }

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-importer'), 'admin.php')),
                'title' => __('Importer', 'buddypress-media'),
                'name' => __('Importer', 'buddypress-media'),
                'class' => ($tab == 'bp-media-importer') ? $active_class : $idle_class
            );

            $tabs = apply_filters('bp_media_add_sub_tabs', $tabs, $tab);
            foreach ($tabs as $tab) {
                $tabs_html.= '<a title="' . $tab['title'] . '" href="' . $tab['href'] . '" class="' . $tab['class'] . ' ' . sanitize_title($tab['name']) . '">' . $tab['name'] . '</a>';
            }
            echo $tabs_html;
        }

        /*
         * Updates the media count of all users.
         */

        /**
         *
         * @global type $wpdb
         * @return boolean
         */
        public function update_count() {
            global $wpdb;

            $query =
                    "SELECT
		p.post_author,pmp.meta_value,
		SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
		SUM(CASE WHEN post_mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as Audio,
		SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
		SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums
	FROM
		$wpdb->posts p inner join $wpdb->postmeta  pm on pm.post_id = p.id INNER JOIN $wpdb->postmeta pmp
	on pmp.post_id = p.id  WHERE
		pm.meta_key = 'bp-media-key' AND
		pm.meta_value > 0 AND
		pmp.meta_key = 'bp_media_privacy' AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
	GROUP BY p.post_author,pmp.meta_value order by p.post_author";
            $result = $wpdb->get_results($query);
            if (!is_array($result))
                return false;
            $formatted = array();
            foreach ($result as $obj) {
                $formatted[$obj->post_author][$obj->meta_value] = array(
                    'image' => $obj->Images,
                    'video' => $obj->Videos,
                    'audio' => $obj->Audio,
                    'album' => $obj->Albums,
                );
            }

            foreach ($formatted as $user => $obj) {
                bp_update_user_meta($user, 'bp_media_count', $obj);
            }
            return true;
        }

        /* Multisite Save Options - http://wordpress.stackexchange.com/questions/64968/settings-api-in-multisite-missing-update-message#answer-72503 */

        /**
         *
         * @global type $bp_media_admin
         */
        public function save_multisite_options() {
            global $bp_media_admin;
            if (isset($_POST['refresh-count'])) {
                $bp_media_admin->update_count();
            }
            do_action('bp_media_sanitize_settings', $_POST);

            if (isset($_POST['bp_media_options'])) {
                bp_update_option('bp_media_options', $_POST['bp_media_options']);
//
//                // redirect to settings page in network
                wp_redirect(
                        add_query_arg(
                                array('page' => 'bp-media-settings', 'updated' => 'true'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php'))
                        )
                );
                exit;
            }
        }

        /* Admin Sidebar */

        /**
         *
         * @global type $bp_media
         */
        public function admin_sidebar() {
            $current_user = wp_get_current_user();

            $message = sprintf(__('I use @buddypressmedia http://goo.gl/8Upmv on %s', 'buddypress-media'), home_url());
            $addons = '<label for="bp-media-add-linkback"><input' . checked(bp_get_option('bp_media_add_linkback', false), true, false) . ' type="checkbox" name="bp-media-add-linkback" value="1" id="bp-media-add-linkback"/> ' . __('Add link to footer', 'buddypress-media') . '</label>
						<a href="http://twitter.com/home/?status=' . $message . '" class="button button-tweet" target= "_blank">' . __('Tweet', 'buddypress-media') . '</a>
						<a href="http://wordpress.org/support/view/plugin-reviews/buddypress-media?rate=5#postform" class="button button-rating" target= "_blank">' . __('Rate on WordPress.org', 'buddypress-media') . '</a>';
            new BPMediaAdminWidget('spread-the-word', __('Spread the Word', 'buddypress-media'), $addons);

            $donate = '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                           <!-- Identify your business so that you can collect the payments. -->
                           <input type="hidden" name="business"
                           value="paypal@rtcamp.com">
                           <!-- Specify a Donate button. -->
                           <input type="hidden" name="cmd" value="_donations">
                           <!-- Specify details about the contribution -->
                           <input type="hidden" name="item_name" value="BuddyPress Media">
                           <label><b>' . __('USD', 'buddypress-media') . '</b></label>
						   <input type="text" name="amount" size="3">
                           <input type="hidden" name="currency_code" value="USD">
                           <!-- Display the payment button. -->
                           <input type="hidden" name="cpp_header_image" value="' . BP_MEDIA_URL . 'app/assets/img/rtcamp-logo.png">
                           <input type="image" id="rt-donate-button" name="submit" border="0"
                           src="' . BP_MEDIA_URL . 'app/assets/img/paypal-donate-button.png"
                           alt="PayPal - The safer, easier way to pay online">
                       </form><br />
                       <center><b>' . __('OR', 'buddypress-media') . '</b></center><br />
                       <center>' . __('Use <a href="https://rtcamp.com/store/product-category/buddypress/?utm_source=dashboard&utm_medium=plugin&utm_campaign=buddypress-media">premium add-ons</a> starting from $9', 'buddypress-media') . '</center>';
            ;
            new BPMediaAdminWidget('donate', __('Donate', 'buddypress-media'), $donate);

            $branding = '<form action="http://rtcamp.us1.list-manage1.com/subscribe/post?u=85b65c9c71e2ba3fab8cb1950&amp;id=9e8ded4470" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                                    <div class="mc-field-group">
                                    <input type="email" value="' . $current_user->user_email . '" name="EMAIL" placeholder="Email" class="required email" id="mce-EMAIL">
                                    <input style="display:none;" type="checkbox" checked="checked" value="1" name="group[1721][1]" id="mce-group[1721]-1721-0"><label for="mce-group[1721]-1721-0">
                                    <div id="mce-responses" class="clear">
                                    <div class="response" id="mce-error-response" style="display:none"></div>
                                    <div class="response" id="mce-success-response" style="display:none"></div>
                                    </div>
                                    <input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button">
                                    </div>
                                    </form>
                        <ul id="social">
                            <li><a href="' . sprintf('%s', 'http://www.facebook.com/rtCamp.solutions/') . '"  title="' . __('Become a fan on Facebook', 'buddypress-media') . '" class="bp-media-facebook bp-media-social">' . __('Facebook', 'buddypress-media') . '</a></li>
                            <li><a href="' . sprintf('%s', 'https://twitter.com/rtcamp/') . '"  title="' . __('Follow us on Twitter', 'buddypress-media') . '" class="bp-media-twitter bp-media-social">' . __('Twitter', 'buddypress-media') . '</a></li>
                            <li><a href="' . sprintf('%s', 'http://feeds.feedburner.com/rtcamp/') . '"  title="' . __('Subscribe to our feeds', 'buddypress-media') . '" class="bp-media-rss bp-media-social">' . __('RSS Feed', 'buddypress-media') . '</a></li>
                        </ul>';
            new BPMediaAdminWidget('branding', __('Subscribe', 'buddypress-media'), $branding);

            $news = '<img src ="' . admin_url('/images/wpspin_light.gif') . '" /> Loading...';
            new BPMediaAdminWidget('latest-news', __('Latest News', 'buddypress-media'), $news);
        }

        public function linkback() {
            if (isset($_POST['linkback']) && $_POST['linkback']) {
                return bp_update_option('bp_media_add_linkback', true);
            } else {
                return bp_update_option('bp_media_add_linkback', false);
            }
            die;
        }

        public function convert_videos_mailchimp_send() {
            if ($_POST['interested'] == 'Yes' && !empty($_POST['choice'])) {
                wp_remote_get(add_query_arg(array('bp-media-convert-videos-form' => 1, 'choice' => $_POST['choice'], 'url' => urlencode($_POST['url']), 'email' => $_POST['email']), 'http://rtcamp.com/'));
            } else {
                bp_update_option('bp-media-survey', 0);
            }
            echo 'Thank you for your time.';
            die;
        }

        public function video_transcoding_survey_response() {
            if (isset($_GET['survey-done']) && ($_GET['survey-done'] == md5('survey-done'))) {
                bp_update_option('bp-media-survey', 0);
            }
        }

        public function plugin_meta_premium_addon_link($plugin_meta, $plugin_file, $plugin_data, $status) {
            if (plugin_basename(BP_MEDIA_PATH . 'index.php') == $plugin_file)
                $plugin_meta[] = '<a href="https://rtcamp.com/store/product-category/buddypress/?utm_source=dashboard&#038;utm_medium=plugin&#038;utm_campaign=buddypress-media" title="Premium Add-ons">Premium Add-ons</a>';
            return $plugin_meta;
        }

        public function upload_filetypes_error() {
            global $bp_media;
            $upload_filetypes = get_site_option('upload_filetypes', 'jpg jpeg png gif');
            $upload_filetypes = explode(' ', $upload_filetypes);
            $flag = false;
            if (isset($bp_media->options['images_enabled']) && $bp_media->options['images_enabled']) {
                $not_supported_image = array_diff(array('jpg', 'jpeg', 'png', 'gif'), $upload_filetypes);
                if (!empty($not_supported_image)) {
                    echo '<div class="error upload-filetype-network-settings-error">
                        <p>
                        ' . sprintf(__('You have images enabled on BuddyPress Media but your network allowed filetypes does not allow uploading of %s. Click <a href="%s">here</a> to change your settings manually.', 'buddypress-media'), implode(', ', $not_supported_image), network_admin_url('settings.php#upload_filetypes')) . '
                            <br /><strong>' . __('Recommended', 'buddypress-media') . ':</strong> <input type="button" class="button update-network-settings-upload-filetypes" class="button" value="' . __('Update Network Settings Automatically', 'buddypress-media') . '"> <img style="display:none;" src="' . admin_url('images/wpspin_light.gif') . '" />
                        </p>
                        </div>';
                    $flag = true;
                }
            }
            if (isset($bp_media->options['videos_enabled']) && $bp_media->options['videos_enabled']) {
                if (!in_array('mp4', $upload_filetypes)) {
                    echo '<div class="error upload-filetype-network-settings-error">
                        <p>
                        ' . sprintf(__('You have video enabled on BuddyPress Media but your network allowed filetypes does not allow uploading of mp4. Click <a href="%s">here</a> to change your settings manually.', 'buddypress-media'), network_admin_url('settings.php#upload_filetypes')) . '
                            <br /><strong>' . __('Recommended', 'buddypress-media') . ':</strong> <input type="button" class="button update-network-settings-upload-filetypes" class="button" value="' . __('Update Network Settings Automatically', 'buddypress-media') . '"> <img style="display:none;" src="' . admin_url('images/wpspin_light.gif') . '" />
                        </p>
                        </div>';
                    $flag = true;
                }
            }
            if (isset($bp_media->options['audio_enabled']) && $bp_media->options['audio_enabled']) {
                if (!in_array('mp3', $upload_filetypes)) {
                    echo '<div class="error upload-filetype-network-settings-error"><p>' . sprintf(__('You have audio enabled on BuddyPress Media but your network allowed filetypes does not allow uploading of mp3. Click <a href="%s">here</a> to change your settings manually.', 'buddypress-media'), network_admin_url('settings.php#upload_filetypes')) . '
                            <br /><strong>' . __('Recommended', 'buddypress-media') . ':</strong> <input type="button" class="button update-network-settings-upload-filetypes" class="button" value="' . __('Update Network Settings Automatically', 'buddypress-media') . '"> <img style="display:none;" src="' . admin_url('images/wpspin_light.gif') . '" />
                        </p>
                        </div>';
                    $flag = true;
                }
            }
            if ($flag) {
                            ?>
                <script type="text/javascript">
                    jQuery('.upload-filetype-network-settings-error').on('click','.update-network-settings-upload-filetypes', function(){
                        jQuery('.update-network-settings-upload-filetypes').siblings('img').show();
                        jQuery('.update-network-settings-upload-filetypes').prop('disabled',true);
                        jQuery.post(ajaxurl,{action: 'bp_media_correct_upload_filetypes'}, function(response){
                            if(response){
                                jQuery('.upload-filetype-network-settings-error:first').after('<div style="display: none;" class="updated bp-media-network-settings-updated-successfully"><p><?php _e('Network settings updated successfully.', 'buddypress-media'); ?></p></div>')
                                jQuery('.upload-filetype-network-settings-error').remove();
                                jQuery('.bp-media-network-settings-updated-successfully').show();
                            }
                        });
                    }); </script><?php
            }
        }

        public function correct_upload_filetypes() {
            global $bp_media;
            $upload_filetypes_orig = $upload_filetypes = get_site_option('upload_filetypes', 'jpg jpeg png gif');
            $upload_filetypes = explode(' ', $upload_filetypes);
            if (isset($bp_media->options['images_enabled']) && $bp_media->options['images_enabled']) {
                $not_supported_image = array_diff(array('jpg', 'jpeg', 'png', 'gif'), $upload_filetypes);
                if (!empty($not_supported_image)) {
                    $update_image_support = NULL;
                    foreach ($not_supported_image as $ns) {
                        $update_image_support .= ' ' . $ns;
                    }
                    if ($update_image_support) {
                        $upload_filetypes_orig .= $update_image_support;
                        update_site_option('upload_filetypes', $upload_filetypes_orig);
                    }
                }
            }
            if (isset($bp_media->options['videos_enabled']) && $bp_media->options['videos_enabled']) {
                if (!in_array('mp4', $upload_filetypes)) {
                    $upload_filetypes_orig .= ' mp4';
                    update_site_option('upload_filetypes', $upload_filetypes_orig);
                }
            }
            if (isset($bp_media->options['audio_enabled']) && $bp_media->options['audio_enabled']) {
                if (!in_array('mp3', $upload_filetypes)) {
                    $upload_filetypes_orig .= ' mp3';
                    update_site_option('upload_filetypes', $upload_filetypes_orig);
                }
            }
            echo true;
            die();
        }

    }

}
            ?>
