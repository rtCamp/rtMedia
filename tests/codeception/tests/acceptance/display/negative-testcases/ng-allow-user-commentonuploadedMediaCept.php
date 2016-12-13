<?php

/**
* Scenario : Should not allow the user to comment on uploaded media.
*/
    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $userName = 'krupa';
    $password = 'Test123';

    $I = new AcceptanceTester($scenario);
    $I->wantTo('User should not allowed to comment on uploaded media');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin($userName,$password);

    $settings = new DashboardSettingsPage($I);
    $settings->disableComment($I);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($userName);

    $I->reloadPage();
    $I->wait(7);

    $uploadmedia->fisrtThumbnailMedia($I);

    $I->dontSeeElement(UploadMediaPage::$commentTextArea);

    $I->reloadPage();
    $I->wait(5);

?>
