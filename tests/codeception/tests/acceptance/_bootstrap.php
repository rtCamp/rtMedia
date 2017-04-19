<?php
// Here you can initialize variables that will be available to your tests
//This is global bootstrap for autoloading
\Codeception\Configuration::$defaultSuiteSettings['modules']['config']['WPWebDriver'] = [
    'capabilities' => [
        'browserstack.key' => getenv( 'BROWSERSTACK_ACCESS_KEY' ),
        'browserstack.localIdentifier' => getenv( 'BROWSERSTACK_LOCAL_IDENTIFIER' )

        // getenv('BROWSERSTACK_USERNAME') ? ($this->config["capabilities"]["browserstack.user"] = getenv('BROWSERSTACK_USERNAME')) : 0;
        // getenv('BROWSERSTACK_ACCESS_KEY') ? ($this->config["capabilities"]["browserstack.key"] = getenv('BROWSERSTACK_ACCESS_KEY')) : 0;

    ]
];
