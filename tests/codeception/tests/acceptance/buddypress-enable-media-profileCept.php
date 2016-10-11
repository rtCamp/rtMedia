<?php 

/*  
Prerequisites:

For this test to pass. Buddypress and rtMedia should be installed and activated. rtMedia Settings > BuddyPress > Enable Media in Profile should be ON. 
*/

$I = new AcceptanceTester($scenario);
$I->wantTo('Upload an image at profile page');
require('demo-login.php');
$I-> amonPage('/members/demo/media/');
// Click on upload 
$I->amGoingTo('Upload a png image');
$I-> click('#rtm_show_upload_ui');
// Check Agree to terms
$I->click('#rtmedia_upload_terms_conditions');

$I->attachFile('input[type="file"]','1.png');
$I->wait(5);
$I->click('.start-media-upload');
$I->wait(10);

?>

