<?php
/**
 * Plguin Deactivation Survey Class.
 */
class Deactivation_Survey {

    /**
     * API Url.
     *
     * @var string
     */
    private $api_url = 'https://rtmedia.io/wp-json/rtps/v1'; // Replace the API Url by the production API Url.


    /**
     * Constructor function.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'load_scripts' ] );
        add_action( 'wp_ajax_rt_send_deactivation_feedback', [ $this, 'rt_send_deactivation_feedback' ] );
    }

    /**
     * Loading scripts.
     *
     * @return void
     */
    public function load_scripts() {
        global $pagenow;

        wp_register_style( 'rt-deactivation-survey', RTMEDIA_URL . '/lib/deactivation-survey/deactivation-survey.css', [], wp_rand(), 'all' );
        wp_register_script( 'rt-deactivation-survey', RTMEDIA_URL . '/lib/deactivation-survey/deactivation-survey.js', [ 'jquery' ], wp_rand(), true );

        if ( is_admin() && 'plugins.php' === $pagenow ) {

            wp_enqueue_style( 'rt-deactivation-survey' );
            wp_enqueue_script( 'rt-deactivation-survey' );

            $reasons = [
                'I couldn\'t understand how to make it work.',
                'I found a better plugin.',
                'The plugin is great but I need specific features, that you don\'t support.',
                'The plugin isn\'t working.',
                'It\'s not what I was looking for.',
                'The plugin didn\'t work as expected.',
            ];

            $current_user = wp_get_current_user();

            $rt_deactivate = [
                'home_url'    => home_url(),
                'admin_url'   => admin_url(),
                'ajax_url'    => admin_url( 'admin-ajax.php' ),
                'nonce'       => wp_create_nonce( 'rtmedia' ),
                'reasons'     => wp_json_encode( $reasons ),
                'user_name'   => $current_user->user_nicename,
                'user_email'  => $current_user->user_email,
                'header_text' => esc_html__( 'If you have a moment, please let us know why you are deactivating: ', 'buddypress-media' )
            ];

            wp_localize_script( 'rt-deactivation-survey', 'rtDeactivate', $rt_deactivate );
        }
    }

    /**
     * Ajax Function call.
     *
     * @return string.
     */
    public function rt_send_deactivation_feedback() {
        // Checking ajax referer.
        check_ajax_referer( 'rtmedia', 'nonce' );

        if ( ! $_POST['reason'] && empty( $_POST['user'] && ! $_POST['site_url'] ) ) {
            return;
        }

        // Filter the inputs.
        $site_url = filter_input( INPUT_POST, 'site_url', FILTER_SANITIZE_URL );
        $reason   = filter_input( INPUT_POST, 'reason', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
        $user     = filter_input( INPUT_POST, 'user', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

        $data = [
            'plugin_name' => 'rtMedia Core',
            'plugin_slug' => 'rtmedia-core',
            'site_url'    => $site_url,
            'reason'      => $reason,
            'user_name'   => $user['name'],
            'user_email'  => $user['email'],
        ];

        $api_response = wp_remote_get( $this->api_url . '/auth_access' );
        $response     = json_decode( wp_remote_retrieve_body( $api_response ) );

        if ( null !== $response && ! empty( $response ) ) {
            $auth_user     = $response->auth_username;
            $auth_password = $response->auth_password;

            $options = [
                'body'             => $data,
                'headers'          => [
                    'Content-type' => "application/x-www-form-urlencoded",
                    'Authorization' => "Basic " . base64_encode("{$auth_user}:{$auth_password}")
                ],
                'timeout'          => 60,
                'redirection'      => 5,
                'httpversion'      => '1.0',
                'sslverify'        => false,
                'data_format'      => 'body'
            ];

            $api_response = wp_remote_post( $this->api_url . '/survey', $options );
            $response     = json_decode( wp_remote_retrieve_body( $api_response ) );

            if ( 'integer' === gettype( $response ) ) {
                echo wp_json_encode( 'success' );
				wp_die();
            }
        }

	    echo wp_json_encode( 'failed' );
        wp_die();
    }

}

new Deactivation_Survey();
