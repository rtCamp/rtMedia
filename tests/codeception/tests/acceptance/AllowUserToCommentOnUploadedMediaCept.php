/*
Scenario : To Allow the user to comment on uploaded media.
Pre-requisite : In backend - Goto rtMedia settings -> Display -> SINGLE MEDIA VIEW ->
Allow user to comment on uploaded media. Assuming this option is on.
*/


<?php
        $userName = 'demo';
        $password = 'demo';

        $I = new AcceptanceTester($scenario);
        $I->wantTo('To check if the user is allwed to comment on uploaded media');

        $I->login($userName,$password); //Login to site with demo

        $I->uploadPhoto($userName); //Upload Photo

        $I->click('ul.rtm-gallery-list li:first-child'); //This will click on the fisrt child element
        $I->wait(5);
        $I->seeElement('#comment_content'); //Check if the comment form is visible
        $I->fillfield('#comment_content',"test comment using script!"); //Add you comment here
        $I->click('.rt_media_comment_submit');
        $I->wait(5);
        $I->see('test comment using script!');

        $I->reloadPage();
        $I->wait(5);
?>
