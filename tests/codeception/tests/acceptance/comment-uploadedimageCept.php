<?php 
/*  
Prerequisites:

For this test to pass. Buddypress and rtMedia should be installed and activated. BuddyPress activity page needs to be at yoursite.com/activity. rtMedia Settings > Display > Allow users to comment on uploaded media should be ON. rtMedia Settings > BuddyPress > Allow upload from activity stream should be ON.
*/
$I = new AcceptanceTester($scenario);
$I->wantTo( 'Upload an image in buddypress activity and add a comment' );
require('demo-login.php');
$I->see( "What's new, Demo?" );
$I->click('#whats-new');
$I->fillfield('#whats-new','Upload test');
$I->attachFile('input[type="file"]','hello.png');
$I->wait(5);
$I->click('Post Update');
$I->wait(10);
//$I->click('Post Update');
$I->wait(2);

// Add a comment
$I->click('.rtmedia-item-thumbnail');
$I->wait(15);
$I->click('textarea#comment_content');
$I->fillField( 'textarea#comment_content', 'This is a test comment' );
$I->click('input#rt_media_comment_submit');
$I->wait(4);

?>

