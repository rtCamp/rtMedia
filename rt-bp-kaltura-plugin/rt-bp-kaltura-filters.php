<?php

/**
 * In this file you'll want to add filters to the template tag output of your component.
 * You can use any of the built in WordPress filters, and you can even create your
 * own filter functions in this file.
 */

 /**
  * Some WP filters you may want to use:
  *  - wp_filter_kses() VERY IMPORTANT see below.
  *  - wptexturize()
  *  - convert_smilies()
  *  - convert_chars()
  *  - wpautop()
  *  - stripslashes_deep()
  *  - make_clickable()
  */

/**
 * --- NOTE ----
 * It's very very important that you use the wp_filter_kses() function to filter all
 * input AND output in your plugin. This will stop users adding malicious scripts and other
 * bad things onto any page.
 */

/**
 * In all your template tags that output data, you should have an apply_filters() call, you can
 * then use those filters to automatically add the wp_filter_kses() call.
 * The third parameter "1" adds the highest priority to the filter call.
 */
 
 add_filter( 'rt_media_get_item_name', 'wp_filter_kses', 1 );

/**
 * In your save() method in 'rt-bp-kaltura-classes.php' you will have 'before save' filters on
 * values. You should use these filters to attach the wp_filter_kses() function to them.
 */

 add_filter( 'example_data_fieldname1_before_save', 'wp_filter_kses', 1 );
 add_filter( 'example_data_fieldname2_before_save', 'wp_filter_kses', 1 );

?>