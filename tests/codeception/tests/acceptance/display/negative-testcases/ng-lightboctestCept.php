<?php

/**
* Scenario : To Check if the media is opening in Light Box.
*/

    use Page\Login as LoginPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Lightbox as LightboxPage;
    use Page\DashboardSettings as DashboardSettingsPage;

    $userName = 'krupa';
    $password = 'Test123';
    
    $I = new AcceptanceTester($scenario);
    $I->wantTo('To check if the lightbox is disabled');

    $loginPage = new LoginPage($I);
    $loginPage->loginAsAdmin($userName,$password);

    $settings = new DashboardSettingsPage($I);
    $settings->disableLightbox($I);

    $uploadmedia = new UploadMediaPage($I);
    $uploadmedia->uploadMediaUsingStartUploadButton($userName); //Assuming direct uplaod is disabled
    $uploadmedia->fisrtThumbnailMedia($I);

    $I->dontSeeElement(LightboxPage::$closeButton);   //The close button will only be visible if the media is opened in Lightbox

?>
