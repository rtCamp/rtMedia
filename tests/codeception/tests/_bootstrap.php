<?php
// This is global bootstrap for autoloading
\Codeception\Configuration::$defaultSuiteSettings['modules']['config'] = [
    'WPWebDriver' => [
        'host' => 'hub-cloud.browserstack.com',
        'access_key' => getenv('BROWSER_STACK_ACCESS_KEY'),
    ]
];
