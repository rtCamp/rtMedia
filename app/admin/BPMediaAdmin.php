<?php
/**
 * Description of BPMediaAdmin
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaAdmin')) {

    class BPMediaAdmin {

        public $bp_media_upgrade;
        public $bp_media_settings;
        public $bp_media_feed;

        public function __construct() {
            $bp_media_feed =  new BPMediaFeed();
            add_action( 'wp_ajax_bp_media_fetch_feed', array($bp_media_feed, 'fetch_feed'), 1);
            $bp_media_support =  new BPMediaSupport();
            add_action('wp_ajax_bp_media_request_form', array($bp_media_support, 'get_form'), 1);
            add_action( 'wp_ajax_bp_media_fetch_feed', array($bp_media_feed, 'fetch_feed'), 1);
            if (is_admin()) {
                add_action('admin_enqueue_scripts', array($this, 'ui'));
                add_action(bp_core_admin_hook(), array($this, 'menu'));
                if (current_user_can('manage_options'))
                    add_action('bp_admin_tabs', array($this, 'tab'));
            }
            $this->bp_media_upgrade = new BPMediaUpgrade();
            $this->bp_media_settings = new BPMediaSettings();
        }

        /**
         * Generates the Admin UI
         *
         * @param string $hook
         */
        public function ui($hook) {
            $admin_ajax = admin_url('admin-ajax.php');
            wp_enqueue_script('bp-media-admin', BP_MEDIA_URL . 'app/assets/js/main.js');
            wp_localize_script('bp-media-admin', 'bp_media_admin_ajax', $admin_ajax);
            wp_enqueue_style('bp-media-admin', BP_MEDIA_URL . 'app/assets/css/main.css');
        }

        /**
         * Admin Menu
         *
         * @global string BP_MEDIA_TXT_DOMAIN
         */
        public function menu() {
            add_menu_page(__('Buddypress Media Component', BP_MEDIA_TXT_DOMAIN), __('BuddyPress Media', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-settings', array($this, 'settings_page'));
            add_submenu_page('bp-media-settings', __('Buddypress Media Settings', BP_MEDIA_TXT_DOMAIN), __('Settings', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-settings', array($this, 'settings_page'));
            add_submenu_page('bp-media-settings', __('Buddypress Media Addons', BP_MEDIA_TXT_DOMAIN), __('Addons', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-addons', array($this, 'addons_page'));
            add_submenu_page('bp-media-settings', __('Buddypress Media Support', BP_MEDIA_TXT_DOMAIN), __('Support ', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-support', array($this, 'support_page'));
        }

        /**
         * Render the BuddyPress Media Settings page
         */
        public function settings_page() {
            $this->render_page('bp-media-settings', true);
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
         * Render BPMedia Settings
         *
         * @global string BP_MEDIA_TXT_DOMAIN
         */
        public function render_page($page, $is_settings = false) {
            ?>

            <div class="wrap bp-media-admin">
                <div id="icon-buddypress" class="icon32"><br></div>
                <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(__('Media', BP_MEDIA_TXT_DOMAIN)); ?></h2>
                <?php settings_errors(); ?>
                <div class="metabox-holder columns-2">
                    <div class="bp-media-settings-tabs"><?php
            // Check to see which tab we are on
            if (current_user_can('manage_options'))
                $this->sub_tabs();
                ?>
                    </div>

                    <div id="bp-media-settings-boxes">

                        <form id="bp_media_settings_form" name="bp_media_settings_form" action="options.php" method="post" enctype="multipart/form-data"><?php
            echo '<div class="bp-media-metabox-holder">';

//            if (isset($_REQUEST['request_type'])) {
//                bp_media_bug_report_form($_REQUEST['request_type']);
//            } else {
            if ($is_settings) {
                settings_fields('bp_media');
                do_settings_sections($page);
                submit_button();
            } else {
                do_settings_sections($page);
            }

            echo '</div>';
                ?>

                        </form>
                    </div><!-- .bp-media-settings-boxes -->
                    <div class="metabox-fixed metabox-holder alignright bp-media-metabox-holder">
                        <?php $this->admin_sidebar(); ?>
                    </div>
                </div><!-- .metabox-holder -->
            </div><!-- .bp-media-admin --><?php
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
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
            /* BP Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('Buddypress Media', BP_MEDIA_TXT_DOMAIN),
                'name' => __('Buddypress Media', BP_MEDIA_TXT_DOMAIN),
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
            $idle_class = 'media-nav-tab';
            $active_class = 'media-nav-tab media-nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
            /* BP Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('Buddypress Media Settings', BP_MEDIA_TXT_DOMAIN),
                'name' => __('Settings', BP_MEDIA_TXT_DOMAIN),
                'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class . ' first_tab'
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-addons'), 'admin.php')),
                'title' => __('Buddypress Media Addons', BP_MEDIA_TXT_DOMAIN),
                'name' => __('Addons', BP_MEDIA_TXT_DOMAIN),
                'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-support'), 'admin.php')),
                'title' => __('Buddypress Media Support', BP_MEDIA_TXT_DOMAIN),
                'name' => __('Support', BP_MEDIA_TXT_DOMAIN),
                'class' => ($tab == 'bp-media-support') ? $active_class : $idle_class . ' last_tab'
            );

            $pipe = '|';
            $i = '1';
            foreach ($tabs as $tab) {
                if ($i != 1)
                    $tabs_html.=$pipe;
                $tabs_html.= '<a title=""' . $tab['title'] . '" " href="' . $tab['href'] . '" class="' . $tab['class'] . '">' . $tab['name'] . '</a>';
                $i++;
            }
            echo $tabs_html;
        }

        /*
         * Updates the media count of all users.
         */

        public function update_count() {
            global $wpdb;
            $query =
                    "SELECT
		post_author,
		SUM(CASE WHEN post_mime_type LIKE 'image%' THEN 1 ELSE 0 END) as Images,
		SUM(CASE WHEN post_mime_type LIKE 'audio%' THEN 1 ELSE 0 END) as Audio,
		SUM(CASE WHEN post_mime_type LIKE 'video%' THEN 1 ELSE 0 END) as Videos,
		SUM(CASE WHEN post_type LIKE 'bp_media_album' THEN 1 ELSE 0 END) as Albums,
		COUNT(*) as Total
	FROM
		$wpdb->posts RIGHT JOIN $wpdb->postmeta on wp_postmeta.post_id = wp_posts.id
	WHERE
		`meta_key` = 'bp-media-key' AND
		`meta_value` > 0 AND
		( post_mime_type LIKE 'image%' OR post_mime_type LIKE 'audio%' OR post_mime_type LIKE 'video%' OR post_type LIKE 'bp_media_album')
	GROUP BY post_author";
            $result = $wpdb->get_results($query);
            if (!is_array($result))
                return false;

            foreach ($result as $obj) {

                $count = array(
                    'images' => isset($obj->Images) ? $obj->Images : 0,
                    'videos' => isset($obj->Videos) ? $obj->Videos : 0,
                    'audio' => isset($obj->Audio) ? $obj->Audio : 0,
                    'albums' => isset($obj->Albums) ? $obj->Albums : 0
                );
                bp_update_user_meta($obj->post_author, 'bp_media_count', $count);
            }
            return true;
        }

        public function admin_sidebar() {
            global $bp_media;
            $branding = '<a href="http://rtcamp.com" title="' . __('Empowering The Web With WordPress', BP_MEDIA_TXT_DOMAIN) . '" id="logo"><img src="' . BP_MEDIA_URL . 'app/assets/img/rtcamp-logo.png" alt="' . __('rtCamp', BP_MEDIA_TXT_DOMAIN) . '" /></a>
                        <ul id="social">
                            <li><a href="' . sprintf('%s', 'http://www.facebook.com/rtCamp.solutions/') . '"  title="' . __('Become a fan on Facebook', BP_MEDIA_TXT_DOMAIN) . '" class="bp-media-facebook bp-media-social">' . __('Facebook', BP_MEDIA_TXT_DOMAIN) . '</a></li>
                            <li><a href="' . sprintf('%s', 'https://twitter.com/rtcamp/') . '"  title="' . __('Follow us on Twitter', BP_MEDIA_TXT_DOMAIN) . '" class="bp-media-twitter bp-media-social">' . __('Twitter', BP_MEDIA_TXT_DOMAIN) . '</a></li>
                            <li><a href="' . sprintf('%s', 'http://feeds.feedburner.com/rtcamp/') . '"  title="' . __('Subscribe to our feeds', BP_MEDIA_TXT_DOMAIN) . '" class="bp-media-rss bp-media-social">' . __('RSS Feed', BP_MEDIA_TXT_DOMAIN) . '</a></li>
                        </ul>';
            new BPMediaWidget('branding', '', $branding);

            $support = '<p><ul>
            <li>' . sprintf(__('<a href="%s">Read FAQ</a>', BP_MEDIA_TXT_DOMAIN), 'http://rtcamp.com/buddypress-media/faq/') . '</li>
            <li>' . sprintf(__('<a href="%s">Free Support Forum</a>', BP_MEDIA_TXT_DOMAIN), 'http://rtcamp.com/support/forum/buddypress-media/') . '</li>
            <li>' . sprintf(__('<a href="%s">Github Issue Tracker</a>', BP_MEDIA_TXT_DOMAIN), 'https://github.com/rtCamp/buddypress-media/issues/') . '</li>
            <li>' . sprintf(__('<a href="%s">Hire us!</a> To get professional customisation/setup service.', BP_MEDIA_TXT_DOMAIN), 'http://rtcamp.com/buddypress-media/hire/') . '</li>
            </ul></p>';
            new BPMediaWidget('support', __('Need Help?', BP_MEDIA_TXT_DOMAIN), $support);

            $donate = '<span><a href="http://rtcamp.com/donate/" title="' . __('Help the development keep going.', BP_MEDIA_TXT_DOMAIN) . '"><img class="bp-media-donation-image" src ="' . BP_MEDIA_URL . 'app/assets/img/donate.gif" /></a></span>
                        <p>' . sprintf(__('Help us release more amazing features faster. Consider making a donation to our consistent efforts.', BP_MEDIA_TXT_DOMAIN)) . '</p>';
            new BPMediaWidget('donate', __('Donate', BP_MEDIA_TXT_DOMAIN), $donate);

            $addons = '<ul>
                            <li><a href="http://rtcamp.com/store/buddypress-media-kaltura/" title="' . __('BuddyPress Media Kaltura', BP_MEDIA_TXT_DOMAIN) . '">' . __('BPM-Kaltura', BP_MEDIA_TXT_DOMAIN) . '</a> - ' . __('Add support for Kaltura.com/Kaltura-CE based video conversion support', BP_MEDIA_TXT_DOMAIN) . '</li>
                            <li><a href="http://rtcamp.com/store/buddy-press-media-ffmpeg/" title="' . __('BuddyPress Media FFMPEG', BP_MEDIA_TXT_DOMAIN) . '">' . __('BPM-FFMPEG', BP_MEDIA_TXT_DOMAIN) . '</a> - ' . __('Add FFMEG-based audio/video conversion support', BP_MEDIA_TXT_DOMAIN) . '</li>
			</ul>
			<h4>' . sprintf(__('Are you a developer?', BP_MEDIA_TXT_DOMAIN)) . '</h4>
			<p>' . sprintf(__('If you are developing a BuddyPress Media addon we would like to include it in above list. We can also help you sell them. <a href="%s">More info!</a>', BP_MEDIA_TXT_DOMAIN), 'http://rtcamp.com/contact/') . '</p></h4>';
            new BPMediaWidget('premium-addons', __('Premium Addons', BP_MEDIA_TXT_DOMAIN), $addons);

            $news = '<img src ="' . admin_url('/images/wpspin_light.gif') . '" /> Loading...';
            new BPMediaWidget('latest-news', __('Latest News', BP_MEDIA_TXT_DOMAIN), $news);
        }

    }

}
            ?>
