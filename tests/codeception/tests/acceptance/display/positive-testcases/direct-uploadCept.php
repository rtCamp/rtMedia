<?php

/**
* Scenario : To check direct media upload.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $userName = 'krupa';
    $password = 'Test123';
    
    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to upload the media directly');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin($userName,$password);

    $settings = new DashboardSettingsPage($I);
    $settings->enableDirectUpload($I);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaDirectly($userName);

?>
