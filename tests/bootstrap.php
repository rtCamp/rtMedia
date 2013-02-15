<?php
// Load the test environment
// https://github.com/nb/wordpress-tests

$path = '/usr/share/nginx/www/wordpress-tests/bootstrap.php';

if (file_exists($path)) {
        $GLOBALS['wp_tests_options'] = array(
                'active_plugins' => array('/usr/share/nginx/www/WordPress-Skeleton/content/plugins/buddypress-media/loader.php')
        );
        require_once $path;
} else {
        exit("Couldn't find wordpress-tests/bootstrap.php\n");
}
