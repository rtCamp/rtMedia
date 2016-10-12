<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('Set privacy to Private in BuddyPress activity');
require('demo-login.php');
// Add some text in buddypress activity
$I->fillfield('#whats-new','Privacy test'); 
// Change Privacy to Private 
$I->selectOption('select#rtSelectPrivacy', 'Private');
// Post this comment when logged in
$I->click('Post Update');
$I->see('Privacy test');

// Logout
$I->click('Log Out');
$I->amonPage('/');
$I->dontSee('Privacy test');
$I->wait(5);


?>