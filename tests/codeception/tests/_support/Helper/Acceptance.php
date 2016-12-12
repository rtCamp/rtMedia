<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{

    public function uploadMediaFromActivity(){
        $I = $this;
        $I->click('#whats-new');
        $I->waitForElementVisible('.rtmedia-add-media-button',2);
        $I->click('.rtmedia-add-media-button');
        $I->pressKey('.rtmedia-add-media-button',array('command','tab'));
        $I->wait(3);
        $I->pressKey('.rtmedia-add-media-button',array('shift','command','g'));
        $I->sendKeys('/Users/javalnanda/Desktop/1.jpeg');
        $I->pressKey('.rtmedia-add-media-button',\Facebook\WebDriver\WebDriverKeys::ENTER);
        $I->wait(5);

        // $I->pressKey('#page','a'); // => olda
        // $I->pressKey('#page',array('ctrl','a'),'new'); //=> new
        // $I->pressKey('#page',array('shift','111'),'1','x'); //=> old!!!1x
        // $I->pressKey('descendant-or-self::*[ * `id='page']','u');
        // $I->pressKey('#name', array('ctrl', 'a'), \Facebook\WebDriver\WebDriverKeys::DELETE);

    }


}
