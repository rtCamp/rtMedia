<?php
// Login to the demo site
$I = new AcceptanceTester($scenario);
$I->wantTo('Upload an image at profile page');
$I->amonPage('/');
$I->fillField( 'input#bp-login-widget-user-login', 'demo' );
$I->fillField( 'input#bp-login-widget-user-pass', 'demo' );
$I->click('Log In');

?>