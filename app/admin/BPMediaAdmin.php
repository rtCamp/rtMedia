<?php
/**
 * Description of BPMediaAdmin
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaAdmin')) {

    class BPMediaAdmin {

        public $bpm_upgrade;
        public $bpm_settaings;

        public function __construct() {
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
            $admin_js = trailingslashit(site_url()) . '?bp_media_get_feeds=1';
            wp_enqueue_script('bp-media-js', plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
            wp_localize_script('bp-media-js', 'bp_media_news_url', $admin_js);
            wp_enqueue_style('bp-media-admin-style', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));
        }

        /**
         * Admin Menu
         * 
         * @global string $bp_media->text_domain
         */
        public function menu() {
            global $bp_media;
            add_menu_page(__('Buddypress Media Component', $bp_media->text_domain), __('BP Media', $bp_media->text_domain), 'manage_options', 'bp-media-settings', array($this, 'settings_page'));
            add_submenu_page('bp-media-settings', __('Buddypress Media Settings', $bp_media->text_domain), __('Settings', $bp_media->text_domain), 'manage_options', 'bp-media-settings', array($this, 'settings_page'));
            add_submenu_page('bp-media-settings', __('Buddypress Media Addons', $bp_media->text_domain), __('Addons', $bp_media->text_domain), 'manage_options', 'bp-media-addons', array($this, 'addons_page'));
            add_submenu_page('bp-media-settings', __('Buddypress Media Support', $bp_media->text_domain), __('Support ', $bp_media->text_domain), 'manage_options', 'bp-media-support', array($this, 'support_page'));
        }

        /**
         * Render the BuddyPress Media Settings page
         */
        public function settings_page() {
            $this->render_page('bp-media-settings');
        }

        /**
         * Render the BuddyPress Media Addons page
         */
        public function addons_page() {
            $this->render_page('bp-media-addon');
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
         * @global string $bp_media->text_domain
         */
        public function render_page($page, $is_settings = false) {
            global $bp_media;
            ?>

            <div class="wrap bp-media-admin">
                <div id="icon-buddypress" class="icon32"><br></div>
                <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(__('Media', $bp_media->text_domain)); ?></h2>
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

            global $bp_media;
            $tabs_html = '';
            $idle_class = 'nav-tab';
            $active_class = 'nav-tab nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
            /* BP Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('Buddypress Media', $bp_media->text_domain),
                'name' => __('Buddypress Media', $bp_media->text_domain),
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
            global $bp_media;
            $tabs_html = '';
            $idle_class = 'media-nav-tab';
            $active_class = 'media-nav-tab media-nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = isset($_GET['page']) ? $_GET['page'] : "bp-media-settings";
            /* BP Media */
            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-settings'), 'admin.php')),
                'title' => __('Buddypress Media Settings', $bp_media->text_domain),
                'name' => __('Settings', $bp_media->text_domain),
                'class' => ($tab == 'bp-media-settings') ? $active_class : $idle_class . ' first_tab'
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-addons'), 'admin.php')),
                'title' => __('Buddypress Media Addons', $bp_media->text_domain),
                'name' => __('Addons', $bp_media->text_domain),
                'class' => ($tab == 'bp-media-addons') ? $active_class : $idle_class
            );

            $tabs[] = array(
                'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-support'), 'admin.php')),
                'title' => __('Buddypress Media Support', $bp_media->text_domain),
                'name' => __('Support', $bp_media->text_domain),
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

        public function admin_sidebar() {
            global $bp_media;
            $branding = '<a href="http://rtcamp.com" title="' . __('Empowering The Web With WordPress', $bp_media->text_domain) . '" id="logo"><img src="' . BP_MEDIA_URL . 'app/assets/img/rtcamp-logo.png" alt="' . __('rtCamp', $bp_media->text_domain) . '" /></a>
                        <ul id="social">
                            <li><a href="' . sprintf('%s', 'http://www.facebook.com/rtCamp.solutions/') . '"  title="' . __('Become a fan on Facebook', $bp_media->text_domain) . '" class="bp-media-facebook bp-media-social">' . __('Facebook', $bp_media->text_domain) . '</a></li>
                            <li><a href="' . sprintf('%s', 'https://twitter.com/rtcamp/') . '"  title="' . __('Follow us on Twitter', $bp_media->text_domain) . '" class="bp-media-twitter bp-media-social">' . __('Twitter', $bp_media->text_domain) . '</a></li>
                            <li><a href="' . sprintf('%s', 'http://feeds.feedburner.com/rtcamp/') . '"  title="' . __('Subscribe to our feeds', $bp_media->text_domain) . '" class="bp-media-rss bp-media-social">' . __('RSS Feed', $bp_media->text_domain) . '</a></li>
                        </ul>';
            new BPMediaWidget('branding', '', $branding);

            $support = '<p>' . sprintf(__(' Please use our <a href="%s">free support forum</a>.<br/>
                        <span class="bpm-aligncenter">OR</span><br/>
                        <a href="%s">Hire us!</a> To get professional customisation/setup service.', $bp_media->text_domain), 'http://rtcamp.com/support/forum/buddypress-media/', 'http://rtcamp.com/buddypress-media/hire/') . '
                        </p>';
            new BPMediaWidget('support', __('Need Help?', $bp_media->text_domain), $support);

            $donate = '<span><a href="http://rtcamp.com/donate/" title="' . __('Help the development keep going.', $bp_media->text_domain) . '"><img class="bp-media-donation-image" src ="' . BP_MEDIA_URL . 'app/assets/img/donate.gif" /></a></span>
                        <p>' . sprintf(__('Help us release more amazing features faster. Consider making a donation to our consistent efforts.', $bp_media->text_domain)) . '</p>';
            new BPMediaWidget('donate', __('Donate', $bp_media->text_domain), $donate);

            $addons = '<ul>
                            <li><a href="http://rtcamp.com/store/buddypress-media-kaltura/" title="' . __('BuddyPress Media Kaltura', $bp_media->text_domain) . '">' . __('BPM-Kaltura', $bp_media->text_domain) . '</a> - ' . __('Add support for Kaltura.com/Kaltura-CE based video conversion support', $bp_media->text_domain) . '</li>
                            <li><a href="http://rtcamp.com/store/buddy-press-media-ffmpeg/" title="' . __('BuddyPress Media FFMPEG', $bp_media->text_domain) . '">' . __('BPM-FFMPEG', $bp_media->text_domain) . '</a> - ' . __('Add FFMEG-based audio/video conversion support', $bp_media->text_domain) . '</li>
			</ul>
			<h4>' . sprintf(__('Are you a developer?', $bp_media->text_domain)) . '</h4>
			<p>' . sprintf(__('If you are developing a BuddyPress Media addon we would like to include it in above list. We can also help you sell them. <a href="%s">More info!</a>', $bp_media->text_domain), 'http://rtcamp.com/contact/') . '</p></h4>';
            new BPMediaWidget('premium-addons', __('Premium Addons', $bp_media->text_domain), $addons);

            $news = '<img src ="' . admin_url('/images/wpspin_light.gif') . '" /> Loading...';
            new BPMediaWidget('latest-news', __('Latest News', $bp_media->text_domain), $news);
        }

    }

}
            ?>
