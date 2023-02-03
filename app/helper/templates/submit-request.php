<?php
/**
 * RTMediaSupport::submit_request().
 *
 * @package rtMedia
 */

?>
<html>
<head>
	<title><?php echo esc_html( wp_strip_all_tags( $title . get_bloginfo( 'name' ) ) ); ?></title>
</head>
<body>
	<table>
		<tr>
			<td>Name</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['name'] ) ); ?></td>
		</tr>
		<tr>
			<td>Email</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['email'] ) ); ?></td>
		</tr>
		<tr>
			<td>Website</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['website'] ) ); ?></td>
		</tr>
		<tr>
			<td>Subject</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['subject'] ) ); ?></td>
		</tr>
		<tr>
			<td>Details</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['details'] ) ); ?></td>
		</tr>
		<tr>
			<td>Request ID</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['request_id'] ) ); ?></td>
		</tr>
		<tr>
			<td>Server Address</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['server_address'] ) ); ?></td>
		</tr>
		<tr>
			<td>IP Address</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['ip_address'] ) ); ?></td>
		</tr>
		<tr>
			<td>Server Type</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['server_type'] ) ); ?></td>
		</tr>
		<tr>
			<td>User Agent</td>
			<td><?php echo esc_html( wp_strip_all_tags( $form_data['user_agent'] ) ); ?></td>
		</tr>

		<?php if ( 'bug_report' === sanitize_text_field( $form_data['request_type'] ) ) { ?>
			<tr>
				<td>WordPress Admin Username</td>
				<td><?php echo esc_html( wp_strip_all_tags( $form_data['wp_admin_username'] ) ); ?></td>
			</tr>
			<tr>
				<td>WordPress Admin Password</td>
				<td><?php echo esc_html( wp_strip_all_tags( $form_data['wp_admin_pwd'] ) ); ?></td>
			</tr>
			<tr>
				<td>SSH FTP Host</td>
				<td><?php echo esc_html( wp_strip_all_tags( $form_data['ssh_ftp_host'] ) ); ?></td>
			</tr>
			<tr>
				<td>SSH FTP Username</td>
				<td><?php echo esc_html( wp_strip_all_tags( $form_data['ssh_ftp_username'] ) ); ?></td>
			</tr>
			<tr>
				<td>SSH FTP Password</td>
				<td><?php echo esc_html( wp_strip_all_tags( $form_data['ssh_ftp_pwd'] ) ); ?></td>
			</tr>
		<?php } ?>

	</table>

</body>
</html>
