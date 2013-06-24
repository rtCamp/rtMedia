<div class="rt-media-container">

	<?php rt_media_uploader() ?>

	<h2><?php echo __('Media Gallery','rt-media'); ?></h2>

    <?php
			if (have_rt_media()) { ?>

			<ul class="rt-media-list">

				<?php while (have_rt_media()) : rt_media(); ?>

					<?php include ('media-gallery-item.php'); ?>

				<?php endwhile; ?>

			</ul>


			<!--  these links will be handled by backbone later
							-- get request parameters will be removed  -->
			<?php
				$display = '';
				if(rt_media_offset() != 0)
					$display = 'style="display:block;"';
				else
					$display = 'style="display:none;"';
			?>
			<a id="rtMedia-galary-prev" <?php echo $display; ?> href="<?php echo rt_media_pagination_prev_link(); ?>">Prev</a>

			<?php
				$display = '';
				if(rt_media_offset()+ rt_media_per_page_media() < rt_media_count())
					$display = 'style="display:block;"';
				else
					$display = 'style="display:none;"';
			?>
			<a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rt_media_pagination_next_link(); ?>">Next</a>

		<?php } else { ?>
			<p><?php echo __("Oops !! There's no media found for the request !!","rt-media"); ?></p>
		<?php } ?>
</div>