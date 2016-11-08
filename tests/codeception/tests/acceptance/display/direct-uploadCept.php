<?php

/**
* Scenario : To check direct media upload.
* Pre-requisite : In backend - Goto rtMedia settings -> Display -> DIRECT UPLOAD -> Enable Direct Upload. Assuming this option is on.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Logout as LogoutPage;

    $userName = 'demo';
    $password = 'demo';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to upload the media directly');

    $loginPage = new LoginPage($I);
    $loginPage->login($userName,$password);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaDirectly($userName);

?>
