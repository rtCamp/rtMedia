<?php
require 'vendor/autoload.php';

class BrowserStackWebDriver extends \Codeception\Module\WPWebDriver
{
    private $bs_local;
    public function _initialize(){

         getenv('BROWSERSTACK_USER') ? ($this->config["capabilities"]["browserstack.user"] = getenv('BROWSERSTACK_USER')) : 0;
        getenv('BROWSERSTACK_ACCESS_KEY') ? ($this->config["capabilities"]["browserstack.key"] = getenv('BROWSERSTACK_ACCESS_KEY')) : 0;

        if(array_key_exists("browserstack.local", $this->config["capabilities"]) && $this->config["capabilities"]["browserstack.local"])
        {
            $bs_local_args = array("key" => $this->config["capabilities"]["browserstack.key"]);
            $this->bs_local = new BrowserStack\Local();
            $this->bs_local->start($bs_local_args);
        }
        parent::_initialize();
    }
    // HOOK: after suite
    public function _afterSuite() {
        parent::_afterSuite();
        if($this->bs_local) $this->bs_local->stop();
    }
}
