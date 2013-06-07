<div class="rt-media-container rt-media-list-container">

		<?php if (have_rt_media()) { ?>

			<ul class="rt-media-list">

				<?php while (have_rt_media()): $the_media = rt_media(); ?>

					<?php echo $the_media; ?>

				<?php endwhile; ?>

			</ul>

		<?php } ?>

</div>
