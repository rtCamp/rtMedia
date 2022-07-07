<?php
/**
 * Loading scripts
 *
 * @return void
 */
function load_scripts() {
    wp_register_style( 'rt-deactivation-survey', RTMEDIA_URL . '/lib/deactivation-survey/deactivation-survey.css', [], wp_rand(), 'all' );
    wp_register_script( 'rt-deactivation-survey', RTMEDIA_URL . '/lib/deactivation-survey/deactivation-survey.js', [ 'jquery' ], wp_rand(), true );

    wp_enqueue_style( 'rt-deactivation-survey' );
    wp_enqueue_script( 'rt-deactivation-survey' );

    $reasons = [
        'I could\'t understand how to make it work.',
        'I found a better plugin.',
        'The plugin is greate but I need spicific features, that you do\'t support.',
        'The plugin is\'t working.',
        'It\'s not what I was looking for.',
        'The plugin didn\'t work as expected.',
        'Others.'
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
        'header_text' => esc_html__( 'If you have a moment, please let us know why you are deactivating: ' )
    ];

    wp_localize_script( 'rt-deactivation-survey', 'rtDeactivate', $rt_deactivate );
}
add_action( 'admin_enqueue_scripts', 'load_scripts' );

/**
 * Ajax Function call
 */
function rt_send_deactivation_feedback() {
    // Checking ajax referer.
    check_ajax_referer( 'rtmedia', 'nonce' );

    if ( ! $_POST['reason'] && empty( $_POST['user'] && ! $_POST['site_url'] ) ) {
        return;
    }

    // Filter the inputs.
    $site_url = filter_input( INPUT_POST, 'site_url', FILTER_SANITIZE_URL );
    $reason   = filter_input( INPUT_POST, 'reason', FILTER_SANITIZE_STRING );
    $user     = filter_input( INPUT_POST, 'user', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

    $data = [
        'reason'   => $reason,
        'username' => $user['name'],
        'email'    => $user['email'],
        'site_url' => $site_url
    ];

    // echo '<pre>';
    // print_r( $data );
    // echo '</pre>';die(  );

    echo wp_json_encode( 'success' );
    wp_die();
}
add_action( 'wp_ajax_rt_send_deactivation_feedback', 'rt_send_deactivation_feedback' );