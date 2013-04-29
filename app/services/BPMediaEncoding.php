<?php

/**
 * Description of BPMediaEncoding
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class BPMediaEncoding {

    protected $api_url = 'http://api.rtcamp.com/';
    protected $sandbox_testing = 1;
    protected $merchant_id = 'SA8CC3GSCMB2U';

    public function __construct() {
        $this->api_key = bp_get_option('bp-media-encoding-api-key');
        if (is_admin()) {
            add_action(bp_core_admin_hook(), array($this, 'menu'));
            add_action('admin_init', array($this, 'encoding_settings'));
            add_filter('bp_media_add_sub_tabs', array($this, 'encoding_tab'), '', 2);
            if ($this->api_key)
                add_action('bp_media_before_default_admin_widgets', array($this, 'usage_widget'));
        }
        add_action('init', array($this, 'save_api_key'));
        add_filter('bp_media_add_admin_bar_item', array($this, 'admin_bar_menu'));
        if ($this->api_key) {
            $usage_info = bp_get_option('bp-media-encoding-usage');
            if ($usage_info) {
                if (isset($usage_info[$this->api_key]->status) && $usage_info[$this->api_key]->status) {
                    if (isset($usage_info[$this->api_key]->remaining) && $usage_info[$this->api_key]->remaining > 0) {
                        if ($usage_info[$this->api_key]->remaining < 524288000 && !bp_get_option('bp-media-encoding-usage-limit-mail'))
                            $this->nearing_usage_limit($usage_info);
                        elseif ($usage_info[$this->api_key]->remaining > 524288000 && bp_get_option('bp-media-encoding-usage-limit-mail'))
                            bp_update_option('bp-media-encoding-usage-limit-mail', 0);
                        add_filter('bp_media_transcoder', array($this, 'enqueue'), 10, 2);
                        add_filter('bp_media_plupload_files_filter', array($this, 'allowed_types'));
                    }
                }
            }
        }
        add_action('bp_init', array($this, 'handle_callback'), 20);
        add_action('wp_ajax_bp_media_free_encoding_subscribe', array($this, 'free_encoding_subscribe'));
    }

    function enqueue($class, $type) {
        switch ($type) {
            case 'video':
            case 'audio':
                return 'BPMediaEncodingTranscoder';
            default:
                return $class;
        }
    }

    public function menu() {
        add_submenu_page('bp-media-settings', __('BuddyPress Media Audio/Video Encoding Service', 'buddypress-media'), __('Audio/Video Encoding', 'buddypress-media'), 'manage_options', 'bp-media-encoding', array($this, 'encoding_page'));
        global $submenu;
        $menu = $submenu['bp-media-settings'];
        $encoding_menu = array_pop($menu);
        $submenu['bp-media-settings'] = array_merge(array_slice($menu, 0, 1), array($encoding_menu), array_slice($menu, 1));
    }

    /**
     * Render the BuddyPress Media Encoding page
     */
    public function encoding_page() {
        global $bp_media_admin;
        $bp_media_admin->render_page('bp-media-encoding');
    }

    public function encoding_settings() {
        add_settings_section('bpm-encoding', __('Audio/Video Encoding Service', 'buddypress-media'), array($this, 'encoding_service_intro'), 'bp-media-encoding');
    }

    public function encoding_tab($tabs, $tab) {
        $idle_class = 'nav-tab';
        $active_class = 'nav-tab nav-tab-active';
        $encoding_tab = array(
            'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-encoding'), 'admin.php')),
            'title' => __('BuddyPress Media Audio/Video Encoding Service', 'buddypress-media'),
            'name' => __('Audio/Video Encoding', 'buddypress-media'),
            'class' => ($tab == 'bp-media-encoding') ? $active_class : $idle_class . ' last_tab'
        );
        $reordered_tabs = NULL;
        foreach ($tabs as $key => $tab) {
            if ($key == 1)
                $reordered_tabs[] = $encoding_tab;
            $reordered_tabs[] = $tab;
        }

        return $reordered_tabs;
    }

    public function admin_bar_menu($bp_media_admin_nav) {
// Encoding Service
        $admin_nav = array(
            'parent' => 'bp-media-menu',
            'id' => 'bp-media-encoding',
            'title' => __('Audio/Video Encoding', 'buddypress-media'),
            'href' => bp_get_admin_url(add_query_arg(array('page' => 'bp-media-encoding'), 'admin.php'))
        );
        $reordered_admin_nav = NULL;
        foreach ($bp_media_admin_nav as $key => $nav) {
            if ($key == 2)
                $reordered_admin_nav[] = $admin_nav;
            $reordered_admin_nav[] = $nav;
        }
        return $reordered_admin_nav;
    }

    public function is_valid_key($key) {
        $validate_url = trailingslashit($this->api_url) . 'api/validate/' . $key;
        $validation_page = wp_remote_get($validate_url, array('timeout' => 20));
        if (!is_wp_error($validation_page)) {
            $validation_info = json_decode($validation_page['body']);
            $status = $validation_info->status;
        } else {
            $status = false;
        }
        return $status;
    }

    public function update_usage($key) {
        $usage_url = trailingslashit($this->api_url) . 'api/usage/' . $key;
        $usage_page = wp_remote_get($usage_url, array('timeout' => 20));
        if (!is_wp_error($usage_page))
            $usage_info = json_decode($usage_page['body']);
        else
            $usage_info = NULL;
        bp_update_option('bp-media-encoding-usage', array($key => $usage_info));
        return $usage_info;
    }

    public function nearing_usage_limit($usage_details) {
        $subject = __('BuddyPress Media Encoding: Nearing quota limit.', 'buddypress-media');
        $message = __('<p>You are nearing the quota limit for your BuddyPress Media encoding service.</p><p>Following are the details:</p><p><strong>Used:</strong> %s</p><p><strong>Remaining</strong>: %s</p><p><strong>Total:</strong> %s</p>', 'buddypress-media');
        $users = get_users(array('role' => 'administrator'));
        if ($users) {
            foreach ($users as $user)
                $admin_email_ids[] = $user->user_email;
            add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
            wp_mail($admin_email_ids, $subject, sprintf($message, size_format($usage_details[$this->api_key]->used, 2), size_format($usage_details[$this->api_key]->remaining, 2), size_format($usage_details[$this->api_key]->total, 2)));
        }
        bp_update_option('bp-media-encoding-usage-limit-mail', 1);
    }

    public function usage_quota_over() {
        $usage_details = bp_get_option('bp-media-encoding-usage');
        if (!$usage_details[$this->api_key]->remaining) {
            $subject = __('BuddyPress Media Encoding: Usage quota over.', 'buddypress-media');
            $message = __('<p>Your usage quota is over. Upgrade your plan</p><p>Following are the details:</p><p><strong>Used:</strong> %s</p><p><strong>Remaining</strong>: %s</p><p><strong>Total:</strong> %s</p>', 'buddypress-media');
            $users = get_users(array('role' => 'administrator'));
            if ($users) {
                foreach ($users as $user)
                    $admin_email_ids[] = $user->user_email;
                add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
                wp_mail($admin_email_ids, $subject, sprintf($message, size_format($usage_details[$this->api_key]->used, 2), 0, size_format($usage_details[$this->api_key]->total, 2)));
            }
            bp_update_option('bp-media-encoding-usage-limit-mail', 1);
        }
    }

    public function save_api_key() {
        if (isset($_GET['apikey']) && is_admin() && isset($_GET['page']) && ($_GET['page'] == 'bp-media-encoding') && $this->is_valid_key($_GET['apikey'])) {
            bp_update_option('bp-media-encoding-api-key', $_GET['apikey']);
            $this->update_usage($_GET['apikey']);
            $return_page = add_query_arg(array('page' => 'bp-media-encoding'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php')));
            wp_safe_redirect($return_page);
        }
    }

    public function allowed_types($types) {
//        $this->update_usage($this->api_key);
        $types = array(); //Allow all types of file to be uploded
        return $types;
    }

    public function encoding_subscription_form($name = 'No Name', $price = '0') {
        $action = $this->sandbox_testing ? 'https://sandbox.paypal.com/cgi-bin/webscr' : 'https://paypal.com/cgi-bin/webscr';
        $return_page = add_query_arg(array('page' => 'bp-media-encoding'), (is_multisite() ? network_admin_url('admin.php') : admin_url('admin.php')));

        $usage_details = bp_get_option('bp-media-encoding-usage');
        if (isset($usage_details[$this->api_key]->plan->name) && (strtolower($usage_details[$this->api_key]->plan->name) == strtolower($name))) {
            $form = '<button disabled="disabled" type="submit" class="button">' . __('Current Plan', 'buddypress-media') . '</button>';
        } else {
            $form = '<form method="post" action="' . $action . '" class="paypal-button" target="_top">
                        <input type="hidden" name="button" value="subscribe">
                        <input type="hidden" name="item_name" value="' . $name . '">

                        <input type="hidden" name="currency_code" value="USD">


                        <input type="hidden" name="a3" value="' . $price . '">
                        <input type="hidden" name="p3" value="1">
                        <input type="hidden" name="t3" value="M">

                        <input type="hidden" name="cmd" value="_xclick-subscriptions">

                        <!-- Merchant ID -->
                        <input type="hidden" name="business" value="' . $this->merchant_id . '">


                        <input type="hidden" name="custom" value="' . $return_page . '">

                        <!-- Flag to no shipping -->
                        <input type="hidden" name="no_shipping" value="1">

                        <!-- Flag to post payment return url -->
                        <input type="hidden" name="return" value="' . trailingslashit($this->api_url) . 'payment/process">


                        <!-- Flag to post payment data to given return url -->
                        <input type="hidden" name="rm" value="2">

                        <input type="hidden" name="src" value="1">
                        <input type="hidden" name="sra" value="1">
                        
                        <input type="image" src="http://www.paypal.com/en_US/i/btn/btn_subscribe_SM.gif" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!">
                    </form>';
        }
        return $form;
    }

    public function usage_widget() {
        $this->update_usage($this->api_key);
        $usage_details = bp_get_option('bp-media-encoding-usage');
        $content = '';
        if ($usage_details && isset($usage_details[$this->api_key]->status) && $usage_details[$this->api_key]->status) {
            if (isset($usage_details[$this->api_key]->plan->name))
                $content .= '<p><strong>' . __('Current Plan', 'buddypress-media') . ':</strong> ' . $usage_details[$this->api_key]->plan->name . '</p>';
            if (isset($usage_details[$this->api_key]->used))
                $content .= '<p><span class="encoding-used"></span><strong>' . __('Used', 'buddypress-media') . ':</strong> ' . (($used_size = size_format($usage_details[$this->api_key]->used, 2)) ? $used_size : '0MB') . '</p>';
            if (isset($usage_details[$this->api_key]->remaining))
                $content .= '<p><span class="encoding-remaining"></span><strong>' . __('Remaining', 'buddypress-media') . ':</strong> ' . (($remaining_size = size_format($usage_details[$this->api_key]->remaining, 2)) ? $remaining_size : '0MB') . '</p>';
            if (isset($usage_details[$this->api_key]->total))
                $content .= '<p><strong>' . __('Total', 'buddypress-media') . ':</strong> ' . size_format($usage_details[$this->api_key]->total, 2) . '</p>';
            $usage = new rtProgress();
            $content .= $usage->progress_ui($usage->progress($usage_details[$this->api_key]->used, $usage_details[$this->api_key]->total), false);
            if ($usage_details[$this->api_key]->remaining <= 0)
                $content .= '<div class="error below-h2"><p>' . __('Your usage limit has been reached. Upgrade your plan.', 'buddypress-media') . '</p></div>';
        } else {
            $content .= '<div class="error below-h2"><p>' . __('Your API key is not valid or is expired.', 'buddypress-media') . '</p></div>';
        }
        new BPMediaAdminWidget('bp-media-encoding-usage', __('Encoding Usage', 'buddypress-media'), $content);
    }

    public function encoding_service_intro() {
//        $api_key = bp_get_option('bp-media-encoding-api-key');
//        if ( !$api_key )
//            echo '<div class="updated" id="bp-media-no-api-key"><p>'.__('You would need an API key to use this service.','buddypress-media').'</p></div>';
//        echo '<table class="form-table">
//                <tbody>
//                    <tr valign="top">
//                        <th scope="row">'.__('API Key','buddypress-media').'</th>
//                        <td><label for="bp-media-encoding-api-key"><input value="'.$api_key.'" name="bp-media-encoding-api-key" id="bp-media-encoding-api-key" type="text"></label></td>
//                    </tr>
//                </tbody>
//            </table>';
        ?>
        <p><?php _e('BuddyPress Media team has started offering an audio/video encoding service.', 'buddypress-media'); ?></p>
        <table  class="bp-media-encoding-table widefat fixed" cellspacing="0">
            <tbody>
                <!-- Results table headers -->
            <thead>
                <tr>
                    <th><?php _e('Feature\Plan', 'buddypress-media'); ?></th>
                    <th><?php _e('Free', 'buddypress-media'); ?></th>
                    <th><?php _e('Silver', 'buddypress-media'); ?></th>
                    <th><?php _e('Gold', 'buddypress-media'); ?></th>
                    <th><?php _e('Platinum', 'buddypress-media'); ?></th>
                </tr>
            </thead>
            <tr>
                <th><?php _e('File Size Limit', 'buddypress-media'); ?></th>
                <td>20MB</td>
                <td>2GB</td>
                <td>2GB</td>
                <td>2GB</td>
            </tr>
            <tr>
                <th><?php _e('Bandwidth (monthly)', 'buddypress-media'); ?></th>
                <td>1GB</td>
                <td>100GB</td>
                <td>1TB</td>
                <td>10TB</td>
            </tr>
            <tr>
                <th><?php _e('Overage Bandwidth', 'buddypress-media'); ?></th>
                <td><?php _e('Not Available', 'buddypress-media'); ?></td>
                <td>$0.10 per GB</td>
                <td>$0.08 per GB</td>
                <td>$0.05 per GB</td>
            </tr>
            <tr>
                <th><?php _e('Amazon S3 Support', 'buddypress-media'); ?></th>
                <td><?php _e('Not Available', 'buddypress-media'); ?></td>
                <td colspan="3" class="column-posts"><?php _e('Coming Soon', 'buddypress-media'); ?></td>
            </tr>
            <tr>
                <th><?php _e('HD Profile', 'buddypress-media'); ?></th>
                <td><?php _e('Not Available', 'buddypress-media'); ?></td>
                <td colspan="3" class="column-posts"><?php _e('Coming Soon', 'buddypress-media'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Webcam Recording', 'buddypress-media'); ?></th>
                <td colspan="4" class="column-posts"><?php _e('Coming Soon', 'buddypress-media'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Pricing', 'buddypress-media'); ?></th>
                <td><?php _e('Free', 'buddypress-media'); ?></td>
                <td><?php _e('$9/month', 'buddypress-media'); ?></td>
                <td><?php _e('$99/month', 'buddypress-media'); ?></td>
                <td><?php _e('$999/month', 'buddypress-media'); ?></td>
            </tr>
            <tr>
                <th></th>
                <td><?php
        $usage_details = bp_get_option('bp-media-encoding-usage');
        if (isset($usage_details[$this->api_key]->plan->name) && (strtolower($usage_details[$this->api_key]->plan->name) == 'free')) {
            echo '<button disabled="disabled" type="submit" class="encoding-try-now button">' . __('Current Plan', 'buddypress-media') . '</button>';
        } else {
            ?>
                        <form id="encoding-try-now-form" method="get" action="">
                            <input type="hidden" name="email" value="<?php echo bp_get_option('admin_email'); ?>" />
                            <button type="submit" class="encoding-try-now button"><?php _e('Try Now', 'buddypress-media'); ?></button>
                        </form><?php }
        ?>
                </td>
                <td><?php echo $this->encoding_subscription_form('silver', 9.0) ?></td>
                <td><?php echo $this->encoding_subscription_form('gold', 99.0) ?></td>
                <td><?php echo $this->encoding_subscription_form('platinum', 999.0) ?></td>
            </tr>
        </tbody>
        </table><br /><?php
    }

    /**
     * Function to handle the callback request by the FFMPEG encoding server
     *
     * @since 1.0
     */
    public function handle_callback() {
        if (isset($_GET['job_id']) && isset($_GET['download_url'])) {
            global $wpdb;
            $query_string =
                    "SELECT $wpdb->postmeta.post_id
					FROM $wpdb->postmeta
					WHERE $wpdb->postmeta.meta_key = 'bp-media-encoding-job-id'
						AND $wpdb->postmeta.meta_value='" . $_GET['job_id'] . "' ";
            $result = $wpdb->get_results($query_string);
            if (is_array($result) && count($result) == 1) {
                $attachment_id = $result[0]->post_id;
                $download_url = urldecode($_GET['download_url']);
                $new_wp_attached_file_pathinfo = pathinfo($download_url);
                $post_mime_type = $new_wp_attached_file_pathinfo['extension'] == 'mp4' ? 'video/mp4' : 'audio/mp3';
                $file_bits = file_get_contents($download_url);
                if ($file_bits) {
                    unlink(get_attached_file($attachment_id));
                    $upload_info = wp_upload_bits($new_wp_attached_file_pathinfo['basename'], null, $file_bits);
                    $wpdb->update($wpdb->posts, array('guid' => $upload_info['url'], 'post_mime_type' => $post_mime_type), array('ID' => $attachment_id));
                    $old_wp_attached_file = get_post_meta($attachment_id, '_wp_attached_file', true);
                    $old_wp_attached_file_pathinfo = pathinfo($old_wp_attached_file);
                    update_post_meta($attachment_id, '_wp_attached_file', str_replace($old_wp_attached_file_pathinfo['basename'], $new_wp_attached_file_pathinfo['basename'], $old_wp_attached_file));
                    $media_entry = new BPMediaHostWordpress($attachment_id);
                    $activity_content = str_replace($old_wp_attached_file_pathinfo['basename'], $new_wp_attached_file_pathinfo['basename'], $media_entry->get_media_activity_content());
                    $wpdb->update($wpdb->prefix . 'bp_activity', array('content' => $activity_content), array('id' => get_post_meta($attachment_id, 'bp_media_child_activity', true)));
//                    $subject = __('BuddyPress Media Encoding: Nearing quota limit.', 'buddypress-media');
//                    $message = __('<p>You are nearing the quota limit for your BuddyPress Media encoding service.</p><p>Following are the details:</p><p><strong>Used:</strong> %s</p><p><strong>Remaining</strong>: %s</p><p><strong>Total:</strong> %s</p>', 'buddypress-media');
//                    $users = get_users(array('role' => 'administrator'));
//                    if ($users) {
//                        foreach ($users as $user)
//                            $admin_email_ids[] = $user->user_email;
//                        add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
//                        wp_mail($admin_email_ids, $subject, sprintf($message, size_format($usage_details[$this->api_key]->used, 2), size_format($usage_details[$this->api_key]->remaining, 2), size_format($usage_details[$this->api_key]->total, 2)));
//                    }
                } else {
                    error_log(__('Could not read file.', 'buddypress-media'));
                }
            } else {
                error_log(__('Something went wrong. The required attachment id does not exists. It must have been deleted.', 'buddypress-media'));
            }

            $this->update_usage($this->api_key);

            die();
        }
    }

    public function free_encoding_subscribe() {
        $form_data = wp_parse_args($_GET['form_data']);
        if (isset($form_data['email']) && $form_data['email']) {
            $free_subscription_url = add_query_arg(array('email' => urlencode($form_data['email'])), trailingslashit($this->api_url) . 'api/free/');
            error_log($free_subscription_url);
            $free_subscribe_page = wp_remote_get($free_subscription_url, array('timeout' => 120));
            error_log(var_export($free_subscribe_page, true));
            if (!is_wp_error($free_subscribe_page) && (!isset($free_subscribe_page['headers']['status']) || (isset($free_subscribe_page['headers']['status']) && ($free_subscribe_page['headers']['status'] == 200)))) {
                $subscription_info = json_decode($free_subscribe_page['body']);
                if (isset($subscription_info->status) && $subscription_info->status) {
                    echo json_encode(array('apikey' => $subscription_info->apikey));
                } else {
                    echo json_encode(array('error' => $subscription_info->message));
                }
            } else {
                echo json_encode(array('error' => 'Something went wrong please try again.'));
            }
        }
        die();
    }

}
?>
