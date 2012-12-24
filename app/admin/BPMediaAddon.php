<?php
/**
 * Description of BPMediaAddon
 *
 * @author Gagandeep Singh <gagandeep.singh@rtcamp.com>
 * @author Joshua Abenazer <joshua.abenazer@rtcamp.com>
 */
if (!class_exists('BPMediaAddon')) {

    class BPMediaAddon {

        public function __construct($args) {
            global $bp_media;

            $defaults = array(
                'title' => '',
                'img_src' => '',
                'product_link' => '',
                'desc' => '',
                'price' => '',
                'demo_link' => '',
                'buy_now' => '',
            );
            $args = wp_parse_args($args, $defaults);
            extract($args);
            echo "Joshua";
            $addon = '<a href="'. $product_link.'"  title="'.$title.'">
                <img width="240" height="184" title="'.$title.'" alt="'.$title.'" src="'.$img_src.'">
            </a>
            <h4><a href="'.$product_link.'"  title="'.$title.'">'.$title.'</a></h4>
            <div class="product_desc">
                '.$desc.'
            </div>
            <div class="product_footer">
                <span class="price alignleft"><span class="amount">'.$price.'</span></span>
                <a class="add_to_cart_button  alignright product_type_simple"  href="'.$buy_now.'">'.__('Buy Now', $bp_media->text_domain).'</a>
                <a class="alignleft product_demo_link"  href="'.$demo_link.'" title="'.$title.'">'.__('Live Demo', $bp_media->text_domain).'</a>
            </div>';
            return $addon;
        }

    }

}
    ?>
