<?php
// This is global bootstrap for autoloading
\Codeception\Configuration::$defaultSuiteSettings['modules']['config'] = [
    'WPWebDriver' => [
        'host' => 'hub-cloud.browserstack.com',
        'browserstack.user' => getenv( 'BROWSER_STACK_USERNAME' ),
        'browserstack.key' => getenv( 'BROWSER_STACK_ACCESS_KEY' )
    ]
];
