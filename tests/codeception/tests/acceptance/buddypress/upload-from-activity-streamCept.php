<?php

/**
* Scenario : Allow upload from activity stream
* Pre-requisite : In backend - Goto rtMedia settings -> Buddypress -> INTEGRATION WITH BUDDYPRESS FEATURES -> Allow upload from activity stream. This option must be selected.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('Check if the user is allowed to upload media from activity stream.');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $I->amGoingTo('Test if I am able to uplaod media from activity');
    $I->amonPage('/');

//    $I->uploadMediaFromActivity();

     $uploadmedia = new UploadMediaPage($I);
     $uploadmedia->uploadMediaFromActivity($I);



?>
