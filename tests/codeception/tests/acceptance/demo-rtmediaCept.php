<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see result');
$I->wantTo( 'Ensure WordPress Login Works' );

// Let's start on the login page
$I->amOnPage('/');

// Populate the login form's user id field
$I->fillField( 'input#bp-login-widget-user-login', 'demo' );

// Popupate the login form's password field
$I->fillField( 'input#bp-login-widget-user-pass', 'demo' );

// Submit the login form
$I->click( 'Log In' );

// Validate the successful loading of the Dashboard
$I->see( "What's new, Demo?" );

$I->click('#whats-new');

$I->fillfield('#whats-new','Upload test');

// google how to upload a file using codeception
//$I->click("//button[@id='rtmedia-add-media-button-post-update']");
$I->attachFile('input[type="file"]','hello.png');
//$I->attachFile('input[ * type="file"]',  'hello.png');
$I->wait(20);
$I->click('input#aw-whats-new-submit');
$I->wait(2);
?>

