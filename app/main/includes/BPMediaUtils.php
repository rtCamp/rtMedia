<?php

class BPMediaUtils {

    function __construct() {
        if (version_compare(BP_MEDIA_DB_VERSION, get_site_option('bp_media_db_version', '1.0'), '>')) {
            add_action('admin_notices', array($this,'upgrade_db_notice'));
        }
        add_action('wp_loaded', array($this,'upgrade_script'));
        add_action(bp_core_admin_hook(), array($this,'add_admin_menu'));
        add_action('admin_init', array($this,'on_load_page'));
        add_action('wp_ajax_bp_media_cancel_request', array($this,'cancel_request'));
        add_action('wp_ajax_bp_media_request_type', array($this,'handle_request_type'));
        add_action('admin_enqueue_scripts', array($this,'admin_enqueue'));
        add_action('bp_admin_tabs', array($this,'admin_tab'));
    }

    function upgrade_script() {
        if (isset($_GET['bp_media_upgrade_db']) && empty($_REQUEST['settings-updated'])) {
            check_admin_referer('bp_media_upgrade_db', 'wp_nonce');
            require_once('bp-media-upgrade-script.php');
            $current_version = get_site_option('bp_media_db_version', '1.0');
            if ($current_version == '2.0')
                BPMediaUpgradeScript::upgrade_from_2_0_to_2_1();
            else
                BPMediaUpgradeScript::upgrade_from_1_0_to_2_1();
            remove_action('admin_notices', array($this,'upgrade_db_notice'));
        }
    }

    /**
     * Displays admin notice to upgrade BuddyPress Media Database
     */
    function upgrade_db_notice() {
        ?>
        <div class="error"><p>
                Please click upgrade to upgrade the database of BuddyPress Media <a class="button" id="refresh_media_count" href ="<?php echo bp_media_get_admin_url(add_query_arg(array('page' => 'bp-media-settings', 'bp_media_upgrade_db' => 1, 'wp_nonce' => wp_create_nonce('bp_media_upgrade_db')), 'admin.php')) ?>" class="button" title="<?php printf(__('It will migrate your BuddyPress Media\'s earlier database to new database.')); ?>">Upgrade</a>
            </p></div>
        <?php
    }

    /**
     * Add the BuddyPress Media's options menu in the BuddyPress' options subnavigation.
     *
     * @since BuddyPress Media 2.0
     */
    function add_admin_menu() {

        global $bp, $bp_media_errors, $bp_media_messages;
        if (!is_super_admin())
            return false;
        $bp_media_errors = array();
        $bp_media_messages = array();
        global $bp_media_options;
        $bp_media_options = get_site_option('bp_media_options', array(
            'videos_enabled' => true,
            'audio_enabled' => true,
            'images_enabled' => true,
            'download_enabled' => true,
            'remove_linkback' => 1,
                ));
        if (isset($_POST['submit'])) {
            if (isset($_POST['bp_media_options'])) {
                foreach ($bp_media_options as $option => $value) {
                    if (isset($_POST['bp_media_options'][$option])) {
                        switch ($_POST['bp_media_options'][$option]) {
                            case 'true' :
                                $bp_media_options[$option] = true;
                                break;
                            case '1' :
                                $bp_media_options[$option] = 1;
                                break;
                            case '2' :
                                $bp_media_options[$option] = 2;
                                break;
                            default :
                                $bp_media_options[$option] = false;
                        }
                    } else {
                        $bp_media_options[$option] = false;
                    }
                }
                if (update_site_option('bp_media_options', $bp_media_options)) {
                    $bp_media_messages[0] = "<b>Settings saved.</b>";
                }
            }
            do_action('bp_media_save_options');
            $bp_media_messages = apply_filters('bp_media_settings_messages', $bp_media_messages);
            $bp_media_errors = apply_filters('bp_media_settings_errors', $bp_media_errors);
        } else if (isset($_GET['bp_media_refresh_count'])) {

            check_admin_referer('bp_media_refresh_count', 'wp_nonce');
            if (!BPMediaFunction::update_count())
                $bp_media_errors[] = "<b>Recounting Failed</b>";
            else
                $bp_media_messages[] = "<b>Recounting of media files done successfully</b>";
        }else if (isset($_REQUEST['submit-report'])) {

            if (empty($_REQUEST['ur_name'])) {
                $bp_media_errors[] = "<b>Please Enter Name</b>";
            }
            if (empty($_REQUEST['ur_email'])) {
                $bp_media_errors[] = "<b>Please Enter Valid Email Address</b>";
            }
            if (!empty($_REQUEST['ur_email']) && !is_email(trim($_REQUEST['ur_email']))) {
                $bp_media_errors[] = "<b>Please Enter Valid Email Address</b>";
            }
            if (empty($_REQUEST['ur_subject'])) {
                $bp_media_errors[] = "<b>Please Enter Subject</b>";
            }
            if (empty($_REQUEST['ur_query'])) {
                $bp_media_errors[] = "<b>Please Enter Details</b>";
            }
            if (isset($_REQUEST['request_type']) && $_REQUEST['request_type'] == 'bug_report') {
                if (!file_exists(BP_MEDIA_TMP_DIR) && @!mkdir(BP_MEDIA_TMP_DIR, 0777))
                    $bp_media_errors[] = "The Buddypress Media Temporary directory does not exist and could not be created. Please check that directory have write permissions for the 'uploads' directory. ";
            }
            if (empty($bp_media_errors)) {

                $attachments = array();
                $str_to = BP_MEDIA_SUPPORT_EMAIL;
                $str_subject = html_entity_decode(esc_attr(stripslashes($_REQUEST['ur_subject'])), ENT_QUOTES, 'UTF-8');

                $request_type = $_REQUEST['request_type'];
                $request_id = $_REQUEST['request_id'];
                $server_address = $_REQUEST['server_address'];
                $ip_address = $_REQUEST['ip_address'];
                $server_type = $_REQUEST['server_type'];
                $user_agent = $_REQUEST['user_agent'];
                $str_message = '';
                $str_title = ($request_type == 'bug_report') ? 'Bug Report' : 'New Feature Request';
                switch ($request_type) {
                    case "bug_report":
                        $str_title = __('Bug Report', BP_MEDIA_TXT_DOMAIN);
                        break;
                    case "new_feature":
                        $str_title = __('New Feature Request', BP_MEDIA_TXT_DOMAIN);
                        break;
                    case "premium_support":
                        $str_title = __('Premium Support Request', BP_MEDIA_TXT_DOMAIN);
                        break;
                }
                $str_message .= "<h3><strong>$str_title</strong></h3>";

                $str_message .= "<table>";
                if (isset($_REQUEST['request_id']))
                    $str_message .= "<tr><td><strong>Request Id : </strong></td><td>" . $_REQUEST['request_id'] . "</td></tr>";
                if (isset($_REQUEST['ip_address']))
                    $str_message .= "<tr><td><strong>Request IP Address : </strong></td><td>" . $_REQUEST['ip_address'] . "</td></tr>";
                if (isset($_REQUEST['server_address']))
                    $str_message .= "<tr><td><strong>Request Server Address : </strong></td><td>" . $_REQUEST['server_address'] . "</td></tr>";
                if (isset($_REQUEST['server_type']))
                    $str_message .= "<tr><td><strong>Request Server Type : </strong></td><td>" . $_REQUEST['server_type'] . "</td></tr>";
                if (isset($_REQUEST['user_agent']))
                    $str_message .= "<tr><td><strong>Request User Agent : </strong></td><td>" . $_REQUEST['user_agent'] . "</td></tr>";
                if (BP_MEDIA_VERSION)
                    $str_message .= "<tr><td><strong>Buddypress Media Version : </strong></td><td>" . BP_MEDIA_VERSION . "</td></tr>";
                if (isset($_REQUEST['ur_name']))
                    $str_message .= "<tr><td><strong>Name : </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_name']))) . "</td></tr>";
                if (isset($_REQUEST['ur_phone']))
                    $str_message .= "<tr><td><strong>Phone No. : </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_phone']))) . "</td></tr>";
                $str_message .= "</table><br/><br/>";

                if ($request_type == 'bug_report') {

                    $str_message .= "<h3><strong>Wordpress and Hosting Details</strong></h3>";
                    $str_message .= "<table>";

                    if (isset($_REQUEST['ur_wp_admin_login']))
                        $str_message .= "<tr><td><strong>WP Admin Login : </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_wp_admin_login']))) . "</td></tr>";
                    if (isset($_REQUEST['ur_wp_admin_pwd']))
                        $str_message .= "<tr><td><strong>WP Admin Password : </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_wp_admin_pwd']))) . "</td></tr>";
                    if (isset($_REQUEST['ur_ssh_ftp_host']))
                        $str_message .= "<tr><td><strong>SSH / FTP Host : </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_ssh_ftp_host']))) . "</td></tr>";
                    if (isset($_REQUEST['ur_ssh_ftp_login']))
                        $str_message .= "<tr><td><strong>SSH / FTP Login: </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_ssh_ftp_login']))) . "</td></tr>";
                    if (isset($_REQUEST['ur_ssh_ftp_pwd']))
                        $str_message .= "<tr><td><strong>SSH / FTP Password: </strong></td><td>" . esc_attr(stripslashes(trim($_REQUEST['ur_ssh_ftp_pwd']))) . "</td></tr>";
                    $str_message .= "</table><br/><br/>";


                    /* Create server info file */
                    $server_data = $this->get_server_info();
                    $server_info = '';
                    if (!empty($server_data)) {
                        $server_info .= '<table cellpadding="2px" >';
                        foreach ($server_data as $key => $val) {
                            $server_info .= '<tr><th><strong>' . $key . '</strong></th></th></tr>';
                            foreach ($val as $title => $content) {
                                $server_info .= '<tr>';
                                $server_info .= '<td valign = "top"><strong>' . $title . '</strong></td>';
                                if (is_array($content)) {
                                    $server_info .= '<td valign = "top"><table>';
                                    foreach ($content as $sub_title => $sub_content) {
                                        $server_info .= '<tr>';
                                        $server_info .= '<td valign = "top"><strong>' . $sub_title . '</strong></td>';
                                        $server_info .= '<td valign = "top">' . print_r($sub_content, true) . '</td>';
                                        $server_info .= '</tr>';
                                    }
                                    $server_info .= '</table></td>';
                                } else {
                                    $server_info .= '<td>' . $content . '</td>';
                                }

                                $server_info .= '</tr>';
                                $server_info .= '<tr><td></td><td></td></tr>';
                            }
                        }
                        $server_info .= '</table>';
                    }

                    $server_info_path = BP_MEDIA_TMP_DIR . '/server_info.html';

                    if (@file_put_contents($server_info_path, $server_info)) {
                        $attachments[] = $server_info_path;
                    }

                    /* Create phpinfo file and attach to Email */
                    if (isset($_REQUEST['ur_send_phpinfo']) && $_REQUEST['ur_send_phpinfo'] == 'true') {
                        ob_start();
                        phpinfo();
                        $php_info = ob_get_contents();
                        ob_end_clean();

                        $php_info_path = BP_MEDIA_TMP_DIR . '/php_info.html';
                        if (@file_put_contents($php_info_path, $php_info)) {
                            $attachments[] = $php_info_path;
                        }
                    }
                    /* Attach other files */
                    if (!empty($_FILES['ur_attachment'])) {
                        $files = (array) $_FILES['ur_attachment'];
                        for ($i = 0, $l = count($files); $i < $l; $i++) {
                            if (isset($files['tmp_name'][$i]) && isset($files['name'][$i]) && isset($files['error'][$i]) && $files['error'][$i] == UPLOAD_ERR_OK) {
                                $path = BP_MEDIA_TMP_DIR . '/' . $files['name'][$i];
                                if (@move_uploaded_file($files['tmp_name'][$i], $path)) {
                                    $attachments[] = $path;
                                }
                            }
                        }
                    }

                    /* if(isset($_REQUEST['ur_templates'])){
                      $templates = $_REQUEST['ur_templates'];
                      if(!empty($templates)){
                      foreach ($templates as $template) {
                      if (!empty($template)) {
                      $attachments[] = $template;
                      }
                      }
                      }
                      } */
                }
                $str_message .= nl2br(esc_attr(stripslashes($_REQUEST['ur_query'])));


                /* Uniqid Session */
                $strSid = md5(uniqid(time()));

                /* Creating Header */
                $str_header = "";
                $str_header .= "From: " . $_REQUEST['ur_name'] . "<" . $_REQUEST['ur_email'] . ">";
                $str_header .= "Reply-To: " . $_REQUEST['ur_name'] . "<" . $_REQUEST['ur_email'] . ">";

                $str_header .= "MIME-Version: 1.0\n";
                $str_header .= "Content-Type: multipart/mixed; boundary=\"" . $strSid . "\"\n\n";
                $str_header .= "Content-type: text/html; charset=utf-8\n";
                $str_header .= "Content-Transfer-Encoding: 7bit\n\n";

                $flgSend = wp_mail($str_to, $str_subject, $str_message, $str_header, $attachments);  // @ = No Show Error //

                /* Delete temporary files */
                if (!empty($attachments)) {
                    foreach ($attachments as $attachment) {
                        if (strstr($attachment, BP_MEDIA_TMP_DIR) !== false) {
                            @unlink($attachment);
                        }
                    }
                }
                if ($flgSend) {
                    switch ($request_type) {
                        case "bug_report":
                            $succ_msg = __('Thank you, Your bug report sent successfully.', BP_MEDIA_TXT_DOMAIN);
                            break;
                        case "new_feature":
                            $succ_msg = __('Thank you, Your new feature request sent successfully.', BP_MEDIA_TXT_DOMAIN);
                            break;
                        case "premium_support":
                            $succ_msg = __('Thank you, Your premium support request sent successfully, We will contact you soon.', BP_MEDIA_TXT_DOMAIN);
                            break;
                    }
                    $bp_media_messages[] = '<strong>' . $succ_msg . '</strong>';
                } else {
                    $bp_media_errors[] = "<strong>Mail could not be sent</strong>";
                }
            }
        }

        if (isset($bp_media_errors) && count($bp_media_errors)) {
            ?>
            <div class="error"><p><?php foreach ($bp_media_errors as $error)
                echo $error . '<br/>'; ?></p></div><?php } if (isset($bp_media_messages) && count($bp_media_messages)) {
            ?>
            <div class="updated"><p><?php foreach ($bp_media_messages as $message)
                echo $message . '<br/>'; ?></p></div><?php
        }

        add_menu_page('Buddypress Media Component', 'BuddyPress Media', 'manage_options', 'bp-media-settings', array($this,'settings_page'));
        add_submenu_page('bp-media-settings', __('Buddypress Media Settings', BP_MEDIA_TXT_DOMAIN), __('Settings', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-settings', array($this,"settings_page"));
        add_submenu_page('bp-media-settings', __('Buddypress Media Addons', BP_MEDIA_TXT_DOMAIN), __('Addons', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-addons', array($this,"settings_page"));
        add_submenu_page('bp-media-settings', __('Buddypress Media Support', BP_MEDIA_TXT_DOMAIN), __('Support ', BP_MEDIA_TXT_DOMAIN), 'manage_options', 'bp-media-support', array($this,"settings_page"));

        $tab = BPMediaAdmin::get_current_tab();
        add_action('admin_print_styles-' . $tab, array($this,'admin_enqueue'));
    }

    /**
     *   Applies WordPress metabox funtionality to metaboxes
     *
     *
     */
    function on_load_page() {

        /* Javascripts loaded to allow drag/drop, expand/collapse and hide/show of boxes. */
        wp_enqueue_script('common');
        wp_enqueue_script('wp-lists');
        wp_enqueue_script('postbox');

        // Check to see which tab we are on
        $tab = BPMediaAdmin::get_current_tab();

        switch ($tab) {
            case 'bp-media-addons' :
                // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('addons_list_metabox', __('BuddyPress Media Addons for Audio/Video Conversion', BP_MEDIA_TXT_DOMAIN), array($this,'addons_list'), 'bp-media-settings', 'normal', 'core');
                break;
            case 'bp-media-support' :
                // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_support_metabox', __('BuddyPress Media Support', 'rtPanel'), array($this,'bp_media_support'), 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_form_report_metabox', __('Submit a request form', 'rtPanel'), array($this,'send_request'), 'bp-media-settings', 'normal', 'core');
                break;
            case $tab :
                // All metaboxes registered during load page can be switched off/on at "Screen Options" automatically, nothing special to do therefore
                add_meta_box('bp_media_settings_metabox', __('BuddyPress Media Settings', 'rtPanel'), 'bp_media_admin_menu', 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_options_metabox', __('Spread the word', 'rtPanel'), array($this,'settings_options'), 'bp-media-settings', 'normal', 'core');
                add_meta_box('bp_media_other_options_metabox', __('BuddyPress Media Other options', 'rtPanel'), array($this,'settings_other_options'), 'bp-media-settings', 'normal', 'core');
                break;
        }
    }

    function settings_page() {

        $tab = BPMediaAdmin::get_current_tab();
        ?>

        <div class="wrap bp-media-admin">
            <div id="icon-buddypress" class="icon32"><br></div>
            <h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs(__('Media', BP_MEDIA_TXT_DOMAIN)); ?></h2>
            <div class="metabox-holder columns-2">
                <div class="bp-media-settings-tabs"><?php
        // Check to see which tab we are on
        if (current_user_can('manage_options')) {
            $tabs_html = '';
            $idle_class = 'media-nav-tab';
            $active_class = 'media-nav-tab media-nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = BPMediaAdmin::get_current_tab();
            /* BuddyPress Media */
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
        ?>
                </div>

                <div id="bp-media-settings-boxes">

                    <form id="bp_media_settings_form" name="bp_media_settings_form" action="" method="post" enctype="multipart/form-data"><?php
            settings_fields('bp_media_options_settings');
            do_settings_fields('bp_media_options_settings', '');
            echo '<div class="bp-media-metabox-holder">';

            if (isset($_REQUEST['request_type'])) {
                BPMediaUtils::bp_media_bug_report_form($_REQUEST['request_type']);
            } else {
                do_meta_boxes('bp-media-settings', 'normal', '');
            }

            echo '</div>';
        ?>

                        <script type="text/javascript">
                            //<![CDATA[
                            jQuery(document).ready( function($) {
                                // close postboxes that should be closed
                                $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                                // postboxes setup
                                postboxes.add_postbox_toggles('bp-media-settings');
                            });
                            //]]>
                        </script>

                    </form>
                </div><!-- .bp-media-settings-boxes -->
                <div class="metabox-fixed metabox-holder alignright bp-media-metabox-holder">
                    <?php $this->default_admin_sidebar(); ?>
                </div>
            </div><!-- .metabox-holder -->
        </div><!-- .bp-media-admin --><?php
                }

                /**
                 * Displays and updates the options menu of BuddyPress Media
                 *
                 * @since BuddyPress Media 2.0
                 */
                function bp_media_admin_menu() {

                    $bp_media_errors = array();
                    $bp_media_messages = array();

                    global $bp_media_options;
                    $bp_media_options = get_site_option('bp_media_options', array(
                        'videos_enabled' => true,
                        'audio_enabled' => true,
                        'images_enabled' => true,
                        'download_enabled' => true,
                        'remove_linkback' => 1,
                            ));
                    ?>

        <?php if (count($bp_media_errors)) { ?>
            <div class="error"><p><?php foreach ($bp_media_errors as $error)
                echo $error . '<br/>'; ?></p></div>
        <?php } if (count($bp_media_messages)) { ?>
            <div class="updated"><p><?php foreach ($bp_media_messages as $message)
                echo $message . '<br/>'; ?></p></div>
        <?php } ?>
        <table class="form-table ">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="videos_enabled">Videos</label></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enable Videos</span></legend>
                            <label for="videos_enabled"><input name="bp_media_options[videos_enabled]" type="checkbox" id="videos_enabled" value="true" <?php global $bp_media_options;
        checked($bp_media_options['videos_enabled'], true) ?>> (Check to enable video upload functionality)</label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="audio_enabled">Audio</label></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enable Audio</span></legend>
                            <label for="audio_enabled"><input name="bp_media_options[audio_enabled]" type="checkbox" id="audio_enabled" value="true" <?php checked($bp_media_options['audio_enabled'], true) ?>> (Check to enable audio upload functionality)</label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="images_enabled">Images</label></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enable Images</span></legend>
                            <label for="images_enabled"><input name="bp_media_options[images_enabled]" type="checkbox" id="images_enabled" value="true" <?php checked($bp_media_options['images_enabled'], true) ?>> (Check to enable images upload functionality)</label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="download_enabled">Download</label></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enable Download</span></legend>
                            <label for="download_enabled"><input name="bp_media_options[download_enabled]" type="checkbox" id="download_enabled" value="true" <?php checked($bp_media_options['download_enabled'], true) ?>> (Check to enable download functionality)</label>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php do_action('bp_media_extension_options'); ?>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary bp-media-submit" value="Save Changes"></p>
        <div class="clear"></div><?php
    }

    function settings_other_options() {
        ?>

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="refresh_media_count">Re-Count Media Entries</label></th>
                    <td> <fieldset>
                            <a id="refresh_media_count" href ="?page=bp-media-settings&bp_media_refresh_count=1&wp_nonce=<?php echo wp_create_nonce('bp_media_refresh_count'); ?>" class="button" title="<?php printf(__('It will re-count all media entries of all users and correct any discrepancies.')); ?>">Re-Count</a>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="clear"></div>

    <?php
    }

    function settings_options() {
        global $bp_media_options;
        $bp_media_options = get_site_option('bp_media_options', array(
            'videos_enabled' => true,
            'audio_enabled' => true,
            'images_enabled' => true,
            'download_enabled' => true,
            'remove_linkback' => 1,
                ));
        ?>
        <table class="form-table ">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="remove_linkback">Spread the word</label></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Yes, I want to support BuddyPress Media</span></legend>
                            <label for="remove_linkback_yes"><input name="bp_media_options[remove_linkback]" type="radio" id="remove_linkback_yes" value="2" <?php checked($bp_media_options['remove_linkback'], '2'); ?>> Yes, I support BuddyPress Media</label>
                            <br/>
                            <legend class="screen-reader-text"><span>No, I don't want to support BuddyPress Media</span></legend>
                            <label for="remove_linkback_no"><input name="bp_media_options[remove_linkback]" type="radio" id="remove_linkback_no" value="1" <?php checked($bp_media_options['remove_linkback'], '1'); ?>> No, I don't support BuddyPress Media</label>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button-primary bp-media-submit" value="Save Changes"></p>
        <div class="clear"></div>
    <?php }

    function addons_list() {
        ?>

        <div class="addon-list">
            <ul class="products">

                <li class="product first">
                    <a href="http://rtcamp.com/store/buddypress-media-kaltura/"  title="BuddyPress - Media Kaltura Add-on">
                        <img width="240" height="184" title="BuddyPress - Media Kaltura Add-on" alt="BuddyPress - Media Kaltura Add-on" src="http://cdn.rtcamp.com/files/2012/10/new-buddypress-media-kaltura-logo-240x184.png">
                    </a>
                    <h4><a href="http://rtcamp.com/store/buddypress-media-kaltura/"  title="BuddyPress - Media Kaltura Add-on">BuddyPress-Media Kaltura Add-on</a></h4>
                    <div class="product_desc">
                        <p>Add support for more video formats using Kaltura video solution.</p>
                        <p>Works with Kaltura.com, self-hosted Kaltura-CE and Kaltura-on-premise.</p>
                    </div>
                    <div class="product_footer">
                        <span class="price alignleft"><span class="amount">$99</span></span>
                        <a class="add_to_cart_button  alignright product_type_simple"  href="http://rtcamp.com/store/?add-to-cart=15446"><?php _e('Buy Now', BP_MEDIA_TXT_DOMAIN); ?></a>
                        <a class="alignleft product_demo_link"  href="http://demo.rtcamp.com/bpm-kaltura/" title="BuddyPress Media Kaltura Add-on">Live Demo</a>
                    </div><!-- .product_footer -->
                </li>
                <li class="product last">
                    <a href="http://rtcamp.com/store/buddypress-media-ffmpeg-converter/" title="BuddyPress-Media FFMPEG Converter Plugin" >
                        <img width="240" height="184" title="BuddyPress-Media FFMPEG Converter Plugin" alt="BuddyPress-Media FFMPEG Converter Plugin" src="http://cdn.rtcamp.com/files/2012/09/ffmpeg-logo-240x184.png">
                    </a>
                    <h4><a href="http://rtcamp.com/store/buddypress-media-ffmpeg-converter/" title="BuddyPress-Media FFMPEG Converter Plugin" >BuddyPress-Media FFMPEG Add-on</a></h4>
                    <div class="product_desc">
                        <p>Add supports for more audio &amp; video formats using open-source <a href="https://github.com/rtCamp/media-node">media-node</a>.</p>
                        <p>Media node comes with automated setup script for Ubuntu/Debian.</p>
                    </div>
                    <div class="product_footer">
                        <span class="price alignleft"><span class="amount">$49</span></span>
                        <a class="add_to_cart_button alignright  product_type_simple"  href="http://rtcamp.com/store/?add-to-cart=13677"><?php _e('Buy Now', BP_MEDIA_TXT_DOMAIN); ?></a>
                        <a class="alignleft product_demo_link" href="http://demo.rtcamp.com/bpm-media" title="BuddyPress Media FFMPEG Add-on">Live Demo</a>
                    </div><!-- .product_footer -->
                </li>

            </ul><!-- .products -->
        </div><!-- .addon-list -->

    <?php }

    function support() {
        global $bp_media;
        ?>

        <div class="bp-media-support">
            <h2><?php _e('Need Help/Support?', BP_MEDIA_TXT_DOMAIN); ?></h2>
            <ul class="support_list">
                <li><a href="http://rtcamp.com/buddypress-media/faq/"  title="<?php _e('Read FAQ', BP_MEDIA_TXT_DOMAIN); ?>"><?php _e('Read FAQ', BP_MEDIA_TXT_DOMAIN); ?></a> </li>
                <li><a href="<?php $bp_media->support_url; ?>"  title="<?php _e('Free Support Forum', BP_MEDIA_TXT_DOMAIN); ?>"><?php _e('Free Support Forum', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                <li><a href="https://github.com/rtCamp/buddypress-media/issues/"  title="<?php _e('Github Issue Tracker', BP_MEDIA_TXT_DOMAIN); ?>"><?php _e('Github Issue Tracker', BP_MEDIA_TXT_DOMAIN); ?> </a> </li>
            </ul>
            <br/>

            <h2><?php _e('Hire Us!', BP_MEDIA_TXT_DOMAIN); ?></h2>
            <h4><a href="http://rtcamp.com/contact/?purpose=hire"><?php _e('We are available for customisation and premium support. Get on touch with us. :-)', BP_MEDIA_TXT_DOMAIN); ?></a></h4>
            <br/>
        </div>

    <?php
    }

    function handle_request_type() {
        $request_type = $_REQUEST['request_type'];
        BPMediaUtils::bp_media_bug_report_form($request_type);
        die();
    }

    function cancel_request() {
        ?>
        <div class="postbox ">
            <div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span><?php _e('BuddyPress Media Support', BP_MEDIA_TXT_DOMAIN); ?></span></h3>
            <div class="inside"><?php $this->support(); ?></div>
        </div>
        <div class="postbox ">
            <div title="Click to toggle" class="handlediv"><br></div><h3 class="hndle"><span><?php _e('Submit a request form', BP_MEDIA_TXT_DOMAIN); ?></span></h3>
            <div class="inside"><?php $this->send_request(); ?></div>
        </div><?php
        die();
    }

    function send_request() {
        ?>
        <div id="support-form" class="bp-media-form">
            <ul>
                <li>
                    <label class="bp-media-label" for="bp-media-request"><?php _e('Request type:', BP_MEDIA_TXT_DOMAIN); ?></label>
                    <select class="bp-media-select" id="request_type_select">
                        <option value=""><?php _e('-- Choose Type --', BP_MEDIA_TXT_DOMAIN); ?></option>
                        <option value="premium_support"><?php _e('Premium Support', BP_MEDIA_TXT_DOMAIN); ?></option>
                        <option value="new_feature"><?php _e('Suggest a New Feature', BP_MEDIA_TXT_DOMAIN); ?></option>
                        <option value="bug_report"><?php _e('Submit a Bug Report', BP_MEDIA_TXT_DOMAIN); ?></option>
                    </select>
                </li>
            </ul>
        </div>
    <?php
    }

    /**
     * Default BuddyPress Media admin sidebar with metabox styling
     *
     * @since BuddyPress Media 2.0
     */
    function default_admin_sidebar() {
        global $bp_media;
        ?>

        <div class="rtmetabox postbox" id="branding">
            <div class="inside">
                <a href="http://rtcamp.com" title="Empowering The Web With WordPress" id="logo"><img src="<?php echo plugins_url('/img/rtcamp-logo.png', __FILE__); ?>" alt="rtCamp" /></a>
                <ul id="social">
                    <li><a href="<?php printf('%s', 'http://www.facebook.com/rtCamp.solutions/'); ?>"  title="<?php _e('Become a fan on Facebook', BP_MEDIA_TXT_DOMAIN); ?>" class="bp-media-facebook bp-media-social"><?php _e('Facebook', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                    <li><a href="<?php printf('%s', 'https://twitter.com/rtcamp/'); ?>"  title="<?php _e('Follow us on Twitter', BP_MEDIA_TXT_DOMAIN); ?>" class="bp-media-twitter bp-media-social"><?php _e('Twitter', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                    <li><a href="<?php printf('%s', 'http://feeds.feedburner.com/rtcamp/'); ?>"  title="<?php _e('Subscribe to our feeds', BP_MEDIA_TXT_DOMAIN); ?>" class="bp-media-rss bp-media-social"><?php _e('RSS Feed', BP_MEDIA_TXT_DOMAIN); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="rtmetabox postbox" id="support">

            <h3 class="hndle"><span><?php _e('Need Help?', BP_MEDIA_TXT_DOMAIN); ?></span></h3>
            <div class="inside"><p><?php printf(__(' Please use our <a href="%s">free support forum</a>.<br/><span class="bpm-aligncenter">OR</span><br/>
		<a href="%s">Hire us!</a> To get professional customisation/setup service.', BP_MEDIA_TXT_DOMAIN), $bp_media->support_url, 'http://rtcamp.com/buddypress-media/hire/'); ?>.</p></div>
        </div>

        <div class="rtmetabox postbox" id="donate">

            <h3 class="hndle"><span><?php _e('Donate', BP_MEDIA_TXT_DOMAIN); ?></span></h3>
            <span><a href="http://rtcamp.com/donate/" title="Help the development keep going."><img class="bp-media-donation-image" src ="<?php echo plugins_url('/img/donate.gif', __FILE__); ?>"   /></a></span>
            <div class="inside"><p><?php printf(__('Help us release more amazing features faster. Consider making a donation to our consistent efforts.', BP_MEDIA_TXT_DOMAIN)); ?>.</p></div>
        </div>

        <div class="rtmetabox postbox" id="bp-media-premium-addons">

            <h3 class="hndle"><span><?php _e('Premium Addons', BP_MEDIA_TXT_DOMAIN); ?></span></h3>
            <div class="inside">
                <ul>
                    <li><a href="http://rtcamp.com/store/buddypress-media-kaltura/" title="BuddyPress Media Kaltura">BPM-Kaltura</a> - add support for Kaltura.com/Kaltura-CE based video conversion support</li>
                    <li><a href="http://rtcamp.com/store/buddy-press-media-ffmpeg/" title="BuddyPress Media FFMPEG">BPM-FFMPEG</a> - add FFMEG-based audio/video conversion support</li>
                </ul>
                <h4><?php printf(__('Are you a developer?', BP_MEDIA_TXT_DOMAIN)) ?></h4>
                <p><?php printf(__('If you are developing a BuddyPress Media addon we would like to include it in above list. We can also help you sell them. <a href="%s">More info!</a>', BP_MEDIA_TXT_DOMAIN), 'http://rtcamp.com/contact/') ?></p></h4>
            </div>
        </div>

        <div class="rtmetabox postbox" id="bp_media_latest_news">

            <h3 class="hndle"><span><?php _e('Latest News', BP_MEDIA_TXT_DOMAIN); ?></span></h3>
            <div class="inside"><img src ="<?php echo admin_url(); ?>/images/wpspin_light.gif" /> Loading...</div>
        </div><?php
    }

    /**
     * Enqueues the scripts and stylesheets needed for the BuddyPress Media's options page
     */
    function admin_enqueue() {
        $current_screen = get_current_screen();
        $admin_js = trailingslashit(site_url()) . '?bp_media_get_feeds=1';
        wp_enqueue_script('bp-media-js', plugins_url('includes/js/bp-media.js', dirname(__FILE__)));
        wp_localize_script('bp-media-js', 'bp_media_news_url', $admin_js);
        wp_enqueue_style('bp-media-admin-style', plugins_url('includes/css/bp-media-style.css', dirname(__FILE__)));
    }

    /**
     * Adds a tab for Media settings in the BuddyPress settings page
     */
    function admin_tab() {

        if (current_user_can('manage_options')) {
            $tabs_html = '';
            $idle_class = 'nav-tab';
            $active_class = 'nav-tab nav-tab-active';
            $tabs = array();

            // Check to see which tab we are on
            $tab = BPMediaAdmin::get_current_tab();
            /* BuddyPress Media */
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
    }

    function get_server_info() {
        global $wp_version, $wp_db_version, $wpdb;

        $wordpress_plugins = get_plugins();
        $wordpress_plugins_active = array();

        foreach ($wordpress_plugins as $wordpress_plugin_file => $wordpress_plugin) {

            if (is_plugin_active($wordpress_plugin_file)) {

                $plugin_info['plugin_name'] = $wordpress_plugin['Name'];
                $plugin_info['PluginURI'] = $wordpress_plugin['PluginURI'];
                $plugin_info['Version'] = $wordpress_plugin['Version'];
                $plugin_info['AuthorURI'] = $wordpress_plugin['AuthorURI'];
                $wordpress_plugins_active[$wordpress_plugin_file] = $plugin_info;
            }
        }
        $theme_info = wp_get_theme();
        $theme_data = array();
        $theme_data['Name'] = $theme_info->Name;
        $theme_data['ThemeURI'] = $theme_info->ThemeURI;
        $theme_data['Description'] = $theme_info->Description;
        $theme_data['AuthorURI'] = $theme_info->AuthorURI;
        $theme_data['Version'] = $theme_info->Version;

        $mysql_version = $wpdb->get_var('SELECT VERSION()');

        $server_info = array(
            'Wordpress' => array(
                'version' => $wp_version,
                'db_version' => $wp_db_version,
                'abspath' => ABSPATH,
                'home' => get_option('home'),
                'siteurl' => get_option('siteurl'),
                'email' => get_option('admin_email'),
                'upload_info' => @wp_upload_dir(),
            ),
            'Theme' => $theme_data,
            'Plugins' => $wordpress_plugins_active,
            'Mysql' => array(
                'version' => $mysql_version
            )
        );

        return $server_info;
    }

}
?>