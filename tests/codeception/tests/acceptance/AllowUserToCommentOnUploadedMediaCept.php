/*
Scenario : To Allow the user to comment on uploaded media.
Pre-requisite : In backend - Goto rtMedia settings -> Disaply -> SINGLE MEDIA VIEW ->
Allow user to comment on uploaded media. This option must be on
*/

<?php

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allwed to comment on uploaded media');

    require('demo-login.php');

    $I->amonPage('/members/demo/media/6761/'); //This will redirect the user to specific single media page.
    $I->wait(5);

    $I->seeElement('.rt_media_comment_form'); //Check if the comment form is visible
    $I->fillfield('#comment_content',"This is test comment!"); //Add you comment here
    $I->click('.rt_media_comment_submit'); 
    $I->wait(5);
    $I->see('This is test comment!');

?>
