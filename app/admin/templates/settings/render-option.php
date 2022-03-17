<?php
/**
 * Template for RTMediaFormHandler::render_option_content().
 *
 * @package rtMedia
 */

?>

<table class="form-table" <?php echo ( ! empty( $option['depends'] ) ) ? 'data-depends="' . esc_attr( $option['depends'] ) . '"' : ''; ?>>
	<tr>
		<th>
			<?php
			echo wp_kses(
				$option['title'],
				array(
					'a' => array(
						'id'     => array(),
						'href'   => array(),
						'target' => array(),
					),
				)
			);
			?>
		</th>
		<td>
			<fieldset>
				<span class="rtm-field-wrap"><?php call_user_func( $option['callback'], $option['args'] ); ?></span>
				<span class="rtm-tooltip">
					<i class="dashicons dashicons-info"></i>
					<span class="rtm-tip">
						<?php
						echo wp_kses(
							( isset( $option['args']['desc'] ) ) ? $option['args']['desc'] : 'NA',
							array(
								'a' => array(
									'id'     => array(),
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
					</span>
				</span>
			</fieldset>
		</td>
	</tr>
</table>

<?php
if ( ! empty( $option['after_content'] ) ) {
	?>
	<div class="rtm-message rtm-notice">
		<?php
		echo wp_kses(
			wpautop( $option['after_content'] ),
			array(
				'a' => array(
					'id'     => array(),
					'href'   => array(),
					'target' => array(),
				),
				'p' => array(),
			)
		);
		?>
	</div>
	<?php
}
