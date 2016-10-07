<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see result');
$I->wantTo( 'Ensure WordPress Login Works' );


$I->amOnPage('/');


$I->fillField( 'input#bp-login-widget-user-login', 'demo' );


$I->fillField( 'input#bp-login-widget-user-pass', 'demo' );

// Submit the login form
$I->click( 'Log In' );


$I->see( "What's new, Demo?" );

$I->click('#whats-new');

$I->fillfield('#whats-new','Upload test');

$I->attachFile('input[type="file"]','hello.png');

$I->wait(20);
$I->click('input#aw-whats-new-submit');
$I->wait(2);
?>

