<div class="rt-media-container rt-media-list-container">

		<?php if (have_rt_media()) { ?>

			<h2><?php echo __('Media Gallery','rt-media'); ?></h2>

			<ul class="rt-media-list">

				<?php while (have_rt_media()) : rt_media(); ?>

				<li>
					<a href="<?php rt_media_permalink(); ?>">
						<img src="<?php rt_media_thumbnail(); ?>">
					</a>
					<h4 class="rt-media-item-title" title="<?php rt_media_title(); ?>">
						<a href="<?php rt_media_permalink(); ?>">
							<?php rt_media_title(); ?>
						</a>
					</h4>
				</li>

				<?php endwhile; ?>

			</ul>


			<!--  these links will be handled by backbone later
					-- get request parameters will be removed  -->
			<?php if(rt_media_offset() != 0) { ?>
				<a href="?rt_media_page=<?php echo rt_media_page()-1; ?>">Prev</a>
			<?php } ?>
			<?php if(rt_media_offset()+ rt_media_per_page_media() < rt_media_count()) { ?>
				<a href="?rt_media_page=<?php echo rt_media_page()+1; ?>">Next</a>
			<?php } ?>

		<?php } ?>

</div>
