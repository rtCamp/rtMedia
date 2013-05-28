<div class="rt-media-container rt-media-list-container">

		<?php if (have_rt_media()) { ?>

			<ul class="rt-media-list">

				<?php while (have_rt_media()): rt_media(); ?>

					<li class="rt-media-list-item">

						<?php rt_media_gallery_item() ?>

					</li>

				<?php endwhile; ?>

			</ul>

		<?php } ?>

	</div>
