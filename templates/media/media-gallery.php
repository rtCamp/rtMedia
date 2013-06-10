<div class="rt-media-container rt-media-list-container">

		<?php if (have_rt_media()) { ?>

			<ul class="rt-media-list">

				<?php $i = 0; while (have_rt_media()) : rt_album(); ?>

					<?php echo '<br>'.++$i.'<br>'; print_r(rt_media()); ?>

				<?php endwhile; ?>

			</ul>

		<?php } ?>

</div>
