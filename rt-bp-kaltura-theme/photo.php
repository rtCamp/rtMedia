<?php get_header() ?>

<div class="content-header">
	<ul class="content-header-nav">
		<?php rt_media_header_tabs() ?>
	</ul>
</div>

<div id="content">
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
    <?php
        $rt_check_partner_id = rt_media_check_partner_id();
        if($rt_check_partner_id)
            rt_media_photo_library();
        else
            bp_rt_media_get_kaltura();
    ?>
</div>
<?php get_footer() ?>