<?php
// Here you can initialize variables that will be available to your tests
<?php
// This is global bootstrap for autoloading
\Codeception\Configuration::$defaultSuiteSettings['modules']['config']['WPWebDriver'] = [
    'capabilities' => [
        'browserstack.user' => getenv( 'BROWSER_STACK_USERNAME' ),
        'browserstack.key' => getenv( 'BROWSER_STACK_ACCESS_KEY' )
    ]
];
