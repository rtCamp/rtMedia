<?php

/**
 * File Description
 * @author Umesh Kumar <umeshsingla05@gmail.com>
 */
class RTMediaJsonApi {

	var $ec_method_missing = 600001,
		$msg_method_missing = 'no method specified',
		$ec_token_missing = 600002,
		$msg_token_missing = 'token empty',
		$ec_token_invalid = 600003,
		$msg_token_invalid = 'token invalid',
		$ec_server_error = 600004,
		$msg_server_error = 'server error',
		$ec_media_activity_id_missing = 600005,
		$msg_media_activity_id_missing = 'media/activity id missing',
		$ec_invalid_media_id = 600006,
		$msg_invalid_media_id = 'invalid media id',
		$ec_invalid_request_type = 600007,
		$msg_invalid_request_type = 'invalid request type',
		$ec_bp_missing = 600008,
		$msg_bp_missing = 'buddypress not found',
		$ec_api_disabled = 600009,
		$msg_api_disabled = 'API disabled by site administrator',
		$rtmediajsonapifunction,
		$user_id = '';

	function __construct() {
		if ( ! class_exists( 'RTMediaApiLogin' ) || ! class_exists( 'RTMediaJsonApiFunctions' ) ) {
			return;
		}

		add_action( 'wp_ajax_nopriv_rtmedia_api', array( $this, 'rtmedia_api_process_request' ) );
		add_action( 'wp_ajax_rtmedia_api', array( $this, 'rtmedia_api_process_request' ) );
	}

	function rtmedia_api_process_request() {
		$rtmedia_enable_json_api = false;
		if ( function_exists( 'rtmedia_get_site_option' ) ) {
			$rtmedia_options = rtmedia_get_site_option( 'rtmedia-options' );
			if ( ! empty( $rtmedia_options ) ) {
				if ( $rtmedia_options['rtmedia_enable_api'] ) {
					$rtmedia_enable_json_api = true;
				}
			}
		}
		if ( ! $rtmedia_enable_json_api ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_api_disabled, $this->msg_api_disabled ) );
		}
		$method = filter_input( INPUT_POST, 'method', FILTER_SANITIZE_STRING );
		if ( empty( $method ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_method_missing, $this->msg_method_missing ) );
		}
		if ( ! class_exists( 'BuddyPress' ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_bp_missing, $this->msg_bp_missing ) );
		}
		$this->rtmediajsonapifunction = new RTMediaJsonApiFunctions();
		$token                        = filter_input( INPUT_POST, 'token', FILTER_SANITIZE_STRING );

		if ( ! empty( $token ) ) {
			$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
			$this->user_id = $this->rtmediajsonapifunction->rtmedia_api_get_user_id_from_token( $token );
			//add filter
			add_filter( 'rtmedia_current_user', array( $this->rtmediajsonapifunction, 'rtmedia_api_set_user_id' ) );
		}
		//Process Request

		switch ( $method ) {

			case 'wp_login':
				$this->rtmedia_api_process_wp_login_request();
				break;
			case 'wp_logout':
				//todo implement this function rtmedia_api_process_wp_logout_request if needed
				//$this->rtmedia_api_process_wp_logout_request();
				break;
			case 'wp_register':
				$this->rtmedia_api_process_wp_register_request();
				break;
			case 'wp_forgot_password':
				$this->rtmedia_api_process_wp_forgot_password_request();
				break;
			case 'bp_get_profile':
				$this->rtmedia_api_process_bp_get_profile_request();
				break;
			case 'bp_get_activities':
				$this->rtmedia_api_process_bp_get_activities_request();
				break;
			case 'add_rtmedia_comment':
				$this->rtmedia_api_process_add_rtmedia_comment_request();
				break;
			case 'like_media':
				$this->rtmedia_api_process_like_media_request();
				break;
			case 'get_rtmedia_comments':
				$this->rtmedia_api_process_get_rtmedia_comments_request();
				break;
			case 'get_likes_rtmedia':
				$this->rtmedia_api_process_get_likes_rtmedia_request();
				break;
			case 'remove_comment':
				$this->rtmedia_api_process_remove_comment_request();
				break;
			case 'update_profile':
				$this->rtmedia_api_process_update_profile_request();
				break;
			case 'rtmedia_upload_media':
				$this->rtmedia_api_process_rtmedia_upload_media_request();
				break;
			case 'rtmedia_gallery':
				$this->rtmedia_api_process_rtmedia_gallery_request();
				break;
			case 'rtmedia_get_media_details':
				$this->rtmedia_api_process_rtmedia_get_media_details_request();
				break;
			default:
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_invalid_request_type, $this->msg_invalid_request_type ) );
		}

		wp_die();
	}

	/**
	 * Returns a json object
	 *
	 * @param string $status
	 * @param int $status_code
	 * @param string $message
	 * @param bool|array $data
	 *
	 * @return bool
	 */
	function rtmedia_api_response_object( $status, $status_code, $message, $data = false ) {
		if ( '' === $status || empty( $status_code ) || empty( $message ) ) {
			return false;
		}

		if ( ob_get_contents() ) {
			ob_end_clean();
		}
		global $wpdb;
		$rtmapilogin   = new RTMediaApiLogin();
		$token         = filter_input( INPUT_POST, 'token', FILTER_SANITIZE_STRING );
		$login_details = array( 'last_access' => current_time( 'mysql' ) );
		if ( ! empty( $token ) ) {
			$where = array( 'user_id' => $this->user_id, 'token' => $token );
		}
		if ( ! empty( $where ) ) {
			$rtmapilogin->update( $login_details, $where );
		}
		$response_object                = array();
		$response_object['status']      = $status;
		$response_object['status_code'] = $status_code;
		$response_object['message']     = $message;
		$response_object['data']        = $data;

		return $response_object;
	}

	/**
	 * Takes username and password, if succesful returns a access token
	 */
	function rtmedia_api_process_wp_login_request() {
		//Login Errors and Messages
		$ec_user_pass_missing  = 200001;
		$msg_user_pass_missing = esc_html__( 'username/password empty', 'buddypress-media' );

		$ec_incorrect_username  = 200002;
		$msg_incorrect_username = esc_html__( 'incorrect username', 'buddypress-media' );

		$ec_incorrect_pass  = 200003;
		$msg_incorrect_pass = esc_html__( 'incorrect password', 'buddypress-media' );

		$ec_login_success  = 200004;
		$msg_login_success = esc_html__( 'login success', 'buddypress-media' );
		$username          = filter_input( INPUT_POST, 'username', FILTER_SANITIZE_STRING );
		$password          = filter_input( INPUT_POST, 'password', FILTER_SANITIZE_STRING );

		if ( empty( $username ) || empty( $password ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_user_pass_missing, $msg_user_pass_missing ) );
		} else {
			$user_login = wp_authenticate( trim( $username ), trim( $password ) );
			if ( is_wp_error( $user_login ) ) {

				$incorrect_password = ! empty( $user_login->errors['incorrect_password'] ) ? true : false;
				$incorrect_username = ! empty( $user_login->errors['invalid_username'] ) ? true : false;
				if ( $incorrect_password ) {
					wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_incorrect_pass, $msg_incorrect_pass ) );
				} elseif ( $incorrect_username ) {
					wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_incorrect_username, $msg_incorrect_username ) );
				}
			} else {
				$access_token = $this->rtmediajsonapifunction->rtmedia_api_get_user_token( $user_login->ID, $user_login->data->user_login );
				$data         = array(
					'access_token' => $access_token,
				);
				$rtmapilogin  = new RTMediaApiLogin();

				//update all tokens for user to exired on each login
				$rtmapilogin->update( array( 'status' => 'FALSE' ), array( 'user_id' => $user_login->ID ) );
				$remote_addr   = rtm_get_server_var( 'REMOTE_ADDR', 'FILTER_VALIDATE_IP' );
				$login_details = array(
					'user_id'    => intval( $user_login->ID ),
					'ip'         => $remote_addr,
					'token'      => sanitize_text_field( $access_token ),
					'token_time' => date( 'Y-m-d H:i:s' ),
				);
				$rtmapilogin->insert( $login_details );
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_login_success, $msg_login_success, $data ) );
			}
		}
	}

	/**
	 * register a user through api request
	 * requires signup_* => display_name, username, password, confirm password, location,
	 */
	function rtmedia_api_process_wp_register_request() {
		//Registration errors and messages
		$ec_register_fields_missing  = 300001;
		$msg_register_fields_missing = esc_html__( 'fields empty', 'buddypress-media' );

		$ec_invalid_email  = 300002;
		$msg_invalid_email = esc_html__( 'invalid email', 'buddypress-media' );

		$ec_pass_do_not_match  = 300003;
		$msg_pass_do_not_match = esc_html__( 'password do not match', 'buddypress-media' );

		$ec_username_exists  = 300004;
		$msg_username_exists = esc_html__( 'username already registered', 'buddypress-media' );

		$ec_email_exists   = 300005;
		$msg_email_existsh = esc_html__( 'email already exists', 'buddypress-media' );

		$ec_user_insert_success  = 300007;
		$msg_user_insert_success = esc_html__( 'new user created', 'buddypress-media' );

		$registration_fields = array( 'username', 'email', 'password', 'password_confirm' );
		//fields empty field_1, field_4
		$field_1 = filter_input( INPUT_POST, 'field_1', FILTER_SANITIZE_STRING );

		if ( empty( $field_1 ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_register_fields_missing, $msg_register_fields_missing ) );
		}
		foreach ( $registration_fields as $field_name ) {
			$field_signup = filter_input( INPUT_POST, 'signup_' . $field_name, FILTER_SANITIZE_STRING );
			if ( empty( $field_signup ) ) {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_register_fields_missing, $msg_register_fields_missing ) );
			}
		}
		$signup_email            = filter_input( INPUT_POST, 'signup_email', FILTER_VALIDATE_EMAIL );
		$signup_username         = filter_input( INPUT_POST, 'signup_username', FILTER_SANITIZE_STRING );
		$signup_password         = filter_input( INPUT_POST, 'signup_password', FILTER_SANITIZE_STRING );
		$signup_password_confirm = filter_input( INPUT_POST, 'signup_password_confirm', FILTER_SANITIZE_STRING );

		//incorrect email
		if ( ! is_email( $signup_email ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_invalid_email, $msg_invalid_email ) );
		} //Passwords do not match
		elseif ( $signup_password !== $signup_password_confirm ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_pass_do_not_match, $msg_pass_do_not_match ) );
		} //Username already registered
		elseif ( username_exists( $signup_username ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_username_exists, $msg_username_exists ) );
		} //email already registered
		elseif ( email_exists( $signup_email ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_email_exists, $msg_email_existsh ) );
		} else {
			$userdata = array(
				'user_login'   => sanitize_user( $signup_username ),
				'user_pass'    => $signup_password,
				'display_name' => sanitize_text_field( $field_1 ),
			);

			$user_id = wp_insert_user( $userdata );
			if ( ! is_wp_error( $user_id ) ) {
				echo esc_html( xprofile_get_field_id_from_name( 'field_1' ) );
				xprofile_set_field_data( 1, $user_id, sanitize_text_field( $field_1 ) );
				//todo user attr
				update_user_meta( $user_id, 'register_source', 'site_api' );
				echo wp_json_encode( $this->rtmedia_api_response_object( 'TRUE', $ec_user_insert_success, $msg_user_insert_success ) );
				wp_die();
			} else {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_server_error, $this->msg_server_error ) );
			}
		}
	}

	/**
	 * Sends a reset link to user email
	 * @global type $wpdb
	 */
	function rtmedia_api_process_wp_forgot_password_request() {
		global $wpdb;
		//Registration errors and messages
		$ec_email_missing  = 500001;
		$msg_email_missing = esc_html__( 'email empty', 'buddypress-media' );

		$ec_username_email_not_registered  = 500002;
		$msg_username_email_not_registered = esc_html__( 'username/email not registered', 'buddypress-media' );

		$ec_email_sent  = 500003;
		$msg_email_sent = esc_html__( 'reset link sent', 'buddypress-media' );
		$user_login     = filter_input( INPUT_POST, 'user_login', FILTER_SANITIZE_STRING );

		if ( empty( $user_login ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_email_missing, $msg_email_missing ) );
		}

		if ( username_exists( $user_login ) ) {
			$user_exists = true;
			$user        = get_user_by( 'login', sanitize_user( $user_login ) );
		} // Then, by e-mail address
		elseif ( email_exists( $user_login ) ) {
			$user_exists = true;
			$user        = get_user_by( 'email', sanitize_email( $user_login ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_username_email_not_registered, $msg_username_email_not_registered ) );
		}
		$user_login = $user->data->user_login;
		$user_email = $user->data->user_email;

		// Generate something random for a key...
		$key = wp_generate_password( 20, false );
		do_action( 'retrieve_password_key', $user_login, $key );
		// Now insert the new md5 key into the db
		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => sanitize_user( $user_login ) ) );

		//create email message
		$message = esc_html__( 'Someone has asked to reset the password for the following site and username.', 'buddypress-media' ) . "\r\n\r\n";
		$message .= esc_url( get_option( 'siteurl' ) ) . "\r\n\r\n";
		$message .= sprintf( esc_html__( 'Username: %s', 'buddypress-media' ), $user_login ) . "\r\n\r\n";
		$message .= esc_html__( 'To reset your password visit the following address, otherwise just ignore this email and nothing will happen.', 'buddypress-media' ) . "\r\n\r\n";
		$message .= '<' . esc_url( network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) ) . ">\r\n";
		//send email meassage
		if ( false === wp_mail( $user_email, sprintf( esc_html__( '[%s] Password Reset', 'buddypress-media' ), get_option( 'blogname' ) ), $message ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_server_error, $this->msg_server_error ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_email_sent, $msg_email_sent ) );
		}
	}

	/**
	 * Sends a reset link to user email
	 * @global type $wpdb
	 */
	function rtmedia_api_process_bp_get_activities_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		//Feed Errors
		$ec_latest_feed  = 700001;
		$msg_latest_feed = esc_html__( 'bp activities', 'buddypress-media' );

		$ec_my_looks  = 700002;
		$msg_my_looks = esc_html__( 'user activities', 'buddypress-media' );

		//Fetch user id from token
		$activity_user_id = filter_input( INPUT_POST, 'activity_user_id', FILTER_VALIDATE_INT );
		$per_page         = filter_input( INPUT_POST, 'per_page', FILTER_VALIDATE_INT );

		$per_page      = ! empty( $per_page ) ? $per_page : 10;
		$activity_feed = $this->rtmediajsonapifunction->rtmedia_api_get_feed( $activity_user_id, '', $per_page );
		if ( empty( $activity_feed ) ) {
			$activity_feed = esc_html__( 'no updates', 'buddypress-media' );
		}
		if ( ! empty( $activity_user_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_my_looks, $msg_my_looks, $activity_feed ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_latest_feed, $msg_latest_feed, $activity_feed ) );
		}
	}

	/**
	 * Post comment on activity_id or media_id
	 * @global type $this ->msg_server_error
	 * @global int $this ->ec_server_error
	 * @global int $this ->ec_invalid_media_id
	 * @global type $this ->msg_invalid_media_id
	 */
	function rtmedia_api_process_add_rtmedia_comment_request() {

		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$this->rtmediajsonapifunction->rtmedia_api_media_activity_id_missing();
		//Post comment errors
		$ec_comment_content_missing  = 800001;
		$msg_comment_content_missing = esc_html__( 'comment content missing', 'buddypress-media' );

		$ec_comment_posted  = 800002;
		$msg_comment_posted = esc_html__( 'comment posted', 'buddypress-media' );

		//Fetch user id from token
		$user_data = get_userdata( $this->user_id );

		$content = filter_input( INPUT_POST, 'content', FILTER_SANITIZE_STRING );

		if ( empty( $content ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_comment_content_missing, $msg_comment_content_missing ) );
		}

		if ( empty( $activity_id ) && ! empty( $media_id ) ) {
			$activity_id = $this->rtmediajsonapifunction->rtmedia_api_activityid_from_mediaid( $media_id );
		}
		if ( empty( $activity_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_invalid_media_id, $this->msg_invalid_media_id ) );
		}
		$args = array(
			'content'     => $content,
			'activity_id' => intval( $activity_id ),
			'user_id'     => intval( $this->user_id ),
			'parent_id'   => false,
		);
		if ( function_exists( 'bp_activity_new_comment' ) ) {
			$comment_id = bp_activity_new_comment( $args );
		}
		if ( isset( $comment_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_comment_posted, $msg_comment_posted ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->msg_server_error, $this->ec_server_error ) );
		}
	}

	/**
	 * Like/Unlike by media_id or activity_id
	 * @global int $this ->ec_server_error
	 * @global type $this ->msg_server_error
	 * @global int $this ->ec_invalid_media_id
	 * @global type $this ->msg_invalid_media_id
	 */
	function rtmedia_api_process_like_media_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$this->rtmediajsonapifunction->rtmedia_api_media_activity_id_missing();

		//Like errors
		$ec_already_liked  = 900001;
		$msg_already_liked = esc_html__( 'unliked media', 'buddypress-media' );

		$ec_liked_media  = 900002;
		$msg_liked_media = esc_html__( 'liked media', 'buddypress-media' );

		$media_id = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );

		if ( class_exists( 'RTMediaInteractionModel' ) ) :
			$rtmediainteraction = new RTMediaInteractionModel();

			if ( class_exists( 'RTMediaLike' ) ) {
				$rtmedialike = new RTMediaLike();
			}

			$action = 'like';
			// Like or Unlike
			if ( ! rtmedia_media_id( $media_id ) ) {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_invalid_media_id, $this->msg_invalid_media_id ) );
			}

			$like_count_old = get_rtmedia_like( rtmedia_media_id( $media_id ) );
			$check_action   = $rtmediainteraction->check( $this->user_id, $media_id, $action );
			if ( $check_action ) {
				$results    = $rtmediainteraction->get_row( $this->user_id, $media_id, $action );
				$row        = $results[0];
				$curr_value = $row->value;
				if ( 1 === intval( $curr_value ) ) {
					$value    = '0';
					$increase = false;
				} else {
					$value    = '1';
					$increase = true;
				}
				$update_data   = array( 'value' => $value );
				$where_columns = array(
					'user_id'  => $this->user_id,
					'media_id' => $media_id,
					'action'   => $action,
				);
				$update        = $rtmediainteraction->update( $update_data, $where_columns );
			} else {
				$value     = '1';
				$columns   = array(
					'user_id'  => $this->user_id,
					'media_id' => $media_id,
					'action'   => $action,
					'value'    => $value,
				);
				$insert_id = $rtmediainteraction->insert( $columns );
				$increase  = true;
			}
			if ( $increase ) {
				$like_count_old ++;
			} elseif ( ! $increase ) {
				$like_count_old --;
			}
			if ( $like_count_old < 0 ) {
				$like_count_old = 0;
			}
			$data = array( 'like_count' => $like_count_old );
			if ( ! empty( $insert_id ) && isset( $rtmedialike ) ) {
				$rtmedialike->model->update( array( 'likes' => $like_count_old ), array( 'id' => $media_id ) );
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_liked_media, $msg_liked_media, $data ) );
			} elseif ( ! empty( $update ) && isset( $rtmedialike ) ) {
				$rtmedialike->model->update( array( 'likes' => $like_count_old ), array( 'id' => $media_id ) );
				if ( 1 === intval( $value ) ) {
					wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_liked_media, $msg_liked_media, $data ) );
				} elseif ( 0 === intval( $value ) ) {
					wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_already_liked, $msg_already_liked, $data ) );
				}
			} else {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_server_error, $this->msg_server_error ) );
			}
		endif;
	}

	/**
	 * Fetch Comments by media id
	 * @global type $wpdb
	 */
	function rtmedia_api_process_get_rtmedia_comments_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		//Errors Fetching comment
		$ec_no_comments  = 800003;
		$msg_no_comments = esc_html__( 'no comments', 'buddypress-media' );

		$ec_media_comments  = 800004;
		$msg_media_comments = esc_html__( 'media comments', 'buddypress-media' );

		$ec_my_comments  = 800005;
		$msg_my_comments = esc_html__( 'my comments', 'buddypress-media' );

		$media_id = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );

		global $wpdb;
		if ( empty( $media_id ) ) {
			$user_data   = $this->rtmediajsonapifunction->rtmedia_api_user_data_from_id( $this->user_id );
			$comments    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE user_id = %d limit 100", $this->user_id ), ARRAY_A );
			$my_comments = array();
			if ( ! empty( $comments ) ) {
				foreach ( $comments as $comment ) {
					$my_comments['comments'][] = array(
						'comment_ID'      => $comment['comment_ID'],
						'comment_content' => $comment['comment_content'],
						'media_id'        => $comment['comment_post_ID'],
					);
				}
				$my_comments['user'] = array(
					'user_id' => $this->user_id,
					'name'    => $user_data['name'],
					'avatar'  => $user_data['avatar'],
				);

				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_media_comments, $msg_media_comments, $my_comments ) );
			}
		} else {
			$media_comments = $this->rtmediajsonapifunction->rtmedia_api_get_media_comments( $media_id );
			if ( $media_comments ) {
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_media_comments, $msg_media_comments, $media_comments ) );
			} else {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_comments, $msg_no_comments ) );
			}
		}
		//If no comments
		wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_comments, $msg_no_comments ) );
	}

	/**
	 * Fetch Likes by media id
	 * @global type $wpdb
	 */
	function rtmedia_api_process_get_likes_rtmedia_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$this->rtmediajsonapifunction->rtmedia_api_media_activity_id_missing();
		global $wpdb;
		//Errors Fetching Likes
		$ec_no_likes  = 900003;
		$msg_no_likes = esc_html__( 'no likes', 'buddypress-media' );

		$ec_media_likes   = 900004;
		$msg_media_likes  = esc_html__( 'media likes', 'buddypress-media' );
		$media_id         = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );
		$media_likes      = filter_input( INPUT_POST, 'media_likes', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$media_like_users = filter_input( INPUT_POST, 'media_like_users',FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( isset( $media_likes['user'] ) && ! is_array( $media_likes['user'] ) ) {
			$media_likes['user'] = array();
		}
		if ( empty( $media_likes ) || ! is_array( $media_likes ) ) {
			$media_likes = array();
		}
		if ( empty( $media_like_users ) || ! is_array( $media_like_users ) ) {
			$media_like_users = array();
		}

		$media_like_users = $this->rtmediajsonapifunction->rtmedia_api_media_liked_by_user( $media_id );
		if ( ! empty( $media_like_users ) ) {
			foreach ( $media_like_users as $like_details ) {
				if ( ! array_key_exists( $like_details->user_id, $media_likes['user'] ) ) {

					$user_data                                     = $this->rtmediajsonapifunction->rtmedia_api_user_data_from_id( $like_details->user_id );
					$mysql_time                                    = current_time( 'mysql' );
					$like_time                                     = human_time_diff( strtotime( $like_details->action_date ), strtotime( $mysql_time ) );
					$media_likes['likes'][]                        = array(
						'activity_time' => $like_time,
						'user_id'       => $like_details->user_id,
					);
					$media_likes['user'][ $like_details->user_id ] = array(
						'name'   => $user_data['name'],
						'avatar' => $user_data['avatar'],
					);
				}
			}
		}
		if ( ! empty( $media_likes ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_media_likes, $msg_media_likes, $media_likes ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_likes, $msg_no_likes ) );
		}
	}

	/**
	 * Delete comment by activity id or media id
	 */
	function rtmedia_api_process_remove_comment_request() {
		global $wpdb;
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$this->rtmediajsonapifunction->rtmedia_api_media_activity_id_missing();
		//Errors Deleting comment

		$ec_comment_not_found  = 800007;
		$msg_comment_not_found = esc_html__( 'invalid comment/media id', 'buddypress-media' );

		$ec_no_comment_id  = 800008;
		$msg_no_comment_id = esc_html__( 'no comment id', 'buddypress-media' );

		$ec_comment_deleted  = 800009;
		$msg_comment_deleted = esc_html__( 'comment deleted', 'buddypress-media' );

		$media_id   = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );
		$comment_id = filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $comment_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_comment_id, $msg_no_comment_id ) );
		}
		$id = rtmedia_media_id( $media_id );
		$sql = $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE comment_ID = %d AND comment_post_ID = %d AND user_id = %d limit 100", $comment_id, $id, $this->user_id );

		$comments = $wpdb->get_results( $sql, ARRAY_A ); // @codingStandardsIgnoreLine
		//Delete Comment
		if ( ! empty( $comments ) ) {
			$comment = new RTMediaComment();

			$activity_id = get_comment_meta( $comment_id, 'activity_id', true );

			if ( ! empty( $activity_id ) ) {
				$activity_deleted = bp_activity_delete_comment( $activity_id, $comment_id );

				$delete = bp_activity_delete( array( 'id' => $activity_id, 'type' => 'activity_comment' ) );

			}
			$comment_deleted = $comment->rtmedia_comment_model->delete( $comment_id );

			if ( $comment_deleted ) {
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_comment_deleted, $msg_comment_deleted ) );
			} else {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_server_error, $this->msg_server_error ) );
			}
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_comment_not_found, $msg_comment_not_found ) );
		}
	}

	function rtmedia_api_process_bp_get_profile_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		//Errors
		$ec_no_fields  = 400001;
		$msg_no_fields = esc_html__( 'no profile found', 'buddypress-media' );

		$ec_profile_fields  = 400002;
		$msg_profile_fields = esc_html__( 'profile fields', 'buddypress-media' );

		$profile_fields = array();
		$user_id        = $loggedin_user_id = '';

		$user_id          = filter_input( INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT );
		$loggedin_user_id = filter_input( INPUT_POST, 'loggedin_user_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $user_id ) ) {
			$user_id = $this->user_id;
		} else {
			$loggedin_user_id = $this->user_id;
		}
		$user = get_userdata( $user_id );
		if ( empty( $user ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_no_fields, $msg_no_fields ) );
		}
		$user_data                          = $this->rtmediajsonapifunction->rtmedia_api_user_data_from_id( $user_id, 250, 250, 'full' );
		$profile_fields['id']               = $user_id;
		$profile_fields['avatar']['src']    = esc_url( $user_data['avatar'] );
		$profile_fields['avatar']['width']  = 250;
		$profile_fields['avatar']['height'] = 250;

		if ( bp_has_profile( array( 'user_id' => $user_id ) ) ) :
			while ( bp_profile_groups() ) : bp_the_profile_group();

				if ( bp_profile_group_has_fields() ) :

					while ( bp_profile_fields() ) : bp_the_profile_field();

						if ( bp_field_has_data() ) :

							$profile_fields['fields'][ bp_get_the_profile_field_name() ] = array(
								'value'   => strip_tags( bp_get_the_profile_field_value() ),
								'privacy' => bp_get_the_profile_field_visibility_level(),
							);
						endif;

					endwhile;
				endif;
			endwhile;
		else :
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_fields, $msg_no_fields ) );
		endif;
		//If followers plugin exists
		if ( function_exists( 'rtmedia_api_followers' ) ) {
			$followers = rtmedia_api_followers( $user_id );
			$following = $this->rtmediajsonapifunction->rtmedia_api_following( $user_id );

			foreach ( $followers as $follower ) {
				$follower_data                = $this->rtmediajsonapifunction->rtmedia_api_user_data_from_id( $follower, 66, 66 );
				$profile_fields['follower'][] = array(
					'id'     => $follower,
					'name'   => $follower_data['name'],
					'avatar' => $follower_data['avatar'],
				);
			}

			foreach ( $following as $follow ) {
				$follow_data                   = $this->rtmediajsonapifunction->rtmedia_api_user_data_from_id( $follow, 66, 66 );
				$profile_fields['following'][] = array(
					'id'     => $follow,
					'name'   => $follow_data['name'],
					'avatar' => $follow_data['avatar'],
				);
			}
		}
		if ( ! empty( $user_id ) && intval( $loggedin_user_id ) !== intval( $user_id ) ) {
			$args = array(
				'leader_id'   => $user_id,
				'follower_id' => $loggedin_user_id,
			);
			if ( function_exists( 'bp_follow_is_following' ) ) {
				$profile_fields['loggedin_user']['following'] = 'FALSE';
				if ( bp_follow_is_following( $args ) ) {
					$profile_fields['loggedin_user']['following'] = 'TRUE';
				}

				$args                                        = array(
					'leader_id'   => $loggedin_user_id,
					'follower_id' => $user_id,
				);
				$profile_fields['loggedin_user']['followed'] = 'FALSE';
				if ( bp_follow_is_following( $args ) ) {
					$profile_fields['loggedin_user']['followed'] = 'TRUE';
				}
			}
		}
		wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_profile_fields, $msg_profile_fields, $profile_fields ) );
	}

	function rtmedia_api_process_follow_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$ec_empty_follow_id  = 400003;
		$msg_empty_follow_id = esc_html__( 'follow user id missing', 'buddypress-media' );

		$ec_started_following  = 400004;
		$msg_started_following = esc_html__( 'started following', 'buddypress-media' );

		$ec_already_following  = 400005;
		$msg_already_following = esc_html__( 'already following', 'buddypress-media' );

		$follow_id = filter_input( INPUT_POST, 'follow_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $follow_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_empty_follow_id, $msg_empty_follow_id ) );
		}
		$args              = array(
			'leader_id'   => $follow_id,
			'follower_id' => $this->user_id,
		);
		$already_following = bp_follow_is_following( $args );
		if ( ! $already_following ) {
			$follow_user = bp_follow_start_following( $args );
			if ( $follow_user ) {
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_started_following, $msg_started_following ) );
			} else {
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $this->ec_server_error, $this->msg_server_error ) );
			}
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_already_following, $msg_already_following ) );
		}
	}

	function rtmedia_api_process_unfollow_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();

		$ec_empty_unfollow_id  = 400006;
		$msg_empty_unfollow_id = esc_html__( 'unfollow id missing', 'buddypress-media' );

		$ec_stopped_following  = 400007;
		$msg_stopped_following = esc_html__( 'stopped following', 'buddypress-media' );

		$ec_not_following  = 400008;
		$msg_not_following = esc_html__( 'not following', 'buddypress-media' );

		$unfollow_id = filter_input( INPUT_POST, 'unfollow_id', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $unfollow_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_empty_unfollow_id, $msg_empty_unfollow_id ) );
		}

		$args      = array(
			'leader_id'   => $unfollow_id,
			'follower_id' => $this->user_id,
		);
		$following = bp_follow_is_following( $args );
		if ( $following ) {
			$unfollow_user = bp_follow_stop_following( $args );
			if ( $unfollow_user ) {
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_stopped_following, $msg_stopped_following ) );
			} else {
				wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $this->ec_server_error, $this->msg_server_error ) );
			}
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_not_following, $msg_not_following ) );
		}
	}

	function rtmedia_api_process_update_profile_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$ec_empty_name_location  = 120001;
		$msg_empty_name_location = esc_html__( 'name/location empty', 'buddypress-media' );

		$ec_profile_updated  = 120002;
		$msg_profile_updated = esc_html__( 'profile updated', 'buddypress-media' );

		for ( $i = 1; $i <= 12; $i ++ ) {
			$field_str = 'field_';
			$field_str .= $i;
			$field_str_privacy  = $field_str . '_privacy';
			$$field_str         = filter_input( INPUT_POST, $field_str, FILTER_SANITIZE_STRING );
			$$field_str_privacy = filter_input( INPUT_POST, $field_str_privacy, FILTER_SANITIZE_STRING );
			! empty( $$field_str ) ? $$field_str : '';
			! empty( $$field_str_privacy ) ? $$field_str_privacy : 'public';
			if ( 1 === $i || 4 === $i ) {
				$field_str_privacy = 'public';
				if ( empty( $field_str ) ) {
					wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_empty_name_location, $msg_empty_name_location ) );
				}
			}
			xprofile_set_field_data( $i, $this->user_id, $$field_str );
			xprofile_set_field_visibility_level( $i, $this->user_id, $$field_str_privacy );
		}
		wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_profile_updated, $msg_profile_updated ) );
	}

	function rtmedia_api_process_update_avatar_request() {

		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$ec_no_file  = 130001;
		$msg_no_file = esc_html__( 'no file', 'buddypress-media' );

		$ec_invalid_image  = 130002;
		$msg_invalid_image = esc_html__( 'upload failed, check size and file type', 'buddypress-media' );

		$ec_avatar_updated  = 130003;
		$msg_avatar_updated = esc_html__( 'avatar updated', 'buddypress-media' );
		if ( empty( $_FILES['file'] ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_file, $msg_no_file ) );
		}
		$uploaded = bp_core_avatar_handle_upload( $_FILES, 'xprofile_avatar_upload_dir' );
		if ( ! $uploaded ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_invalid_image, $msg_invalid_image ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_avatar_updated, $msg_avatar_updated ) );
		}
	}

	function rtmedia_api_process_rtmedia_upload_media_request() {

		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		//Error Codes for new look
		$ec_no_file  = 140001;
		$msg_no_file = esc_html__( 'no file', 'buddypress-media' );

		$ec_invalid_file_string  = 140005;
		$msg_invalid_file_string = esc_html__( 'invalid file string', 'buddypress-media' );

		$ec_image_type_missing  = 140006;
		$msg_image_type_missing = esc_html__( 'image type missing', 'buddypress-media' );

		$ec_no_file_title  = 140002;
		$msg_no_file_title = esc_html__( 'no title', 'buddypress-media' );

		$ec_invalid_image  = 140003;
		$msg_invalid_image = esc_html__( 'upload failed, check size and file type', 'buddypress-media' );

		$ec_look_updated  = 140004;
		$msg_look_updated = esc_html__( 'media updated', 'buddypress-media' );

		$rtmedia_file = filter_input( INPUT_POST, 'rtmedia_file', FILTER_SANITIZE_STRING );
		$image_type   = filter_input( INPUT_POST, 'image_type', FILTER_SANITIZE_STRING );
		$title        = filter_input( INPUT_POST, 'title', FILTER_SANITIZE_STRING );
		$description  = filter_input( INPUT_POST, 'description', FILTER_SANITIZE_STRING );

		$updated       = false;
		$uploaded_look = false;
		if ( empty( $rtmedia_file ) && empty( $_FILES['rtmedia_file'] ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_file, $msg_no_file ) );
		}
		if ( ! empty( $rtmedia_file ) ) {
			if ( empty( $image_type ) ) {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_image_type_missing, $msg_image_type_missing ) );
			}
			if ( empty( $title ) ) {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_file_title, $msg_no_file_title ) );
			}
		}
		if ( ! empty( $_FILES['rtmedia_file'] ) ) {
			$_POST['rtmedia_upload_nonce']       = $_REQUEST['rtmedia_upload_nonce'] = wp_create_nonce( 'rtmedia_upload_nonce' );
			$_POST['rtmedia_simple_file_upload'] = $_REQUEST['rtmedia_simple_file_upload'] = 1;
			$_POST['context']                    = $_REQUEST['context'] = ! empty( $_REQUEST['context'] ) ? wp_unslash( $_REQUEST['context'] ) : 'profile';
			$_POST['context_id']                 = $_REQUEST['context_id'] = ! empty( $_REQUEST['context_id'] ) ? absint( $_REQUEST['context_id'] ) : $this->user_id;
			$_POST['mode']                       = $_REQUEST['mode'] = 'file_upload';
			$_POST['media_author']               = $_REQUEST['media_author'] = $this->user_id;
			$upload                              = new RTMediaUploadEndpoint();
			//todo refactor below function so it takes param also and use if passed else use POST request
			$uploaded_look = $upload->template_redirect();
		} else {
			//Process rtmedia_file
			$img          = $rtmedia_file;
			$str_replace  = 'data:image/' . $image_type . ';base64,';
			$img          = str_replace( $str_replace, '', $img );
			$rtmedia_file = base64_decode( $img );
			if ( ! $rtmedia_file ) {
				wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_invalid_file_string, $msg_invalid_file_string ) );
			}
			define( 'UPLOAD_DIR_LOOK', sys_get_temp_dir() . '/' );
			$tmp_name = UPLOAD_DIR_LOOK . $title;
			$file     = $tmp_name . '.' . $image_type;
			$success  = file_put_contents( $file, $rtmedia_file );
			add_filter( 'upload_dir', array( $this, 'api_new_media_upload_dir' ) );
			$new_look         = wp_upload_bits( $title . '.' . $image_type, '', $rtmedia_file );
			$new_look['type'] = 'image/' . $image_type;
			remove_filter( 'upload_dir', array( $this, 'api_new_media_upload_dir' ) );
			foreach ( $new_look as $key => $value ) {
				$new_look[0][ $key ] = $value;
				unset( $new_look[ $key ] );
			}
			//Jugaad
			if ( ! empty( $tags ) ) {
				$tags = explode( ',', $tags );
			}

			$album_id   = filter_input( INPUT_POST, 'album_id', FILTER_SANITIZE_NUMBER_INT );
			$context_id = filter_input( INPUT_POST, 'context_id', FILTER_SANITIZE_NUMBER_INT );
			$context    = filter_input( INPUT_POST, 'context', FILTER_SANITIZE_STRING );
			$privacy    = filter_input( INPUT_POST, 'privacy', FILTER_SANITIZE_STRING );
			$tags       = filter_input( INPUT_POST, 'tags', FILTER_SANITIZE_STRING );

			$uploaded['rtmedia_upload_nonce']       = wp_create_nonce( 'rtmedia_upload_nonce' );
			$uploaded['rtmedia_simple_file_upload'] = 1;
			$uploaded['context']                    = ! empty( $context ) ? $context : 'profile';
			$uploaded['context_id']                 = ! empty( $context_id ) ? $context_id : $this->user_id;
			$uploaded['mode']                       = 'file_upload';
			$uploaded['media_author']               = $this->user_id;
			$uploaded['album_id']                   = ! empty( $album_id ) ? $album_id : RTMediaAlbum::get_default();
			$uploaded['privacy']                    = ! empty( $privacy ) ? $privacy : get_rtmedia_default_privacy();
			$uploaded['title']                      = $title;
			$uploaded['description']                = $description;
			$uploaded['taxonomy']                   = array();
			$uploaded['custom_fields']              = array();
			$rtmedia                                = new RTMediaMedia();
			$rtupload                               = $rtmedia->add( $uploaded, $new_look );
			$id                                     = rtmedia_media_id( $rtupload[0] );
			if ( ! empty( $tags ) ) {
				wp_set_post_terms( $id, $tags, 'media-category', true );
			}
			$media       = $rtmedia->model->get( array( 'id' => $rtupload[0] ) );
			$rtmedia_nav = new RTMediaNav();
			if ( isset( $media ) && count( $media ) > 0 ) {
				$perma_link = get_rtmedia_permalink( $media[0]->id );
				if ( 'photo' === $media[0]->media_type ) {
					$thumb_image = rtmedia_image( 'rt_media_thumbnail', $rtupload[0], false );
				} elseif ( 'music' === $media[0]->media_type ) {
					$thumb_image = $media[0]->cover_art;
				} else {
					$thumb_image = '';
				}

				if ( 'group' === $media[0]->context ) {
					$rtmedia_nav->refresh_counts( $media[0]->context_id, array(
						'context'    => sanitize_text_field( $media[0]->context ),
						'context_id' => intval( $media[0]->context_id ),
					) );
				} else {
					$rtmedia_nav->refresh_counts( $media[0]->media_author, array(
						'context'      => 'profile',
						'media_author' => sanitize_text_field( $media[0]->media_author ),
					) );
				}
				$activity_id = $rtmedia->insert_activity( $media[0]->media_id, $media[0] );
				$rtmedia->model->update( array( 'activity_id' => $activity_id ), array( 'id' => intval( $rtupload[0] ) ) );
				//
				$same_medias = $rtmedia->model->get( array( 'activity_id' => $activity_id ) );

				$update_activity_media = array();
				foreach ( $same_medias as $a_media ) {
					$update_activity_media[] = $a_media->id;
				}
				$privacy      = 0;
				$obj_activity = new RTMediaActivity( $update_activity_media, $privacy, false );

				global $wpdb, $bp;
				$updated = $wpdb->update( $bp->activity->table_name, array(
					'type'    => 'rtmedia_update',
					'content' => $obj_activity->create_activity_html(),
				), array( 'id' => $activity_id ) );

				// if there is only single media the $updated value will be false even if the value we are passing to check is correct.
				// So we need to hardcode the $updated to true if there is only single media for same activity
				if ( 1 === count( $same_medias ) && $activity_id ) {
					$updated = true;
				}
			}
		}

		if ( $updated || $uploaded_look ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_look_updated, $msg_look_updated ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_invalid_image, $msg_invalid_image ) );
		}
	}

	function rtmedia_api_process_rtmedia_gallery_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		//Errors
		$ec_media  = 160002;
		$msg_media = esc_html__( 'media list', 'buddypress-media' );

		$ec_no_media  = 160003;
		$msg_no_media = esc_html__( 'no media found for requested media type', 'buddypress-media' );

		$ec_invalid_media_type  = 160004;
		$msg_invalid_media_type = esc_html__( 'media_type not allowed', 'buddypress-media' );

		global $rtmedia;
		$rtmediamodel = new RTMediaModel();
		//Media type to fetch
		$media_type      = $allowed_types = array_keys( $rtmedia->allowed_types );
		$media_type[]    = 'album';
		$allowed_types[] = 'album';

		$media_type_temp  = filter_input( INPUT_POST, 'media_type', FILTER_SANITIZE_STRING );
		$media_type_array = filter_input( INPUT_POST, 'media_type', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( ! empty( $media_type_temp ) || ! empty( $media_type_array ) ) {
			if ( ! is_array( $media_type_array ) ) {
				$media_type = explode( ',', $media_type_array );
			} else {
				$media_type = $media_type_temp;
			}
			//Check array for currently allowed media types
			$media_type = array_intersect( $media_type, $allowed_types );
		}
		//Args for fetching media
		$args = array(
			'media_type' => $media_type,
		);

		//global
		$global = filter_input( INPUT_POST, 'global', FILTER_SANITIZE_STRING );
		if ( isset( $global ) ) {
			if ( 'false' === $global ) {
				$args['context'] = array(
					'compare' => 'IS NOT',
					'value'   => 'NULL',
				);
			}
		}
		//context
		$context = filter_input( INPUT_POST, 'context', FILTER_SANITIZE_STRING );
		if ( isset( $context ) ) {
			$args['context'] = $context;
		}
		//context Id
		$context_id = filter_input( INPUT_POST, 'context_id', FILTER_SANITIZE_NUMBER_INT );
		if ( isset( $context_id ) ) {
			$args['context_id'] = $context_id;
		}

		//album id
		$album_id = filter_input( INPUT_POST, 'album_id', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $album_id ) ) {
			$args['album_id'] = $album_id;
		}
		//Media Author
		if ( ! is_super_admin() ) {
			$media_author         = $this->user_id;
			$args['media_author'] = $media_author;
		}
		$media_author = filter_input( INPUT_POST, 'media_author', FILTER_SANITIZE_NUMBER_INT );
		if ( ! empty( $media_author ) ) {
			if ( is_super_admin( $this->user_id ) ) {
				$args['media_author'] = (int) $media_author;
			}
		}
		$page     = filter_input( INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE );
		$per_page = filter_input( INPUT_POST, 'per_page', FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE );
		$order_by = filter_input( INPUT_POST, 'order_by', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE );

		$offset   = ! empty( $page ) ? (int) $page : 0;
		$per_page = ( isset( $per_page ) && ! is_null( $per_page ) ) ? (int) $per_page : 10;
		$order_by = ! empty( $order_by ) ? $order_by : 'media_id desc';

		$media_list   = $rtmediamodel->get( $args, $offset, $per_page, $order_by );
		$media_result = array();
		foreach ( $media_list as $media ) {
			$data = array(
				'id'           => $media->id,
				'media_title'  => $media->media_title,
				'album_id'     => $media->album_id,
				'media_type'   => $media->media_type,
				'media_author' => $media->media_author,
				'url'          => esc_url( get_rtmedia_permalink( $media->id ) ),
				'cover'        => rtmedia_image( 'rt_media_thumbnail', $media->media_id, false ),
			);
			//for album list all medias
			if ( 'album' === $media->media_type ) {
				$data['media'] = $this->rtmediajsonapifunction->rtmedia_api_album_media( $media->id );
			}
			$media_result[] = $data;
		}
		if ( ! empty( $media_result ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_media, $msg_media, $media_result ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $ec_no_media, $msg_no_media ) );
		}
	}

	function rtmedia_api_process_rtmedia_get_media_details_request() {

		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		$this->rtmediajsonapifunction->rtmedia_api_media_activity_id_missing();
		//Errors
		$ec_single_media  = 150002;
		$msg_single_media = esc_html__( 'single media', 'buddypress-media' );

		$media_id = filter_input( INPUT_POST, 'media_id', FILTER_SANITIZE_NUMBER_INT );
		$id = rtmedia_media_id( $media_id );
		if ( empty( $id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $this->ec_invalid_media_id, $this->msg_invalid_media_id ) );
		}
		if ( class_exists( 'RTMediaModel' ) ) {
			$rtmediamodel = new RTMediaModel();
			$args         = array(
				'media_id' => $id,
				'id'       => $media_id,
			);
			$media        = $rtmediamodel->get( $args );
		}
		$activity_id = ! empty( $media ) ? $media[0]->activity_id : '';
		if ( empty( $activity_id ) ) {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_invalid_media_id, $this->msg_invalid_media_id ) );
		}
		$media_single = $this->rtmediajsonapifunction->rtmedia_api_get_feed( false, $activity_id );

		if ( $media_single ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_single_media, $msg_single_media, $media_single ) );
		}
	}

	function rtmedia_api_process_logout_request() {
		$this->rtmediajsonapifunction->rtmedia_api_verfiy_token();
		//Errors
		$ec_logged_out  = 200005;
		$msg_logged_out = 'logged out';
		$rtmapilogin    = new RTMediaApiLogin();
		$updated        = $rtmapilogin->update( array( 'status' => 'FALSE' ), array( 'user_id' => $this->user_id ) );
		if ( $updated ) {
			wp_send_json( $this->rtmedia_api_response_object( 'TRUE', $ec_logged_out, $msg_logged_out ) );
		} else {
			wp_send_json( $this->rtmedia_api_response_object( 'FALSE', $this->ec_server_error, $this->msg_server_error ) );
		}

	}

	function api_new_media_upload_dir( $args ) {
		$token = filter_input( INPUT_POST, 'token', FILTER_SANITIZE_STRING );

		if ( ! empty( $args ) || ! is_array( $args ) || empty( $token ) ) {
			foreach ( $args as $key => $arg ) {
				$replacestring = 'uploads/rtMedia/users/' . $this->rtmediajsonapifunction->rtmedia_api_get_user_id_from_token( $token );
				$arg           = str_replace( 'uploads', $replacestring, $arg );
				$args[ $key ]  = $arg;
			}
			$args['error'] = false;

			return $args;
		}
	}
}
