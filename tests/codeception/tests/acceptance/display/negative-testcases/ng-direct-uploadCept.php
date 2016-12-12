<?php

/**
* Scenario : Should not allow the user to upload media directly.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $userName = 'admin';
    $password = 'rtdemo@18mar2016';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the user is allowed to upload the media directly');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin($userName,$password);

    $settings = new DashboardSettingsPage($I);
    $settings->disableDirectUpload($I);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMedia($userName);

    $I->seeElement(self::$uploadMediaButton);

?>
