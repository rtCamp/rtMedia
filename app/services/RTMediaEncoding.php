<?php

/**
 * Description of BPMediaEncoding
 *
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
class RTMediaEncoding {

	protected $api_url = 'http://api.rtcamp.com/';
	protected $sandbox_testing = 0;
	protected $merchant_id = 'paypal@rtcamp.com';
	public $uploaded = array();
	public $api_key = false;
	public $stored_api_key = false;
	public $video_extensions = ',mov,m4v,m2v,avi,mpg,flv,wmv,mkv,webm,ogv,mxf,asf,vob,mts,qt,mpeg,x-msvideo';
	public $music_extensions = ',wma,ogg,wav,m4a';

	public function __construct( $no_init = false ) {
		$this->api_key        = get_site_option( 'rtmedia-encoding-api-key' );
		$this->stored_api_key = get_site_option( 'rtmedia-encoding-api-key-stored' );
		if ( $no_init ) {
			return;
		}
		if ( is_admin() && $this->api_key ) {
			add_action( 'rtmedia_before_default_admin_widgets', array( $this, 'usage_widget' ) );
		}

		add_action( 'admin_init', array( $this, 'save_api_key' ), 1 );
		if ( $this->api_key ) {
			// store api key as different db key if user disable encoding service
			if ( ! $this->stored_api_key ) {
				$this->stored_api_key = $this->api_key;
				update_site_option( 'rtmedia-encoding-api-key-stored', $this->stored_api_key );
			}
			add_filter( 'rtmedia_allowed_types', array( $this, 'allowed_types_admin_settings' ), 10, 1 );
			$usage_info = get_site_option( 'rtmedia-encoding-usage' );

			if ( $usage_info ) {
				if ( isset( $usage_info[ $this->api_key ]->status ) && $usage_info[ $this->api_key ]->status ) {
					if ( isset( $usage_info[ $this->api_key ]->remaining ) && $usage_info[ $this->api_key ]->remaining > 0 ) {
						if ( $usage_info[ $this->api_key ]->remaining < 524288000 && ! get_site_option( 'rtmedia-encoding-usage-limit-mail' ) ) {
							$this->nearing_usage_limit( $usage_info );
						} elseif ( $usage_info[ $this->api_key ]->remaining > 524288000 && get_site_option( 'rtmedia-encoding-usage-limit-mail' ) ) {
							update_site_option( 'rtmedia-encoding-usage-limit-mail', 0 );
						}
						if ( ! class_exists( 'RTMediaFFMPEG' ) && ! class_exists( 'RTMediaKaltura' ) ) {
							add_filter( 'rtmedia_after_add_media', array( $this, 'encoding' ), 10, 3 );
						}
						$blacklist = array( 'localhost', '127.0.0.1' );
						if ( ! in_array( wp_unslash( $_SERVER['HTTP_HOST'] ), $blacklist, true ) ) { // @codingStandardsIgnoreLine
							add_filter( 'rtmedia_plupload_files_filter', array( $this, 'allowed_types' ), 10, 1 );
							add_filter( 'rtmedia_allowed_types', array(
								$this,
								'allowed_types_admin_settings',
							), 10, 1 );
							add_filter( 'rtmedia_valid_type_check', array( $this, 'bypass_video_audio' ), 10, 2 );
						}
					}
				}
			}
		}

		add_action( 'init', array( $this, 'handle_callback' ), 20 );
		add_action( 'wp_ajax_rtmedia_free_encoding_subscribe', array( $this, 'free_encoding_subscribe' ) );
		add_action( 'wp_ajax_rtmedia_unsubscribe_encoding_service', array( $this, 'unsubscribe_encoding' ) );
		add_action( 'wp_ajax_rtmedia_hide_encoding_notice', array( $this, 'hide_encoding_notice' ), 1 );
		add_action( 'wp_ajax_rtmedia_enter_api_key', array( $this, 'enter_api_key' ), 1 );
		add_action( 'wp_ajax_rtmedia_disable_encoding', array( $this, 'disable_encoding' ), 1 );
		add_action( 'wp_ajax_rtmedia_enable_encoding', array( $this, 'enable_encoding' ), 1 );
		//add_action('wp_ajax_rtmedia_regenerate_thumbnails', array($this, 'rtmedia_regenerate_thumbnails'), 1);
	}

	/**
	 *
	 * @param type $media_ids
	 * @param type $file_object
	 * @param type $uploaded
	 * @param string $autoformat thumbnails for genrating thumbs only
	 */
	function encoding( $media_ids, $file_object, $uploaded, $autoformat = true ) {
		foreach ( $file_object as $key => $single ) {
			$type_arry        = explode( '.', $single['url'] );
			$type             = strtolower( $type_arry[ count( $type_arry ) - 1 ] );
			$not_allowed_type = array( 'mp3' );
			if ( preg_match( '/video|audio/i', $single['type'], $type_array ) && ! in_array( $single['type'], array( 'audio/mp3' ) ) && ! in_array( $type, $not_allowed_type ) ) {
				$options             = rtmedia_get_site_option( 'rtmedia-options' );
				$options_vedio_thumb = $options['general_videothumbs'];
				if ( '' === $options_vedio_thumb ) {
					$options_vedio_thumb = 3;
				}

				/**  fORMAT * */
				if ( 'video/mp4' === $single['type'] || 'mp4' === $type ) {
					$autoformat = 'thumbnails';
				}

				$query_args   = array(
					'url'         => urlencode( $single['url'] ),
					'callbackurl' => urlencode( trailingslashit( home_url() ) . 'index.php' ),
					'force'       => 0,
					'size'        => filesize( $single['file'] ),
					'formats'     => ( true === $autoformat ) ? ( ( 'video' === $type_array[0] ) ? 'mp4' : 'mp3' ) : $autoformat,
					'thumbs'      => $options_vedio_thumb,
					'rt_id'       => $media_ids[ $key ],
				);
				$encoding_url = $this->api_url . 'job/new/';
				$upload_url   = add_query_arg( $query_args, $encoding_url . $this->api_key );
				$upload_page = wp_remote_get( $upload_url, array( 'timeout' => 200 ) );

				if ( ! is_wp_error( $upload_page ) && ( ! isset( $upload_page['headers']['status'] ) || ( isset( $upload_page['headers']['status'] ) && ( 200 === intval( $upload_page['headers']['status'] ) ) ) ) ) {
					$upload_info = json_decode( $upload_page['body'] );
					if ( isset( $upload_info->status ) && $upload_info->status && isset( $upload_info->job_id ) && $upload_info->job_id ) {
						$job_id = $upload_info->job_id;
						update_rtmedia_meta( $media_ids[ $key ], 'rtmedia-encoding-job-id', $job_id );
						$model = new RTMediaModel();
						$model->update( array( 'cover_art' => '0' ), array( 'id' => $media_ids[ $key ] ) );
					}
				}
				$this->update_usage( $this->api_key );
			}
		}
	}

	public function bypass_video_audio( $flag, $file ) {
		if ( isset( $file['type'] ) ) {
			$fileinfo = explode( '/', $file['type'] );
			if ( in_array( $fileinfo[0], array( 'audio', 'video' ), true ) ) {
				$flag = true;
			}
		}

		return $flag;
	}

	public function is_valid_key( $key ) {
		$validate_url    = trailingslashit( $this->api_url ) . 'api/validate/' . $key;
		$validation_page = wp_remote_get( $validate_url, array( 'timeout' => 20 ) );
		if ( ! is_wp_error( $validation_page ) ) {
			$validation_info = json_decode( $validation_page['body'] );
			$status          = $validation_info->status;
		} else {
			$status = false;
		}

		return $status;
	}

	public function update_usage( $key ) {
		$usage_url  = trailingslashit( $this->api_url ) . 'api/usage/' . $key;
		$usage_page = wp_remote_get( $usage_url, array( 'timeout' => 20 ) );
		if ( ! is_wp_error( $usage_page ) ) {
			$usage_info = json_decode( $usage_page['body'] );
		} else {
			$usage_info = null;
		}
		update_site_option( 'rtmedia-encoding-usage', array( $key => $usage_info ) );

		return $usage_info;
	}

	public function nearing_usage_limit( $usage_details ) {
		$subject = esc_html__( 'rtMedia Encoding: Nearing quota limit.', 'buddypress-media' );
		$message = '<p>' . esc_html__( 'You are nearing the quota limit for your rtMedia encoding service.', 'buddypress-media' ) . '</p><p>'
		           . esc_html__( 'Following are the details:', 'buddypress-media' ) . '</p><p><strong>Used:</strong> %s</p><p><strong>'
		           . esc_html__( 'Remaining', 'buddypress-media' ) . '</strong>: %s</p><p><strong>' . esc_html__( 'Total:', 'buddypress-media' ) . '</strong> %s</p>';
		$users   = get_users( array( 'role' => 'administrator' ) );
		if ( $users ) {
			$admin_email_ids = array();
			foreach ( $users as $user ) {
				$admin_email_ids[] = $user->user_email;
			}

			add_filter( 'wp_mail_content_type', array($this,'rtmedia_mail_content_type'));
			wp_mail( $admin_email_ids, $subject, sprintf( $message, size_format( $usage_details[ $this->api_key ]->used, 2 ), size_format( $usage_details[ $this->api_key ]->remaining, 2 ), size_format( $usage_details[ $this->api_key ]->total, 2 ) ) );
		}
		update_site_option( 'rtmedia-encoding-usage-limit-mail', 1 );
	}

	public function usage_quota_over() {
		$usage_details = get_site_option( 'rtmedia-encoding-usage' );
		if ( ! $usage_details[ $this->api_key ]->remaining ) {
			$subject = esc_html__( 'rtMedia Encoding: Usage quota over.', 'buddypress-media' );
			$message = '<p>' . esc_html__( 'Your usage quota is over. Upgrade your plan' , 'buddypress-media' ) . '</p><p>' .
			           esc_html__( 'Following are the details:', 'buddypress-media' ) . '</p><p><strong>' . esc_html__( 'Used:' , 'buddypress-media' ) .
			           '</strong> %s</p><p><strong>' . esc_html__( 'Remaining' , 'buddypress-media' ) . '</strong>: %s</p><p><strong>' . esc_html__( 'Total:', 'buddypress-media' ) . '</strong> %s</p>';
			$users   = get_users( array( 'role' => 'administrator' ) );
			if ( $users ) {
				foreach ( $users as $user ) {
					$admin_email_ids[] = $user->user_email;
				}
				
				add_filter( 'wp_mail_content_type', array($this,'rtmedia_mail_content_type'));
				wp_mail( $admin_email_ids, $subject, sprintf( $message, size_format( $usage_details[ $this->api_key ]->used, 2 ), 0, size_format( $usage_details[ $this->api_key ]->total, 2 ) ) );
			}
			update_site_option( 'rtmedia-encoding-usage-limit-mail', 1 );
		}
	}

	public function rtmedia_mail_content_type() {
	    return 'text/html';
	}

	public function save_api_key() {
		if ( isset( $_GET['api_key_updated'] ) && sanitize_text_field( wp_unslash( $_GET['api_key_updated'] ) ) ) {
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'successfully_subscribed_notice' ) );
			}

			add_action( 'admin_notices', array( $this, 'successfully_subscribed_notice' ) );
		}

		$apikey = ( isset( $_GET['apikey'] ) ) ? sanitize_text_field( wp_unslash( $_GET['apikey'] ) ) : '';
		if ( isset( $_GET['apikey'] ) && is_admin() && isset( $_GET['page'] ) && ( 'rtmedia-addons' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) && $this->is_valid_key( $apikey ) ) {
			if ( $this->api_key && ! ( isset( $_GET['update'] ) && sanitize_text_field( wp_unslash( $_GET['update'] ) ) ) ) {
				$unsubscribe_url = trailingslashit( $this->api_url ) . 'api/cancel/' . $this->api_key;
				wp_remote_post( $unsubscribe_url, array(
					'timeout' => 120,
					'body'    => array( 'note' => 'Direct URL Input (API Key: ' . $apikey . ')' ),
				) );
			}

			update_site_option( 'rtmedia-encoding-api-key', $apikey );
			update_site_option( 'rtmedia-encoding-api-key-stored', $apikey );

			$usage_info  = $this->update_usage( $apikey );
			$return_page = add_query_arg( array(
				'page'            => 'rtmedia-addons',
				'api_key_updated' => $usage_info->plan->name,
			), admin_url( 'admin.php' ) );
			wp_safe_redirect( esc_url_raw( $return_page ) );

			die();
		}
	}

	public function allowed_types( $types ) {
		if ( isset( $types[0] ) && isset( $types[0]['extensions'] ) ) {
			if ( is_rtmedia_upload_video_enabled() && strpos( $this->video_extensions, $types[0]['extensions'] ) ) {
				$types[0]['extensions'] .= $this->video_extensions; //Allow all types of video file to be uploded
			}
			if ( is_rtmedia_upload_music_enabled() && strpos( $this->music_extensions, $types[0]['extensions'] ) ) {
				$types[0]['extensions'] .= $this->music_extensions; //Allow all types of music file to be uploded
			}
		}

		return $types;
	}

	public function allowed_types_admin_settings( $types ) {
		$allowed_video_string   = implode( ',', $types['video']['extn'] );
		$allowed_audio_string   = implode( ',', $types['music']['extn'] );
		$allowed_video          = explode( ',', $allowed_video_string . $this->video_extensions );
		$allowed_audio          = explode( ',', $allowed_audio_string . $this->music_extensions );
		$types['video']['extn'] = array_unique( $allowed_video );
		$types['music']['extn'] = array_unique( $allowed_audio );

		return $types;
	}

	public function successfully_subscribed_notice() {
		?>
		<div class="updated">
		<p> <?php esc_html_e( 'You have successfully subscribed for the ', 'buddypress-media' ) ?>
			<strong><?php printf( '%s', esc_html( sanitize_text_field( wp_unslash( $_GET['api_key_updated'] ) ) ) ); // @codingStandardsIgnoreLine ?></strong>
			<?php esc_html_e( ' plan', 'buddypress-media' ) ?>
		</p>
		</div><?php
	}

	public function encoding_subscription_form( $name = 'No Name', $price = '0', $force = false ) {
		if ( $this->api_key ) {
			$this->update_usage( $this->api_key );
		}
		$action      = $this->sandbox_testing ? 'https://sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$return_page = esc_url( add_query_arg( array( 'page' => 'rtmedia-addons' ), ( is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ) ) ) );

		$usage_details = get_site_option( 'rtmedia-encoding-usage' );
		if ( isset( $usage_details[ $this->api_key ]->plan->name ) && ( strtolower( $usage_details[ $this->api_key ]->plan->name ) === strtolower( $name ) ) && $usage_details[ $this->api_key ]->sub_status && ! $force ) {
			$form = '<button data-plan="' . esc_attr( $name ) . '" data-price="' . esc_attr( $price ) . '" type="submit" class="button bpm-unsubscribe">' . esc_html__( 'Unsubscribe', 'buddypress-media' ) . '</button>';
			$form .= '<div id="bpm-unsubscribe-dialog" title="Unsubscribe">
						<p>' . esc_html__( 'Just to improve our service we would like to know the reason for you to leave us.', 'buddypress-media' ) . '</p>
						<p><textarea rows="3" cols="36" id="bpm-unsubscribe-note"></textarea></p>
						</div>';
		} else {
			$form = '<form method="post" action="' . $action . '" class="paypal-button" target="_top">
					<input type="hidden" name="button" value="subscribe">
					<input type="hidden" name="item_name" value="' . esc_attr( ucfirst( $name ) ) . '">

					<input type="hidden" name="currency_code" value="USD">


					<input type="hidden" name="a3" value="' . esc_attr( $price ) . '">
					<input type="hidden" name="p3" value="1">
					<input type="hidden" name="t3" value="M">

					<input type="hidden" name="cmd" value="_xclick-subscriptions">

					<!-- Merchant ID -->
					<input type="hidden" name="business" value="' . esc_attr( $this->merchant_id ) . '">


					<input type="hidden" name="custom" value="' . esc_url( $return_page ) . '">

					<!-- Flag to no shipping -->
					<input type="hidden" name="no_shipping" value="1">

					<input type="hidden" name="notify_url" value="' . esc_url( trailingslashit( $this->api_url ) ) . 'subscribe/paypal">

					<!-- Flag to post payment return url -->
					<input type="hidden" name="return" value="' . esc_url( trailingslashit( $this->api_url ) ) . 'payment/process">


					<!-- Flag to post payment data to given return url -->
					<input type="hidden" name="rm" value="2">

					<input type="hidden" name="src" value="1">
					<input type="hidden" name="sra" value="1">

					<input type="image" src="http://www.paypal.com/en_US/i/btn/btn_subscribe_SM.gif" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!">
				</form>';
		}

		return $form;
	}

	public function usage_widget() {
		$usage_details = get_site_option( 'rtmedia-encoding-usage' );
		$content       = '';
		if ( $usage_details && isset( $usage_details[ $this->api_key ]->status ) && $usage_details[ $this->api_key ]->status ) {
			if ( isset( $usage_details[ $this->api_key ]->plan->name ) ) {
				$content .= '<p><strong>' . esc_html__( 'Current Plan', 'buddypress-media' ) . ':</strong> ' . esc_html( $usage_details[ $this->api_key ]->plan->name ) . ( $usage_details[ $this->api_key ]->sub_status ? '' : ' (' . esc_html__( 'Unsubscribed', 'buddypress-media' ) . ')' ) . '</p>';
			}
			if ( isset( $usage_details[ $this->api_key ]->used ) ) {
				$content .= '<p><span class="encoding-used"></span><strong>' . esc_html__( 'Used', 'buddypress-media' ) . ':</strong> ' . ( ( $used_size = size_format( $usage_details[ $this->api_key ]->used, 2 ) ) ? esc_html( $used_size ) : '0MB' ) . '</p>';
			}
			if ( isset( $usage_details[ $this->api_key ]->remaining ) ) {
				$content .= '<p><span class="encoding-remaining"></span><strong>' . esc_html__( 'Remaining', 'buddypress-media' ) . ':</strong> ' . ( ( $remaining_size = size_format( $usage_details[ $this->api_key ]->remaining, 2 ) ) ? esc_html( $remaining_size ) : '0MB' ) . '</p>';
			}
			if ( isset( $usage_details[ $this->api_key ]->total ) ) {
				$content .= '<p><strong>' . esc_html__( 'Total', 'buddypress-media' ) . ':</strong> ' . esc_html( size_format( $usage_details[ $this->api_key ]->total, 2 ) ) . '</p>';
			}
			$usage = new rtProgress();
			$content .= $usage->progress_ui( $usage->progress( $usage_details[ $this->api_key ]->used, $usage_details[ $this->api_key ]->total ), false );
			if ( $usage_details[ $this->api_key ]->remaining <= 0 ) {
				$content .= '<div class="error below-h2"><p>' . esc_html__( 'Your usage limit has been reached. Upgrade your plan.', 'buddypress-media' ) . '</p></div>';
			}
		} else {
			$content .= '<div class="error below-h2"><p>' . esc_html__( 'Your API key is not valid or is expired.', 'buddypress-media' ) . '</p></div>';
		}
		new RTMediaAdminWidget( 'rtmedia-encoding-usage', esc_html__( 'Encoding Usage', 'buddypress-media' ), $content );
	}

	public function encoding_service_intro() {
		?>

		<h3 class="rtm-option-title"><?php esc_html_e( 'Audio/Video encoding service', 'buddypress-media' ); ?></h3>

		<p><?php esc_html_e( 'rtMedia team has started offering an audio/video encoding service.', 'buddypress-media' ); ?></p>

		<p>
			<label for="new-api-key"><?php esc_html_e( 'Enter API KEY', 'buddypress-media' ); ?></label>
			<input id="new-api-key" type="text" name="new-api-key" value="<?php echo esc_attr( $this->stored_api_key ); ?>"
			       size="60"/>
			<input type="submit" id="api-key-submit" name="api-key-submit"
			       value="<?php echo esc_attr__( 'Save Key', 'buddypress-media' ); ?>" class="button-primary"/>
		</p>

		<p>
			<?php
			$enable_btn_style  = 'style="display:none;"';
			$disable_btn_style = 'style="display:none;"';
			if ( $this->api_key ) {
				$enable_btn_style = 'style="display:block;"';
			} else if ( $this->stored_api_key ) {
				$disable_btn_style = 'style="display:block;"';
			}
			?>
			<input type="submit" id="disable-encoding" name="disable-encoding" value="Disable Encoding"
			       class="button-secondary" <?php echo $enable_btn_style; // @codingStandardsIgnoreLine?> />
			<input type="submit" id="enable-encoding" name="enable-encoding" value="Enable Encoding"
			       class="button-secondary" <?php echo $disable_btn_style; // @codingStandardsIgnoreLine ?> />
		</p>

		<!-- Results table headers -->
		<table class="bp-media-encoding-table fixed widefat rtm-encoding-table">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Feature\Plan', 'buddypress-media' ); ?></th>
				<th><?php esc_html_e( 'Free', 'buddypress-media' ); ?></th>
				<th><?php esc_html_e( 'Silver', 'buddypress-media' ); ?></th>
				<th><?php esc_html_e( 'Gold', 'buddypress-media' ); ?></th>
				<th><?php esc_html_e( 'Platinum', 'buddypress-media' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<tr>
				<th><?php esc_html_e( 'File Size Limit', 'buddypress-media' ); ?></th>
				<td>200MB (
					<del>20MB</del>
					)
				</td>
				<td colspan="3" class="column-posts">16GB (
					<del>2GB</del>
					)
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Bandwidth (monthly)', 'buddypress-media' ); ?></th>
				<td>10GB (
					<del>1GB</del>
					)
				</td>
				<td>100GB</td>
				<td>1TB</td>
				<td>10TB</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Overage Bandwidth', 'buddypress-media' ); ?></th>
				<td><?php esc_html_e( 'Not Available', 'buddypress-media' ); ?></td>
				<td>$0.10 per GB</td>
				<td>$0.08 per GB</td>
				<td>$0.05 per GB</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Amazon S3 Support', 'buddypress-media' ); ?></th>
				<td><?php esc_html_e( 'Not Available', 'buddypress-media' ); ?></td>
				<td colspan="3" class="column-posts"><?php esc_html_e( 'Coming Soon', 'buddypress-media' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'HD Profile', 'buddypress-media' ); ?></th>
				<td><?php esc_html_e( 'Not Available', 'buddypress-media' ); ?></td>
				<td colspan="3" class="column-posts"><?php esc_html_e( 'Coming Soon', 'buddypress-media' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Webcam Recording', 'buddypress-media' ); ?></th>
				<td colspan="4" class="column-posts"><?php esc_html_e( 'Coming Soon', 'buddypress-media' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Pricing', 'buddypress-media' ); ?></th>
				<td><?php esc_html_e( 'Free', 'buddypress-media' ); ?></td>
				<td><?php esc_html_e( '$9/month', 'buddypress-media' ); ?></td>
				<td><?php esc_html_e( '$99/month', 'buddypress-media' ); ?></td>
				<td><?php esc_html_e( '$999/month', 'buddypress-media' ); ?></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<td><?php
					$usage_details = get_site_option( 'rtmedia-encoding-usage' );
				if ( isset( $usage_details[ $this->api_key ]->plan->name ) && ( strtolower( $usage_details[ $this->api_key ]->plan->name ) === 'free' ) ) {
					echo '<button disabled="disabled" type="submit" class="encoding-try-now button button-primary">' . esc_html__( 'Current Plan', 'buddypress-media' ) . '</button>';
				} else {
					?>
					<form id="encoding-try-now-form" method="get">
					<button type="submit"
							class="encoding-try-now button button-primary"><?php esc_html_e( 'Try Now', 'buddypress-media' ); ?></button>
						</form><?php }
					?>
				</td>
				<td><?php echo $this->encoding_subscription_form( 'silver', 9.0 ); // @codingStandardsIgnoreLine ?></td>
				<td><?php echo $this->encoding_subscription_form( 'gold', 99.0 ); // @codingStandardsIgnoreLine ?></td>
				<td><?php echo $this->encoding_subscription_form( 'platinum', 999.0 ); // @codingStandardsIgnoreLine ?></td>
			</tr>
			</tbody>
		</table><br/><?php
	}

	public function add_media_thumbnails( $post_id ) {
		$post_info              = get_post( $post_id );
		$post_date_string       = new DateTime( $post_info->post_date );
		$post_date              = $post_date_string->format( 'Y-m-d G:i:s' );
		$post_date_thumb_string = new DateTime( $post_info->post_date );
		$post_date_thumb        = $post_date_thumb_string->format( 'Y/m/' );
		$post_thumbs            = get_post_meta( $post_id, 'rtmedia_encode_response', true );
		$post_thumbs_array      = maybe_unserialize( $post_thumbs );
		$largest_thumb_size     = 0;
		$model                  = new RTMediaModel();
		$media                  = $model->get( array( 'media_id' => $post_id ) );
		$media_id               = $media[0]->id;
		$largest_thumb          = false;
		$upload_thumbnail_array = array();
		foreach ( $post_thumbs_array['thumbs'] as $thumbs => $thumbnail ) {
			$thumbresource            = wp_remote_get( $thumbnail );
			$thumbinfo                = pathinfo( $thumbnail );
			$temp_name                = $thumbinfo['basename'];
			$temp_name                = urldecode( $temp_name );
			$temp_name_array          = explode( '/', $temp_name );
			$temp_name                = $temp_name_array[ count( $temp_name_array ) - 1 ];
			$thumbinfo['basename']    = $temp_name;
			$thumb_upload_info        = wp_upload_bits( $thumbinfo['basename'], null, $thumbresource['body'] );
			$upload_thumbnail_array[] = $thumb_upload_info['url'];

			$current_thumb_size = @filesize( $thumb_upload_info['url'] );
			if ( $current_thumb_size >= $largest_thumb_size ) {
				$largest_thumb_size = $current_thumb_size;
				$largest_thumb      = $thumb_upload_info['url'];
				$model->update( array( 'cover_art' => $thumb_upload_info['url'] ), array( 'media_id' => $post_id ) );
			}
		}
		update_activity_after_thumb_set( $media_id );
		update_post_meta( $post_id, 'rtmedia_media_thumbnails', $upload_thumbnail_array );

		return $largest_thumb;
	}

	/**
	 * Function to handle the callback request by the FFMPEG encoding server
	 *
	 * @since 1.0
	 */
	public function handle_callback() {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		//todo: nonce required
		// @codingStandardsIgnoreStart
		if ( isset( $_REQUEST['job_id'] ) && isset( $_REQUEST['download_url'] ) ) {
			$has_thumbs = isset( $_POST['thumbs'] ) ? true : false;
			$flag       = false;
			global $wpdb;
			$model        = new RTDBModel( 'rtm_media_meta', false, 10, true );
			$meta_details = $model->get( array(
				'meta_value' => sanitize_text_field( wp_unslash( $_REQUEST['job_id'] ) ),
				'meta_key'   => 'rtmedia-encoding-job-id',
			) );
			if ( ! isset( $meta_details[0] ) ) {
				$id = intval( $_REQUEST['rt_id'] );
			} else {
				$id = $meta_details[0]->media_id;
			}
			if ( isset( $id ) && is_numeric( $id ) ) {
				$model              = new RTMediaModel();
				$media              = $model->get_media( array( 'id' => $id ), 0, 1 );
				$this->media_author = $media[0]->media_author;
				$attachment_id      = $media[0]->media_id;
				update_post_meta( $attachment_id, 'rtmedia_encode_response', $_POST );

				if ( $has_thumbs ) {
					$cover_art = $this->add_media_thumbnails( $attachment_id );
				}

				if ( isset( $_POST['format'] ) && 'thumbnails' === sanitize_text_field( wp_unslash( $_POST['format'] ) ) ) {
					die();
				}

				$this->uploaded['context']      = $media[0]->context;
				$this->uploaded['context_id']   = $media[0]->context_id;
				$this->uploaded['media_author'] = $media[0]->media_author;
				$attachemnt_post                = get_post( $attachment_id );
				$download_url                   = urldecode( urldecode( $_REQUEST['download_url'] ) );
				$new_wp_attached_file_pathinfo = pathinfo( $download_url );
				$post_mime_type                = 'mp4' === $new_wp_attached_file_pathinfo['extension'] ? 'video/mp4' : 'audio/mp3';
				try {
					$file_bits = file_get_contents( $download_url );
				} catch ( Exception $e ) {
					$flag = $e->getMessage();
				}
				if ( $file_bits ) {

					$old_attachment_file = get_attached_file( $attachment_id );
					if( function_exists( 'wp_delete_file' ) ){  // wp_delete_file is introduced in WordPress 4.2
						wp_delete_file( $old_attachment_file );
					} else {
						unlink( $old_attachment_file );
					}

					add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
					$upload_info = wp_upload_bits( $new_wp_attached_file_pathinfo['basename'], null, $file_bits );
					$wpdb->update( $wpdb->posts, array(
						'guid'           => $upload_info['url'],
						'post_mime_type' => $post_mime_type,
					), array( 'ID' => $attachment_id ) );
					$old_wp_attached_file          = get_post_meta( $attachment_id, '_wp_attached_file', true );
					$old_wp_attached_file_pathinfo = pathinfo( $old_wp_attached_file );
					update_post_meta( $attachment_id, '_wp_attached_file', str_replace( $old_wp_attached_file_pathinfo['basename'], $new_wp_attached_file_pathinfo['basename'], $old_wp_attached_file ) );

					$activity_id = $media[0]->activity_id;
					if ( $activity_id ) {
						$content          = $wpdb->get_var( $wpdb->prepare( "SELECT content FROM {$wpdb->base_prefix}bp_activity WHERE id = %d", $activity_id ) );
						$activity_content = str_replace( $attachemnt_post->guid, $upload_info['url'], $content );
						$wpdb->update( $wpdb->base_prefix . 'bp_activity', array( 'content' => $activity_content ), array( 'id' => $activity_id ) );
					}
				} else {
					$flag = esc_html__( 'Could not read file.', 'buddypress-media' );
					error_log( $flag );
				}
			} else {
				$flag = esc_html__( 'Something went wrong. The required attachment id does not exists. It must have been deleted.', 'buddypress-media' );
				error_log( $flag );
			}
			// @codingStandardsIgnoreEnd
			$this->update_usage( $this->api_key );

			if ( isset( $_SERVER['REMOTE_ADDR'] ) && ( '4.30.110.155' === $_SERVER['REMOTE_ADDR'] ) ) {
				$mail = true;
			} else {
				$mail = false;
			}

			if ( $flag && $mail ) {
				$download_link = esc_url( add_query_arg( array(
					'job_id'       => sanitize_text_field( wp_unslash( $_GET['job_id'] ) ),
					'download_url' => esc_url( $_GET['download_url'] ), // @codingStandardsIgnoreLine
				), home_url() ) );
				$subject       = esc_html__( 'rtMedia Encoding: Download Failed', 'buddypress-media' );
				$message       = '<p><a href="' . esc_url( get_edit_post_link( $attachment_id ) ) . '">' . esc_html__( 'Media', 'buddypress-media' ) . '</a> ' .
				                 esc_html__( ' was successfully encoded but there was an error while downloading:', 'buddypress-media' ) . '</p><p><code>' .
				                 esc_html( $flag ) . '</code></p><p>' . esc_html__( 'You can ', 'buddypress-media' ) . '<a href="' . esc_url( $download_link ) . '">'
								. esc_html__( 'retry the download', 'buddypress-media' ) .'</a>.</p>';
				$users         = get_users( array( 'role' => 'administrator' ) );
				if ( $users ) {
					foreach ( $users as $user ) {
						$admin_email_ids[] = $user->user_email;
					}

					add_filter( 'wp_mail_content_type', array($this,'rtmedia_mail_content_type'));
					wp_mail( $admin_email_ids, $subject, $message );
				}
				echo esc_html( $flag );
			} elseif ( $flag ) {
				echo esc_html( $flag );
			} else {
				esc_html_e( 'Done', 'buddypress-media' );
			}
			die();
		}
	}

	public function free_encoding_subscribe() {
		$email         = get_site_option( 'admin_email' );
		$usage_details = get_site_option( 'rtmedia-encoding-usage' );
		if ( isset( $usage_details[ $this->api_key ]->plan->name ) && ( 'free' === strtolower( $usage_details[ $this->api_key ]->plan->name ) ) ) {
			echo wp_json_encode( array( 'error' => 'Your free subscription is already activated.' ) );
		} else {
			$free_subscription_url = esc_url_raw( add_query_arg( array( 'email' => urlencode( $email ) ), trailingslashit( $this->api_url ) . 'api/free/' ) );
			if ( $this->api_key ) {
				$free_subscription_url = esc_url_raw( add_query_arg( array(
					'email'  => urlencode( $email ),
					'apikey' => $this->api_key,
				), $free_subscription_url ) );
			}
			$free_subscribe_page = wp_remote_get( $free_subscription_url, array( 'timeout' => 120 ) );
			if ( ! is_wp_error( $free_subscribe_page ) && ( ! isset( $free_subscribe_page['headers']['status'] ) || ( isset( $free_subscribe_page['headers']['status'] ) && ( 200 === $free_subscribe_page['headers']['status'] ) ) ) ) {
				$subscription_info = json_decode( $free_subscribe_page['body'] );
				if ( isset( $subscription_info->status ) && $subscription_info->status ) {
					echo wp_json_encode( array( 'apikey' => $subscription_info->apikey ) );
				} else {
					echo wp_json_encode( array( 'error' => $subscription_info->message ) );
				}
			} else {
				echo wp_json_encode( array( 'error' => esc_html__( 'Something went wrong please try again.', 'buddypress-media' ) ) );
			}
		}
		die();
	}

	public function hide_encoding_notice() {
		update_site_option( 'rtmedia-encoding-service-notice', true );
		update_site_option( 'rtmedia-encoding-expansion-notice', true );
		echo true;
		die();
	}

	public function unsubscribe_encoding() {
		$unsubscribe_url  = trailingslashit( $this->api_url ) . 'api/cancel/' . $this->api_key;
		$unsubscribe_page = wp_remote_post( $unsubscribe_url, array(
			'timeout' => 120,
			'body'    => array( 'note' => sanitize_text_field( wp_unslash( $_GET['note'] ) ) ), // @codingStandardsIgnoreLine
		) );
		if ( ! is_wp_error( $unsubscribe_page ) && ( ! isset( $unsubscribe_page['headers']['status'] ) || ( isset( $unsubscribe_page['headers']['status'] ) && ( 200 === $unsubscribe_page['headers']['status'] ) ) ) ) {
			$subscription_info = json_decode( $unsubscribe_page['body'] );
			if ( isset( $subscription_info->status ) && $subscription_info->status ) {
				echo wp_json_encode( array(
					'updated' => esc_html__( 'Your subscription was cancelled successfully', 'buddypress-media' ),
					'form'    => $this->encoding_subscription_form( $_GET['plan'], $_GET['price'] ), // @codingStandardsIgnoreLine
				) );
			}
		} else {
			echo wp_json_encode( array( 'error' => esc_html__( 'Something went wrong please try again.', 'buddypress-media' ) ) );
		}
		die();
	}

	public function enter_api_key() {
		if ( isset( $_GET['apikey'] ) && '' !== $_GET['apikey'] ) {
			echo wp_json_encode( array( 'apikey' => $_GET['apikey'] ) );
		} else {
			echo wp_json_encode( array( 'error' => esc_html__( 'Please enter the api key.', 'buddypress-media' ) ) );
		}
		die();
	}

	public function disable_encoding() {
		update_site_option( 'rtmedia-encoding-api-key', '' );
		esc_html_e( 'Encoding disabled successfully.', 'buddypress-media' );
		die();
	}

	function enable_encoding() {
		update_site_option( 'rtmedia-encoding-api-key', $this->stored_api_key );
		esc_html_e( 'Encoding enabled successfully.', 'buddypress-media' );
		die();
	}

	function upload_dir( $upload_dir ) {
		global $rtmedia_interaction, $rt_media_media;
		if ( isset( $this->uploaded['context'] ) && isset( $this->uploaded['context_id'] ) ) {
			if ( 'group' !== $this->uploaded['context'] ) {
				$rtmedia_upload_prefix = 'users/';
				$id                    = $this->uploaded['media_author'];
			} else {
				$rtmedia_upload_prefix = 'groups/';
				$id                    = $this->uploaded['context_id'];
			}
		} else {
			if ( 'group' !== $rtmedia_interaction->context->type ) {
				$rtmedia_upload_prefix = 'users/';
				$id                    = $this->uploaded['media_author'];
			} else {
				$rtmedia_upload_prefix = 'groups/';
				$id                    = $rtmedia_interaction->context->id;
			}
		}

		if ( ! $id ) {
			$id = $this->media_author;
		}

		$rtmedia_folder_name = apply_filters( 'rtmedia_upload_folder_name', 'rtMedia' );

		$upload_dir['path'] = trailingslashit( str_replace( $upload_dir['subdir'], '', $upload_dir['path'] ) )
		                      . $rtmedia_folder_name . '/' . $rtmedia_upload_prefix . $id . $upload_dir['subdir'];
		$upload_dir['url']  = trailingslashit( str_replace( $upload_dir['subdir'], '', $upload_dir['url'] ) )
		                      . $rtmedia_folder_name . '/' . $rtmedia_upload_prefix . $id
		                      . $upload_dir['subdir'];

		$upload_dir = apply_filters( 'rtmedia_filter_upload_dir', $upload_dir, $this->uploaded );

		return $upload_dir;
	}

	public function reencoding( $attachment, $autoformat = true ) {
		$rtmedia_model = new RTMediaModel();
		$media_array   = $rtmedia_model->get( array( 'media_id' => $attachment ) );
		$media_id      = $media_array[0]->id;
		$attached_file = get_post_meta( $attachment, '_wp_attached_file' );
		$upload_path   = trim( get_option( 'upload_path' ) );
		if ( empty( $upload_path ) || 'wp-content/uploads' === $upload_path ) {
			$dir = WP_CONTENT_DIR . '/uploads';
		} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
			// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
			$dir = path_join( ABSPATH, $upload_path );
		} else {
			$dir = $upload_path;
		}
		$file             = trailingslashit( $dir ) . $attached_file[0];
		$url              = wp_get_attachment_url( $attachment );
		$file_name_array  = explode( '/', $url );
		$file_name        = $file_name_array[ count( $file_name_array ) - 1 ];
		$file_object      = array();
		$media_type       = get_post_field( 'post_mime_type', $attachment );
		$media_type_array = explode( '/', $media_type );
		if ( 'video' === $media_type_array[0] ) {
			$file_object[] = array(
				'file' => $file,
				'url'  => $url,
				'name' => $file_name,
				'type' => $media_type,
			);
			$this->encoding( array( $media_id ), $file_object, array(), $autoformat );
		}
	}

	function rtmedia_regenerate_thumbnails() {
		$this->reencoding( intval( $_REQUEST['rtreencoding'] ) );
		die();
	}
}

if ( isset( $_REQUEST['rtreencoding'] ) ) {
	$objRTMediaEncoding = new RTMediaEncoding( true );
	$objRTMediaEncoding->reencoding( intval( $_REQUEST['rtreencoding'] ) );
}
