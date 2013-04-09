<?php
/**
 * Description of BPMediaSupport
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaSupport')) {

    class BPMediaSupport {

        var $debug_info;

        public function __construct() {
            $this->debug_info();
            add_action('bp_media_admin_page_append', array($this, 'debug_info_html'));
        }

        public function debug_info() {
            global $wpdb, $wp_version, $bp;
            $debug_info = array();
            $debug_info['PHP'] = PHP_VERSION;
            $debug_info['MYSQL'] = $wpdb->db_version();
            $debug_info['WordPress'] = $wp_version;
            $debug_info['BuddyPress'] = $bp->version;
            $debug_info['BuddyPress Media'] = BP_MEDIA_VERSION;
            $debug_info['OS'] = PHP_OS;
            if (extension_loaded('imagick')) {
                $imagick = $message = preg_replace(" #((http|https|ftp)://(\S*?\.\S*?))(\s|\;|\)|\]|\[|\{|\}|,|\"|'|:|\<|$|\.\s)#ie", "'<a href=\"$1\" target=\"_blank\">$3</a>$4'", Imagick::getVersion() );
            } else {
                $imagick['versionString'] = 'Not Installed';
            }
            $debug_info['Imagick'] = $imagick['versionString'];
            if (extension_loaded('gd')) {
                $gd = gd_info();
            } else {
                $gd['GD Version'] = 'Not Installed';
            }
            $debug_info['GD'] = $gd['GD Version'];
            $debug_info['[php.ini] post_max_size'] = ini_get('post_max_size');
            $debug_info['[php.ini] upload_max_filesize'] = ini_get('upload_max_filesize');
            $debug_info['[php.ini] memory_limit'] = ini_get('memory_limit');
            $this->debug_info = $debug_info;
        }

        public function debug_info_html($page) {
            if ('bp-media-support' == $page) {
                ?>
                <div id="debug-info">
                    <h3><?php _e('Debug info', 'buddypress-media'); ?></h3>
                    <table class="form-table">
                        <tbody><?php
                if ($this->debug_info) {
                    foreach ($this->debug_info as $configuration => $value) {
                        ?>
                                    <tr valign="top">
                                        <th scope="row"><?php echo $configuration; ?></th>
                                        <td><?php echo $value; ?></td>
                                    </tr><?php
                    }
                }
                ?>
                        </tbody>
                    </table>
                </div><?php
            }
        }

        /**
         *
         * @global type $current_user
         * @param type $form
         */
        public function get_form($form) {
            if (empty($form))
                $form = (isset($_POST['form'])) ? $_POST['form'] : '';

            global $current_user;
            switch ($form) {
                case "bug_report":
                    $meta_title = __('Submit a Bug Report', 'buddypress-media');
                    break;
                case "new_feature":
                    $meta_title = __('Submit a New Feature Request', 'buddypress-media');
                    break;
                case "premium_support":
                    $meta_title = __('Submit a Premium Support Request', 'buddypress-media');
                    break;
            }
            ?>
            <h3><?php echo $meta_title; ?></h3>
            <div id="support-form" class="bp-media-form">
                <ul>
                    <li>
                        <label class="bp-media-label" for="name"><?php _e('Name', 'buddypress-media'); ?>:</label><input class="bp-media-input" id="name" type="text" name="name" value="<?php echo (isset($_REQUEST['name'])) ? esc_attr(stripslashes(trim($_REQUEST['name']))) : $current_user->display_name; ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="email"><?php _e('Email', 'buddypress-media'); ?>:</label><input id="email" class="bp-media-input" type="text" name="email" value="<?php echo (isset($_REQUEST['email'])) ? esc_attr(stripslashes(trim($_REQUEST['email']))) : get_option('admin_email'); ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="website"><?php _e('Website', 'buddypress-media'); ?>:</label><input id="website" class="bp-media-input" type="text" name="website" value="<?php echo (isset($_REQUEST['website'])) ? esc_attr(stripslashes(trim($_REQUEST['website']))) : get_bloginfo('url'); ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="phone"><?php _e('Phone', 'buddypress-media'); ?>:</label><input class="bp-media-input" id="phone" type="text" name="phone" value="<?php echo (isset($_REQUEST['phone'])) ? esc_attr(stripslashes(trim($_REQUEST['phone']))) : ''; ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="subject"><?php _e('Subject', 'buddypress-media'); ?>:</label><input id="subject" class="bp-media-input" type="text" name="subject" value="<?php echo (isset($_REQUEST['subject'])) ? esc_attr(stripslashes(trim($_REQUEST['subject']))) : ''; ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="details"><?php _e('Details', 'buddypress-media'); ?>:</label><textarea id="details" class="bp-media-textarea" type="text" name="details" required/><?php echo (isset($_REQUEST['details'])) ? esc_textarea(stripslashes(trim($_REQUEST['details']))) : ''; ?></textarea>
                    </li>
                    <input type="hidden" name="request_type" value="<?php echo $form; ?>"/>
                    <input type="hidden" name="request_id" value="<?php echo wp_create_nonce(date('YmdHis')); ?>"/>
                    <input type="hidden" name="server_address" value="<?php echo $_SERVER['SERVER_ADDR']; ?>"/>
                    <input type="hidden" name="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>"/>
                    <input type="hidden" name="server_type" value="<?php echo $_SERVER['SERVER_SOFTWARE']; ?>"/>
                    <input type="hidden" name="user_agent" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>"/>

                </ul>
            </div><!-- .submit-bug-box --><?php if ($form == 'bug_report') { ?>
                <h3><?php _e('Additional Information', 'buddypress-media'); ?></h3>
                <div id="support-form" class="bp-media-form">
                    <ul>

                        <li>
                            <label class="bp-media-label" for="wp_admin_username"><?php _e('Your WP Admin Login:', 'buddypress-media'); ?></label><input class="bp-media-input" id="wp_admin_username" type="text" name="wp_admin_username" value="<?php echo (isset($_REQUEST['wp_admin_username'])) ? esc_attr(stripslashes(trim($_REQUEST['wp_admin_username']))) : $current_user->user_login; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="wp_admin_pwd"><?php _e('Your WP Admin password:', 'buddypress-media'); ?></label><input class="bp-media-input" id="wp_admin_pwd" type="password" name="wp_admin_pwd" value="<?php echo (isset($_REQUEST['wp_admin_pwd'])) ? esc_attr(stripslashes(trim($_REQUEST['wp_admin_pwd']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ssh_ftp_host"><?php _e('Your SSH / FTP host:', 'buddypress-media'); ?></label><input class="bp-media-input" id="ssh_ftp_host" type="text" name="ssh_ftp_host" value="<?php echo (isset($_REQUEST['ssh_ftp_host'])) ? esc_attr(stripslashes(trim($_REQUEST['ssh_ftp_host']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ssh_ftp_username"><?php _e('Your SSH / FTP login:', 'buddypress-media'); ?></label><input class="bp-media-input" id="ssh_ftp_username" type="text" name="ssh_ftp_username" value="<?php echo (isset($_REQUEST['ssh_ftp_username'])) ? esc_attr(stripslashes(trim($_REQUEST['ssh_ftp_username']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ssh_ftp_pwd"><?php _e('Your SSH / FTP password:', 'buddypress-media'); ?></label><input class="bp-media-input" id="ssh_ftp_pwd" type="password" name="ssh_ftp_pwd" value="<?php echo (isset($_REQUEST['ssh_ftp_pwd'])) ? esc_attr(stripslashes(trim($_REQUEST['ssh_ftp_pwd']))) : ''; ?>"/>
                        </li>
                    </ul>
                </div><!-- .submit-bug-box --><?php } ?>

            <?php submit_button('Submit', 'primary', 'submit-request', false); ?>
            <?php submit_button('Cancel', 'secondary', 'cancel-request', false); ?>

            <?php
            if (DOING_AJAX) {
                die();
            }
        }

        /**
         *
         * @global type $bp_media
         */
        public function submit_request() {
            global $bp_media;
            $form_data = wp_parse_args($_POST['form_data']);
            if ($form_data['request_type'] == 'premium_support') {
                $mail_type = 'Premium Support';
                $title = __('BuddyPress Media Premium Support Request from', 'buddypress-media');
            } elseif ($form_data['request_type'] == 'new_feature') {
                $mail_type = 'New Feature Request';
                $title = __('BuddyPress Media New Feature Request from', 'buddypress-media');
            } elseif ($form_data['request_type'] == 'bug_report') {
                $mail_type = 'Bug Report';
                $title = __('BuddyPress Media Bug Report from', 'buddypress-media');
            } else {
                $mail_type = 'Bug Report';
                $title = __('BuddyPress Media Contact from', 'buddypress-media');
            }
            $message = '<html>
                            <head>
                                    <title>' . $title . get_bloginfo('name') . '</title>
                            </head>
                            <body>
				<table>
                                    <tr>
                                        <td>Name</td><td>' . strip_tags($form_data['name']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td><td>' . strip_tags($form_data['email']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Website</td><td>' . strip_tags($form_data['website']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Phone</td><td>' . strip_tags($form_data['phone']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Subject</td><td>' . strip_tags($form_data['subject']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Details</td><td>' . strip_tags($form_data['details']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Request ID</td><td>' . strip_tags($form_data['request_id']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Server Address</td><td>' . strip_tags($form_data['server_address']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>IP Address</td><td>' . strip_tags($form_data['ip_address']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Server Type</td><td>' . strip_tags($form_data['server_type']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>User Agent</td><td>' . strip_tags($form_data['user_agent']) . '</td>
                                    </tr>';
            if ($form_data['request_type'] == 'bug_report') {
                $message .= '<tr>
                                        <td>WordPress Admin Username</td><td>' . strip_tags($form_data['wp_admin_username']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>WordPress Admin Password</td><td>' . strip_tags($form_data['wp_admin_pwd']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>SSH FTP Host</td><td>' . strip_tags($form_data['ssh_ftp_host']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>SSH FTP Username</td><td>' . strip_tags($form_data['ssh_ftp_username']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>SSH FTP Password</td><td>' . strip_tags($form_data['ssh_ftp_pwd']) . '</td>
                                    </tr>
                                    ';
            }
            $message .= '</table>';
            if ( $this->debug_info ) {
                $message .= '<h3>'.__('Debug Info', 'buddypress-media').'</h3>';
                $message .= '<table>';
                foreach ($this->debug_info as $configuration => $value) {
                    $message .= '<tr>
                                    <td>' . $configuration . '</td><td>' . $value . '</td>
                                </tr>';
                }
                $message .= '</table>';
            }
            $message .= '</body>
                </html>';
            add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
            $headers = 'From: ' . $form_data['name'] . ' <' . $form_data['email'] . '>' . "\r\n";
            if (wp_mail($bp_media->support_email, '[buddypress-media] ' . $mail_type . ' from ' . str_replace(array('http://', 'https://'), '', $form_data['website']), $message, $headers)) {
                if ($form_data['request_type'] == 'new_feature') {
                    echo '<p>' . __('Thank you for your Feedback/Suggestion.', 'buddypress-media') . '</p>';
                } else {
                    echo '<p>' . __('Thank you for posting your support request.', 'buddypress-media') . '</p>';
                    echo '<p>' . __('We will get back to you shortly.', 'buddypress-media') . '</p>';
                }
            } else {
                echo '<p>' . __('Your server failed to send an email.', 'buddypress-media') . '</p>';
                echo '<p>' . __('Kindly contact your server support to fix this.', 'buddypress-media') . '</p>';
                echo '<p>' . sprintf(__('You can alternatively create a support request <a href="%s">here</a>', 'buddypress-media'), $bp_media->support_url) . '</p>';
            }
            die();
        }

    }

}
?>
