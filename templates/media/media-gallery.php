	<div class="rt-media-container rt-media-list-container">

		<?php if (have_rt_media()) { ?>

			<ul class="rt-media-list">

				<?php while (have_rt_media()): rt_media(); ?>

					<li class="rt-media-list-item">

						<h2 class="rt-media-item-title">
							<a href="<?php rt_media_permalink(); ?>"
					   title="<?php rt_media_title(); ?>">
								<?php rt_media_title(); ?>
							</a>
						</h2>

						<div class="rt-media-item-thumbnail">
							<?php rt_media_thumbnail(); ?>
						</div>

						<div class="rt-media-item-actions">
							<?php rt_media_actions(); ?>
						</div>

					</li>

				<?php endwhile; ?>

			</ul>

		<?php } ?>

	</div>
