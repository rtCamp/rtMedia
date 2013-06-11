<!--<li class="rt-media-list-item">-->
	<div class="rt-media-container rt-media-single-container">

		<?php if (have_rt_media()) : rt_album(); ?>

				<?php rt_media(); ?>

				<h2 class="rt-media-item-title">
					<a href="<?php rt_media_permalink(); ?>"
					   title="<?php rt_media_title(); ?>">
						<?php rt_media_title(); ?>
					</a>
				</h2>

				<div class="rt-media-item-content">
					<?php rt_media_content(); ?>
				</div>

				<div class="rt-media-item-actions">
					<?php rt_media_actions(); ?>
				</div>

				<?php rt_media_comments(); ?>

		<?php endif; ?>

	</div>
<!--</li>-->