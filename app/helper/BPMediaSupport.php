<?php
/**
 * Description of BPMediaSupport
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaSupport')) {

    class BPMediaSupport {

        public function __construct() {
            add_action('bp_media_admin_page_append', array($this, 'debug_info'));
        }

        public function debug_info($page) {
            if ('bp-media-support' == $page) {
                global $wpdb, $wp_version, $bp;
                ?>
                <div id="debug-info">
                    <h3><?php _e('Debug Info', BP_MEDIA_TXT_DOMAIN); ?></h3>
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row">PHP</th>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">MYSQL</th>
                                <td><?php echo $wpdb->db_version(); ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">WordPress</th>
                                <td><?php echo $wp_version; ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">BuddyPress</th>
                                <td><?php echo $bp->version; ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">BuddyPress Media</th>
                                <td><?php echo BP_MEDIA_VERSION; ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">OS</th>
                                <td><?php echo PHP_OS; ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Imagick</th><?php
                                if (extension_loaded('imagick')) {
                                    $imagick = Imagick::getVersion();
                                } else {
                                    $imagick['versionString'] = 'Not Installed';
                                } ?>
                                <td><?php echo $imagick['versionString']; ?></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">GD</th><?php
                                if (extension_loaded('gd')) {
                                    $gd = gd_info();
                                } else {
                                    $gd['GD Version'] = 'Not Installed';
                                } ?>
                                <td><?php echo $gd['GD Version']; ?></td>
                            </tr>
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
                    $meta_title = __('Submit a Bug Report', BP_MEDIA_TXT_DOMAIN);
                    break;
                case "new_feature":
                    $meta_title = __('Submit a New Feature Request', BP_MEDIA_TXT_DOMAIN);
                    break;
                case "premium_support":
                    $meta_title = __('Submit a Premium Support Request', BP_MEDIA_TXT_DOMAIN);
                    break;
            }
            ?>
            <h3><?php echo $meta_title; ?></h3>
            <div id="support-form" class="bp-media-form">               
                <ul>
                    <li>
                        <label class="bp-media-label" for="name"><?php _e('Name', BP_MEDIA_TXT_DOMAIN); ?>:</label><input class="bp-media-input" id="name" type="text" name="name" value="<?php echo (isset($_REQUEST['name'])) ? esc_attr(stripslashes(trim($_REQUEST['name']))) : $current_user->display_name; ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="email"><?php _e('Email', BP_MEDIA_TXT_DOMAIN); ?>:</label><input id="email" class="bp-media-input" type="text" name="email" value="<?php echo (isset($_REQUEST['email'])) ? esc_attr(stripslashes(trim($_REQUEST['email']))) : get_option('admin_email'); ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="website"><?php _e('Website', BP_MEDIA_TXT_DOMAIN); ?>:</label><input id="website" class="bp-media-input" type="text" name="website" value="<?php echo (isset($_REQUEST['website'])) ? esc_attr(stripslashes(trim($_REQUEST['website']))) : get_bloginfo('url'); ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="phone"><?php _e('Phone', BP_MEDIA_TXT_DOMAIN); ?>:</label><input class="bp-media-input" id="phone" type="text" name="phone" value="<?php echo (isset($_REQUEST['phone'])) ? esc_attr(stripslashes(trim($_REQUEST['phone']))) : ''; ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="subject"><?php _e('Subject', BP_MEDIA_TXT_DOMAIN); ?>:</label><input id="subject" class="bp-media-input" type="text" name="subject" value="<?php echo (isset($_REQUEST['subject'])) ? esc_attr(stripslashes(trim($_REQUEST['subject']))) : ''; ?>" required />
                    </li>
                    <li>
                        <label class="bp-media-label" for="details"><?php _e('Details', BP_MEDIA_TXT_DOMAIN); ?>:</label><textarea id="details" class="bp-media-textarea" type="text" name="details" required/><?php echo (isset($_REQUEST['details'])) ? esc_textarea(stripslashes(trim($_REQUEST['details']))) : ''; ?></textarea>
                    </li>
                    <input type="hidden" name="request_type" value="<?php echo $form; ?>"/>
                    <input type="hidden" name="request_id" value="<?php echo wp_create_nonce(date('YmdHis')); ?>"/>
                    <input type="hidden" name="server_address" value="<?php echo $_SERVER['SERVER_ADDR']; ?>"/>                    
                    <input type="hidden" name="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>"/>                    
                    <input type="hidden" name="server_type" value="<?php echo $_SERVER['SERVER_SOFTWARE']; ?>"/>
                    <input type="hidden" name="user_agent" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>"/>

                </ul>
            </div><!-- .submit-bug-box --><?php if ($form == 'bug_report') { ?>   
                <h3><?php _e('Additional Information', BP_MEDIA_TXT_DOMAIN); ?></h3>
                <div id="support-form" class="bp-media-form">  
                    <ul>

                        <li>
                            <label class="bp-media-label" for="wp_admin_username"><?php _e('Your WP Admin Login:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="wp_admin_username" type="text" name="wp_admin_username" value="<?php echo (isset($_REQUEST['wp_admin_username'])) ? esc_attr(stripslashes(trim($_REQUEST['wp_admin_username']))) : $current_user->user_login; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="wp_admin_pwd"><?php _e('Your WP Admin password:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="wp_admin_pwd" type="password" name="wp_admin_pwd" value="<?php echo (isset($_REQUEST['wp_admin_pwd'])) ? esc_attr(stripslashes(trim($_REQUEST['wp_admin_pwd']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ssh_ftp_host"><?php _e('Your SSH / FTP host:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ssh_ftp_host" type="text" name="ssh_ftp_host" value="<?php echo (isset($_REQUEST['ssh_ftp_host'])) ? esc_attr(stripslashes(trim($_REQUEST['ssh_ftp_host']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ssh_ftp_username"><?php _e('Your SSH / FTP login:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ssh_ftp_username" type="text" name="ssh_ftp_username" value="<?php echo (isset($_REQUEST['ssh_ftp_username'])) ? esc_attr(stripslashes(trim($_REQUEST['ssh_ftp_username']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ssh_ftp_pwd"><?php _e('Your SSH / FTP password:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ssh_ftp_pwd" type="password" name="ssh_ftp_pwd" value="<?php echo (isset($_REQUEST['ssh_ftp_pwd'])) ? esc_attr(stripslashes(trim($_REQUEST['ssh_ftp_pwd']))) : ''; ?>"/>
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
                $title = __('BuddyPress Media Premium Support Request from', BP_MEDIA_TXT_DOMAIN);
            } elseif ($form_data['request_type'] == 'new_feature') {
                $mail_type = 'New Feature Request';
                $title = __('BuddyPress Media New Feature Request from', BP_MEDIA_TXT_DOMAIN);
            } elseif ($form_data['request_type'] == 'bug_report') {
                $mail_type = 'Bug Report';
                $title = __('BuddyPress Media Bug Report from', BP_MEDIA_TXT_DOMAIN);
            } else {
                $mail_type = 'Bug Report';
                $title = __('BuddyPress Media Contact from', BP_MEDIA_TXT_DOMAIN);
            }
            $message = '<html>
                            <head>
                                    <title>' . $title . get_bloginfo('name') . '</title>
                            </head>
                            <body>
				<table>
                                    <tr>
                                        <td>' . __("Name", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['name']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Email", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['email']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Website", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['website']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Phone", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['phone']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Subject", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['subject']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Details", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['details']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Request ID", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['request_id']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Server Address", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['server_address']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("IP Address", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['ip_address']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("Server Type", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['server_type']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("User Agent", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['user_agent']) . '</td>
                                    </tr>';
            if ($form_data['request_type'] == 'bug_report') {
                $message .= '<tr>
                                        <td>' . __("WordPress Admin Username", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['wp_admin_username']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("WordPress Admin Password", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['wp_admin_pwd']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("SSH FTP Host", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['ssh_ftp_host']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("SSH FTP Username", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['ssh_ftp_username']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __("SSH FTP Password", BP_MEDIA_TXT_DOMAIN) . '</td><td>' . strip_tags($form_data['ssh_ftp_pwd']) . '</td>
                                    </tr>
                                    ';
            }
            $message .= '</table>
                    </body>
                </html>';
            add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
            $headers = 'From: ' . $form_data['name'] . ' <' . $form_data['email'] . '>' . "\r\n";
            if (wp_mail($bp_media->support_email, '[buddypress-media] ' . $mail_type . ' from ' . str_replace(array('http://', 'https://'), '', $form_data['website']), $message, $headers)) {
                if ($form_data['request_type'] == 'new_feature') {
                    echo '<p>' . __('Thank you for your Feedback/Suggestion.', BP_MEDIA_TXT_DOMAIN) . '</p>';
                } else {
                    echo '<p>' . __('Thank you for posting your support request.', BP_MEDIA_TXT_DOMAIN) . '</p>';
                    echo '<p>' . __('We will get back to you shortly.', BP_MEDIA_TXT_DOMAIN) . '</p>';
                }
            } else {
                echo '<p>' . __('Your server failed to send an email.', BP_MEDIA_TXT_DOMAIN) . '</p>';
                echo '<p>' . __('Kindly contact your server support to fix this.', BP_MEDIA_TXT_DOMAIN) . '</p>';
                echo '<p>' . sprintf(__('You can alternatively create a support request <a href="%s">here</a>', BP_MEDIA_TXT_DOMAIN), $bp_media->support_url) . '</p>';
            }
            die();
        }

    }

}
?>
