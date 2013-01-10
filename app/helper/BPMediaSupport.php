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
            ;
        }

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
                        <label class="bp-media-label" for="ur_name"><?php _e('Your Name:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_name" type="text" name="ur_name" value="<?php echo (isset($_REQUEST['ur_name'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_name']))) : $current_user->user_login; ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="ur_email"><?php _e('Your Email-Id:', BP_MEDIA_TXT_DOMAIN); ?></label><input id="ur_email" class="bp-media-input" type="text" name="ur_email" value="<?php echo (isset($_REQUEST['ur_email'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_email']))) : get_option('admin_email'); ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="ur_site_url"><?php _e('Your Site Url:', BP_MEDIA_TXT_DOMAIN); ?></label><input id="ur_site_url" class="bp-media-input" type="text" name="ur_site_url" value="<?php echo (isset($_REQUEST['ur_site_url'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_site_url']))) : get_bloginfo('url'); ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="ur_phone"><?php _e('Your Phone:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_phone" type="text" name="ur_phone" value="<?php echo (isset($_REQUEST['ur_phone'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_phone']))) : ''; ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="ur_subject"><?php _e('Subject:', BP_MEDIA_TXT_DOMAIN); ?></label><input id="ur_subject" class="bp-media-input" type="text" name="ur_subject" value="<?php echo (isset($_REQUEST['ur_subject'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_subject']))) : ''; ?>"/>
                    </li>
                    <li>
                        <label class="bp-media-label" for="ur_query"><?php _e('Details:', BP_MEDIA_TXT_DOMAIN); ?></label><textarea id="ur_query" class="bp-media-textarea" type="text" name="ur_query"/><?php echo (isset($_REQUEST['ur_query'])) ? esc_textarea(stripslashes(trim($_REQUEST['ur_query']))) : ''; ?></textarea>
                    </li><?php if ($form == 'bug_report') { ?>
                        <li class="bp-media-support-attachment">
                            <label class="bp-media-label" for="ur_attachment"><?php _e('Attachment:', BP_MEDIA_TXT_DOMAIN); ?></label>
                            <div class="more-attachment"><input id="ur_attachment-0" class="bp-media-input" type="file" name="ur_attachment[]" value="<?php echo (isset($_REQUEST['bug_report']['ur_attachment'])) ? $_REQUEST['bug_report']['ur_attachment'] : ''; ?>"/></div>
                            <a href="#" class="add-more-attachment-btn"><?php _e('Add more attachment', BP_MEDIA_TXT_DOMAIN); ?></a>
                        </li><?php }
            ?>
                    <input type="hidden" name="request_type" value="<?php echo $form; ?>"/>
                    <input type="hidden" name="request_id" value="<?php echo date('YmdHi'); ?>"/>
                    <input type="hidden" name="server_address" value="<?php echo $_SERVER['SERVER_ADDR']; ?>"/>                    
                    <input type="hidden" name="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>"/>                    
                    <input type="hidden" name="server_type" value="<?php echo $_SERVER['SERVER_SOFTWARE']; ?>"/>
                    <input type="hidden" name="user_agent" value="<?php echo $_SERVER['HTTP_USER_AGENT']; ?>"/>

                </ul>
            </div><!-- .submit-bug-box --><?php if ($form == 'bug_report') { ?>   
                <h3>Additional Information</h3>
                <div id="support-form" class="bp-media-form">  
                    <ul>

                        <li>
                            <label class="bp-media-label" for="ur_wp_admin_login"><?php _e('Your WP Admin Login:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_wp_admin_login" type="text" name="ur_wp_admin_login" value="<?php echo (isset($_REQUEST['ur_wp_admin_login'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_wp_admin_login']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ur_wp_admin_pwd"><?php _e('Your WP Admin password:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_wp_admin_pwd" type="text" name="ur_wp_admin_pwd" value="<?php echo (isset($_REQUEST['ur_wp_admin_pwd'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_wp_admin_pwd']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ur_ssh_ftp_host"><?php _e('Your SSH / FTP host:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_ssh_ftp_host" type="text" name="ur_ssh_ftp_host" value="<?php echo (isset($_REQUEST['ur_ssh_ftp_host'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_ssh_ftp_host']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ur_ssh_ftp_login"><?php _e('Your SSH / FTP login:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_ssh_ftp_login" type="text" name="ur_ssh_ftp_login" value="<?php echo (isset($_REQUEST['ur_ssh_ftp_login'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_ssh_ftp_login']))) : ''; ?>"/>
                        </li>
                        <li>
                            <label class="bp-media-label" for="ur_ssh_ftp_pwd"><?php _e('Your SSH / FTP password:', BP_MEDIA_TXT_DOMAIN); ?></label><input class="bp-media-input" id="ur_ssh_ftp_pwd" type="text" name="ur_ssh_ftp_pwd" value="<?php echo (isset($_REQUEST['ur_ssh_ftp_pwd'])) ? esc_attr(stripslashes(trim($_REQUEST['ur_ssh_ftp_pwd']))) : ''; ?>"/>
                        </li>
                        <li>
                            <input class="bp-media-checkbox" id="ur_send_phpinfo" type="checkbox" name="ur_send_phpinfo" <?php if (isset($_REQUEST['ur_send_phpinfo'])) checked('true', $_REQUEST['ur_send_phpinfo'], true); ?> value="true"/><label class="bp-media-label" for="ur_send_phpinfo"><?php _e('Send PHP Info', BP_MEDIA_TXT_DOMAIN); ?></label>
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

    }

}
?>
